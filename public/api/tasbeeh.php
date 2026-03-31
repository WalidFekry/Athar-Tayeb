<?php
// Tasbeeh API Endpoint — Batch Mode

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Invalid CSRF token'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get memorial ID
$memorialId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
if (!$memorialId) {
    echo json_encode(['success' => false, 'error' => 'Invalid memorial ID'], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Parse batch counts payload ---
// expects: counts = JSON string { subhan: N, alham: N, lailaha: N, allahu: N }
$rawCounts = isset($_POST['counts']) ? trim($_POST['counts']) : null;

if (!$rawCounts) {
    echo json_encode(['success' => false, 'error' => 'No counts provided'], JSON_UNESCAPED_UNICODE);
    exit;
}

$batchCounts = json_decode($rawCounts, true);
if (!is_array($batchCounts)) {
    echo json_encode(['success' => false, 'error' => 'Invalid counts format'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Allowed fields only
$allowedFields = ['subhan', 'alham', 'lailaha', 'allahu'];

// Sanitize: keep only allowed fields with positive integer values
$sanitized = [];
$totalCount = 0;
foreach ($allowedFields as $field) {
    $val = isset($batchCounts[$field]) ? (int) $batchCounts[$field] : 0;
    $val = max(0, $val); // no negative values
    $sanitized[$field] = $val;
    $totalCount += $val;
}

// Nothing to update
if ($totalCount === 0) {
    echo json_encode(['success' => true, 'message' => 'Nothing to update'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate memorial exists
$stmt = $pdo->prepare("SELECT id FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Memorial not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

// --- Rate Limiting (per memorial per session) ---
$rateLimitKey = "tasbeeh_batch_{$memorialId}";

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['total' => 0, 'start' => time()];
}

$rateData = $_SESSION[$rateLimitKey];

// Reset window if a minute has passed
if (time() - $rateData['start'] > 60) {
    $_SESSION[$rateLimitKey] = ['total' => $totalCount, 'start' => time()];
} else {
    if ($rateData['total'] + $totalCount > TASBEEH_RATE_LIMIT) {
        $allowed = max(0, TASBEEH_RATE_LIMIT - $rateData['total']);
        if ($allowed === 0) {
            echo json_encode([
                'success' => false,
                'error' => 'تجاوزت الحد المسموح. يرجى الانتظار قليلاً.'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
        // Scale counts down proportionally to fit remaining quota
        $ratio = $allowed / $totalCount;
        foreach ($allowedFields as $field) {
            $sanitized[$field] = (int) floor($sanitized[$field] * $ratio);
        }
        $totalCount = $allowed;
    }
    $_SESSION[$rateLimitKey]['total'] += $totalCount;
}

// --- Update database in a single query ---
try {
    // Build SET clause only for fields with count > 0
    $setParts = [];
    $params = [];
    foreach ($allowedFields as $field) {
        if ($sanitized[$field] > 0) {
            $col = 'tasbeeh_' . $field;
            $setParts[] = "$col = $col + ?";
            $params[] = $sanitized[$field];
        }
    }

    if (!empty($setParts)) {
        $params[] = $memorialId;
        $sql = "UPDATE memorials SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    }

    // Fetch updated counts to return to client
    $stmt = $pdo->prepare("
        SELECT tasbeeh_subhan, tasbeeh_alham, tasbeeh_lailaha, tasbeeh_allahu
        FROM memorials
        WHERE id = ?
    ");
    $stmt->execute([$memorialId]);
    $counts = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'counts' => [
            'subhan' => (int) $counts['tasbeeh_subhan'],
            'alham' => (int) $counts['tasbeeh_alham'],
            'lailaha' => (int) $counts['tasbeeh_lailaha'],
            'allahu' => (int) $counts['tasbeeh_allahu'],
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log('Tasbeeh API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ. يرجى المحاولة مرة أخرى.'
    ], JSON_UNESCAPED_UNICODE);
}

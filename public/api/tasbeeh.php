<?php
/**
 * Tasbeeh API Endpoint
 * Increments tasbeeh counters with rate limiting
 */

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

// Get parameters
$memorialId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$field = isset($_POST['field']) ? trim($_POST['field']) : '';

// Validate field
$allowedFields = ['subhan', 'alham', 'lailaha', 'allahu'];
if (!in_array($field, $allowedFields)) {
    echo json_encode(['success' => false, 'error' => 'Invalid field'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Validate memorial exists
$stmt = $pdo->prepare("SELECT id FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'Memorial not found'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Rate limiting - per field per memorial per session
$rateLimitKey = "tasbeeh_{$memorialId}_{$field}";

if (!isset($_SESSION[$rateLimitKey])) {
    $_SESSION[$rateLimitKey] = ['count' => 0, 'start' => time()];
}

$rateData = $_SESSION[$rateLimitKey];

// Reset if minute passed
if (time() - $rateData['start'] > 60) {
    $_SESSION[$rateLimitKey] = ['count' => 1, 'start' => time()];
} else {
    // Check limit
    if ($rateData['count'] >= TASBEEH_RATE_LIMIT) {
        echo json_encode([
            'success' => false, 
            'error' => 'تجاوزت الحد المسموح. يرجى الانتظار قليلاً.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Increment
    $_SESSION[$rateLimitKey]['count']++;
}

// Update database
try {
    $columnName = 'tasbeeh_' . $field;
    $stmt = $pdo->prepare("UPDATE memorials SET $columnName = $columnName + 1 WHERE id = ?");
    $stmt->execute([$memorialId]);
    
    // Fetch updated counts
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
            'subhan' => (int)$counts['tasbeeh_subhan'],
            'alham' => (int)$counts['tasbeeh_alham'],
            'lailaha' => (int)$counts['tasbeeh_lailaha'],
            'allahu' => (int)$counts['tasbeeh_allahu']
        ]
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log('Tasbeeh API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'حدث خطأ. يرجى المحاولة مرة أخرى.'
    ], JSON_UNESCAPED_UNICODE);
}

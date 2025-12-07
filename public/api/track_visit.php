<?php
/**
 * ============================================================
 *  API Endpoint: Track Memorial Visit (with interaction checks)
 *  File: api/track_visit.php
 * 
 *  - Requires POST request (JSON)
 *  - Requires valid CSRF token
 *  - Requires real user interaction (scroll + time spent)
 *  - Rejects bots via User-Agent inspection
 *  - Prevents duplicate counting within 300 seconds (session)
 *  - Updates visits count & last_visit timestamp
 * ============================================================
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

header('Content-Type: application/json');

/* -----------------------------------------------------------
 * Validate Request Method (POST Only)
 * ----------------------------------------------------------- */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

/* -----------------------------------------------------------
 * Read JSON Input
 * ----------------------------------------------------------- */
$input = json_decode(file_get_contents('php://input'), true);

$memorialId  = (int)($input['memorial_id'] ?? 0);
$csrfToken   = $input['csrf_token'] ?? '';
$hasScrolled = (bool)($input['has_scrolled'] ?? false);
$timeSpent   = (int)($input['time_spent'] ?? 0);

/* -----------------------------------------------------------
 * CSRF Protection
 * ----------------------------------------------------------- */
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid CSRF token']));
}

/* -----------------------------------------------------------
 * Validate Memorial ID
 * ----------------------------------------------------------- */
if ($memorialId <= 0) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid memorial ID']));
}

/* -----------------------------------------------------------
 * Require Real User Interaction:
 * - Must scroll
 * - Must stay at least 3 seconds
 * ----------------------------------------------------------- */
if (!$hasScrolled || $timeSpent < 3) {
    exit(json_encode([
        'status'       => 'rejected',
        'reason'       => 'No real user interaction detected',
        'has_scrolled' => $hasScrolled,
        'time_spent'   => $timeSpent
    ]));
}

/* -----------------------------------------------------------
 * Basic Bot User-Agent Filtering
 * ----------------------------------------------------------- */
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botKeywords = [
    'bot', 'crawl', 'crawler', 'spider', 'lighthouse', 'headless',
    'phantomjs', 'curl', 'wget'
];

foreach ($botKeywords as $keyword) {
    if (stripos($userAgent, $keyword) !== false) {
        exit(json_encode([
            'status' => 'rejected',
            'reason' => 'Bot detected in User-Agent'
        ]));
    }
}

/* -----------------------------------------------------------
 * Avoid Multiple Visits Within 5 Minutes
 * ----------------------------------------------------------- */
$visitKey = 'visited_' . $memorialId;

if (isset($_SESSION[$visitKey]) && (time() - $_SESSION[$visitKey]) < 300) {
    exit(json_encode([
        'status'  => 'already_counted',
        'message' => 'Visit already recorded in this session'
    ]));
}

try {

    /* -----------------------------------------------------------
     * Update Visit Count (Only for Published Records)
     * ----------------------------------------------------------- */
    $stmt = $pdo->prepare("
        UPDATE memorials 
        SET 
            visits = visits + 1,
            last_visit = CURRENT_TIMESTAMP
        WHERE 
            id = ? AND status = 1
    ");
    $stmt->execute([$memorialId]);

    if ($stmt->rowCount() === 0) {
        throw new Exception('Memorial not found or unpublished');
    }

    /* Save session timestamp to prevent repeat counting */
    $_SESSION[$visitKey] = time();

    /* -----------------------------------------------------------
     * Success Response
     * ----------------------------------------------------------- */
    echo json_encode([
        'status'      => 'success',
        'time_spent'  => $timeSpent,
        'message'     => 'Visit tracked successfully'
    ]);

} catch (Exception $e) {

    /* Log error to server without exposing full details to user */
    error_log('Visit tracking error: ' . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'error'   => 'Failed to track visit',
        'message' => $e->getMessage()
    ]);
}
?>

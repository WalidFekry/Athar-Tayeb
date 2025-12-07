<?php
/**
 * API Endpoint: Track Memorial Page Visits (AJAX)
 * 
 * - Accepts POST JSON { memorial_id, csrf_token }
 * - Validates CSRF token
 * - Prevents repeated visits within 300 seconds (per session)
 * - Updates visit count + last visit timestamp
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/csrf.php';

header('Content-Type: application/json');

// ---------------------------------------------------------
// Validate Request Method
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['error' => 'Method not allowed']));
}

// ---------------------------------------------------------
// Parse JSON Input
// ---------------------------------------------------------
$input = json_decode(file_get_contents('php://input'), true);
$memorialId = (int) ($input['memorial_id'] ?? 0);
$csrfToken = $input['csrf_token'] ?? '';

// ---------------------------------------------------------
// CSRF Protection
// ---------------------------------------------------------
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    exit(json_encode(['error' => 'Invalid token']));
}

// ---------------------------------------------------------
// Validate Memorial ID
// ---------------------------------------------------------
if ($memorialId <= 0) {
    http_response_code(400);
    exit(json_encode(['error' => 'Invalid memorial ID']));
}

// ---------------------------------------------------------
// Prevent counting duplicate visits (within 300 seconds)
// ---------------------------------------------------------
$visitKey = 'visited_' . $memorialId;

if (isset($_SESSION[$visitKey]) && (time() - $_SESSION[$visitKey]) < 300) {
    exit(json_encode(['status' => 'already_counted']));
}

try {

    // ---------------------------------------------------------
    // Update visits only if memorial is approved (status = 1)
    // ---------------------------------------------------------
    $stmt = $pdo->prepare("
        UPDATE memorials 
        SET 
            visits = visits + 1,
            last_visit = CURRENT_TIMESTAMP
        WHERE 
            id = ? AND status = 1
    ");
    $stmt->execute([$memorialId]);

    // Save the visit timestamp to avoid duplicate counting
    $_SESSION[$visitKey] = time();

    // ---------------------------------------------------------
    // Success Response
    // ---------------------------------------------------------
    echo json_encode([
        'status' => 'success',
    ]);

} catch (PDOException $e) {

    // ---------------------------------------------------------
    // Database Error Handling
    // ---------------------------------------------------------
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>
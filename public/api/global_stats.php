<?php
/**
 * Global Statistics API Endpoint
 * Returns total tasbeeh, total memorials, and total visits
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow GET requests 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error'   => 'Method not allowed. Only GET requests are accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    global $pdo;

    // Get total tasbeeh count from published memorials
    $stmt = $pdo->query("
        SELECT 
            SUM(tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) 
        FROM memorials 
        WHERE status = 1
    ");
    $totalTasbeeh = $stmt->fetchColumn() ?: 0;

    // Get total published memorial pages
    $stmt = $pdo->query("SELECT COUNT(*) FROM memorials WHERE status = 1");
    $totalMemorials = $stmt->fetchColumn() ?: 0;

    // Get total visits from published memorials
    $stmt = $pdo->query("SELECT SUM(visits) FROM memorials WHERE status = 1");
    $totalVisits = $stmt->fetchColumn() ?: 0;

    $data = [
        'tasbeeh'   => (int) $totalTasbeeh,
        'memorials' => (int) $totalMemorials,
        'visits'    => (int) $totalVisits,
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Global Stats API DB Error: " . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'An error occurred while fetching statistics'
    ], JSON_UNESCAPED_UNICODE);

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Global Stats API Error: " . $e->getMessage());
    }
}

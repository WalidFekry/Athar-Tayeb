<?php
/**
 * Search API Endpoint
 * Returns JSON results for live search
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

// Get query parameter
$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'error' => 'Query too short'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Search in published memorials
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, death_date, image
        FROM memorials 
        WHERE status = 1 
        AND (name LIKE ? OR from_name LIKE ?)
        ORDER BY visits DESC, created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
    
    // Format results
    $formattedResults = [];
    foreach ($results as $result) {
        $formattedResults[] = [
            'id' => $result['id'],
            'name' => $result['name'],
            'death_date' => $result['death_date'] ? formatArabicDate($result['death_date']) : null,
            'image_url' => getImageUrl($result['image'], true)
        ];
    }
    
    echo json_encode([
        'success' => true,
        'results' => $formattedResults
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    error_log('Search API error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'حدث خطأ في البحث'
    ], JSON_UNESCAPED_UNICODE);
}

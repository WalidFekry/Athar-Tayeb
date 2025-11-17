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
$limit = isset($_GET['limit']) ? trim($_GET['limit']) : 10;

if (empty($query) || strlen($query) < 2) {
    echo json_encode(['success' => false, 'error' => 'Query too short'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Search in published memorials
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, from_name, death_date, image
        FROM memorials 
        WHERE status = 1 
        AND (name LIKE ? OR from_name LIKE ?)
        ORDER BY visits DESC, created_at DESC
        LIMIT $limit
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
    
    // Format results
    $formattedResults = [];
    foreach ($results as $result) {
        $formattedResults[] = [
            'id' => $result['id'],
            'name' => $result['name'],
            'from_name' => $result['from_name'],
            'death_date' => $result['death_date'] ? formatArabicDate($result['death_date']) : null,
            'image_url' => getImageUrl($result['image'], true,"/assets/images/placeholder-memorial.png"),
            'page_url' => site_url('m/' . $result['id']),
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

<?php
/**
 * Get Memorials API Endpoint
 * Returns JSON list of memorial pages with optional ordering
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    // Get ordering parameter
    $order = $_GET['order'] ?? '';
    $defaultLimit = 25; // Default limit for ordered results
    
    // Base query for approved memorials
    $baseQuery = "
        SELECT 
            id,
            name,
            from_name,
            death_date,
            created_at,
            visits,
            last_visit,
            image,
            image_status,
            (tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) as total_tasbeeh
        FROM memorials 
        WHERE status = 1 AND (image_status = 1 OR image IS NULL)
    ";
    
    // Determine ordering and limit
    if ($order === 'created_at') {
        $query = $baseQuery . " ORDER BY created_at DESC LIMIT ?";
        $stmt  = $pdo->prepare($query);
        $stmt->execute([$defaultLimit]);

    } elseif ($order === 'last_visit') {
        $query = $baseQuery . " ORDER BY last_visit DESC LIMIT ?";
        $stmt  = $pdo->prepare($query);
        $stmt->execute([$defaultLimit]);

    } else {
        $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
        $limit  = isset($_GET['limit'])  ? max(1, (int)$_GET['limit'])   : 25;

        $query = $baseQuery . " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
    }
    
    $memorials = $stmt->fetchAll();
    
    // Format the response
    $response = [];
    foreach ($memorials as $memorial) {
        $response[] = [
            'id' => (int)$memorial['id'],
            'name' => $memorial['name'],
            'from_name' => $memorial['from_name'],
            'death_date' => $memorial['death_date'],
            'created_at' => $memorial['created_at'],
            'visits' => (int)$memorial['visits'],
            'total_tasbeeh' => (int)$memorial['total_tasbeeh'],
            'image_url' => getImageUrl(
                $memorial['image'] ?? null,
                $memorial['image_status'] ?? null,
                '/assets/images/placeholder-memorial.png'
            ),
            'page_url' => site_url('m/' . $memorial['id'])
        ];
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $response,
        'count' => count($response)
    ], JSON_UNESCAPED_UNICODE);
    
} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);
    
    if (DEBUG_MODE) {
        error_log("Get Memorials API Error: " . $e->getMessage());
    }
    
} catch (Exception $e) {
    // General error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while fetching memorials'
    ], JSON_UNESCAPED_UNICODE);
    
    if (DEBUG_MODE) {
        error_log("Get Memorials API Error: " . $e->getMessage());
    }
}
?>

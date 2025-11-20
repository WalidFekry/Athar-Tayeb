<?php
/**
 * Bulk Get Memorials API Endpoint
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($data['items']) || !is_array($data['items']) || count($data['items']) === 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Items array is required and cannot be empty'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $results = [];

    $stmt = $pdo->prepare("
        SELECT 
            id,
            name,
            from_name,
            death_date,
            created_at,
            visits,
            image,
            gender,
            whatsapp,
            quote,
            image_status,
            edit_key,
            (tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) as total_tasbeeh
        FROM memorials 
        WHERE id = :id AND edit_key = :edit_key AND status = 1
        LIMIT 1
    ");

    foreach ($data['items'] as $item) {
        if (!isset($item['id']) || !isset($item['edit_key'])) {
            continue; 
        }

        $id = (int)$item['id'];
        $editKey = trim($item['edit_key']);

        $stmt->execute([
            ':id' => $id,
            ':edit_key' => $editKey
        ]);

        $memorial = $stmt->fetch();

        if ($memorial) {
            $results[] = [
                'id' => (int)$memorial['id'],
                'name' => $memorial['name'],
                'from_name' => $memorial['from_name'],
                'death_date' => $memorial['death_date'],
                'gender' => $memorial['gender'],
                'whatsapp' => $memorial['whatsapp'],
                'quote' => $memorial['quote'],
                'created_at' => $memorial['created_at'],
                'visits' => (int)$memorial['visits'],
                'total_tasbeeh' => (int)$memorial['total_tasbeeh'],
                'image_url' => getImageUrl(
                    $memorial['image'] ?? null,
                    $memorial['image_status'] ?? null,
                    '/assets/images/placeholder-memorial.png'
                ),
                'page_url' => site_url('m/' . $memorial['id']),
                'edit_key' => $memorial['edit_key'],
            ];
        }
    
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $results,
        'count' => count($results)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);
    if (DEBUG_MODE) {
        error_log("Bulk Memorials API Error: " . $e->getMessage());
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An unexpected error occurred'
    ], JSON_UNESCAPED_UNICODE);
    if (DEBUG_MODE) {
        error_log("Bulk Memorials API Error: " . $e->getMessage());
    }
}

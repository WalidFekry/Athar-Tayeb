<?php
/**
 * Delete Memorial API Endpoint
 * Accepts POST data to delete a memorial page
 * Requires: api_key, id, edit_key
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Method not allowed. Only POST requests are accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

try {
    $apiKey = $_POST['api_key'] ?? '';
    if ($apiKey !== 'DELETE_MEMORIAL') {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get and validate inputs
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $editKey = trim($_POST['edit_key'] ?? '');

    if ($id <= 0 || empty($editKey)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'المعرف (id) و مفتاح التعديل (edit_key) مطلوبان.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Fetch memorial to confirm and get image name
    $stmt = $pdo->prepare("
        SELECT id, image, edit_key 
        FROM memorials 
        WHERE id = ? 
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $memorial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$memorial) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم العثور على الصفحة التذكارية.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Verify edit key
    if (!hash_equals($memorial['edit_key'], $editKey)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'مفتاح التعديل غير صحيح.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Delete image / thumbnail / duaa card if exists
    if (!empty($memorial['image'])) {
        $imageName = $memorial['image'];

        $imagePath = UPLOAD_PATH . '/' . $imageName;
        if (is_file($imagePath)) {
            @unlink($imagePath);
        }

        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
        $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }

        $duaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $imageName;
        if (is_file($duaaImagePath)) {
            @unlink($duaaImagePath);
        }
    }

    // Delete memorial record
    $stmt = $pdo->prepare("DELETE FROM memorials WHERE id = ? AND edit_key = ?");
    $stmt->execute([$id, $editKey]);

    if ($stmt->rowCount() === 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم الحذف. يرجى المحاولة مرة أخرى.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Success response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'تم حذف الصفحة التذكارية وكل الصور المرتبطة بها بنجاح.',
        'data' => [
            'id' => $id,
            'edit_key' => $editKey
        ]
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Delete Memorial API Error (DB): " . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while deleting the memorial'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Delete Memorial API Error: " . $e->getMessage());
    }
}

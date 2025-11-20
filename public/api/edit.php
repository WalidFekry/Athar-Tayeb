<?php
/**
 * Edit Memorial API Endpoint
 * Accepts POST data to update an existing memorial page
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
    // Check API key
    $apiKey = $_POST['api_key'] ?? '';
    if ($apiKey !== 'EDIT_MEMORIAL') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Get ID and edit key
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $editKey = trim($_POST['edit_key'] ?? '');

    if ($id <= 0 || empty($editKey)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid memorial ID or edit key.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Check memorial exists and edit_key is valid
    $stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ? AND edit_key = ?");
    $stmt->execute([$id, $editKey]);
    $memorial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$memorial) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'error' => 'لم يتم العثور على الصفحة التذكارية أو مفتاح التعديل غير صحيح.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ===== Validation & input handling =====

    $name       = trim($_POST['name'] ?? '');
    $from_name  = trim($_POST['from_name'] ?? '');
    $death_day   = trim($_POST['death_day'] ?? '');
    $death_month = trim($_POST['death_month'] ?? '');
    $death_year  = trim($_POST['death_year'] ?? '');
    $gender     = trim($_POST['gender'] ?? 'male');
    $whatsapp   = trim($_POST['whatsapp'] ?? '');
    $quote      = trim($_POST['quote'] ?? '');
    // $generateDuaaImage = isset($_POST['generate_duaa_image']) ? 1 : 0;

    $death_date = $memorial['death_date'];

    if (!empty($death_year) && !empty($death_month) && !empty($death_day)) {
        $death_date = sprintf('%04d-%02d-%02d', $death_year, $death_month, $death_day);
    } elseif ($death_day === '' && $death_month === '' && $death_year === '') {
        $death_date = null;
    }

    $errors = [];

    if (!empty($from_name) && mb_strlen($from_name) > 30) {
        $errors[] = 'اسم منشئ الصفحة يجب ألا يتجاوز 30 حرف';
    }

    if (empty($name)) {
        $errors[] = 'اسم المتوفى مطلوب';
    } elseif (mb_strlen($name) > 30) {
        $errors[] = 'اسم المتوفى يجب ألا يتجاوز 30 حرف';
    }

    if (!empty($quote) && mb_strlen($quote) > 300) {
        $errors[] = 'الرسالة أو الدعاء يجب ألا تتجاوز 300 حرف';
    }

    if (!in_array($gender, ['male', 'female'])) {
        $gender = 'male';
    }

    // ===== Image handling =====
    $imageName    = $memorial['image']; 
    $imageChanged = false;

    if (empty($errors)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {

            if ($imageName) {
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

            $uploadResult = processUploadedImage($_FILES['image'], 0);
            if ($uploadResult['success']) {
                $imageName    = $uploadResult['filename'];
                $imageChanged = true;
            } else {
                $errors[] = $uploadResult['error'];
            }

            // if ($generateDuaaImage && $imageName) {
            //     require_once __DIR__ . '/../includes/generate_duaa_image.php';
            //     $imagePath = $imageName ? UPLOAD_PATH . '/' . $imageName : null;
            //     generateDuaaImage($imageName, $name, $gender, $imagePath, $death_date);
            // }  
        }
    }

    // Return validation errors if any
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ===== Get auto approval setting & determine statuses =====

    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_messages'");
    $stmt->execute();
    $autoApproveMessagesSetting = $stmt->fetchColumn();
    $autoApproveMessages        = ($autoApproveMessagesSetting == '1') ? 1 : 0;

    $messageStatus = $memorial['quote_status'];
    if ($quote !== $memorial['quote']) {
        $messageStatus = $autoApproveMessages;
    }

    $imageStatus = $memorial['image_status'];
    if ($imageChanged) {
        $imageStatus = 0;
    }

    // ===== Update in database =====

    if ($imageChanged) {
        $stmt = $pdo->prepare("
            UPDATE memorials
            SET name = ?, 
                from_name = ?, 
                image = ?, 
                death_date = ?, 
                gender = ?, 
                whatsapp = ?, 
                quote = ?, 
                quote_status = ?, 
                image_status = ?
            WHERE id = ? AND edit_key = ?
        ");

        $stmt->execute([
            $name,
            $from_name ?: null,
            $imageName,
            $death_date ?: null,
            $gender,
            $whatsapp ?: null,
            $quote ?: null,
            $messageStatus,
            $imageStatus,
            $id,
            $editKey
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE memorials
            SET name = ?, 
                from_name = ?, 
                death_date = ?, 
                gender = ?, 
                whatsapp = ?, 
                quote = ?, 
                quote_status = ?
            WHERE id = ? AND edit_key = ?
        ");

        $stmt->execute([
            $name,
            $from_name ?: null,
            $death_date ?: null,
            $gender,
            $whatsapp ?: null,
            $quote ?: null,
            $messageStatus,
            $id,
            $editKey
        ]);
    }

    $memorialData = [
        'id'           => (int) $id,
        'name'         => $name,
        'from_name'    => $from_name ?: null,
        'death_date'   => $death_date ?: null,
        'gender'       => $gender,
        'quote'        => $quote ?: null,
        'quote_status' => $messageStatus,
        'image_status' => $imageStatus,
        'image_url'    => $imageName ? getImageUrl($imageName) : null,
        'page_url'     => site_url('m/' . $id),
        'status'       => $memorial['status'] ? 'approved' : 'pending',
        'edit_key'     => $editKey,
        'updated_at'   => date('Y-m-d H:i:s')
    ];

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'تم تحديث الصفحة التذكارية بنجاح',
        'data'    => $memorialData
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Edit Memorial API Error: " . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while updating the memorial'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Edit Memorial API Error: " . $e->getMessage());
    }
}

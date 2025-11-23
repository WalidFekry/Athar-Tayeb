<?php
/**
 * Create Memorial API Endpoint
 * Accepts POST data to create a new memorial page
 * Replicates functionality from public/create.php
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
    if ($apiKey !== 'CREATE_MEMORIAL') {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Unauthorized'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validate inputs
    $name = trim($_POST['name'] ?? '');
    $from_name = trim($_POST['from_name'] ?? '');

    $death_day = trim($_POST['death_day'] ?? '');
    $death_month = trim($_POST['death_month'] ?? '');
    $death_year = trim($_POST['death_year'] ?? '');
    $death_date = '';

    if (!empty($death_year) && !empty($death_month) && !empty($death_day)) {
        $death_date = sprintf('%04d-%02d-%02d', $death_year, $death_month, $death_day);
    }

    $gender = trim($_POST['gender'] ?? 'male');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $quote = trim($_POST['quote'] ?? '');
    $generateDuaaImage = isset($_POST['generate_duaa_image']) ? $_POST['generate_duaa_image'] : 0;


    $errors = [];

    // Check rate limiting
    $ip = getUserIp();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM memorials 
        WHERE ip_address = ? 
          AND created_at >= (NOW() - INTERVAL 1 HOUR)
    ");
    $stmt->execute([$ip]);
    $countLastHour = (int) $stmt->fetchColumn();
    if ($countLastHour >= 1) {
        $errors[] = 'يمكنك إنشاء صفحة تذكارية واحدة فقط كل ساعة من هذا الجهاز. يرجى المحاولة لاحقاً.';
    }

    // Validation rules (same as original create.php)
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

    // Process image upload if no errors so far
    $imageName = null;
    if (empty($errors)) {
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = processUploadedImage($_FILES['image'], 0);
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
    }

    $duaaImageUrl = null;

if (empty($errors)) {
    if ($generateDuaaImage && $imageName) {
        require_once __DIR__ . '/../../includes/generate_duaa_image.php';
        $imagePath = $imageName ? UPLOAD_PATH . '/' . $imageName : null;

        // استدعاء الدالة وتخزين النتيجة
        $result = generateDuaaImage($imageName, $name, $gender, $imagePath, $death_date);

        if ($result['success']) {
            $duaaImageUrl = $result['url'];
        } else {
            $errors[] = 'حدث خطأ أثناء إنشاء بطاقة الدعاء';
        }
    } elseif ($generateDuaaImage && !$imageName) {
        $errors[] = 'يجب تحميل صورة تذكارية للمتوفي لإنشاء بطاقة دعاء.';
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

    // Insert memorial into database
    // Get auto approval settings
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approval'");
    $stmt->execute();
    $autoApprovalSetting = $stmt->fetchColumn();
    $autoApproval = ($autoApprovalSetting == '1') ? 1 : 0;

    // Get auto approval setting for messages
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_messages'");
    $stmt->execute();
    $autoApproveMessagesSetting = $stmt->fetchColumn();
    $autoApproveMessages = ($autoApproveMessagesSetting == '1') ? 1 : 0;

    // Generate unique edit key
    $editKey = generateEditKey();

    $stmt = $pdo->prepare("
                INSERT INTO memorials (name, from_name, image, death_date, gender, whatsapp, quote, image_status, quote_status, status, edit_key, generate_duaa_image, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, 0, ?, ?, ?, ?, ?)
            ");

    $stmt->execute([
        $name,
        $from_name ?: null,
        $imageName,
        $death_date ?: null,
        $gender,
        $whatsapp ?: null,
        $quote ?: null,
        $autoApproveMessages,
        $autoApproval,
        $editKey,
        $generateDuaaImage,
        $ip
    ]);

    $memorialId = $pdo->lastInsertId();

    // Prepare response data
    $memorialData = [
        'id' => (int) $memorialId,
        'name' => $name,
        'from_name' => $from_name ?: null,
        'death_date' => $death_date ?: null,
        'gender' => $gender,
        'quote' => $quote ?: null,
        'quote_status' => $autoApproveMessages ?: 0,
        'image_url' => $imageName ? getImageUrl($imageName) : null,
        'duaa_card_url' => $duaaImageUrl,
        'page_url' => site_url('m/' . $memorialId),
        'status' => $autoApproval ? 'approved' : 'pending',
        'edit_key' => $editKey,
        'created_at' => date('Y-m-d H:i:s')
    ];

    // Success response
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => $autoApproval ? 'تم إنشاء الصفحة التذكارية بنجاح' : 'تم إنشاء الصفحة التذكارية وهي في انتظار المراجعة',
        'data' => $memorialData
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Database error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Create Memorial API Error: " . $e->getMessage());
    }

} catch (Exception $e) {
    // General error
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while creating the memorial'
    ], JSON_UNESCAPED_UNICODE);

    if (DEBUG_MODE) {
        error_log("Create Memorial API Error: " . $e->getMessage());
    }
}
?>
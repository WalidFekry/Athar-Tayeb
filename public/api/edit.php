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
        'error'   => 'Method not allowed. Only POST requests are accepted.'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/functions.php';

/**
 * Helper: send JSON error response & exit
 */
function jsonErrorResponse(int $statusCode, string $message, array $extra = []): void
{
    http_response_code($statusCode);

    $payload = array_merge([
        'success' => false,
        'error'   => $message,
    ], $extra);

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Helper: normalize boolean-ish POST field
 */
function getPostBool(string $key, $default = 0): bool
{
    if (!isset($_POST[$key])) {
        return (bool)$default;
    }

    $value = $_POST[$key];

    // Handle common true values
    if (is_string($value)) {
        $value = strtolower(trim($value));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    return (bool)$value;
}

/**
 * Helper: delete duaa image file if exists
 */
function deleteDuaaImage(?string $imageName): void
{
    if (!$imageName) {
        return;
    }

    $duaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $imageName;
    if (is_file($duaaImagePath)) {
        @unlink($duaaImagePath);
    }
}

/**
 * Helper: delete main image + thumb + duaa image
 */
function deleteAllRelatedImages(?string $imageName): void
{
    if (!$imageName) {
        return;
    }

    // Main image
    $imagePath = UPLOAD_PATH . '/' . $imageName;
    if (is_file($imagePath)) {
        @unlink($imagePath);
    }

    // Thumbnail
    $ext       = pathinfo($imageName, PATHINFO_EXTENSION);
    $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
    if (is_file($thumbPath)) {
        @unlink($thumbPath);
    }

    // Duaa card
    deleteDuaaImage($imageName);
}

/**
 * Helper: generate duaa card
 */
function createDuaaImage(
    string $imageName,
    string $name,
    string $gender,
    ?string $imagePath,
    ?string $deathDate,
    array &$errors
): ?string {
    require_once __DIR__ . '/../../includes/generate_duaa_image.php';

    $result = generateDuaaImage($imageName, $name, $gender, $imagePath, $deathDate);

    if (!empty($result['success'])) {
        return $result['url'] ?? null;
    }

    $errors[] = 'حدث خطأ أثناء إنشاء بطاقة الدعاء';
    return null;
}

try {
    // ===== 1) Auth & memorial fetch =====

    // Check API key
    $apiKey = $_POST['api_key'] ?? '';
    if ($apiKey !== 'EDIT_MEMORIAL') {
        jsonErrorResponse(403, 'Unauthorized');
    }

    // Get ID and edit key
    $id      = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $editKey = trim($_POST['edit_key'] ?? '');

    if ($id <= 0 || empty($editKey)) {
        jsonErrorResponse(400, 'Invalid memorial ID or edit key.');
    }

    // Check memorial exists and edit_key is valid
    $stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ? AND edit_key = ?");
    $stmt->execute([$id, $editKey]);
    $memorial = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$memorial) {
        jsonErrorResponse(404, 'لم يتم العثور على الصفحة التذكارية أو مفتاح التعديل غير صحيح.');
    }

    // ===== 2) Input handling & validation =====

    $name             = trim($_POST['name'] ?? '');
    $fromName         = trim($_POST['from_name'] ?? '');
    $deathDay         = trim($_POST['death_day'] ?? '');
    $deathMonth       = trim($_POST['death_month'] ?? '');
    $deathYear        = trim($_POST['death_year'] ?? '');
    $gender           = trim($_POST['gender'] ?? 'male');
    $whatsapp         = trim($_POST['whatsapp'] ?? '');
    $quote            = trim($_POST['quote'] ?? '');
    $generateDuaaFlag = getPostBool('generate_duaa_image', $memorial['generate_duaa_image'] ?? 0);

    // Death date logic
    $deathDate = $memorial['death_date'];

    if ($deathYear !== '' && $deathMonth !== '' && $deathDay !== '') {
        $deathDate = sprintf('%04d-%02d-%02d', $deathYear, $deathMonth, $deathDay);
    } elseif ($deathDay === '' && $deathMonth === '' && $deathYear === '') {
        $deathDate = null;
    }

    $errors = [];

    // from_name length
    if (!empty($fromName) && mb_strlen($fromName) > 30) {
        $errors[] = 'اسم منشئ الصفحة يجب ألا يتجاوز 30 حرف';
    }

    // name validation
    if (empty($name)) {
        $errors[] = 'اسم المتوفى مطلوب';
    } elseif (mb_strlen($name) > 30) {
        $errors[] = 'اسم المتوفى يجب ألا يتجاوز 30 حرف';
    }

    // quote length
    if (!empty($quote) && mb_strlen($quote) > 300) {
        $errors[] = 'الرسالة أو الدعاء يجب ألا تتجاوز 300 حرف';
    }

    // gender normalization
    if (!in_array($gender, ['male', 'female'], true)) {
        $gender = 'male';
    }

    // ===== 3) Duaa image handling (existing image) =====

    $imageName   = $memorial['image'];
    $duaaImageUrl = null;

    if ($imageName) {
        if (!$generateDuaaFlag) {
            // User turned OFF duaa card → delete existing one
            deleteDuaaImage($imageName);
        } else {
            // User wants duaa card (re-)generated based on current name/gender/date
            $imagePath   = UPLOAD_PATH . '/' . $imageName;
            $duaaImageUrl = createDuaaImage($imageName, $name, $gender, $imagePath, $deathDate, $errors);
        }
    }

    // ===== 4) Uploaded image handling =====

    $imageChanged = false;

    if (empty($errors)) {
        $hasNewImage = isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($hasNewImage) {
            // Remove old image + thumb + duaa
            deleteAllRelatedImages($imageName);

            // Process new upload
            $uploadResult = processUploadedImage($_FILES['image'], 0);

            if (!empty($uploadResult['success'])) {
                $imageName   = $uploadResult['filename'];
                $imageChanged = true;
            } else {
                $errors[] = $uploadResult['error'] ?? 'خطأ في رفع الصورة';
            }

            // Generate duaa card for new image if requested
            if (empty($errors)) {
                if ($generateDuaaFlag && $imageName) {
                    $imagePath    = UPLOAD_PATH . '/' . $imageName;
                    $duaaImageUrl = createDuaaImage($imageName, $name, $gender, $imagePath, $deathDate, $errors);
                } elseif ($generateDuaaFlag && !$imageName) {
                    $errors[] = 'يجب تحميل صورة تذكارية للمتوفي لإنشاء بطاقة دعاء.';
                }
            }
        }
    }

    // Return validation errors if any
    if (!empty($errors)) {
        jsonErrorResponse(400, 'Validation error', ['errors' => $errors]);
    }

    // ===== 5) Auto approval settings & statuses =====

    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_messages'");
    $stmt->execute();
    $autoApproveMessagesSetting = $stmt->fetchColumn();
    $autoApproveMessages        = ($autoApproveMessagesSetting == '1') ? 1 : 0;

    // Message status: reset if changed
    $messageStatus = $memorial['quote_status'];
    if ($quote !== $memorial['quote']) {
        $messageStatus = $autoApproveMessages;
    }

    // Image status: reset if changed
    $imageStatus = $memorial['image_status'];
    if ($imageChanged) {
        $imageStatus = 0;
    }

    // ===== 6) Update in database =====

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
                image_status = ?,
                generate_duaa_image = ?, 
                updated_at = NOW()
            WHERE id = ? AND edit_key = ?
        ");

        $stmt->execute([
            $name,
            $fromName ?: null,
            $imageName,
            $deathDate ?: null,
            $gender,
            $whatsapp ?: null,
            $quote ?: null,
            $messageStatus,
            $imageStatus,
            $generateDuaaFlag ? 1 : 0,
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
                quote_status = ?, 
                generate_duaa_image = ?,
                updated_at = NOW()
            WHERE id = ? AND edit_key = ?
        ");

        $stmt->execute([
            $name,
            $fromName ?: null,
            $deathDate ?: null,
            $gender,
            $whatsapp ?: null,
            $quote ?: null,
            $messageStatus,
            $generateDuaaFlag ? 1 : 0,
            $id,
            $editKey
        ]);
    }

    // ===== 7) Response payload =====

    $memorialData = [
        'id'                 => (int)$id,
        'name'               => $name,
        'from_name'          => $fromName ?: null,
        'death_date'         => $deathDate ?: null,
        'gender'             => $gender,
        'quote'              => $quote ?: null,
        'quote_status'       => $messageStatus,
        'image_status'       => $imageStatus,
        'image_url'          => $imageName ? getImageUrl($imageName) : null,
        'duaa_card_url'      => $duaaImageUrl,
        'generate_duaa_image'=> $generateDuaaFlag,
        'page_url'           => site_url('m/' . $id),
        'status'             => $memorial['status'] ? 'approved' : 'pending',
        'edit_key'           => $editKey,
        'updated_at'         => date('Y-m-d H:i:s')
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
        'error'   => 'Database error occurred'
    ], JSON_UNESCAPED_UNICODE);

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Edit Memorial API Error (DB): " . $e->getMessage());
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'An error occurred while updating the memorial'
    ], JSON_UNESCAPED_UNICODE);

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
        error_log("Edit Memorial API Error: " . $e->getMessage());
    }
}

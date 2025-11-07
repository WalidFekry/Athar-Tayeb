<?php
/**
 * Create Memorial Page
 * Form to create a new memorial page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    // Rate limiting
    if (!checkRateLimit('create_memorial', CREATE_RATE_LIMIT, 3600)) {
        $errors[] = 'لقد تجاوزت الحد المسموح من الطلبات. يرجى المحاولة لاحقاً.';
    } else {
        // Validate inputs
        $name = trim($_POST['name'] ?? '');
        $from_name = trim($_POST['from_name'] ?? '');
        $death_date = trim($_POST['death_date'] ?? '');
        $gender = trim($_POST['gender'] ?? 'male');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $quote = trim($_POST['quote'] ?? '');
        
        if (empty($name)) {
            $errors[] = 'اسم المتوفى مطلوب';
        }
        
        if (!in_array($gender, ['male', 'female'])) {
            $gender = 'male';
        }
        
        // Process image upload
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = processUploadedImage($_FILES['image'], 0);
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
        
        // If no errors, insert into database
        if (empty($errors)) {
            try {
                // Get auto approval setting
                $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approval'");
                $stmt->execute();
                $autoApprovalSetting = $stmt->fetchColumn();
                $autoApproval = ($autoApprovalSetting == '1') ? 1 : 0;
                
                // Insert memorial
                $stmt = $pdo->prepare("
                    INSERT INTO memorials (name, from_name, image, death_date, gender, whatsapp, quote, image_status, quote_status, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, ?)
                ");
                
                $stmt->execute([
                    $name,
                    $from_name ?: null,
                    $imageName,
                    $death_date ?: null,
                    $gender,
                    $whatsapp ?: null,
                    $quote ?: null,
                    $autoApproval
                ]);
                
                $memorialId = $pdo->lastInsertId();
                
                if($autoApproval) {
                    // Redirect to success page
                redirect(BASE_URL . '/success.php?id=' . $memorialId);
                } else {
                    // Redirect to unpublished page
                redirect(BASE_URL . '/unpublished.php?id=' . $memorialId);
                }
                
                
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    $errors[] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
                }
            }
        }
    }
}

// Page metadata
$pageTitle = 'أنشئ صفحة تذكارية — ' . SITE_NAME;
$pageDescription = 'أنشئ صفحة تذكارية لمن تحب. صفحة دائمة للدعاء والذكر والقرآن.';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Page Header -->
            <div class="text-center mb-5">
                <h1>🌿 أنشئ صفحة تذكارية</h1>
                <p class="lead text-muted">
                    صفحتك ستبقى دائماً، والأجر يستمر بإذن الله
                </p>
            </div>
            
            <!-- Info Alert -->
            <div class="alert alert-info">
                <h5 class="alert-heading">⚠️ يُرجى العلم</h5>
                <p class="mb-0">
                    الصور والعبارات المضافة تخضع للمراجعة قبل النشر للتأكد من مطابقتها للمعايير الشرعية.
                    ستتمكن من مشاركة الرابط فوراً، لكن الصورة والرسالة ستظهر بعد الموافقة عليها.
                </p>
            </div>
            
            <!-- Errors Display -->
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading">حدثت أخطاء:</h5>
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?= e($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <!-- Create Form -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" enctype="multipart/form-data" data-validate>
                        <?php csrfField(); ?>
                        
                        <!-- From Name -->
                        <div class="mb-4">
                            <label for="from_name" class="form-label">
                                إهداء من (اختياري)
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="from_name" 
                                name="from_name"
                                placeholder="مثال: عائلة السيد"
                                value="<?= e($_POST['from_name'] ?? '') ?>"
                            >
                            <small class="form-text text-muted">
                                يمكنك كتابة اسمك أو اسم العائلة
                            </small>
                        </div>
                        
                        <!-- Name (Required) -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                اسم المتوفى <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                class="form-control" 
                                id="name" 
                                name="name"
                                placeholder="الاسم الكامل"
                                required
                                value="<?= e($_POST['name'] ?? '') ?>"
                            >
                        </div>
                        
                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label for="imageInput" class="form-label">
                                رفع صورة المتوفى (اختياري)
                            </label>
                            <input 
                                type="file" 
                                class="form-control" 
                                id="imageInput" 
                                name="image"
                                accept=".jpg,.jpeg,.png"
                            >
                            <small class="form-text text-muted">
                                الحد الأقصى: 2 ميجابايت | الصيغ المسموحة: JPG, PNG
                            </small>
                            <div id="imagePreview" class="mt-3 text-center"></div>
                        </div>
                        
                        <!-- Death Date -->
                        <div class="mb-4">
                            <label for="death_date" class="form-label">
                                يوم الذكرى (تاريخ الوفاة) - اختياري
                            </label>
                            <input 
                                type="date" 
                                class="form-control" 
                                id="death_date" 
                                name="death_date"
                                value="<?= e($_POST['death_date'] ?? '') ?>"
                            >
                        </div>
                        
                        <!-- Gender -->
                        <div class="mb-4">
                            <label for="gender" class="form-label">
                                نوع المتوفى
                            </label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="male" <?= ($_POST['gender'] ?? 'male') === 'male' ? 'selected' : '' ?>>
                                    ذكر
                                </option>
                                <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>
                                    أنثى
                                </option>
                            </select>
                            <small class="form-text text-muted">
                                لتخصيص الأدعية والضمائر بشكل صحيح
                            </small>
                        </div>
                        
                        <!-- WhatsApp -->
                        <div class="mb-4">
                            <label for="whatsapp" class="form-label">
                                رقم واتساب للتواصل (اختياري)
                            </label>
                            <input 
                                type="tel" 
                                class="form-control" 
                                id="whatsapp" 
                                name="whatsapp"
                                placeholder="+20 123 456 7890"
                                value="<?= e($_POST['whatsapp'] ?? '') ?>"
                            >
                            <small class="form-text text-muted">
                                سيظهر للزوار للتواصل معك
                            </small>
                        </div>
                        
                        <!-- Quote/Message -->
                        <div class="mb-4">
                            <label for="quote" class="form-label">
                                اقتباس أو رسالة قصيرة (اختياري)
                            </label>
                            <textarea 
                                class="form-control" 
                                id="quote" 
                                name="quote"
                                rows="4"
                                placeholder="كلمات جميلة عن الفقيد، أو دعاء خاص..."
                            ><?= e($_POST['quote'] ?? '') ?></textarea>
                            <small class="form-text text-muted">
                                ستخضع للمراجعة قبل النشر
                            </small>
                        </div>
                        
                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                ✨ إنشاء الصفحة التذكارية
                            </button>
                        </div>
                        
                    </form>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="mt-4 text-center">
                <p class="text-muted">
                    بإنشائك للصفحة، فإنك توافق على أن المحتوى المقدم يتوافق مع الشريعة الإسلامية
                </p>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

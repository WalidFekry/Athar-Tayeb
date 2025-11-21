<?php
/**
 * Edit Memorial Page
 * Allows users to edit their memorial using the edit key
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$errors = [];
$success = false;
$memorial = null;
$editKey = isset($_GET['key']) ? trim($_GET['key']) : '';

// Rate limiting for edit page access
if (!checkRateLimit('edit_access', EDIT_RATE_LIMIT, 3600)) {
    $errors[] = 'تم تجاوز الحد المسموح من المحاولات. يرجى المحاولة بعد ساعة.';
}

// Validate edit key and fetch memorial
if (empty($errors) && !empty($editKey)) {
    // Validate edit key format first
    if (!isValidEditKeyFormat($editKey)) {
        $errors[] = 'رابط التعديل غير صحيح أو منتهي الصلاحية.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM memorials WHERE edit_key = ?");
            $stmt->execute([$editKey]);
            $memorial = $stmt->fetch();
            
            if (!$memorial) {
                $errors[] = 'رابط التعديل غير صحيح أو منتهي الصلاحية.';
            }
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                $errors[] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
            } else {
                $errors[] = 'حدث خطأ في النظام. يرجى المحاولة لاحقاً.';
            }
        }
    }
} elseif (empty($editKey)) {
    $errors[] = 'رابط التعديل مطلوب.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $memorial && empty($errors)) {
    checkCSRF();

    // Handle delete action
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        if (isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === 'yes') {
            try {
                // Delete image file if exists
                if ($memorial['image']) {
                    $imagePath = UPLOAD_PATH . '/' . $memorial['image'];
                    if (file_exists($imagePath)) {
                        unlink($imagePath);
                    }
                    
                    // Delete thumbnail if exists
                    $ext = pathinfo($memorial['image'], PATHINFO_EXTENSION);
                    $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                    }
                    
                    // Delete Duaa card if exists
                    $duaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $memorial['image'];
                    if (file_exists($duaaImagePath)) {
                        unlink($duaaImagePath);
                    }
                }
                
                // Delete memorial from database
                $stmt = $pdo->prepare("DELETE FROM memorials WHERE id = ?");
                $stmt->execute([$memorial['id']]);
    
                redirect(site_url('deleted'));
                
            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    $errors[] = 'خطأ في حذف الصفحة: ' . $e->getMessage();
                } else {
                    $errors[] = 'حدث خطأ أثناء حذف الصفحة. يرجى المحاولة لاحقاً.';
                }
            }
        } else {
            $errors[] = 'يجب تأكيد الحذف.';
        }
    } else {
        // Handle edit action
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

        // Validation
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

        // Process image upload if provided
        $newImageName = null;
        $imageChanged = false;
        if (empty($errors) && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = processUploadedImage($_FILES['image'], $memorial['id']);
            if ($uploadResult['success']) {
                $newImageName = $uploadResult['filename'];
                $imageChanged = true;
                
                // Delete old image
                if ($memorial['image']) {
                    $oldImagePath = UPLOAD_PATH . '/' . $memorial['image'];
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                    
                    // Delete old thumbnail
                    $ext = pathinfo($memorial['image'], PATHINFO_EXTENSION);
                    $oldThumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $oldImagePath);
                    if (file_exists($oldThumbPath)) {
                        unlink($oldThumbPath);
                    }
                    
                    // Delete old Duaa card if exists
                    $oldDuaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $memorial['image'];
                    if (file_exists($oldDuaaImagePath)) {
                        unlink($oldDuaaImagePath);
                    }
                }
            } else {
                $errors[] = $uploadResult['error'];
            }
        }

        // Update memorial if no errors
        if (empty($errors)) {
            try {
                // Get auto approval settings
                $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approve_messages'");
                $stmt->execute();
                $autoApproveMessagesSetting = $stmt->fetchColumn();
                $autoApproveMessages = ($autoApproveMessagesSetting == '1') ? 1 : 0;

                // Determine message status
                $messageStatus = $memorial['quote_status'];
                if ($quote !== $memorial['quote']) {
                    $messageStatus = $autoApproveMessages;
                }

                // Determine image status
                $imageStatus = $memorial['image_status'];
                if ($imageChanged) {
                    $imageStatus = 0; // Always require approval for new images
                }

                // Update query
                if ($imageChanged) {
                    $stmt = $pdo->prepare("
                        UPDATE memorials 
                        SET name = ?, from_name = ?, image = ?, death_date = ?, gender = ?, 
                            whatsapp = ?, quote = ?, quote_status = ?, image_status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name,
                        $from_name ?: null,
                        $newImageName,
                        $death_date ?: null,
                        $gender,
                        $whatsapp ?: null,
                        $quote ?: null,
                        $messageStatus,
                        $imageStatus,
                        $memorial['id']
                    ]);
                } else {
                    $stmt = $pdo->prepare("
                        UPDATE memorials 
                        SET name = ?, from_name = ?, death_date = ?, gender = ?, 
                            whatsapp = ?, quote = ?, quote_status = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $name,
                        $from_name ?: null,
                        $death_date ?: null,
                        $gender,
                        $whatsapp ?: null,
                        $quote ?: null,
                        $messageStatus,
                        $memorial['id']
                    ]);
                }
                
                // Refresh memorial data
                $stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
                $stmt->execute([$memorial['id']]);
                $memorial = $stmt->fetch();

                $success = true;

            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    $errors[] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
                } else {
                    $errors[] = 'حدث خطأ أثناء التحديث. يرجى المحاولة لاحقاً.';
                }
            }
        }
    }
}

// Page metadata
$pageTitle = 'تعديل الصفحة التذكارية — ' . SITE_NAME;
$pageDescription = 'تعديل أو حذف الصفحة التذكارية';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <?php if (!$memorial && !empty($errors)): ?>
            <!-- Invalid Link Page -->
            <div class="text-center mb-5">
                <div class="display-1 mb-3">❌</div>
                <h1 class="text-danger">رابط غير صحيح</h1>
                <p class="lead">
                    رابط التعديل غير صحيح أو منتهي الصلاحية
                </p>
            </div>
            
            <div class="alert alert-danger">
                <h5 class="alert-heading">لا يمكن الوصول للصفحة</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <div class="text-center">
                <a href="<?= site_url('') ?>" class="btn btn-primary">
                    🏠 العودة للرئيسية
                </a>
            </div>

            <?php else: ?>
            <!-- Edit Form -->
            <header class="text-center mb-5">
                <h1>✏️ تعديل الصفحة التذكارية</h1>
                <p class="lead text-muted">
                    تعديل بيانات الصفحة التذكارية لـ <strong><?= e($memorial['name']) ?></strong>
                </p>
            </header>

            <!-- Success Message -->
            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <h5 class="alert-heading">✅ تم التحديث بنجاح!</h5>
                <p class="mb-0">تم حفظ التغييرات على الصفحة التذكارية.</p>
            </div>
            <?php endif; ?>

            <!-- Errors Display -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <h5 class="alert-heading">حدثت أخطاء:</h5>
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                    <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Memorial Link -->
            <div class="alert alert-info mb-4">
                <h6 class="alert-heading">🔗 رابط الصفحة التذكارية</h6>
                <p class="mb-2">
                    <a href="<?= site_url('m/' . $memorial['id']) ?>" target="_blank" class="fw-bold">
                        <?= site_url('m/' . $memorial['id']) ?>
                    </a>
                </p>
                <small class="text-muted">يمكنك مشاركة هذا الرابط مع الآخرين</small>
            </div>

            <!-- Edit Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">📝 تعديل البيانات</h5>
                    
                    <form method="POST" enctype="multipart/form-data" data-validate>
                        <?php csrfField(); ?>

                        <!-- From Name -->
                        <div class="mb-4">
                            <label for="from_name" class="form-label">
                                اسم منشئ الصفحة - اختياري
                            </label>
                            <input type="text" class="form-control" id="from_name" name="from_name"
                                placeholder="مثال: عائلة الإمبابي" maxlength="31"
                                value="<?= e($memorial['from_name'] ?? '') ?>">
                        </div>

                        <!-- Name (Required) -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                اسم المتوفى <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="الاسم الكامل"
                                required maxlength="31" value="<?= e($memorial['name']) ?>">
                        </div>

                        <!-- Current Image Display -->
                        <?php if ($memorial['image']): ?>
                        <div class="mb-4">
                            <label class="form-label">الصورة الحالية</label>
                            <div class="text-center">
                                <img src="<?= getImageUrl($memorial['image'], true) ?>" 
                                     alt="<?= e($memorial['name']) ?>" 
                                     class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                <div class="mt-2">
                                    <span class="badge <?= $memorial['image_status'] ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $memorial['image_status'] ? 'معتمدة' : 'قيد المراجعة' ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label for="imageInput" class="form-label">
                                <?= $memorial['image'] ? 'تغيير الصورة - اختياري' : 'صورة المتوفى - اختياري' ?>
                            </label>
                            <input type="file" class="form-control" id="imageInput" name="image"
                                accept=".jpg,.jpeg,.png">
                            <small class="form-text text-muted">
                                الحد الأقصى: 2 ميجابايت | الصيغ المسموحة: JPG, PNG
                                <?php if ($memorial['image']): ?>
                                <br><strong>ملاحظة:</strong> رفع صورة جديدة سيحذف الصورة الحالية ويتطلب موافقة الإدارة مرة أخرى.
                                <?php endif; ?>
                            </small>
                            <div id="imagePreview" class="mt-3 text-center"></div>
                        </div>

                        <!-- Death Date -->
                        <fieldset class="mb-4">
                            <legend class="form-label">
                                يوم الذكرى (تاريخ الوفاة) - اختياري
                            </legend>
                            <div class="mb-3">
                                <input type="text" id="death_date_picker" class="form-control"
                                    placeholder="اضغط هنا لاختيار التاريخ 📅" readonly>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <input type="number" class="form-control text-center" id="death_day"
                                        name="death_day" placeholder="اليوم" min="1" max="31"
                                        value="<?= $memorial['death_date'] ? date('j', strtotime($memorial['death_date'])) : '' ?>">
                                    <small class="form-text text-muted d-block text-center mt-1">اليوم</small>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control text-center" id="death_month"
                                        name="death_month" placeholder="الشهر" min="1" max="12"
                                        value="<?= $memorial['death_date'] ? date('n', strtotime($memorial['death_date'])) : '' ?>">
                                    <small class="form-text text-muted d-block text-center mt-1">الشهر</small>
                                </div>
                                <div class="col-4">
                                    <input type="number" class="form-control text-center" id="death_year"
                                        name="death_year" placeholder="السنة" min="1900" max="<?= date('Y') ?>"
                                        value="<?= $memorial['death_date'] ? date('Y', strtotime($memorial['death_date'])) : '' ?>">
                                    <small class="form-text text-muted d-block text-center mt-1">السنة</small>
                                </div>
                            </div>
                        </fieldset>

                        <!-- Gender -->
                        <div class="mb-4">
                            <label for="gender" class="form-label">
                                الجنس <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="gender" name="gender" required>
                                <option value="male" <?= $memorial['gender'] === 'male' ? 'selected' : '' ?>>
                                    ذكر
                                </option>
                                <option value="female" <?= $memorial['gender'] === 'female' ? 'selected' : '' ?>>
                                    أنثى
                                </option>
                            </select>
                        </div>

                        <!-- WhatsApp -->
                        <div class="mb-4">
                            <label for="whatsapp" class="form-label">
                                رقم الواتساب - اختياري
                            </label>
                            <input type="tel" class="form-control" id="whatsapp" name="whatsapp"
                                placeholder="+20 123 456 7890" value="<?= e($memorial['whatsapp'] ?? '') ?>">
                        </div>

                        <!-- Quote/Message -->
                        <div class="mb-4">
                            <label for="quote" class="form-label">
                                رسالة أو دعاء - اختياري
                                <?php if ($memorial['quote']): ?>
                                <span class="badge <?= $memorial['quote_status'] ? 'bg-success' : 'bg-warning' ?> ms-2">
                                    <?= $memorial['quote_status'] ? 'معتمدة' : 'قيد المراجعة' ?>
                                </span>
                                <?php endif; ?>
                            </label>
                            <textarea class="form-control" id="quote" name="quote" rows="4" maxlength="301"
                                placeholder="كلمات جميلة عن الفقيد، أو دعاء خاص..."><?= e($memorial['quote'] ?? '') ?></textarea>
                            <small class="form-text text-muted">
                                تعديل الرسالة قد يتطلب موافقة الإدارة مرة أخرى
                            </small>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                💾 حفظ التغييرات
                            </button>
                        </div>

                    </form>
                </div>
            </div>

            <!-- Delete Section -->
            <div class="card shadow-sm border-danger mb-4">
                <div class="card-body p-4">
                    <h5 class="card-title text-danger mb-4">🗑️ حذف الصفحة التذكارية</h5>
                    <p class="text-muted mb-3">
                        <strong>تحذير:</strong> حذف الصفحة التذكارية عملية لا يمكن التراجع عنها. 
                        سيتم حذف جميع البيانات والصور نهائياً.
                    </p>
                    
                    <form method="POST" onsubmit="return confirmDelete()">
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_delete" name="confirm_delete" value="yes" required>
                            <label class="form-check-label text-danger" for="confirm_delete">
                                <strong>أؤكد أنني أريد حذف هذه الصفحة التذكارية نهائياً</strong>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-danger">
                            🗑️ حذف الصفحة نهائياً
                        </button>
                    </form>
                </div>
            </div>

            <!-- Back to Memorial -->
            <div class="text-center">
                <a href="<?= site_url('m/' . $memorial['id']) ?>" class="btn btn-outline-primary">
                    👁️ عرض الصفحة التذكارية
                </a>
            </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
// Confirm delete function
function confirmDelete() {
    return confirm('هل أنت متأكد من حذف هذه الصفحة التذكارية نهائياً؟\n\nهذا الإجراء لا يمكن التراجع عنه!');
}

// Initialize Flatpickr for date picker
(function() {
    const datePickerInput = document.getElementById('death_date_picker');
    const deathDayInput = document.getElementById('death_day');
    const deathMonthInput = document.getElementById('death_month');
    const deathYearInput = document.getElementById('death_year');

    if (datePickerInput) {
        const fp = flatpickr(datePickerInput, {
            dateFormat: "Y-m-d",
            locale: "ar",
            disableMobile: false,
            maxDate: "today",
            minDate: "1900-01-01",
            allowInput: false,
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    const date = selectedDates[0];
                    const day = date.getDate();
                    const month = date.getMonth() + 1;
                    const year = date.getFullYear();

                    if (deathDayInput) deathDayInput.value = day;
                    if (deathMonthInput) deathMonthInput.value = month;
                    if (deathYearInput) deathYearInput.value = year;
                }
            }
        });

        // Populate picker if fields already have values
        if (deathYearInput && deathMonthInput && deathDayInput) {
            const year = deathYearInput.value;
            const month = deathMonthInput.value;
            const day = deathDayInput.value;

            if (year && month && day) {
                const dateStr = year + '-' + String(month).padStart(2, '0') + '-' + String(day).padStart(2, '0');
                fp.setDate(dateStr, false);
            }
        }
    }
})();

// Character counter for quote, name, and from_name
(function() {
    const fields = [
        { id: 'quote', max: 300 },
        { id: 'name', max: 30 },
        { id: 'from_name', max: 30 }
    ];

    fields.forEach(field => {
        const input = document.getElementById(field.id);
        if (!input) return;

        // Create counter container
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted d-block text-end mt-1';
        counter.innerHTML = `<span id="${field.id}_current">0</span>/${field.max}`;
        input.insertAdjacentElement('afterend', counter);

        const currentSpan = document.getElementById(`${field.id}_current`);
        const MAX_LENGTH = field.max;

        // Function to update character count
        function updateCharCount() {
            const currentLength = input.value.length;
            currentSpan.textContent = currentLength;

            if (currentLength > MAX_LENGTH) {
                input.style.borderColor = '#dc3545';
                input.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
                counter.style.color = '#dc3545';
                counter.style.fontWeight = 'bold';
            } else if (currentLength >= MAX_LENGTH - 5) {
                input.style.borderColor = '#ffc107';
                input.style.boxShadow = '';
                counter.style.color = '#ffc107';
                counter.style.fontWeight = 'bold';
            } else {
                input.style.borderColor = '';
                input.style.boxShadow = '';
                counter.style.color = '#6c757d';
                counter.style.fontWeight = 'normal';
            }
        }

        // Events
        input.addEventListener('input', updateCharCount);
        input.addEventListener('keyup', updateCharCount);
        input.addEventListener('change', updateCharCount);

        // Initialize
        updateCharCount();
    });
})();

// Image preview functionality
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="mt-3">
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                    <div class="mt-2 text-muted">معاينة الصورة الجديدة</div>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>

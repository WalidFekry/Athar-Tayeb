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

    // Validate inputs first
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

    $errors = [];

    if (empty($name)) {
        $errors[] = 'اسم المتوفى مطلوب';
    }

    if (!empty($quote) && mb_strlen($quote) > 300) {
        $errors[] = 'الرسالة أو الدعاء يجب ألا تتجاوز 300 حرف';
    }

    if (!in_array($gender, ['male', 'female'])) {
        $gender = 'male';
    }

    // Check rate limiting
    if (empty($errors)) {
        if (!checkRateLimit('create_memorial', CREATE_RATE_LIMIT, 3600)) {
            $errors[] = 'يمكنك إنشاء صفحة تذكارية واحدة فقط كل ساعة. يرجى المحاولة لاحقاً.';
        }
    }

    // Process image upload if no errors so far
    if (empty($errors)) {
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = processUploadedImage($_FILES['image'], 0);
            if ($uploadResult['success']) {
                $imageName = $uploadResult['filename'];
            } else {
                $errors[] = $uploadResult['error'];
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'auto_approval'");
            $stmt->execute();
            $autoApprovalSetting = $stmt->fetchColumn();
            $autoApproval = ($autoApprovalSetting == '1') ? 1 : 0;

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
                redirect(site_url('success?id=' . $memorialId));
            } else {
                redirect(site_url('unpublished?id=' . $memorialId));
            }

        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                $errors[] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
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
            <header class="text-center mb-5">
                <h1> أنشئ صفحة تذكارية 🌿</h1>
                <p class="lead text-muted">
                    صفحتك ستبقى دائماً، والأجر يستمر بإذن الله
                </p>
            </header>

            <!-- Info Alert -->
            <div class="alert alert-info">
                <h5 class="alert-heading"> يُرجى العلم ⚠️</h5>
                <p class="mb-0">
                    الصور والعبارات المضافة تخضع للمراجعة قبل النشر للتأكد من مطابقتها للمعايير الشرعية.
                    ستتمكن من مشاركة الرابط فوراً، لكن الصورة والرسالة ستظهر بعد الموافقة عليها.
                </p>
            </div>

            <!-- Errors Display -->
            <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert" aria-live="assertive">
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
                                اسم منشئ الصفحة - اختياري
                            </label>
                            <input type="text" class="form-control" id="from_name" name="from_name"
                                placeholder="مثال: عائلة الإمبابي" value="<?= e($_POST['from_name'] ?? '') ?>" aria-describedby="from_name_help">
                            <small id="from_name_help" class="form-text text-muted">
                                يمكنك كتابة اسمك أو اسم العائلة
                            </small>
                        </div>

                        <!-- Name (Required) -->
                        <div class="mb-4">
                            <label for="name" class="form-label">
                                اسم المتوفى <span class="text-danger" aria-label="حقل إجباري">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="الاسم الكامل"
                                required aria-required="true" value="<?= e($_POST['name'] ?? '') ?>">
                        </div>

                        <!-- Image Upload -->
                        <div class="mb-4">
                            <label for="imageInput" class="form-label">
                                صورة المتوفى - اختياري
                            </label>
                            <input type="file" class="form-control" id="imageInput" name="image"
                                accept=".jpg,.jpeg,.png" aria-describedby="image_help">
                            <small id="image_help" class="form-text text-muted">
                                الحد الأقصى: 2 ميجابايت | الصيغ المسموحة: JPG, PNG
                            </small>
                            <div id="imagePreview" class="mt-3 text-center" role="img" aria-live="polite"></div>
                        </div>

                        <!-- Death Date - Three Separate Fields -->
                        <fieldset class="mb-4">
                            <legend class="form-label">
                                يوم الذكرى (تاريخ الوفاة) - اختياري
                            </legend>
                            <div class="mb-3">
                                <label for="death_date_picker" class="visually-hidden">اختر تاريخ الوفاة</label>
                                <div class="input-group">
                                    <input type="text" id="death_date_picker" class="form-control"
                                        placeholder="اضغط هنا لاختيار التاريخ 📅" readonly aria-label="حقل اختيار تاريخ الوفاة">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-4">
                                    <label for="death_day" class="visually-hidden">اليوم</label>
                                    <input type="number" class="form-control text-center" id="death_day"
                                        name="death_day" placeholder="اليوم" min="1" max="31"
                                        value="<?= e($_POST['death_day'] ?? '') ?>" aria-label="يوم الوفاة">
                                    <small class="form-text text-muted d-block text-center mt-1" aria-hidden="true">اليوم</small>
                                </div>
                                <div class="col-4">
                                    <label for="death_month" class="visually-hidden">الشهر</label>
                                    <input type="number" class="form-control text-center" id="death_month"
                                        name="death_month" placeholder="الشهر" min="1" max="12"
                                        value="<?= e($_POST['death_month'] ?? '') ?>" aria-label="شهر الوفاة">
                                    <small class="form-text text-muted d-block text-center mt-1" aria-hidden="true">الشهر</small>
                                </div>
                                <div class="col-4">
                                    <label for="death_year" class="visually-hidden">السنة</label>
                                    <input type="number" class="form-control text-center" id="death_year"
                                        name="death_year" placeholder="السنة" min="1900" max="<?= date('Y') ?>"
                                        value="<?= e($_POST['death_year'] ?? '') ?>" aria-label="سنة الوفاة">
                                    <small class="form-text text-muted d-block text-center mt-1" aria-hidden="true">السنة</small>
                                </div>
                            </div>
                            <small class="form-text text-muted d-block mt-2" id="date_help">
                                مثال: اليوم: 19، الشهر: 8، السنة: 1999
                            </small>
                        </fieldset>

                        <!-- Gender -->
                        <div class="mb-4">
                            <label for="gender" class="form-label">
                                الجنس <span class="text-danger" aria-label="حقل إجباري">*</span>
                            </label>
                            <select class="form-select" id="gender" name="gender" required aria-required="true" aria-describedby="gender_help">
                                <option value="male" <?= ($_POST['gender'] ?? 'male') === 'male' ? 'selected' : '' ?>>
                                    ذكر
                                </option>
                                <option value="female" <?= ($_POST['gender'] ?? '') === 'female' ? 'selected' : '' ?>>
                                    أنثى
                                </option>
                            </select>
                            <small id="gender_help" class="form-text text-muted">
                                لتخصيص الأدعية والضمائر بشكل صحيح
                            </small>
                        </div>

                        <!-- WhatsApp -->
                        <div class="mb-4">
                            <label for="whatsapp" class="form-label">
                                رقم الواتساب - اختياري
                            </label>
                            <input type="tel" class="form-control" id="whatsapp" name="whatsapp"
                                placeholder="+20 123 456 7890" value="<?= e($_POST['whatsapp'] ?? '') ?>" aria-describedby="whatsapp_help">
                            <small id="whatsapp_help" class="form-text text-muted">
                                لنتمكن من التواصل معك في حال وجود أي استفسار بخصوص الصفحة
                            </small>
                        </div>

                        <!-- Quote/Message -->
                        <div class="mb-4">
                            <label for="quote" class="form-label">
                                رسالة أو دعاء - اختياري
                            </label>
                            <textarea class="form-control" id="quote" name="quote" rows="4" maxlength="301"
                                placeholder="كلمات جميلة عن الفقيد، أو دعاء خاص..." aria-describedby="quote_help quote_counter"><?= e($_POST['quote'] ?? '') ?></textarea>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small id="quote_help" class="form-text text-muted">
                                    سوف تظهر هذه الرسالة في الصفحة التذكارية وستخضع للمراجعة قبل النشر
                                </small>
                                <small id="quote_counter" class="form-text" aria-live="polite">
                                    <span id="quote_current">0</span>/300
                                </small>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                إنشاء الصفحة التذكارية 💚
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

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ar.js"></script>

<script>
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

                    // Visual feedback
                    [deathDayInput, deathMonthInput, deathYearInput].forEach(function(input) {
                        if (input) {
                            input.style.backgroundColor = 'var(--muted-bg)';
                            input.style.transition = 'background-color 0.3s ease';
                            setTimeout(function() {
                                input.style.backgroundColor = '';
                            }, 800);
                        }
                    });
                }
            },
            onReady: function(selectedDates, dateStr, instance) {
                // Ensure mobile compatibility
                instance.calendarContainer.style.touchAction = 'manipulation';
            }
        });

        // Also open on input click
        datePickerInput.addEventListener('click', function(e) {
            e.preventDefault();
            fp.open();
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

// Character counter for quote textarea
(function() {
    const quoteTextarea = document.getElementById('quote');
    const quoteCurrentSpan = document.getElementById('quote_current');
    const quoteCounter = document.getElementById('quote_counter');
    const form = quoteTextarea ? quoteTextarea.closest('form') : null;
    const MAX_LENGTH = 300;

    if (!quoteTextarea || !quoteCurrentSpan || !quoteCounter) {
        return;
    }

    // Function to update character count
    function updateCharCount() {
        const currentLength = quoteTextarea.value.length;
        quoteCurrentSpan.textContent = currentLength;

        // Change color based on character count
        if (currentLength > MAX_LENGTH) {
            // Exceeded limit - red border and red counter
            quoteTextarea.style.borderColor = '#dc3545';
            quoteTextarea.style.boxShadow = '0 0 0 0.2rem rgba(220, 53, 69, 0.25)';
            quoteCounter.style.color = '#dc3545';
            quoteCounter.style.fontWeight = 'bold';
        } else if (currentLength >= MAX_LENGTH - 20) {
            // Approaching limit - warning color
            quoteTextarea.style.borderColor = '#ffc107';
            quoteTextarea.style.boxShadow = '';
            quoteCounter.style.color = '#ffc107';
            quoteCounter.style.fontWeight = 'bold';
        } else {
            // Normal state
            quoteTextarea.style.borderColor = '';
            quoteTextarea.style.boxShadow = '';
            quoteCounter.style.color = '#6c757d';
            quoteCounter.style.fontWeight = 'normal';
        }
    }

    // Update count on input
    quoteTextarea.addEventListener('input', updateCharCount);
    quoteTextarea.addEventListener('keyup', updateCharCount);
    quoteTextarea.addEventListener('change', updateCharCount);
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
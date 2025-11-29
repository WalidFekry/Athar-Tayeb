<?php
/**
 * Contact Page
 * Contact form for users to send messages to admins
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'تواصل معنا — ' . SITE_NAME;

// Initialize variables
$successMessage = '';
$errorMessage = '';
$formData = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $errorMessage = 'طلب غير صالح. يرجى المحاولة مرة أخرى.';
    }
    // Check honeypot (spam protection)
    elseif (!empty($_POST['website'])) {
        // Honeypot filled - likely spam
        $errorMessage = 'حدث خطأ. يرجى المحاولة مرة أخرى.';
    }
    // Rate limiting: max 3 submissions per hour per IP
    elseif (!checkRateLimit('contact_form', CONTACT_RATE_LIMIT, 3600)) {
        $errorMessage = 'لقد تجاوزت الحد المسموح من الرسائل. يرجى المحاولة بعد ساعة.';
    } else {
        // Get form data
        $name = trim($_POST['name'] ?? '');
        $whatsapp = trim($_POST['whatsapp'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        // Store form data to repopulate on error
        $formData = compact('name', 'whatsapp', 'email', 'message');

        // Validation
        $errors = [];

        if (empty($name)) {
            $errors[] = 'الاسم مطلوب';
        } elseif (strlen($name) > 30) {
            $errors[] = 'الاسم طويل جداً (الحد الأقصى 30 حرف)';
        }

        if (empty($message)) {
            $errors[] = 'الرسالة مطلوبة';
        } elseif (strlen($message) < 10) {
            $errors[] = 'الرسالة قصيرة جداً (الحد الأدنى 10 أحرف)';
        } elseif (strlen($message) > 5000) {
            $errors[] = 'الرسالة طويلة جداً (الحد الأقصى 5000 حرف)';
        }

        // Validate email if provided
        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'البريد الإلكتروني غير صالح';
        }

        // Validate whatsapp if provided (basic sanity check)
        if (!empty($whatsapp) && !preg_match('/^[\d\s\+\-\(\)]+$/', $whatsapp)) {
            $errors[] = 'رقم الواتساب غير صالح';
        }

        if (empty($errors)) {
            try {
                // Insert into database using prepared statement
                $stmt = $pdo->prepare("
                    INSERT INTO contact_messages (name, whatsapp, email, message, ip_address, created_at)
                    VALUES (:name, :whatsapp, :email, :message, :ip_address, NOW())
                ");

                $stmt->execute([
                    ':name' => $name,
                    ':whatsapp' => !empty($whatsapp) ? $whatsapp : null,
                    ':email' => !empty($email) ? $email : null,
                    ':message' => $message,
                    ':ip_address' => getUserIp()
                ]);

                $successMessage = 'تم إرسال رسالتك بنجاح! سنتواصل معك في أقرب وقت ممكن إن شاء الله.';

                // Clear form data on success
                $formData = [];

            } catch (PDOException $e) {
                if (DEBUG_MODE) {
                    $errorMessage = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
                } else {
                    $errorMessage = 'حدث خطأ أثناء إرسال رسالتك. يرجى المحاولة مرة أخرى.';
                }
            }
        } else {
            $errorMessage = implode('<br>', $errors);
        }
    }
}

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="text-center mb-5">
                <h1>تواصل معنا 📧</h1>
                <p class="lead text-muted">
                    نسعد بتواصلكم واستفساراتكم
                </p>
            </div>

            <!-- Contact Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">أرسل لنا رسالة</h4>

                    <?php if ($successMessage): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong>✓ نجح!</strong> <?= e($successMessage) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($errorMessage): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>✗ خطأ!</strong> <?= $errorMessage ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <?php csrfField(); ?>

                        <!-- Honeypot field (hidden from users, trap for bots) -->
                        <div style="position: absolute; left: -5000px;" aria-hidden="true">
                            <input type="text" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="mb-3">
                            <label for="name" class="form-label">الاسم الكامل <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="<?= e($formData['name'] ?? '') ?>" required maxlength="30"
                                placeholder="أدخل اسمك الكامل">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="whatsapp" class="form-label">رقم الواتساب (اختياري)</label>
                                <input type="text" class="form-control" id="whatsapp" name="whatsapp"
                                    value="<?= e($formData['whatsapp'] ?? '') ?>" maxlength="50"
                                    placeholder="مثال: +201234567890">
                                <small class="text-muted">للتواصل السريع معك</small>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">البريد الإلكتروني (اختياري)</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= e($formData['email'] ?? '') ?>" maxlength="255"
                                    placeholder="مثال: walid_fekry@hotmail.com">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">رسالتك أو اقتراحك <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="6" required minlength="10"
                                maxlength="5000"
                                placeholder="اكتب رسالتك هنا..."><?= e($formData['message'] ?? '') ?></textarea>
                            <small class="text-muted">الحد الأدنى 10 أحرف، الحد الأقصى 5000 حرف</small>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                إرسال الرسالة 📤
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">عن المنصة</h4>
                    <p>
                        <strong><?= SITE_NAME ?></strong> هي منصة رقمية مجانية لإنشاء صفحات تذكارية للمتوفين.
                        نهدف إلى توفير وسيلة سهلة للأهل والأصدقاء للدعاء والذكر والقرآن لمن فارقونا.
                    </p>
                    <p>
                        كل صفحة تذكارية تحتوي على أدعية، قرآن، تسبيح إلكتروني، وأذكار يمكن للجميع المشاركة فيها.
                        الصفحات تبقى دائماً، والأجر يستمر بإذن الله.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">للاستفسارات العاجلة</h4>
                    <p>
                        يمكنك أيضاً التواصل معنا مباشرة عبر البريد الإلكتروني:
                    </p>

                    <div class="d-grid gap-3 mb-3">
                        <a href="mailto:<?= SUPPORT_EMAIL ?>" class="btn btn-outline-primary">
                            📧 <?= SUPPORT_EMAIL ?>
                        </a>
                    </div>

                    <p class="mb-0 small text-muted">
                        قبل مراسلتنا، يمكنك الاطلاع على صفحة
                        <a href="<?= site_url('faq') ?>">الأسئلة الشائعة</a>
                        فقد تجد فيها إجابة سريعة لاستفسارك.
                    </p>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">تطبيقاتنا الإسلامية</h4>
                    <p>صُممت خصيصًا لدعمك في طلب العلم، وذكر الله، والدعاء، لتجعل رحلتك الروحية أكثر ثراءً وفائدة.</p>

                    <div class="row g-4">
                        <!-- تطبيق مكتبتي -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5>📱 تطبيق مكتبتي</h5>
                                    <p>
                                        هو تطبيق إسلامي مميز يضم قصص الأنبياء بأسلوب بسيط ومشوق، مناسب لكل الأعمار.
                                        يحتوي على أذكار، أدعية، وميزات كثيرة، ويعمل بدون إنترنت لتكون الفائدة دائمًا في
                                        متناولك.
                                    </p>
                                </div>
                                <div>
                                    <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-primary w-100 mb-2">
                                        تحميل تطبيق مكتبتي
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- تطبيق مكتبتي بلس -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h5>📱 مكتبتي بلس</h5>
                                    <p>
                                        النسخة المتقدمة من تطبيق مكتبتي، مكتبة شاملة لكل مسلم، مصمم للمسلمين ذاتيا أفضل
                                        من أي وقت مضى، يحتوي على كل ما يحتاجه المسلم يوميا.
                                    </p>
                                </div>
                                <div>
                                    <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank"
                                        class="btn btn-primary w-100 mb-2">
                                        تحميل مكتبتي بلس
                                    </a>
                                    <a href="<?= APP_MAKTBTI_PLUS_IOS ?>" target="_blank"
                                        class="btn btn-primary w-100 mb-2">
                                        تحميل للآيفون
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
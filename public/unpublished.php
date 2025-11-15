<?php
/**
 * Unpublished Memorial Page
 * Shown when a memorial exists but is not yet published
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';


$memorialId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editKey = isset($_GET['edit_key']) ? trim($_GET['edit_key']) : '';

if (!$memorialId) {
    redirect(BASE_URL);
}

// Fetch memorial
$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

// Check if memorial exists
if (!$memorial) {
    // Redirect to 404 page
    header('Location: ' . site_url('404'));
    exit;
}

// Check if memorial is published
if ($memorial['status'] == 1) {
    // Redirect to the published memorial page
    header('Location: ' . site_url('m/' . $memorialId));
    exit;
}

$pageTitle = 'الصفحة قيد المراجعة — ' . SITE_NAME;
$pageDescription = 'هذه الصفحة التذكارية قيد المراجعة من قبل الإدارة';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="text-center mb-5">
                <div class="display-1 mb-4">⏳</div>
                <h1 class="display-4 mb-4">الصفحة قيد المراجعة</h1>
            </div>

            <div class="card shadow-sm border-warning">
                <div class="card-body p-5 text-center">
                    <h4 class="text-warning mb-4">📋 في انتظار الموافقة</h4>

                    <p class="lead mb-4">
                        هذه الصفحة التذكارية تم إنشاؤها بنجاح ولكنها قيد المراجعة من قبل الإدارة.
                    </p>

                    <div class="alert alert-info text-start">
                        <h5 class="alert-heading">ℹ️ ماذا يعني هذا؟</h5>
                        <ul class="mb-0">
                            <li>الصفحة موجودة في النظام ولكنها غير منشورة بعد</li>
                            <li>نقوم بمراجعة المحتوى للتأكد من مطابقته للمعايير الشرعية</li>
                            <li>عادة ما تستغرق المراجعة من 24 إلى 48 ساعة</li>
                            <li>سيتم نشر الصفحة تلقائياً بعد الموافقة عليها</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning text-start">
                        <h5 class="alert-heading">⚠️ ملاحظة مهمة</h5>
                        <p class="mb-0">
                            إذا كنت قد أنشأت هذه الصفحة، يُرجى حفظ الرابط الخاص بها.
                            بمجرد الموافقة عليها، ستتمكن من الوصول إليها ومشاركتها مع الآخرين.
                        </p>
                    </div>

                    <?php if ($memorialId): ?>
                        <div class="mt-4">
                            <p class="text-muted">رقم الصفحة: <strong><?= $memorialId ?></strong></p>

                            <?php
                            $memorialLink = site_url('m/' . $memorialId);
                            ?>
                            <!-- Memorial Link -->
                            <div class="card shadow-sm mb-4">
                                <div class="card-body">
                                    <h5 class="card-title">🔗رابط الصفحة التذكارية المؤقت</h5>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" value="<?= e($memorialLink) ?>" readonly
                                            id="memorialLink">
                                        <button class="btn btn-outline-primary copy-link-btn"
                                            data-url="<?= e($memorialLink) ?>" type="button">
                                            📋 نسخ
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Manage Memorial Section -->
                            <?php if ($editKey): ?>
                                <div class="card shadow-sm mb-4 border-warning">
                                    <div class="card-body">
                                        <h5 class="card-title text-warning">🔧 إدارة صفحتك التذكارية</h5>
                                        <p class="text-muted mb-3">
                                            يمكنك تعديل أو حذف صفحتك التذكارية في أي وقت باستخدام الرابط التالي.
                                            <strong class="text-danger">احتفظ بهذا الرابط في مكان آمن!</strong>
                                        </p>

                                        <div class="alert alert-warning mb-3">
                                            <strong>⚠️ تنبيه مهم:</strong> أي شخص يملك هذا الرابط يمكنه تعديل أو حذف الصفحة
                                            التذكارية.
                                            لا تشاركه مع أحد إلا إذا كنت تثق به تماماً.
                                        </div>

                                        <div class="input-group mb-3">
                                            <input type="text" class="form-control"
                                                value="<?= e(site_url('edit?key=' . $editKey)) ?>" readonly id="editLink">
                                            <button class="btn btn-outline-warning copy-link-btn"
                                                data-url="<?= e(site_url('edit?key=' . $editKey)) ?>" type="button">
                                                📋 نسخ
                                            </button>
                                        </div>

                                        <a href="<?= site_url('edit?key=' . $editKey) ?>" class="btn btn-warning w-100"
                                            target="_blank">
                                            ✏️ تعديل أو حذف الصفحة
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                               <!-- Duaa Image Preview -->
            <?php 
            $duaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $memorial['image'];
            $duaaImageUrl = BASE_URL . '/uploads/duaa_images/' . $memorial['image'];
            if ($memorial['generate_duaa_image'] && file_exists($duaaImagePath)): 
            ?>
            <div class="card shadow-sm mb-4 border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">📜 بطاقة الدعاء</h5>
                    <p class="text-muted mb-3">
                        تم إنشاء بطاقة دعاء جميلة لـ <strong><?= e($memorial['name']) ?></strong>. 
                        يمكنك مشاركتها أو تحميلها.
                    </p>
                    
                    <div class="text-center mb-3">
                        <img src="<?= $duaaImageUrl ?>" alt="بطاقة دعاء <?= e($memorial['name']) ?>" 
                             class="img-fluid rounded shadow" style="width: 100%; max-width: 500px; height: auto; cursor: pointer;">
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-center flex-wrap">
                        <a href="<?= $duaaImageUrl ?>" download="duaa_<?= e($memorial['name']) ?>.png" class="btn btn-success">
                            💾 تحميل البطاقة
                        </a>
                        <button class="btn btn-outline-primary copy-link-btn" data-url="<?= e($duaaImageUrl) ?>">
                            📋 نسخ رابط البطاقة
                        </button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
                    <div class="mt-5">
                        <a href="<?= site_url('') ?>" class="btn btn-primary btn-lg">
                            🏠 العودة للرئيسية
                        </a>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted">
                    للاستفسارات، يمكنك <a href="<?= site_url('contact') ?>">التواصل معنا</a>
                </p>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
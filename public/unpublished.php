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


$memorialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

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
                        <input 
                            type="text" 
                            class="form-control" 
                            value="<?= e($memorialLink) ?>" 
                            readonly
                            id="memorialLink"
                        >
                        <button 
                            class="btn btn-outline-primary copy-link-btn" 
                            data-url="<?= e($memorialLink) ?>"
                            type="button"
                        >
                            📋 نسخ
                        </button>
                    </div>
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

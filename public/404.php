<?php
/**
 * 404 Not Found Page
 * Shown when a memorial or page is not found
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

// Set 404 header
http_response_code(404);

$pageTitle = 'الصفحة غير موجودة — ' . SITE_NAME;
$pageDescription = 'الصفحة التي تبحث عنها غير موجودة';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <div class="text-center mb-5">
                <div class="display-1 mb-4">🔍</div>
                <h1 class="display-4 mb-4">404 — الصفحة غير موجودة</h1>
            </div>
            
            <div class="card shadow-sm border-danger">
                <div class="card-body p-5 text-center">
                    <h4 class="text-danger mb-4">❌ لم نتمكن من العثور على الصفحة</h4>
                    
                    <p class="lead mb-4">
                        عذراً، الصفحة التذكارية التي تبحث عنها غير موجودة أو تم حذفها.
                    </p>
                    
                    <div class="alert alert-info text-start">
                        <h5 class="alert-heading">💡 ماذا يمكنك أن تفعل؟</h5>
                        <ul class="mb-0">
                            <li>تأكد من صحة الرابط الذي استخدمته</li>
                            <li>ابحث عن الصفحة التذكارية باستخدام الاسم</li>
                            <li>تصفح جميع الصفحات التذكارية المتاحة</li>
                            <li>أنشئ صفحة تذكارية جديدة</li>
                        </ul>
                    </div>
                    
                    <div class="row g-3 mt-4">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>" class="btn btn-primary w-100">
                                🏠 الصفحة الرئيسية
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/search.php" class="btn btn-outline-primary w-100">
                                🔍 البحث
                            </a>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-2">
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/all.php" class="btn btn-outline-secondary w-100">
                                📋 جميع الصفحات
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= BASE_URL ?>/create.php" class="btn btn-success w-100">
                                ✨ أنشئ صفحة جديدة
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <p class="text-muted">
                    إذا كنت تعتقد أن هذا خطأ، يمكنك <a href="<?= BASE_URL ?>/contact.php">التواصل معنا</a>
                </p>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

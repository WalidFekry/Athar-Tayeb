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
        <div class="col-lg-10 mx-auto">
            
            <!-- Professional 404 Header -->
            <div class="text-center mb-5">
                <div class="error-icon mb-4">
                    <div class="display-1 text-primary" style="font-size: 8rem; line-height: 1;">4<span class="text-danger">0</span>4</div>
                </div>
                <h1 class="display-5 fw-bold text-dark mb-3">الصفحة غير موجودة</h1>
                <p class="lead text-muted mb-0">نعتذر، لم نتمكن من العثور على الصفحة التي تبحث عنها</p>
            </div>
            
            <!-- Main Error Card -->
            <div class="card shadow-lg border-0 mb-5">
                <div class="card-body p-5">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="text-primary mb-3">🔍 ماذا حدث؟</h3>
                            <p class="mb-4">
                                الصفحة التذكارية التي تحاول الوصول إليها قد تكون غير موجودة، أو تم حذفها، أو أن الرابط غير صحيح.
                            </p>
                            
                            <div class="alert alert-light border-primary">
                                <h5 class="alert-heading text-primary">💡 اقتراحات مفيدة:</h5>
                                <ul class="mb-0 text-dark">
                                    <li>تحقق من صحة الرابط المستخدم</li>
                                    <li>ابحث عن الصفحة التذكارية بالاسم</li>
                                    <li>تصفح جميع الصفحات التذكارية</li>
                                    <li>أنشئ صفحة تذكارية جديدة مجاناً</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="error-illustration">
                                <div style="font-size: 6rem; opacity: 0.1;">🌿</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Options -->
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <a href="<?= site_url('') ?>" class="btn btn-primary w-100 py-3 shadow-sm">
                        <div class="d-flex flex-column align-items-center">
                            <span style="font-size: 1.5rem;">🏠</span>
                            <span class="mt-1">الصفحة الرئيسية</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('search') ?>" class="btn btn-outline-primary w-100 py-3 shadow-sm">
                        <div class="d-flex flex-column align-items-center">
                            <span style="font-size: 1.5rem;">🔍</span>
                            <span class="mt-1">البحث</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('all') ?>" class="btn btn-outline-secondary w-100 py-3 shadow-sm">
                        <div class="d-flex flex-column align-items-center">
                            <span style="font-size: 1.5rem;">📋</span>
                            <span class="mt-1">جميع الصفحات</span>
                        </div>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= site_url('create') ?>" class="btn btn-success w-100 py-3 shadow-sm">
                        <div class="d-flex flex-column align-items-center">
                            <span style="font-size: 1.5rem;">✨</span>
                            <span class="mt-1">أنشئ صفحة جديدة</span>
                        </div>
                    </a>
                </div>
            </div>
            
            <!-- Contact Section -->
            <div class="text-center">
                <div class="card border-0 bg-light">
                    <div class="card-body p-4">
                        <h5 class="text-muted mb-3">هل تحتاج مساعدة؟</h5>
                        <p class="text-muted mb-3">
                            إذا كنت تعتقد أن هذا خطأ أو تحتاج مساعدة في العثور على صفحة معينة
                        </p>
                        <a href="<?= site_url('contact') ?>" class="btn btn-outline-primary">
                            📧 تواصل معنا
                        </a>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

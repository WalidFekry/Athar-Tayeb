<?php
/**
 * Memorial Deleted Confirmation Page
 * Shown after successfully deleting a memorial
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'تم حذف الصفحة التذكارية — ' . SITE_NAME;
$pageDescription = 'تأكيد حذف الصفحة التذكارية';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="display-1 mb-3">✅</div>
                <h1 class="text-success">تم حذف الصفحة التذكارية</h1>
                <p class="lead">
                    تم حذف الصفحة التذكارية بنجاح ونهائياً
                </p>
            </div>
            
            <!-- Info Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-body text-center p-5">
                    <h5 class="card-title mb-3">🗑️ تم الحذف بنجاح</h5>
                    <p class="text-muted mb-4">
                        تم حذف الصفحة التذكارية وجميع البيانات المرتبطة بها نهائياً من النظام.
                        لا يمكن استرداد هذه البيانات.
                    </p>
                    
                    <div class="alert alert-info">
                        <h6 class="alert-heading">💡 هل تريد إنشاء صفحة تذكارية جديدة؟</h6>
                        <p class="mb-0">
                            يمكنك إنشاء صفحة تذكارية جديدة في أي وقت من خلال النقر على الزر أدناه.
                        </p>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                        <a href="<?= site_url('create') ?>" class="btn btn-primary btn-lg">
                            ➕ إنشاء صفحة تذكارية جديدة
                        </a>
                        <a href="<?= site_url('') ?>" class="btn btn-outline-primary btn-lg">
                            🏠 العودة للرئيسية
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="card-title">📞 هل تحتاج مساعدة؟</h6>
                    <p class="text-muted mb-3">
                        إذا كان لديك أي استفسار أو كنت بحاجة لمساعدة، يمكنك التواصل معنا.
                    </p>
                    <a href="<?= site_url('contact') ?>" class="btn btn-outline-secondary">
                        📧 تواصل معنا
                    </a>
                </div>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

<?php
/**
 * Success Page
 * Shown after successfully creating a memorial
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';


// Get memorial ID and edit key
$memorialId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$editKey = isset($_GET['edit_key']) ? trim($_GET['edit_key']) : '';

if (!$memorialId) {
    redirect(BASE_URL);
}

// Fetch memorial details
$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

if (!$memorial) {
    redirect(BASE_URL);
}

// Generate URL (ID-based only)
$memorialUrl = site_url('m/' . $memorial['id']);

$pageTitle = 'تم إنشاء الصفحة بنجاح — ' . SITE_NAME;

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <!-- Success Message -->
            <div class="text-center mb-5">
                <div class="display-1 mb-3">✅</div>
                <h1 class="text-success">تم إنشاء الصفحة التذكارية بنجاح!</h1>
                <p class="lead">
                     صفحة <strong><?= e($memorial['name']) ?></strong> جاهزة الآن 🌸
                </p>
            </div>
            
            <!-- Status Info -->
            <div class="alert alert-info mb-4">
                <h5 class="alert-heading">📋 حالة الصفحة</h5>
                <p>
                    صفحتك الآن قيد المراجعة من حيث الصورة والرسالة. يمكنك مشاركة الرابط فوراً، 
                    لكن الصورة والرسالة ستظهر بعد موافقة الإدارة (عادة خلال 24 ساعة).
                </p>
                <hr>
                <ul class="mb-0">
                    <li>
                        <strong>الصورة:</strong> 
                        <?php if ($memorial['image'] && $memorial['image_status'] == 0): ?>
                            <span class="badge badge-pending">قيد المراجعة</span>
                            <?php elseif ($memorial['image'] && $memorial['image_status'] == 1): ?>
                            <span class="badge badge-pending">تم المراجعة</span>
                        <?php else: ?>
                            <span class="text-muted">لم يتم رفع صورة</span>
                        <?php endif; ?>
                    </li>
                    <li>
                        <strong>الرسالة:</strong> 
                        <?php if ($memorial['quote'] && $memorial['quote_status'] == 0): ?>
                            <span class="badge badge-pending">قيد المراجعة</span>
                            <?php elseif ($memorial['quote'] && $memorial['quote_status'] == 1): ?>
                            <span class="badge badge-pending">تم المراجعة</span>
                        <?php else: ?>
                            <span class="text-muted">لم يتم إضافة رسالة</span>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
            
            <!-- Memorial Link -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">🔗 رابط الصفحة التذكارية</h5>
                    <div class="input-group mb-3">
                        <input 
                            type="text" 
                            class="form-control" 
                            value="<?= e($memorialUrl) ?>" 
                            readonly
                            id="memorialLink"
                        >
                        <button 
                            class="btn btn-outline-primary copy-link-btn" 
                            data-url="<?= e($memorialUrl) ?>"
                            type="button"
                        >
                            📋 نسخ
                        </button>
                    </div>
                    
                    <a href="<?= $memorialUrl ?>" class="btn btn-primary w-100 mb-3" target="_blank">
                        👁️ عرض الصفحة
                    </a>
                </div>
            </div>

            <!-- Share Buttons -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">📤 شارك الصفحة</h5>
                    <p class="text-muted">شارك الصفحة مع الأهل والأصدقاء ليشاركوا في الأجر والدعاء لـ <strong><?= e($memorial['name']) ?></strong> 💚</p>
                    
                    <div class="share-buttons d-flex justify-content-center gap-3 flex-wrap">
                <a href="https://wa.me/?text=<?= urlencode('دعاء وذكرى ' . getPronoun($memorial['gender'], 'للمرحوم') . ' ' . $memorial['name'] . '، شارك الدعاء والصدقة الجارية من خلال هذه الصفحة: ' . $memorialUrl) ?>"
                    target="_blank" rel="noopener" class="share-btn share-whatsapp" aria-label="شارك عبر واتساب">
                    📱 واتساب
                </a>

                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($memorialUrl) ?>" target="_blank"
                    rel="noopener" class="share-btn share-facebook" aria-label="شارك عبر فيسبوك">
                    📘 فيسبوك
                </a>

                <a href="https://t.me/share/url?url=<?= urlencode($memorialUrl) ?>&text=<?= urlencode('دعاء وذكرى ' . getPronoun($memorial['gender'], 'للمرحوم') . ' ' . $memorial['name'] . '، شارك الدعاء والصدقة الجارية من خلال هذه الصفحة.') ?>"
                    target="_blank" rel="noopener" class="share-btn share-telegram" aria-label="شارك عبر تيليجرام">
                    ✈️ تيليجرام
                </a>
                
                <button class="share-btn share-copy copy-link-btn" data-url="<?= e($memorialUrl) ?>"
                    aria-label="نسخ رابط المشاركة">
                    📋 نسخ الرابط
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
                        <strong>⚠️ تنبيه مهم:</strong> أي شخص يملك هذا الرابط يمكنه تعديل أو حذف الصفحة التذكارية. 
                        لا تشاركه مع أحد إلا إذا كنت تثق به تماماً.
                    </div>
                    
                    <div class="input-group mb-3">
                        <input 
                            type="text" 
                            class="form-control" 
                            value="<?= e(site_url('edit?key=' . $editKey)) ?>" 
                            readonly
                            id="editLink"
                        >
                        <button 
                            class="btn btn-outline-warning copy-link-btn" 
                            data-url="<?= e(site_url('edit?key=' . $editKey)) ?>"
                            type="button"
                        >
                            📋 نسخ
                        </button>
                    </div>
                    
                    <a href="<?= site_url('edit?key=' . $editKey) ?>" class="btn btn-warning w-100" target="_blank">
                        ✏️ تعديل أو حذف الصفحة
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Next Steps -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">✨ الخطوات التالية</h5>
                    <ul>
                        <li>شارك الرابط مع العائلة والأصدقاء</li>
                        <li>احفظ الرابط لديك للرجوع إليه</li>
                        <?php if ($editKey): ?>
                        <li><strong>احفظ رابط التعديل في مكان آمن</strong> - ستحتاجه لتعديل الصفحة لاحقاً</li>
                        <?php endif; ?>
                        <li>تابع الصفحة لمشاهدة التسبيحات والزيارات</li>
                        <li>انتظر موافقة الإدارة على الصورة والرسالة</li>
                    </ul>
                </div>
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-4">
                <a href="<?= site_url('') ?>" class="btn btn-outline-primary">
                    🏠 العودة للرئيسية
                </a>
            </div>
            
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

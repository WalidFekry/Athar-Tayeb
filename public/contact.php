<?php
/**
 * Contact Page
 * Simple contact information page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'تواصل معنا — ' . SITE_NAME;

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">

            <div class="text-center mb-5">
                <h1>📧 تواصل معنا</h1>
                <p class="lead text-muted">
                    نسعد بتواصلكم واستفساراتكم
                </p>
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
                    <h4 class="mb-4">للاستفسارات والدعم</h4>
                    <p>
                        إذا كان لديك أي استفسار أو اقتراح أو مشكلة تقنية، يمكنك التواصل معنا عبر:
                    </p>

                    <div class="d-grid gap-3">
                        <a href="mailto:<?= SUPPORT_EMAIL ?>" class="btn btn-outline-primary">
                            📧 البريد الإلكتروني
                        </a>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h4 class="mb-4">تطبيقاتنا</h4>
                    <p>
                        تعرف على تطبيقاتنا الإسلامية الأخرى:
                    </p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-primary w-100">
                                📱 تطبيق مكتبتي
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank" class="btn btn-primary w-100">
                                📱 مكتبتي بلس
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="mb-4">الأسئلة الشائعة</h4>

                    <div class="mb-3">
                        <h6 class="fw-bold">هل الخدمة مجانية؟</h6>
                        <p class="text-muted">نعم، جميع خدمات المنصة مجانية تماماً.</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">كم يستغرق وقت الموافقة على الصورة؟</h6>
                        <p class="text-muted">عادة خلال 24 ساعة من الإنشاء.</p>
                    </div>

                    <div class="mb-3">
                        <h6 class="fw-bold">هل يمكن تعديل الصفحة بعد إنشائها؟</h6>
                        <p class="text-muted">حالياً لا يمكن التعديل، لكن يمكنك التواصل معنا لأي تعديلات ضرورية.</p>
                    </div>

                    <div class="mb-0">
                        <h6 class="fw-bold">هل يمكن حذف الصفحة؟</h6>
                        <p class="text-muted">نعم، تواصل معنا وسنقوم بحذفها فوراً.</p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-5">
                <p class="text-muted">
                    تصميم وتطوير: <a href="<?= DEVELOPER_URL ?>" target="_blank"><?= DEVELOPER_NAME ?></a>
                </p>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
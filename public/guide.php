<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'دليل الاستخدام — ' . SITE_NAME;
$pageDescription = 'صفحة رئيسية تجمع أهم الأدلة والصفحات الإرشادية لمساعدة المستخدم على الاستفادة من أثر طيب.';

include __DIR__ . '/../includes/header.php';
?>

<section class="hero-section">
    <div class="container">
        <h1 class="mb-3">دليل الاستخدام</h1>
        <p class="lead">
            كل ما تحتاجه لفهم فكرة أثر طيب وكيفية إنشاء صفحة تذكارية ومشاركتها واستخدامها من الجوال في مكان واحد.
        </p>
    </div>
</section>

<div class="container my-5">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">🕊 ما هي صفحات أثر طيب؟</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        تعريف مبسّط بفكرة الصفحات التذكارية وكيف تجمع الدعاء والتسبيح والقرآن في مكان واحد.
                    </p>
                    <a href="<?= site_url('athar-pages') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل
                        للصفحة</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">📋 دليل إنشاء صفحة تذكارية</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        خطوات عملية لاختيار الصورة والنبذة والعنوان وترتيب محتوى الصفحة التذكارية بشكل جميل ومؤثر.
                    </p>
                    <a href="<?= site_url('memorial-guide') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل
                        للصفحة</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">🔗 دليل مشاركة الصفحة</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        نصائح عملية ونماذج رسائل جاهزة لمشاركة صفحة المتوفى مع العائلة والأصدقاء بلطف وبدون إحراج.
                    </p>
                    <a href="<?= site_url('share-guide') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل
                        للصفحة</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">📱 استخدام أثر طيب على الجوال</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        شرح مبسّط لكيفية فتح الموقع من الجوال، وحفظه كاختصار، واستخدام التسبيح الإلكتروني في أي وقت.
                    </p>
                    <a href="<?= site_url('mobile-guide') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل
                        للصفحة</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">🤲 آداب الدعاء للميت</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        تذكير لطيف بأهم الآداب العامة في الدعاء وكيفية ربطها باستخدام صفحات أثر طيب.
                    </p>
                    <a href="<?= site_url('duaa-etiquette') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل
                        للصفحة</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body p-4 d-flex flex-column">
                    <h2 class="h5 mb-2">❓ الأسئلة الشائعة (FAQ)</h2>
                    <p class="small text-muted mb-3 flex-grow-1">
                        إجابات عن أكثر الأسئلة شيوعًا حول استخدام المنصة وإنشاء الصفحات التذكارية والتعديل عليها.
                    </p>
                    <a href="<?= site_url('faq') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل للصفحة</a>
                </div>
            </div>
        </div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card h-100 shadow-sm">
            <div class="card-body p-4 d-flex flex-column">
                <h2 class="h5 mb-2">🌿 كيف تستفيد من أثر طيب؟</h2>
                <p class="small text-muted mb-3 flex-grow-1">
                    دليلك الشامل للاستفادة من منصة أثر طيب في إنشاء صفحات تذكارية ومشاركة الأجر والثواب.
                </p>
                <a href="<?= site_url('how-to-benefit') ?>" class="btn btn-outline-primary btn-sm mt-auto">انتقل للصفحة</a>
            </div>
        </div>
    </div>
</div>

    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
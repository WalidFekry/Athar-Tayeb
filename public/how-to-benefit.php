<?php
/**
 * How to Benefit from Athar Tayeb Page
 * Static informational page explaining how users can benefit from the platform
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

// Page metadata
$pageTitle = 'كيف تستفيد من أثر طيب؟ — ' . SITE_NAME;
$pageDescription = 'اكتشف كيف تستفيد من منصة أثر طيب لإنشاء صفحات تذكارية للمتوفين، ومشاركة الدعاء والتسبيح والقرآن عبر الصدقة الجارية الرقمية — ابدأ الرحلة الآن.';

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-4">كيف تستفيد من أثر طيب؟ 🌿</h1>
                <p class="lead">
                    دليلك الشامل للاستفادة من منصة أثر طيب في إنشاء صفحات تذكارية ومشاركة الأجر والثواب
                </p>
            </div>
        </div>
    </div>
</section>

<div class="container my-5">

    <!-- Introduction -->
    <section class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">منصة رقمية للصدقة الجارية 💚</h2>
                    <p class="text-center lead">
                        أثر طيب هي منصة مجانية تتيح لك إنشاء صفحات تذكارية تفاعلية لأحبائك المتوفين،
                        حيث يمكن للزوار المشاركة في الدعاء والتسبيح والقرآن، ليكون لك ولهم أجر مستمر بإذن الله.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Steps Section -->
    <section class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">خطوات الاستفادة من المنصة 📋</h2>
        </div>

        <!-- Step 1 -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm step-card">
                <div class="card-body text-center p-4">
                    <div class="step-number">1</div>
                    <div class="step-icon mb-3">🌱</div>
                    <h4 class="text-muted mb-3">أنشئ صفحة تذكارية</h4>
                    <p class="text-muted">
                        ابدأ بإنشاء صفحة تذكارية لأحد أحبائك المتوفين. أدخل اسمه وتاريخ وفاته،
                        وأضف صورة شخصية ورسالة خاصة إن أردت.
                    </p>
                    <div class="mt-4">
                        <a href="<?= site_url('create') ?>" class="btn btn-primary">ابدأ الآن</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm step-card">
                <div class="card-body text-center p-4">
                    <div class="step-number">2</div>
                    <div class="step-icon mb-3">📤</div>
                    <h4 class="text-muted mb-3">شارك الصفحة مع الأحباب</h4>
                    <p class="text-muted">
                        بعد إنشاء الصفحة، شاركها مع الأهل والأصدقاء عبر وسائل التواصل المختلفة.
                        كل مشاركة تزيد من فرص الدعاء والأجر.
                    </p>
                    <div class="mt-4">
                        <span class="badge bg-success">مشاركة سهلة</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 border-0 shadow-sm step-card">
                <div class="card-body text-center p-4">
                    <div class="step-number">3</div>
                    <div class="step-icon mb-3">📿</div>
                    <h4 class="text-muted mb-3">اجعل التسبيح عادة يومية</h4>
                    <p class="text-muted">
                        استخدم التسبيح الإلكتروني يومياً للدعاء لأحبائك. كل تسبيحة تُحسب لك ولهم،
                        وتكون صدقة جارية مستمرة.
                    </p>
                    <div class="mt-4">
                        <span class="badge bg-info">أجر مستمر</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="row mb-5">
        <div class="col-12">
            <h2 class="text-center mb-5">مميزات الصفحة التذكارية ✨</h2>
        </div>

        <div class="col-md-6 mb-4">
            <div class="feature-item d-flex align-items-start">
                <div class="feature-icon me-3">📿</div>
                <div>
                    <h5>تسبيح إلكتروني تفاعلي</h5>
                    <p class="text-muted mb-0">
                        عدادات تسبيح مع شريط تقدم يصل إلى 33 تسبيحة لكل نوع، مع رسائل تشجيعية عند الإكمال.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="feature-item d-flex align-items-start">
                <div class="feature-icon me-3">📖</div>
                <div>
                    <h5>قرآن وأذكار يومية</h5>
                    <p class="text-muted mb-0">
                        صفحة قرآن عشوائية يومياً، وأذكار الصباح والمساء مع إمكانية الاستماع للصوت.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="feature-item d-flex align-items-start">
                <div class="feature-icon me-3">🤲</div>
                <div>
                    <h5>أدعية مخصصة للميت</h5>
                    <p class="text-muted mb-0">
                        مجموعة من الأدعية المختارة للميت مع إمكانية الاستماع للصوت والمشاركة.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="feature-item d-flex align-items-start">
                <div class="feature-icon me-3">🔗</div>
                <div>
                    <h5>مشاركة سهلة ومرنة</h5>
                    <p class="text-muted mb-0">
                        إمكانية مشاركة الصفحة عبر جميع وسائل التواصل الاجتماعي ونسخ الرابط مباشرة.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Tips Section -->
    <section class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body p-5">
                    <h3 class="text-muted mb-4">نصائح لزيادة الأجر والفائدة 💡</h3>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="tip-item">
                                <h6 class="fw-bold text-primary">📅 اجعلها عادة يومية</h6>
                                <p class="small mb-0">
                                    خصص وقتاً يومياً للدعاء والتسبيح لأحبائك المتوفين، ولو دقائق قليلة.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="tip-item">
                                <h6 class="fw-bold text-primary">👥 شارك مع الآخرين</h6>
                                <p class="small mb-0">
                                    كلما زاد عدد الزوار والمشاركين، زاد الأجر والثواب للمتوفى.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="tip-item">
                                <h6 class="fw-bold text-primary">📱 استخدم الهاتف</h6>
                                <p class="small mb-0">
                                    الموقع يعمل بشكل ممتاز على الهواتف الذكية، يمكنك الوصول إليه في أي وقت.
                                </p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="tip-item">
                                <h6 class="fw-bold text-primary">💚 انشر الخير</h6>
                                <p class="small mb-0">
                                    شجع الآخرين على إنشاء صفحات لأحبائهم المتوفين ونشر ثقافة الصدقة الجارية.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Usage Guide Link -->
    <section class="row mb-4">
        <div class="col-lg-8 mx-auto text-center">
            <a href="<?= site_url('guide') ?>" class="btn btn-outline-primary btn-lg">
                📖 دليل الاستخدام: كل ما تحتاجه لفهم فكرة أثر طيب وكيفية إنشاء صفحة تذكارية ومشاركتها واستخدامها من
                الجوال في مكان واحد
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="row">
        <div class="col-lg-8 mx-auto text-center">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body p-5">
                    <h3 class="mb-4">ابدأ رحلتك مع أثر طيب الآن 🌟</h3>
                    <p class="lead mb-4">
                        لا تؤجل الخير، ابدأ بإنشاء صفحة تذكارية لأحد أحبائك واجعل ذكراهم صدقة جارية تنفعهم في الآخرة.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="<?= site_url('create') ?>" class="btn btn-light btn-lg px-5">
                            أنشئ صفحة تذكارية 🌱
                        </a>
                        <a href="<?= site_url('all') ?>" class="btn btn-outline-light btn-lg px-5">
                            تصفح الصفحات الموجودة 👁️
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</div>

<style>
    /* Custom styles for how-to-benefit page */
    .step-card {
        position: relative;
        transition: var(--transition);
        background-color: var(--card-bg);
        border: 1px solid var(--border);
    }

    .step-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .step-number {
        position: absolute;
        top: -15px;
        right: 20px;
        background: var(--primary);
        color: var(--card-bg);
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
    }

    .step-icon {
        font-size: 3rem;
    }

    .feature-icon {
        font-size: 2.5rem;
        min-width: 60px;
    }

    .feature-item {
        padding: 1.5rem;
        border-radius: var(--radius);
        transition: var(--transition);
    }

    .feature-item:hover {
        background-color: var(--muted-bg);
    }

    .tip-item {
        padding: 1rem;
        border-right: 3px solid var(--primary);
        background-color: var(--card-bg);
        border-radius: var(--radius);
        color: var(--text);
    }

    /* Dark mode specific adjustments */
    [data-theme="dark"] .tip-item {
        border-right-color: var(--primary);
    }

    [data-theme="dark"] .step-number {
        color: var(--bg);
    }
</style>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * Home Page - Athar Tayeb
 * Main landing page with intro, search, and latest memorials
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

// Page metadata
$pageTitle = SITE_NAME . ' — ' . SITE_TAGLINE;
$pageDescription = 'منصة رقمية لإنشاء صفحات تذكارية للمتوفين. شارك الرحمة والحسنات في ذكرى من أحببت. صدقة جارية تبقى بعد الرحيل.';

// Fetch latest approved memorials (by creation date)
$stmt = $pdo->prepare("
    SELECT id, name, death_date, image, visits, gender
    FROM memorials 
    WHERE status = 1 AND (image_status = 1 OR image IS NULL)
    ORDER BY created_at DESC 
    LIMIT 3
");
$stmt->execute();
$latestMemorials = $stmt->fetchAll();

// Fetch most recently visited memorials (by last_visit)
$stmt = $pdo->prepare("
    SELECT id, name, death_date, image, visits, gender, created_at,
           tasbeeh_allahu, tasbeeh_lailaha, tasbeeh_alham, tasbeeh_subhan
    FROM memorials 
    WHERE status = 1 
      AND (image_status = 1 OR image IS NULL)
      AND last_visit IS NOT NULL
      AND DATE(created_at) != CURDATE()
    ORDER BY last_visit DESC 
    LIMIT 3
");
$stmt->execute();
$recentlyVisitedMemorials = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section" aria-labelledby="hero-heading">
    <div class="container">
        <h1 id="hero-heading"> فارقوك؟ لا تنساهم! 🌿</h1>
        <p class="lead">
            أنشئ صفحة تذكارية لأحبائك المتوفين وشاركها مع من تحب ليظل ذكرهم حيًا ودعاؤهم مستمرًا.
        </p>
        <div class="mb-4">
            <p class="fst-italic">
                قال رسول الله ﷺ: <strong>"إذا مات ابن آدم انقطع عمله إلا من ثلاث: صدقة جارية، أو علم ينتفع به، أو ولد
                    صالح يدعو له"</strong>
            </p>
        </div>
        <a href="<?= site_url('create') ?>" class="btn btn-light btn-lg px-5 py-3" aria-label="انتقل إلى صفحة إنشاء صفحة تذكارية جديدة">
            أنشئ صفحة تذكارية الآن 💚
        </a>
    </div>
</section>

<div class="container my-5">

    <!-- About Section -->
    <section class="row mb-5" aria-labelledby="about-heading">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h2 id="about-heading" class="text-center mb-4">أثر طيب.. صدقة جارية تبقى باقية 💠</h2>
                    <p class="text-center lead">
                        شارك الخير والرحمة في ذكرى أحبائك. أنشئ صفحة تذكارية تحمل الأدعية، القرآن، التسبيح، والأذكار،
                        ليشارك فيها الجميع.
                    </p>
                    <p class="text-center">
                        كل دعاء، وكل تسبيحة، وكل قراءة قرآن على صفحات "أثر طيب" صدقة جارية تستمر بإذن الله، ليظل أثر
                        أحبائك طيبًا يدوم.
                    </p>

                    <div class="text-center mt-4" role="list" aria-label="مميزات الخدمة">
                        <span class="badge bg-primary fs-6 px-4 py-2" role="listitem">مجاني تماماً</span>
                        <span class="badge bg-success fs-6 px-4 py-2 mx-2" role="listitem">سهل الاستخدام</span>
                        <span class="badge bg-info fs-6 px-4 py-2" role="listitem">قابل للمشاركة</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="row mb-5" aria-labelledby="search-heading">
        <div class="col-lg-8 mx-auto">
            <h2 id="search-heading" class="visually-hidden">البحث عن صفحة تذكارية</h2>
            <div class="search-box">
                <form action="<?= site_url('search') ?>" method="GET" role="search">
                    <div class="input-group input-group-lg">
                        <label for="searchInput" class="visually-hidden">ابحث عن اسم المتوفى</label>
                        <input type="text" name="q" id="searchInput" class="form-control"
                            placeholder="🔍 ابحث عن شخص تحبه لتتذكره بالدعاء..." autocomplete="off" aria-label="حقل البحث عن اسم المتوفى">
                        <button class="btn btn-primary px-4" type="submit">بحث</button>
                    </div>
                </form>
                <div id="searchResults" class="search-results" style="display: none;" role="region" aria-live="polite" aria-atomic="true"></div>
            </div>
        </div>
    </section>

    <!-- Latest Memorials -->
    <section aria-labelledby="latest-heading">
        <div class="row mb-4">
            <div class="col-12">
                <h2 id="latest-heading" class="text-center mb-4">صدقات أضيفت حديثاً 🤲</h2>
            </div>
        </div>

        <!-- Group 1: Most Recently Created Pages -->
        <?php if (count($latestMemorials) > 0): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="h5 text-center text-muted mb-3">أحدث الصفحات المضافة</h3>
                </div>
            </div>
            <div class="row g-4 mb-5">
                <?php foreach ($latestMemorials as $memorial): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card memorial-card h-100">
                            <div class="card-body text-center">
                                <img src="<?= getImageUrl($memorial['image'], true) ?>" alt="<?= e($memorial['name']) ?>"
                                    class="memorial-image" loading="lazy">
                                <h5 class="memorial-name"><?= e($memorial['name']) ?></h5>
                                <?php if ($memorial['death_date']): ?>
                                    <p class="memorial-date">
                                        📅 <?= formatArabicDate($memorial['death_date']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="memorial-visits">
                                    👁️ زارها <?= toArabicNumerals($memorial['visits']) ?> شخصاً
                                </p>
                                <a href="<?= site_url('m/' . $memorial['id']) ?>" class="btn btn-primary w-100" aria-label="عرض الصفحة التذكارية للمرحوم <?= e($memorial['name']) ?>">
                                    عرض الصفحة
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Group 2: Most Recently Visited Pages -->
        <?php if (count($recentlyVisitedMemorials) > 0): ?>
            <div class="row mb-3">
                <div class="col-12">
                    <h3 class="h5 text-center text-muted mb-3">صفحات تمت زيارتها مؤخراً</h3>
                </div>
            </div>
            <div class="row g-4 mb-4">
                <?php foreach ($recentlyVisitedMemorials as $memorial): ?>
                    <?php 
                        $totalTasbeeh = $memorial['tasbeeh_allahu'] + $memorial['tasbeeh_lailaha'] + 
                                       $memorial['tasbeeh_alham'] + $memorial['tasbeeh_subhan'];
                    ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card memorial-card h-100">
                            <div class="card-body text-center">
                                <img src="<?= getImageUrl($memorial['image'], true) ?>" alt="<?= e($memorial['name']) ?>"
                                    class="memorial-image" loading="lazy">
                                <h5 class="memorial-name"><?= e($memorial['name']) ?></h5>
                                <?php if ($memorial['death_date']): ?>
                                    <p class="memorial-date">
                                        📅 <?= formatArabicDate($memorial['death_date']) ?>
                                    </p>
                                <?php endif; ?>
                                <p class="memorial-date text-muted small">
                                    🗓️ أُنشئت: <?= formatArabicDate($memorial['created_at'], 'short') ?>
                                </p>
                                <p class="memorial-visits">
                                    👁️ زارها <?= toArabicNumerals($memorial['visits']) ?> شخصاً
                                </p>
                                <p class="memorial-tasbeeh text-success small fw-bold">
                                    📿 إجمالي التسبيح: <?= toArabicNumerals(number_format($totalTasbeeh)) ?>
                                </p>
                                <a href="<?= site_url('m/' . $memorial['id']) ?>" class="btn btn-primary w-100" aria-label="عرض الصفحة التذكارية للمرحوم <?= e($memorial['name']) ?>">
                                    عرض الصفحة
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- View More Button -->
        <?php if (count($latestMemorials) > 0 || count($recentlyVisitedMemorials) > 0): ?>
            <div class="text-center">
                <a href="<?= site_url('all') ?>" class="btn btn-outline-primary btn-lg" aria-label="انتقل إلى صفحة جميع الصفحات التذكارية">
                    عرض المزيد من الصفحات
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                <p class="mb-0">لا توجد صفحات تذكارية حالياً. كن أول من ينشئ صفحة!</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- Features Section -->
    <section class="row mt-5 g-4" aria-labelledby="features-heading">
            <div class="col-12">
                <h2 id="features-heading" class="text-center mb-4"> مميزات الصفحة التذكارية ✨</h2>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3" aria-hidden="true">📿</div>
                        <h3 class="h5">تسبيح إلكتروني</h3>
                        <p class="text-muted">عدادات تسبيح تفاعلية للزوار للمشاركة في الأجر</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3" aria-hidden="true">📖</div>
                        <h3 class="h5">قرآن وأذكار</h3>
                        <p class="text-muted">صفحة قرآن عشوائية وأذكار الصباح والمساء مع الصوت</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3" aria-hidden="true">🤲</div>
                        <h3 class="h5">أدعية مخصصة</h3>
                        <p class="text-muted">أدعية للميت مع إمكانية الاستماع للصوت</p>
                    </div>
                </div>
            </div>
    </section>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
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

// Page metadata
$pageTitle = SITE_NAME . ' — ' . SITE_TAGLINE;
$pageDescription = 'منصة رقمية لإنشاء صفحات تذكارية للمتوفين. شارك الرحمة والحسنات في ذكرى من أحببت. صدقة جارية تبقى بعد الرحيل.';

// Fetch latest approved memorials
$stmt = $pdo->prepare("
    SELECT id, name, slug, death_date, image, visits, gender
    FROM memorials 
    WHERE status = 1 AND image_status = 1
    ORDER BY created_at DESC 
    LIMIT 6
");
$stmt->execute();
$latestMemorials = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <h1>🕌 فارقوك؟ لا تنساهم!</h1>
        <p class="lead">
            أنشئ صفحة تذكارية لأحبائك المتوفين وشاركها مع الأهل والأصدقاء ليدعوا لهم بالخير
        </p>
        <div class="mb-4">
            <p class="fst-italic">
                قال رسول الله ﷺ: <strong>"إذا مات ابن آدم انقطع عمله إلا من ثلاث: صدقة جارية، أو علم ينتفع به، أو ولد صالح يدعو له"</strong>
            </p>
        </div>
        <a href="<?= BASE_URL ?>/create.php" class="btn btn-light btn-lg px-5 py-3">
            ✨ أنشئ صفحة تذكارية الآن
        </a>
    </div>
</section>

<div class="container my-5">
    
    <!-- About Section -->
    <div class="row mb-5">
        <div class="col-lg-10 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h3 class="text-center mb-4">💠 صدقة جارية رقمية</h3>
                    <p class="text-center lead">
                        شارك الرحمة والحسنات في ذكرى من أحببت. أنشئ صفحة تذكارية تحتوي على أدعية، قرآن، تسبيح، وأذكار يمكن للجميع المشاركة فيها.
                    </p>
                    <p class="text-center">
                        كل دعاء، كل تسبيحة، كل قراءة قرآن على هذه الصفحة هي صدقة جارية تصل لمن تحب. 
                        الصفحة تبقى دائماً، والأجر يستمر بإذن الله.
                    </p>
                    <div class="text-center mt-4">
                        <span class="badge bg-primary fs-6 px-4 py-2">مجاني تماماً</span>
                        <span class="badge bg-success fs-6 px-4 py-2 mx-2">سهل الاستخدام</span>
                        <span class="badge bg-info fs-6 px-4 py-2">قابل للمشاركة</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto">
            <div class="search-box">
                <form action="<?= BASE_URL ?>/search.php" method="GET">
                    <div class="input-group input-group-lg">
                        <input 
                            type="text" 
                            name="q" 
                            id="searchInput"
                            class="form-control" 
                            placeholder="🔍 ابحث عن شخص تحبه لتتذكره بالدعاء..."
                            autocomplete="off"
                        >
                        <button class="btn btn-primary px-4" type="submit">بحث</button>
                    </div>
                </form>
                <div id="searchResults" class="search-results" style="display: none;"></div>
            </div>
        </div>
    </div>
    
    <!-- Latest Memorials -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="text-center mb-4">صدقات أضيفت حديثاً 🤲</h2>
        </div>
    </div>
    
    <?php if (count($latestMemorials) > 0): ?>
        <div class="row g-4 mb-4">
            <?php foreach ($latestMemorials as $memorial): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card memorial-card h-100">
                        <div class="card-body text-center">
                            <img 
                                src="<?= getImageUrl($memorial['image'], true) ?>" 
                                alt="<?= e($memorial['name']) ?>"
                                class="memorial-image"
                                loading="lazy"
                            >
                            <h5 class="memorial-name"><?= e($memorial['name']) ?></h5>
                            <?php if ($memorial['death_date']): ?>
                                <p class="memorial-date">
                                    📅 <?= formatArabicDate($memorial['death_date']) ?>
                                </p>
                            <?php endif; ?>
                            <p class="memorial-visits">
                                👁️ زارها <?= toArabicNumerals($memorial['visits']) ?> شخصاً
                            </p>
                            <a href="<?= BASE_URL ?>/memorial.php?id=<?= $memorial['id'] ?>" class="btn btn-primary w-100">
                                عرض الصفحة
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="text-center">
            <a href="<?= BASE_URL ?>/all.php" class="btn btn-outline-primary btn-lg">
                عرض المزيد من الصفحات
            </a>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            <p class="mb-0">لا توجد صفحات تذكارية حالياً. كن أول من ينشئ صفحة!</p>
        </div>
    <?php endif; ?>
    
    <!-- Features Section -->
    <div class="row mt-5 g-4">
        <div class="col-12">
            <h3 class="text-center mb-4">✨ مميزات الصفحة التذكارية</h3>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 mb-3">📿</div>
                    <h5>تسبيح إلكتروني</h5>
                    <p class="text-muted">عدادات تسبيح تفاعلية للزوار للمشاركة في الأجر</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 mb-3">📖</div>
                    <h5>قرآن وأذكار</h5>
                    <p class="text-muted">صفحة قرآن عشوائية وأذكار الصباح والمساء مع الصوت</p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 mb-3">🤲</div>
                    <h5>أدعية مخصصة</h5>
                    <p class="text-muted">أدعية للميت مع إمكانية الاستماع للصوت</p>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

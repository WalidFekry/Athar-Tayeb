<?php
/**
 * Memorial Page (Fallback by ID)
 * Main memorial viewing page with all features
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Get memorial ID
$memorialId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$memorialId) {
    redirect(BASE_URL);
}

// Fetch memorial
$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

if (!$memorial) {
    redirect(BASE_URL);
}

// Increment visit counter (simple debounce using session)
$visitKey = 'visited_' . $memorialId;
if (!isset($_SESSION[$visitKey]) || (time() - $_SESSION[$visitKey]) > 300) {
    $stmt = $pdo->prepare("UPDATE memorials SET visits = visits + 1 WHERE id = ?");
    $stmt->execute([$memorialId]);
    $_SESSION[$visitKey] = time();
    $memorial['visits']++;
}

// Generate page metadata
$pageTitle = 'للمغفور ' . getPronoun($memorial['gender'], 'له') . ' بإذن الله تعالى ' . $memorial['name'] . ' — ' . SITE_NAME;
$pageDescription = $memorial['quote'] ?? 'صفحة تذكارية للمغفور ' . getPronoun($memorial['gender'], 'له') . ' ' . $memorial['name'];
$pageImage = $memorial['image'] && $memorial['image_status'] == 1 ? getImageUrl($memorial['image']) : null;
$memorialUrl = BASE_URL . '/memorial.php?id=' . $memorial['id'];

// Generate OG tags and structured data
$ogTags = generateOGTags($pageTitle, $pageDescription, $pageImage, $memorialUrl);
$structuredData = generateStructuredData($memorial);

// Random Quran page (1-604)
$randomQuranPage = rand(1, 604);

// Asma Allah Al-Husna (99 names)
$asmaAllah = [
    'الرَّحْمَنُ',
    'الرَّحِيمُ',
    'الْمَلِكُ',
    'الْقُدُّوسُ',
    'السَّلاَمُ',
    'الْمُؤْمِنُ',
    'الْمُهَيْمِنُ',
    'الْعَزِيزُ',
    'الْجَبَّارُ',
    'الْمُتَكَبِّرُ',
    'الْخَالِقُ',
    'الْبَارِئُ',
    'الْمُصَوِّرُ',
    'الْغَفَّارُ',
    'الْقَهَّارُ',
    'الْوَهَّابُ',
    'الرَّزَّاقُ',
    'الْفَتَّاحُ',
    'اَلْعَلِيْمُ',
    'الْقَابِضُ',
    'الْبَاسِطُ',
    'الْخَافِضُ',
    'الرَّافِعُ',
    'الْمُعِزُّ',
    'المُذِلُّ',
    'السَّمِيعُ',
    'الْبَصِيرُ',
    'الْحَكَمُ',
    'الْعَدْلُ',
    'اللَّطِيفُ',
    'الْخَبِيرُ',
    'الْحَلِيمُ',
    'الْعَظِيمُ',
    'الْغَفُورُ',
    'الشَّكُورُ',
    'الْعَلِيُّ',
    'الْكَبِيرُ',
    'الْحَفِيظُ',
    'المُقيِت',
    'الْحسِيبُ',
    'الْجَلِيلُ',
    'الْكَرِيمُ',
    'الرَّقِيبُ',
    'الْمُجِيبُ',
    'الْوَاسِعُ',
    'الْحَكِيمُ',
    'الْوَدُودُ',
    'الْمَجِيدُ',
    'الْبَاعِثُ',
    'الشَّهِيدُ',
    'الْحَقُّ',
    'الْوَكِيلُ',
    'الْقَوِيُّ',
    'الْمَتِينُ',
    'الْوَلِيُّ',
    'الْحَمِيدُ',
    'الْمُحْصِي',
    'الْمُبْدِئُ',
    'الْمُعِيدُ',
    'الْمُحْيِي',
    'اَلْمُمِيتُ',
    'الْحَيُّ',
    'الْقَيُّومُ',
    'الْوَاجِدُ',
    'الْمَاجِدُ',
    'الْواَحِدُ',
    'اَلاَحَدُ',
    'الصَّمَدُ',
    'الْقَادِرُ',
    'الْمُقْتَدِرُ',
    'الْمُقَدِّمُ',
    'الْمُؤَخِّرُ',
    'الأوَّلُ',
    'الآخِرُ',
    'الظَّاهِرُ',
    'الْبَاطِنُ',
    'الْوَالِي',
    'الْمُتَعَالِي',
    'الْبَرُّ',
    'التَّوَابُ',
    'الْمُنْتَقِمُ',
    'العَفُوُّ',
    'الرَّؤُوفُ',
    'مَالِكُ الْمُلْكِ',
    'ذُوالْجَلاَلِ وَالإكْرَامِ',
    'الْمُقْسِطُ',
    'الْجَامِعُ',
    'الْغَنِيُّ',
    'الْمُغْنِي',
    'اَلْمَانِعُ',
    'الضَّارَّ',
    'النَّافِعُ',
    'النُّورُ',
    'الْهَادِي',
    'الْبَدِيعُ',
    'اَلْبَاقِي',
    'الْوَارِثُ',
    'الرَّشِيدُ',
    'الصَّبُورُ'
];

include __DIR__ . '/../includes/header.php';
?>

<!-- CSRF Token for AJAX -->
<input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

<div class="container my-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>">الرئيسية</a></li>
            <li class="breadcrumb-item active"><?= e($memorial['name']) ?></li>
        </ol>
    </nav>

    <!-- Memorial Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-5">

            <!-- Image -->
            <?php if ($memorial['image'] && $memorial['image_status'] == 1): ?>
                <img src="<?= getImageUrl($memorial['image']) ?>" alt="<?= e($memorial['name']) ?>"
                    class="memorial-image mb-3" style="width: 180px; height: 180px;">
            <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/images/placeholder-memorial.svg" alt="صورة افتراضية"
                    class="memorial-image mb-3" style="width: 180px; height: 180px;">
                <?php if ($memorial['image'] && $memorial['image_status'] == 0): ?>
                    <div class="mb-3">
                        <span class="badge badge-pending">الصورة قيد المراجعة</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- From Name -->
            <?php if ($memorial['from_name']): ?>
                <p class="text-muted mb-2">إهداء من: <strong><?= e($memorial['from_name']) ?></strong></p>
            <?php endif; ?>

            <!-- Name -->
            <h1 class="display-5 fw-bold text-primary mb-3">
                للمغفور <?= getPronoun($memorial['gender'], 'له') ?> بإذن الله تعالى<br>
                <?= e($memorial['name']) ?> 🌱
            </h1>

            <!-- Death Date -->
            <?php if ($memorial['death_date']): ?>
                <p class="lead text-muted mb-3">
                    📅 <?= formatArabicDate($memorial['death_date']) ?>
                </p>
            <?php endif; ?>

            <!-- Visits Counter -->
            <p class="text-muted">
                👁️ زار هذه الصفحة <strong><?= toArabicNumerals($memorial['visits']) ?></strong> شخصاً
            </p>

        </div>
    </div>

    <!-- Owner's Quote/Message -->
    <?php if ($memorial['quote'] && $memorial['quote_status'] == 1): ?>
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">💬 رسالة من الأهل</h5>
                <p class="card-text" style="white-space: pre-wrap;"><?= e($memorial['quote']) ?></p>
            </div>
        </div>
    <?php elseif ($memorial['quote'] && $memorial['quote_status'] == 0): ?>
        <div class="alert alert-warning">
            <strong>الرسالة قيد المراجعة</strong> — ستظهر بعد الموافقة عليها
        </div>
    <?php endif; ?>

    <!-- Duas Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3 class="text-center mb-4">
                وَمَا تُقَدِّمُوا لِأَنْفُسِكُمْ مِنْ خَيْرٍ تَجِدُوهُ عِنْدَ اللَّهِ
            </h3>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <strong>🤲 دعاء:</strong><br>
                            اللهم اغفر <?= getPronoun($memorial['gender'], 'له') ?>
                            وارحم<?= getPronoun($memorial['gender'], 'له') ?>،
                            وعاف<?= getPronoun($memorial['gender'], 'عنه') ?> واعف
                            <?= getPronoun($memorial['gender'], 'عنه') ?>،
                            وأكرم نزل<?= getPronoun($memorial['gender'], 'له') ?>، ووسع
                            مدخل<?= getPronoun($memorial['gender'], 'له') ?>،
                            واغسل<?= getPronoun($memorial['gender'], 'له') ?> بالماء والثلج والبرد،
                            ونق<?= getPronoun($memorial['gender'], 'له') ?> من الخطايا كما ينقى الثوب الأبيض من الدنس.
                        </p>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <strong>🤲 دعاء:</strong><br>
                            اللهم أبدل<?= getPronoun($memorial['gender'], 'له') ?> داراً خيراً من
                            دار<?= getPronoun($memorial['gender'], 'له') ?>،
                            وأهلاً خيراً من أهل<?= getPronoun($memorial['gender'], 'له') ?>،
                            وأدخل<?= getPronoun($memorial['gender'], 'له') ?> الجنة،
                            وأعذ<?= getPronoun($memorial['gender'], 'له') ?> من عذاب القبر ومن عذاب النار.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Audio Dua -->
            <div class="audio-player mt-3">
                <label class="form-label fw-bold">🎧 استمع للدعاء:</label>
                <audio controls preload="none">
                    <source src="https://post.walid-fekry.com/athkar/salaa.mp3" type="audio/mpeg">
                    متصفحك لا يدعم تشغيل الصوت
                </audio>
            </div>
        </div>
    </div>

    <!-- Azkar Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">📿 أذكار الصباح والمساء</h4>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">🌅 أذكار الصباح</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/athkar/morning.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">🌙 أذكار المساء</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/athkar/evening.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Surahs -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">📖 قراءة سريعة</h4>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">سورة يس</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/quran/surah/yasin.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">سورة الفاتحة</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/quran/surah/fatiha.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Random Quran Page -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-3">📖 صفحة عشوائية من القرآن الكريم</h4>
            <p class="text-center text-muted mb-3">
                وَإِذَا قُرِئَ الْقُرْآنُ فَاسْتَمِعُواْ لَهُ وَأَنصِتُواْ لَعَلَّكُمْ تُرْحَمُونَ
            </p>
            <p class="text-center mb-4">
                🌿 هب ثواب هذه القراءة للمغفور <?= getPronoun($memorial['gender'], 'له') ?>
                <strong><?= e($memorial['name']) ?></strong>
            </p>

            <div class="quran-page-container">
                <img src="https://post.walid-fekry.com/quran/<?= $randomQuranPage ?>.jpg"
                    alt="صفحة قرآن <?= $randomQuranPage ?>" class="quran-page-image" loading="lazy">

                <div class="audio-player mt-3">
                    <audio controls preload="none">
                        <source src="https://post.walid-fekry.com/quran/mp3/<?= $randomQuranPage ?>.mp3"
                            type="audio/mpeg">
                    </audio>
                </div>
            </div>
        </div>
    </div>

    <!-- Ruqyah PDF -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center">
            <h4 class="mb-3">🛡️ الرقية الشرعية</h4>
            <button id="ruqyahToggle" class="btn btn-primary">
                📖 تشغيل الرقية الشرعية
            </button>

            <iframe id="ruqyahFrame" src="https://post.walid-fekry.com/pdf/roquia.pdf"
                style="display: none; width: 100%; height: 600px; border: none; margin-top: 1rem;"
                title="الرقية الشرعية"></iframe>
        </div>
    </div>

    <!-- Tasbeeh Counters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">📿 التسبيح الإلكتروني</h4>
            <p class="text-center text-muted mb-4 d-flex justify-content-between align-items-center" style="gap: 1rem;">
                انقر على أي تسبيحة للمشاركة في الأجر
                <button id="resetTasbeeh" class="btn btn-danger btn-sm">تصـفير العداد</button>
            </p>

            <div class="tasbeeh-container">
                <div class="tasbeeh-card local-only" data-field="localcounter">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم ارحمها' : 'اللهم ارحمه' ?>
                    </div>
                    <div class="tasbeeh-count"><?= number_format(0) ?></div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم اغفر لها' : 'اللهم اغفر له' ?>
                    </div>
                    <div class="tasbeeh-count"><?= number_format(0) ?></div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم اعفُ عنها' : 'اللهم اعفُ عنه' ?>
                    </div>
                    <div class="tasbeeh-count"><?= number_format(0) ?></div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم ارفع  درجاتها' : 'اللهم ارفع  درجاته' ?>
                    </div>
                    <div class="tasbeeh-count"><?= number_format(0) ?></div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                </div>
                <div class="tasbeeh-card" data-field="subhan" data-memorial-id="<?= $memorial['id'] ?>">
                    <div class="tasbeeh-title">سُبْحَانَ اللهِ</div>
                    <div class="tasbeeh-count"><?= number_format($memorial['tasbeeh_subhan']) ?></div>
                    <div class="tasbeeh-label">
                        جلستك: <span class="tasbeeh-local">0</span>
                    </div>
                </div>

                <div class="tasbeeh-card" data-field="alham" data-memorial-id="<?= $memorial['id'] ?>">
                    <div class="tasbeeh-title">الْحَمْدُ للهِ</div>
                    <div class="tasbeeh-count"><?= number_format($memorial['tasbeeh_alham']) ?></div>
                    <div class="tasbeeh-label">
                        جلستك: <span class="tasbeeh-local">0</span>
                    </div>
                </div>

                <div class="tasbeeh-card" data-field="lailaha" data-memorial-id="<?= $memorial['id'] ?>">
                    <div class="tasbeeh-title">لَا إِلَهَ إِلَّا اللهُ</div>
                    <div class="tasbeeh-count"><?= number_format($memorial['tasbeeh_lailaha']) ?></div>
                    <div class="tasbeeh-label">
                        جلستك: <span class="tasbeeh-local">0</span>
                    </div>
                </div>

                <div class="tasbeeh-card" data-field="allahu" data-memorial-id="<?= $memorial['id'] ?>">
                    <div class="tasbeeh-title">اللهُ أَكْبَرُ</div>
                    <div class="tasbeeh-count"><?= number_format($memorial['tasbeeh_allahu']) ?></div>
                    <div class="tasbeeh-label">
                        جلستك: <span class="tasbeeh-local">0</span>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Asma Allah Al-Husna -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">✨ أسماء الله الحسنى</h4>

            <div class="asma-grid">
                <?php foreach (array_slice($asmaAllah, 0, 12) as $name): ?>
                    <div class="asma-item"><?= $name ?></div>
                <?php endforeach; ?>

                <?php foreach (array_slice($asmaAllah, 12) as $name): ?>
                    <div class="asma-item hidden" style="display: none;"><?= $name ?></div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <button id="showMoreAsma" class="btn btn-outline-primary">
                    عرض المزيد
                </button>
            </div>
        </div>
    </div>

    <!-- Share Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">📤 شارك هذه الصفحة</h4>
            <p class="text-center text-muted mb-4">
                شارك الصفحة ليشارك الآخرون في الأجر والدعاء
            </p>

            <div class="share-buttons">
                <a href="https://wa.me/?text=<?= urlencode('صفحة تذكارية: ' . $memorial['name'] . ' - ' . $memorialUrl) ?>"
                    target="_blank" class="share-btn share-whatsapp">
                    📱 واتساب
                </a>

                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($memorialUrl) ?>" target="_blank"
                    class="share-btn share-facebook">
                    📘 فيسبوك
                </a>

                <a href="https://t.me/share/url?url=<?= urlencode($memorialUrl) ?>&text=<?= urlencode('صفحة تذكارية: ' . $memorial['name']) ?>"
                    target="_blank" class="share-btn share-telegram">
                    ✈️ تيليجرام
                </a>

                <button class="share-btn share-copy copy-link-btn" data-url="<?= e($memorialUrl) ?>">
                    📋 نسخ الرابط
                </button>
            </div>
        </div>
    </div>

    <!-- Apps Section -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>📱 تطبيق مكتبتي</h5>
                    <p class="text-muted small">مكتبة إسلامية شاملة</p>
                    <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-sm btn-primary">
                        تحميل التطبيق
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h5>📱 مكتبتي بلس</h5>
                    <p class="text-muted small">النسخة المتقدمة</p>
                    <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank" class="btn btn-sm btn-primary">
                        تحميل التطبيق
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
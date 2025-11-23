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
require_once __DIR__ . '/../includes/maintenance_check.php';


// Get memorial ID
$memorialId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if (!$memorialId) {
    redirect(BASE_URL);
}

// Fetch memorial
$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

// Check if memorial exists
if (!$memorial) {
    // Redirect to 404 page
    header('Location: ' . site_url('404'));
    exit;
}

// Check if memorial is published
if ($memorial['status'] != 1) {
    // Redirect to unpublished page
    header('Location: ' . site_url('unpublished?id=' . $memorialId));
    exit;
}

// Update visit count and last visit time if not a bot
if (!isBot()) {
    $visitKey = 'visited_' . $memorialId;
    if (!isset($_SESSION[$visitKey]) || (time() - $_SESSION[$visitKey]) > 300) {
        $stmt = $pdo->prepare("UPDATE memorials SET visits = visits + 1 , last_visit = current_timestamp() WHERE id = ?");
        $stmt->execute([$memorialId]);
        $_SESSION[$visitKey] = time();
        $memorial['visits']++;
    }
}

// Generate page metadata
$pageTitle = 'للمغفور ' . getPronoun($memorial['gender'], 'له') . ' بإذن الله تعالى ' . $memorial['name'] . ' — ' . SITE_NAME;
$pageDescription = $memorial['quote'] ?? 'صفحة تذكارية للمغفور ' . getPronoun($memorial['gender'], 'له') . ' ' . $memorial['name'];
$pageImage = $memorial['image'] && $memorial['image_status'] == 1 ? getImageUrl($memorial['image']) : BASE_URL . '/assets/images/placeholder-memorial.png';
$memorialUrl = site_url('m/' . $memorial['id']);

// Generate OG tags and structured data
$ogTags = generateOGTags($pageTitle, $pageDescription, $pageImage, $memorialUrl);
$structuredData = generateStructuredData($memorial);

// Random Quran page (1-604)
$randomQuranPage = rand(1, 604);
// Pad with leading zeros
$randomQuranPageMp3 = str_pad($randomQuranPage, 3, "0", STR_PAD_LEFT);

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

// Generate prayers for the memorial
$prayers = getPrayers($memorial['gender'], htmlspecialchars($memorial['name']));

// Generate memorial share text
$shareText = getMemorialShareText(
    $memorial['gender'],
    $memorial['name'],
    $memorialUrl
);

include __DIR__ . '/../includes/header.php';
?>

<!-- CSRF Token for AJAX -->
<input type="hidden" name="csrf_token" value="<?= getCSRFToken() ?>">

<div class="container my-4">

    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= site_url('') ?>">الرئيسية</a></li>
            <li class="breadcrumb-item"><?= e($memorial['name']) ?></li>
        </ol>
    </nav>

    <!-- Memorial Header -->
    <div class="card shadow-sm mb-4">
        <div class="card-body text-center py-5 position-relative">

            <!-- Creation Date -->
            <div class="memorial-creation-date">
                <small class="text-muted">
                    تاريخ انشاء الصفحة: <strong><?= formatArabicDate($memorial['created_at'], 'short') ?></strong>
                </small>
            </div>

            <!-- Image -->
            <?php if ($memorial['image'] && $memorial['image_status'] == 1): ?>
                <img src="<?= getImageUrl($memorial['image']) ?>" alt="<?= e($memorial['name']) ?>"
                    class="memorial-image mb-3">
            <?php elseif ($memorial['image'] && $memorial['image_status'] == 0): ?>
                <img src="<?= BASE_URL ?>/assets/images/placeholder-memorial.svg" alt="صورة افتراضية"
                    class="memorial-image mb-3">
                <div class="mb-3">
                    <span class="badge badge-pending">الصورة قيد المراجعة</span>
                </div>
            <?php else: ?>
                <img src="<?= BASE_URL ?>/assets/images/placeholder-memorial.svg" alt="صورة افتراضية"
                    class="memorial-image mb-3">
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

            <!-- Visits & Last Visit -->
            <p class="text-muted mb-0">
                👁️ زار هذه الصفحة
                <strong><?= toArabicNumerals($memorial['visits']) ?></strong> شخصاً
                <?php if ($memorial['last_visit']): ?>
                    — آخر زيارة:
                    <strong><?= timeAgoInArabic($memorial['last_visit']) ?></strong>
                <?php endif; ?>
            </p>


        </div>
    </div>

    <!-- Owner's Quote/Message -->
    <?php if ($memorial['quote'] && $memorial['quote_status'] == 1): ?>
        <div class="card shadow-sm mb-4 border-primary">
            <div class="card-body">
                <h5 class="card-title text-primary">كلمة من صاحب الإهداء 💬</h5>
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

            <h5 class="text-center mb-4">
                نسألكم الدعاء <?= getPronoun($memorial['gender'], 'له') ?> 💚
            </h5>


            <!-- Prayers -->
            <div class="row g-3">
                <?php foreach ($prayers as $prayer): ?>
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <p class="mb-0"><?= $prayer ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Audio Dua -->
            <div class="audio-dua-section mt-4 p-4 bg-muted rounded">
                <p class="text-center mb-3 fst-italic" style="line-height: 2; color: var(--muted-text);">
                    إن المؤمن يحزن على فراق أحبّته، ويشتاق لمن فقد<br>
                    وأفضل ما يُقدّمه المؤمن للميت: أن يدعو له كما أوصانا حبيبنا محمد صلى الله عليه وسلم
                </p>
                <div class="audio-player">
                    <label class="form-label fw-bold">🎧 استمع للدعاء:</label>
                    <audio controls preload="none">
                        <source src="../assets/audios/doaa-die.mp3" type="audio/mpeg">
                        متصفحك لا يدعم تشغيل الصوت
                    </audio>
                </div>
            </div>
        </div>
    </div>

    <!-- Duaa Image Section -->
    <?php
    $duaaImagePath = PUBLIC_PATH . '/uploads/duaa_images/' . $memorial['image'];
    $duaaImageUrl = BASE_URL . '/uploads/duaa_images/' . $memorial['image'];
    if ($memorial['image_status'] == 1 && $memorial['generate_duaa_image'] && file_exists($duaaImagePath)):
        ?>
        <div class="card shadow-sm mb-4 border-success">
            <div class="card-body">
                <h4 class="text-center mb-4 text-success">بطاقة الدعاء 📜</h4>
                <p class="text-center text-muted mb-4">
                    بطاقة دعاء مخصصة <?= getPronoun($memorial['gender'], 'للمرحوم') ?>
                    <strong><?= e($memorial['name']) ?></strong>
                </p>

                <div class="text-center mb-4">
                    <img src="<?= $duaaImageUrl ?>" alt="بطاقة دعاء <?= e($memorial['name']) ?>"
                        class="img-fluid rounded shadow duaa-card-image"
                        style="width: 100%; max-width: 500px; height: auto; cursor: pointer;"
                        onclick="openDuaaImageModal('<?= $duaaImageUrl ?>', '<?= e($memorial['name']) ?>')">
                </div>

                <div class="d-flex gap-2 justify-content-center flex-wrap">
                    <button class="btn btn-success"
                        onclick="openDuaaImageModal('<?= $duaaImageUrl ?>', '<?= e($memorial['name']) ?>')">
                        👁️ عرض بالحجم الكامل
                    </button>
                    <a href="<?= $duaaImageUrl ?>" download="duaa_<?= e($memorial['name']) ?>.png"
                        class="btn btn-outline-primary">
                        💾 تحميل البطاقة
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Azkar Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4"> أذكار الصباح والمساء 📿</h4>

            <!-- Azkar Reading Buttons -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <button class="btn btn-primary w-100 py-3 azkar-read-btn"
                        data-azkar-image="<?= BASE_URL ?>/assets/images/azkar-alsabah.webp"
                        data-azkar-title="أذكار الصباح">
                        قراءة أذكار الصباح
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-primary w-100 py-3 azkar-read-btn"
                        data-azkar-image="<?= BASE_URL ?>/assets/images/azkar-almasaa.webp"
                        data-azkar-title="أذكار المساء">
                        قراءة أذكار المساء
                    </button>
                </div>
            </div>

            <!-- Azkar Audio Players -->
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">🌅 أذكار الصباح</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/athkar/saba7.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">🌙 أذكار المساء</label>
                        <audio controls preload="none">
                            <source src="https://post.walid-fekry.com/athkar/msaa.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Surahs -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4"> قراءة سريعة 📖</h4>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">سورة يس</label>
                        <p class="text-muted small mb-2">قراءة سورة يس تُسهل على المتوفى قبره، وتُخفّف عنه عذاب القبر،
                            وتكون له نورًا يوم القيامة. عن النبي صلى الله عليه وسلم قال: "إن لكل شيء قلبًا، وقلب القرآن
                            يس"، وقراءتها تُعتبر صدقة جارية تُثقل حسنات المتوفى.</p>
                        <div class="d-flex gap-2 mb-2">
                            <button class="btn btn-primary btn-sm flex-grow-1" id="readYaseenBtn">
                                📖 قراءة
                            </button>
                            <button class="btn btn-outline-primary btn-sm"
                                onclick="document.getElementById('yaseenAudio').play()">
                                ▶️ استماع
                            </button>
                        </div>
                        <audio id="yaseenAudio" controls preload="none" class="w-100">
                            <source src="../assets/audios/yassin.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="audio-player">
                        <label class="form-label fw-bold">سورة الفاتحة</label>
                        <p class="text-muted small mb-2">سورة الفاتحة سبب في رحمة الله ومغفرته للميت، وتفتح له أبواب
                            الجنة وتُيسر حسابه يوم القيامة. قراءتها والدعاء بها من الأعمال التي تنفع المتوفى، فهي شفاعة
                            له يوم العرض على الله.</p>
                        <div class="d-flex gap-2 mb-2">
                            <button class="btn btn-primary btn-sm flex-grow-1" id="readFatihaDirectBtn">
                                📖 قراءة
                            </button>
                            <button class="btn btn-outline-primary btn-sm"
                                onclick="document.getElementById('fatihaAudio').play()">
                                ▶️ استماع
                            </button>
                        </div>
                        <audio id="fatihaAudio" controls preload="none" class="w-100">
                            <source src="../assets/audios/alfatiha.mp3" type="audio/mpeg">
                        </audio>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Random Quran Page -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-3"> ورد اليوم من القرآن الكريم📖</h4>
            <p class="text-center text-muted mb-3">
                وَإِذَا قُرِئَ الْقُرْآنُ فَاسْتَمِعُواْ لَهُ وَأَنصِتُواْ لَعَلَّكُمْ تُرْحَمُونَ
            </p>
            <p class="text-center mb-4">
                هب ثواب هذه القراءة للمغفور <?= getPronoun($memorial['gender'], 'له') ?>
                <strong><?= e($memorial['name']) ?></strong> 🌿
                <span>— لعلها المنجية بإذن الله 💚</span>
            </p>


            <div class="quran-page-container">
                <img src="https://post.walid-fekry.com/quran/<?= $randomQuranPage ?>.jpg"
                    alt="صفحة قرآن <?= $randomQuranPage ?>" class="quran-page-image" loading="lazy">

                <div class="audio-player mt-3">
                    <audio controls preload="none">
                        <source src="https://post.walid-fekry.com/quran/mp3/<?= $randomQuranPageMp3 ?>.mp3"
                            type="audio/mpeg">
                    </audio>
                </div>
            </div>
        </div>
    </div>

    <!-- Tasbeeh Counters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">التسبيح الإلكتروني 📿</h4>
            <p class="text-center text-muted">
                🌱 اللهم هب مثل ثواب هذا العمل إلى روح <strong><?= e($memorial['name']) ?></strong> 🌱
            </p>
            <p class="text-center mb-2">انقر على أي تسبيحة للمشاركة في الأجر</p>
            <div class="tasbeeh-container">
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

                <div class="tasbeeh-card local-only" data-field="localcounter" data-tasbeeh-id="mercy">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم ارحمها' : 'اللهم ارحمه' ?>
                    </div>
                    <div class="tasbeeh-count">0</div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="completion-message" style="display: none;">
                        <div class="completion-icon">✅</div>
                        <div class="completion-text">
                            تم إكمال ٣٣ تسبيحة<br>
                            <?= $memorial['gender'] === 'female'
                                ? 'نسأل الله أن يتقبّلها ويجعلها نورًا يضيء قبرها 💚'
                                : 'نسأل الله أن يتقبّلها ويجعلها نورًا يضيء قبره 💚' ?>
                        </div>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter" data-tasbeeh-id="forgiveness">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم اغفر لها' : 'اللهم اغفر له' ?>
                    </div>
                    <div class="tasbeeh-count">0</div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="completion-message" style="display: none;">
                        <div class="completion-icon">✅</div>
                        <div class="completion-text">
                            تم إكمال ٣٣ تسبيحة<br>
                            <?= $memorial['gender'] === 'female'
                                ? 'نسأل الله أن يجعلها في ميزان حسناتها ويرفع درجتها 💚'
                                : 'نسأل الله أن يجعلها في ميزان حسناته ويرفع درجته 💚' ?>
                        </div>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter" data-tasbeeh-id="pardon">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم اعفُ عنها' : 'اللهم اعفُ عنه' ?>
                    </div>
                    <div class="tasbeeh-count">0</div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="completion-message" style="display: none;">
                        <div class="completion-icon">✅</div>
                        <div class="completion-text">
                            تم إكمال ٣٣ تسبيحة<br>
                            <?= $memorial['gender'] === 'female'
                                ? 'نسأل الله أن يغفر لها ويتجاوز عن سيئاتها 💚'
                                : 'نسأل الله أن يغفر له ويتجاوز عن سيئاته 💚' ?>
                        </div>
                    </div>
                </div>

                <div class="tasbeeh-card local-only" data-field="localcounter" data-tasbeeh-id="elevation">
                    <div class="tasbeeh-title">
                        <?= $memorial['gender'] === 'female' ? 'اللهم ارفع درجاتها' : 'اللهم ارفع درجاته' ?>
                    </div>
                    <div class="tasbeeh-count">0</div>
                    <div class="tasbeeh-label">
                        / <span class="tasbeeh-local">33</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="completion-message" style="display: none;">
                        <div class="completion-icon">✅</div>
                        <div class="completion-text">
                            تم إكمال ٣٣ تسبيحة<br>
                            <?= $memorial['gender'] === 'female'
                                ? 'نسأل الله أن يرفع منزلتها في الجنة ويجعل قبرها روضةً من رياض الجنة 💚'
                                : 'نسأل الله أن يرفع منزلته في الجنة ويجعل قبره روضةً من رياض الجنة 💚' ?>
                        </div>
                    </div>
                </div>


            </div>

        </div>
    </div>

    <!-- Asma Allah Al-Husna -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h4 class="text-center mb-4">أسماء الله الحسنى 📗</h4>
            <p class="text-center mb-3">
                كما وردت في القرآن الكريم والسنة
            </p>
            <p class="text-center text-muted">
                فقد روى البخاري ومسلم عن أبي هريرة رضي الله عنه أن النبي ﷺ قال:<br>
                <em>"إن لله تسعة وتسعين اسماً، مائة إلا واحداً، من أحصاها دخل الجنة."</em>
            </p>


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
        <h4 class="text-center mb-4">شارك الخير وكن سببًا في صدقة جارية 📤</h4>
        <p class="text-center text-muted mb-4">
            بمشاركتك هذه الصفحة، تساهم في نشر الخير والدعاء <?= getPronoun($memorial['gender'], 'للمرحوم') ?>
            <strong><?= htmlspecialchars($memorial['name']) ?></strong>.<br>
            كل مشاركة هي صدقة جارية لك وله، تزيد من أجر الدعاء وتُذكر الجميع بفضل الدعاء للمتوفى.<br>
            شارك الرابط مع أصدقائك وعائلتك ليكونوا جزءًا من هذا الأجر العظيم.
        </p>

        <div class="text-center mb-3 text-secondary fst-italic">
            نسأل الله أن يجزيك خير الجزاء على مشاركتك الطيبة ويثقل بها ميزان حسناتك.
        </div>

        <div class="share-buttons d-flex justify-content-center gap-3 flex-wrap">
            <a href="https://wa.me/?text=<?= urlencode($shareText) ?>"
               target="_blank" rel="noopener" class="share-btn share-whatsapp"
               aria-label="شارك عبر واتساب">
                📱 واتساب
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($memorialUrl) ?>"
               target="_blank" rel="noopener" class="share-btn share-facebook"
               aria-label="شارك عبر فيسبوك">
                📘 فيسبوك
            </a>

            <a href="https://t.me/share/url?url=<?= urlencode($memorialUrl) ?>&text=<?= urlencode($shareText) ?>"
               target="_blank" rel="noopener" class="share-btn share-telegram"
               aria-label="شارك عبر تيليجرام">
                ✈️ تيليجرام
            </a>

            <button class="share-btn share-copy copy-link-btn"
                    data-url="<?= e($memorialUrl) ?>"
                    aria-label="نسخ رابط المشاركة">
                📋 نسخ الرابط
            </button>
        </div>
    </div>
</div>

    <!-- Create Your Own Memorial CTA Section -->
    <div class="card shadow-sm mb-4 memorial-cta-section">
        <div class="card-body text-center p-5">
            <h3 class="mb-4 fw-bold">عاوز تعمل نفس الصفحة التذكارية دي لحد عزيز فقدته؟</h3>
            <p class="lead mb-4">
                الموضوع مجاني وسهل ودايم علطول 💚
            </p>
            <div class="mb-4">
                <a href="<?= site_url('create.php') ?>" class="btn btn-primary btn-lg px-5 py-3">
                    أنشئ صفحة تذكارية الآن 🌿
                </a>
            </div>
            <hr class="my-4">
            <p class="text-muted mb-3">
                هدفنا هو فحص كل معلومة موجودة على الموقع بعناية فائقة، وتجنب أي افتراضات مشكوك فيها،<br>
                ونحن مستعدون لقبول أي تعديل على أي محتوى قد يكون نتيجة لخطأ غير مقصود أو جهل.
            </p>
            <a href="<?= site_url('contact.php') ?>" class="btn btn-outline-primary">
                تواصل معنا 📧
            </a>
        </div>
    </div>

    <!-- Quran Radio Section -->
    <div class="card shadow-sm mb-4 quran-radio-section">
        <div class="card-body text-center p-4">
            <div class="radio-icon-wrapper mb-4">
                <div class="radio-icon">
                    📻
                </div>
            </div>
            <h3 class="mb-3 fw-bold">إذاعة القرآن الكريم</h3>
            <p class="text-muted mb-4">
                استمع إلى البث المباشر للقرآن الكريم على مدار الساعة
            </p>

            <div class="radio-controls">
                <audio id="quranRadio" preload="none">
                    <source src="https://stream.radiojar.com/8s5u5tpdtwzuv" type="audio/mpeg">
                    متصفحك لا يدعم تشغيل الصوت
                </audio>

                <div class="d-flex justify-content-center align-items-center gap-3">
                    <button id="playRadioBtn" class="btn btn-primary btn-lg px-5 py-3">
                        ▶️ تشغيل
                    </button>
                    <button id="pauseRadioBtn" class="btn btn-outline-primary btn-lg px-5 py-3" style="display: none;">
                        ⏸️ إيقاف مؤقت
                    </button>
                </div>

                <div class="volume-control mt-4">
                    <label for="radioVolume" class="form-label fw-bold">🔊 مستوى الصوت</label>
                    <input type="range" class="form-range" id="radioVolume" min="0" max="100" value="70">
                </div>
            </div>
        </div>
    </div>

    <!-- Ruqyah Section -->
    <div class="card shadow-sm mb-4 ruqyah-section">
        <div class="card-body text-center p-4">
            <div class="ruqyah-icon-wrapper mb-4">
                <div class="ruqyah-icon">🕌</div>
            </div>
            <h3 class="mb-3 fw-bold">الاستماع للرقية الشرعية</h3>
            <p class="text-muted mb-4">
                استمع إلى الرقية الشرعية من القرآن والسنة للحفظ والشفاء بإذن الله — تذكيراً وطمأنينة لقلوب الزائرين
            </p>

            <div class="ruqyah-player d-flex flex-column align-items-center gap-3">
                <button class="btn btn-primary ruqyah-play-btn">
                    <span class="play-icon">▶️</span>
                    <span class="pause-icon" style="display: none;">⏸️</span>
                </button>

                <audio id="ruqyahAudio" preload="none">
                    متصفحك لا يدعم تشغيل الصوت
                </audio>
            </div>
        </div>
    </div>


    <!-- Apps Section -->
    <div class="card shadow-sm mb-4 apps-promo-section">
        <div class="card-body p-4">
            <h3 class="text-center mb-4 fw-bold"> تطبيقاتنا الإسلامية 📱</h3>
            <p class="text-center text-muted mb-5">مكتبة إسلامية شاملة في جيبك - متاحة الآن على أندرويد و iOS</p>

            <div class="row g-4">
                <!-- Maktbti App -->
                <div class="col-md-6">
                    <div class="app-card">
                        <div class="app-icon-wrapper">
                            <div class="app-icon">
                                <img src="<?= BASE_URL ?>/assets/images/maktbti-logo.png" alt="Maktbti Logo"
                                    height="50">
                            </div>
                        </div>
                        <h4 class="app-title">مكتبتي</h4>
                        <p class="app-description">مكتبة إسلامية شاملة تحتوي على قصص الأنبياء، أذكار المسلم، رسائل
                            التفاؤل، والقرآن الكريم الكامل</p>
                        <div class="app-features mb-3">
                            <span class="feature-badge">✓ قصص الأنبياء</span>
                            <span class="feature-badge">✓ أذكار المسلم</span>
                            <span class="feature-badge">✓ القرآن الكريم</span>
                        </div>
                        <div class="app-platforms mb-3">
                            <span class="platform-badge android">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M2.76 3.061a.5.5 0 0 1 .679.2l1.283 2.352A8.94 8.94 0 0 1 8 5a8.94 8.94 0 0 1 3.278.613l1.283-2.352a.5.5 0 1 1 .878.478l-1.252 2.295C14.475 7.266 16 9.477 16 12H0c0-2.523 1.525-4.734 3.813-5.966L2.56 3.74a.5.5 0 0 1 .2-.678Z" />
                                </svg>
                                Android
                            </span>
                        </div>
                        <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-primary w-100 app-download-btn">
                            <span>تحميل التطبيق</span>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path
                                    d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                <path
                                    d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Maktbti Plus App -->
                <div class="col-md-6">
                    <div class="app-card featured">
                        <div class="featured-badge">الأكثر شعبية</div>
                        <div class="app-icon-wrapper">
                            <div class="app-icon plus">
                                <img src="<?= BASE_URL ?>/assets/images/maktbti-plus-logo.png" alt="Maktbti Logo"
                                    height="50">
                            </div>
                        </div>
                        <h4 class="app-title">مكتبتي بلس</h4>
                        <p class="app-description">النسخة المتقدمة والشاملة - مكتبة إسلامية متكاملة مع مميزات إضافية،
                            تصميم أنيق، وطريقة استخدام سلسة</p>
                        <div class="app-features mb-3">
                            <span class="feature-badge">✓ أذكار وأدعية</span>
                            <span class="feature-badge">✓ مواقيت الصلاة</span>
                            <span class="feature-badge">✓ القرآن الكريم</span>
                        </div>
                        <div class="app-platforms mb-3">
                            <span class="platform-badge android">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M2.76 3.061a.5.5 0 0 1 .679.2l1.283 2.352A8.94 8.94 0 0 1 8 5a8.94 8.94 0 0 1 3.278.613l1.283-2.352a.5.5 0 1 1 .878.478l-1.252 2.295C14.475 7.266 16 9.477 16 12H0c0-2.523 1.525-4.734 3.813-5.966L2.56 3.74a.5.5 0 0 1 .2-.678Z" />
                                </svg>
                                Android
                            </span>
                            <span class="platform-badge ios">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path
                                        d="M11.182.008C11.148-.03 9.923.023 8.857 1.18c-1.066 1.156-.902 2.482-.878 2.516.024.034 1.52.087 2.475-1.258.955-1.345.762-2.391.728-2.43Zm3.314 11.733c-.048-.096-2.325-1.234-2.113-3.422.212-2.189 1.675-2.789 1.698-2.854.023-.065-.597-.79-1.254-1.157a3.692 3.692 0 0 0-1.563-.434c-.108-.003-.483-.095-1.254.116-.508.139-1.653.589-1.968.607-.316.018-1.256-.522-2.267-.665-.647-.125-1.333.131-1.824.328-.49.196-1.422.754-2.074 2.237-.652 1.482-.311 3.83-.067 4.56.244.729.625 1.924 1.273 2.796.576.984 1.34 1.667 1.659 1.899.319.232 1.219.386 1.843.067.502-.308 1.408-.485 1.766-.472.357.013 1.061.154 1.782.539.571.197 1.111.115 1.652-.105.541-.221 1.324-1.059 2.238-2.758.347-.79.505-1.217.473-1.282Z" />
                                </svg>
                                iOS
                            </span>
                        </div>
                        <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank"
                            class="btn btn-primary w-100 mb-2 app-download-btn d-flex align-items-center justify-content-center gap-2">
                            <span>تحميل التطبيق</span>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"
                                focusable="false">
                                <path
                                    d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                <path
                                    d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                            </svg>
                        </a>

                        <a href="<?= APP_MAKTBTI_PLUS_IOS ?>" target="_blank"
                            class="btn btn-primary w-100 app-download-btn d-flex align-items-center justify-content-center gap-2">
                            <span>تحميل للآيفون</span>
                            <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"
                                focusable="false">
                                <path
                                    d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z" />
                                <path
                                    d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z" />
                            </svg>
                        </a>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Azkar Image Modal -->
<div class="modal fade" id="azkarModal" tabindex="-1" aria-labelledby="azkarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="azkarModalLabel">أذكار</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-3">
                <img id="azkarModalImage" src="" alt="أذكار" class="azkar-image">
            </div>
        </div>
    </div>
</div>

<!-- Duaa Image Modal -->
<div class="modal fade" id="duaaImageModal" tabindex="-1" aria-labelledby="duaaImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="duaaImageModalLabel">بطاقة الدعاء</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <div class="modal-body p-3 text-center">
                <img id="duaaModalImage" src="" alt="بطاقة دعاء" class="img-fluid rounded shadow"
                    style="max-height: 80vh;">
            </div>
            <div class="modal-footer justify-content-center">
                <a id="duaaDownloadBtn" href="" download="" class="btn btn-success">
                    💾 تحميل البطاقة
                </a>
                <button class="btn btn-outline-primary" onclick="copyDuaaImageLink()">
                    📋 نسخ الرابط
                </button>
                <button class="btn btn-outline-secondary" onclick="shareDuaaImageFromModal()">
                    📤 مشاركة
                </button>
            </div>
        </div>
    </div>
</div>



<?php include __DIR__ . '/../includes/yaseen_modal.php'; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
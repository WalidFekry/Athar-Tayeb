<?php
/**
 * Developer Page
 * About the site developer, video, and project information
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

$pageTitle = 'عن المطور وليد فكري — قصة البداية والأثر | ' . SITE_NAME;
$pageDescription = 'أنا وليد، بدأت بحب التكنولوجيا وبناء تطبيقات نافعة كالقرآن والأذكار. النجاح مش بس في اللي بتوصله، لكن في الأثر اللي بتسيبه. شاهد كلمة المطور وتعرف على قصة منصة أثر طيب.';
$pageImage = BASE_URL . '/assets/images/video-poster-2.png';
$pageUrl = BASE_URL . '/developer';

$videoUrl = BASE_URL . '/assets/videos/walid-fekry.mp4';
$posterUrl = BASE_URL . '/assets/images/video-poster-2.png';
$videoDurationIso = 'PT1M1S';
$videoUploadDate = '2026-11-12T01:28:00+02:00';

$videoTranscript = "أنا وليد... مش هقول إني وصلت، ولا إني حققت كل اللي بحلم بيه... بس أقدر أقول إني بدأت. بدأت بحب التكنولوجيا، وفضول إني أفهم الحاجات بتشتغل إزاي... وإزاي أحوّل فكرة بسيطة لحاجة حقيقية الناس تستخدمها وتستفيد منها. ومع الوقت، البرمجة مبقتش مجرد شغل... بقت وسيلتي إني أبني حاجة ليها معنى. عملت تطبيقات، ومواقع، واشتغلت على أفكار كتير... من القرآن والأذكار، لحد أدوات ومشاريع هدفها تسهّل حياة الناس. اتعلمت أبرمج، وأدير سيرفرات، وأسوق شغلي، وأواجه المشاكل بنفسي... وكل مرة كانت بتعلمني حاجة جديدة. أكيد الطريق مش سهل... وفيه حاجات كتير لسه متعملتش، وأحلام لسه مستنية دورها. لكن يمكن أجمل حاجة في الرحلة دي كلها... إني مؤمن إن النجاح مش بس في اللي بتوصله، لكن في الأثر اللي بتسيبه. أنا لسه في البداية... واللي جاي، بإذن الله، أحلى.";

// Custom Open Graph & Twitter Cards for Video SEO
$ogTags = '
    <meta property="og:title" content="' . e($pageTitle) . '">
    <meta property="og:description" content="' . e($pageDescription) . '">
    <meta property="og:type" content="video.other">
    <meta property="og:site_name" content="' . e(SITE_NAME) . '">
    <meta property="og:locale" content="ar_AR">
    <meta property="og:url" content="' . e($pageUrl) . '">
    <meta property="og:image" content="' . e($posterUrl) . '">
    <meta property="og:image:secure_url" content="' . e($posterUrl) . '">
    <meta property="og:image:type" content="image/png">
    <meta property="og:image:width" content="1080">
    <meta property="og:image:height" content="1920">
    <meta property="og:image:alt" content="وليد فكري - قصة البداية والأثر">
    <meta property="og:video" content="' . e($videoUrl) . '">
    <meta property="og:video:secure_url" content="' . e($videoUrl) . '">
    <meta property="og:video:type" content="video/mp4">
    <meta property="og:video:width" content="1080">
    <meta property="og:video:height" content="1920">
    <meta property="video:duration" content="61">
    <meta property="video:release_date" content="' . e($videoUploadDate) . '">
    <meta name="twitter:card" content="player">
    <meta name="twitter:title" content="' . e($pageTitle) . '">
    <meta name="twitter:description" content="' . e($pageDescription) . '">
    <meta name="twitter:image" content="' . e($posterUrl) . '">
    <meta name="twitter:player" content="' . e($pageUrl) . '">
    <meta name="twitter:player:width" content="1080">
    <meta name="twitter:player:height" content="1920">
';

// Schema.org VideoObject + Person for SEO
$structuredData = '<script type="application/ld+json">' . json_encode([
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Person',
            '@id' => $pageUrl . '#person',
            'name' => 'وليد فكري',
            'alternateName' => 'Walid Fekry',
            'jobTitle' => 'Software Developer',
            'description' => 'مطور برمجيات ومبرمج متخصص في تطوير تطبيقات الويب والموبايل، مطور منصة أثر طيب وتطبيقات إسلامية وخيرية.',
            'image' => BASE_URL . '/assets/images/profile-picture.png',
            'url' => $pageUrl,
            'sameAs' => [
                'https://github.com/WalidFekry',
                'https://github.com/WalidFekry/Athar-Tayeb'
            ],
            'knowsAbout' => ['PHP', 'JavaScript', 'MySQL', 'Bootstrap', 'Android', 'Islamic Web Apps']
        ],
        [
            '@type' => 'VideoObject',
            '@id' => $pageUrl . '#video',
            'name' => 'أنا وليد — قصة البداية والأثر',
            'description' => $pageDescription,
            'thumbnailUrl' => [
                $posterUrl,
                BASE_URL . '/assets/images/profile-picture.png'
            ],
            'uploadDate' => $videoUploadDate,
            'duration' => $videoDurationIso,
            'contentUrl' => $videoUrl,
            'embedUrl' => $pageUrl,
            'inLanguage' => 'ar',
            'caption' => 'قصة البداية والأثر - وليد فكري',
            'transcript' => $videoTranscript,
            'author' => [
                '@id' => $pageUrl . '#person'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => SITE_NAME,
                'url' => BASE_URL,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => BASE_URL . '/assets/images/icon-512.png'
                ]
            ]
        ]
    ]
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5" id="main-content">
    <div class="row">
        <div class="col-lg-10 mx-auto">

            <!-- Page Header -->
            <div class="text-center mb-5">
                <div class="developer-page-icon mb-3">💻</div>
                <h1 class="fw-bold">عن المطور</h1>
                <p class="lead text-muted">
                    قصة المشروع والقائم عليه
                </p>
            </div>

            <!-- Developer Profile Card -->
            <div class="card shadow-sm mb-4 developer-profile-card">
                <div class="card-body p-4 p-md-5">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center mb-4 mb-md-0">
                            <div class="developer-photo-placeholder">
                                <img src="<?= BASE_URL ?>/assets/images/profile-picture.png" alt="وليد فكري"
                                    class="developer-photo"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="photo-placeholder-icon" style="display: none;">
                                    👨‍💻
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <h2 class="fw-bold text-primary mb-3">وليد فكري</h2>
                            <p class="lead mb-3">
                                مطور برمجيات ومبرمج متخصص في تطوير تطبيقات الويب والموبايل
                            </p>
                            <div class="developer-skills">
                                <span class="skill-badge">PHP</span>
                                <span class="skill-badge">JavaScript</span>
                                <span class="skill-badge">MySQL</span>
                                <span class="skill-badge">Bootstrap</span>
                                <span class="skill-badge">Android</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Developer Video Section (Clean & Simple) -->
            <section class="card shadow-sm mb-4 developer-video-card" id="developerStoryVideo"
                aria-labelledby="videoSectionTitle">
                <div class="card-body p-4 p-md-5 text-center">
                    <div class="video-header mb-4">
                        <span
                            class="badge bg-success-subtle text-success px-3 py-2 rounded-pill fw-semibold mb-2 d-inline-block">
                            🎬 رسالة مصوّرة
                        </span>
                        <h2 class="h3 fw-bold text-primary mb-2" id="videoSectionTitle">
                            <span class="section-icon">🎙️</span>
                            قصة البداية والأثر
                        </h2>
                    </div>

                    <!-- Centered Video Player in Phone Frame -->
                    <div class="video-showcase-wrapper">
                        <div class="video-phone-frame">
                            <div class="video-phone-notch" aria-hidden="true"></div>
                            <video id="developerVideo" class="developer-video-player" controls playsinline
                                preload="metadata" poster="<?= e($posterUrl) ?>"
                                aria-label="فيديو قصة البداية والأثر - وليد فكري">
                                <source src="<?= e($videoUrl) ?>" type="video/mp4">
                                عذراً، متصفحك لا يدعم تشغيل الفيديو مباشرة. يمكنك <a href="<?= e($videoUrl) ?>"
                                    download>تحميل الفيديو من هنا</a>.
                            </video>
                            <button type="button" class="video-custom-play-btn" id="customPlayBtn"
                                aria-label="تشغيل الفيديو">
                                <span class="play-btn-ripple" aria-hidden="true"></span>
                                <span class="play-btn-icon" aria-hidden="true">▶</span>
                            </button>
                        </div>
                    </div>

                    <!-- Simple Golden Quote -->
                    <div class="video-quote-simple mt-4">
                        <blockquote class="mb-1 fw-bold fs-5 text-primary">
                            ❝ النجاح مش بس في اللي بتوصله، لكن في الأثر اللي بتسيبه. ❞
                        </blockquote>
                    </div>
                </div>
            </section>

            <!-- About the Project -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-primary mb-4">
                        <span class="section-icon">🌟</span>
                        عن المشروع
                    </h3>
                    <p class="mb-3">
                        هذا العمل خيري بحت وغير ربحي، وقد تَحَمَّل المطوّر كامل التكاليف والمصروفات البرمجية لإطلاق هذا
                        الموقع ابتغاء وجه الله تعالى.
                    </p>
                    <p class="mb-0">
                        جميع صفحات هذا الموقع غير قابلة للبيع أو للاستخدام التجاري تحت أي مسمّى.
                    </p>
                </div>
            </div>

            <!-- Project Features -->
            <div class="row g-4 mb-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🌟</div>
                        <h5 class="fw-bold">عمل خيري</h5>
                        <p>مشروع مجاني بالكامل لوجه الله تعالى، بدون أي أهداف ربحية</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">💚</div>
                        <h5 class="fw-bold">صدقة جارية</h5>
                        <p>كل صفحة تذكارية هي صدقة جارية إلكترونية تبقى بعد الرحيل</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h5 class="fw-bold">غير تجاري</h5>
                        <p>الموقع غير قابل للبيع أو الاستخدام التجاري بأي شكل</p>
                    </div>
                </div>
            </div>

            <!-- Developer Mission -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h3 class="fw-bold text-primary mb-4">
                        <span class="section-icon">🎯</span>
                        الرؤية والرسالة
                    </h3>
                    <div class="mission-content">
                        <div class="mission-item">
                            <h5 class="fw-bold">💡 الهدف</h5>
                            <p>توفير منصة رقمية سهلة ومجانية لإنشاء صفحات تذكارية للمتوفين، تساعد الأهل والأصدقاء على
                                الدعاء والذكر والقرآن لمن فارقونا.</p>
                        </div>
                        <div class="mission-item">
                            <h5 class="fw-bold">🚀 الطموح</h5>
                            <p>أن يكون هذا المشروع صدقة جارية تنفع الأحياء والأموات، وأن يستمر نفعه بإذن الله تعالى.</p>
                        </div>
                        <div class="mission-item">
                            <h5 class="fw-bold">🤝 المساهمة</h5>
                            <p>المشروع مفتوح المصدر على GitHub، ونرحب بأي مساهمات أو اقتراحات لتطوير المنصة.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Prayer Section -->
            <div class="card shadow-sm prayer-card">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div class="prayer-icon mb-3">🤲</div>
                        <h3 class="fw-bold text-primary">نرجوا الدعاء للقائم على هذا العمل</h3>
                        <p class="text-muted">لتكون صدقة في محياه وبعد مماته</p>
                    </div>

                    <div class="prayer-text">
                        <div class="prayer-item">
                            <p>اللهم اجعل هذا العمل الذي قام به عبدك وليد فكري في ميزان حسناته، وبارك له في عمله ووقته،
                                واجعل كل سطر وحرف من هذا الموقع شهادة له بالخير وحجة له يوم القيامة.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم اجعل عمله خالصًا لوجهك الكريم، وارزقه الإبداع والتميز في كل عمله، ووفّقه لما تحب
                                وترضى.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم بارك له في علمه، وزد في فكره، وارزقه الإلهام والتوفيق والنجاح في كل ما يسعى إليه.
                            </p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم ارزقه رزقًا حلالًا طيبًا مباركًا فيه، وافتح له أبواب الخير والرزق، واكفه بحلالك عن
                                حرامك واغنه بفضلك عمن سواك.</p>
                        </div>

                        <div class="prayer-item">
                            <p>اللهم اجعله وأهله وأحباءه وأصدقاءه من أهل الجنة، واغفر لهم ما تقدم من ذنبهم وما تأخر،
                                وزدهم من فضلك ونعيمك.</p>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <p class="text-muted mb-0">
                            <strong>آمين يا رب العالمين</strong>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Developer Page Styles */
    .developer-page-icon {
        font-size: 4rem;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }

    .developer-profile-card {
        border: 2px solid var(--primary);
        background: linear-gradient(135deg, var(--muted-bg) 0%, var(--card-bg) 100%);
    }

    .developer-photo-placeholder {
        position: relative;
        width: 200px;
        height: 200px;
        margin: 0 auto;
        border-radius: 50%;
        overflow: hidden;
        border: 4px solid var(--primary);
        box-shadow: var(--shadow);
    }

    .developer-photo {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .photo-placeholder-icon {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 5rem;
        background: linear-gradient(135deg, var(--primary) 0%, #6a9d5f 100%);
        color: white;
    }

    .developer-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .skill-badge {
        display: inline-block;
        padding: 0.5rem 1rem;
        background-color: var(--primary);
        color: white;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .section-icon {
        font-size: 1.5rem;
        margin-left: 0.5rem;
    }

    .feature-card {
        background-color: var(--card-bg);
        border: 2px solid var(--border);
        border-radius: var(--radius);
        padding: 2rem;
        text-align: center;
        height: 100%;
        transition: var(--transition);
    }

    .feature-card:hover {
        border-color: var(--primary);
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .feature-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }

    .feature-card h5 {
        color: var(--primary);
        margin-bottom: 1rem;
    }

    .feature-card p {
        color: var(--muted-text);
        line-height: 1.6;
        margin: 0;
    }

    .mission-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .mission-item h5 {
        color: var(--primary);
        margin-bottom: 0.75rem;
    }

    .mission-item p {
        color: var(--text);
        line-height: 1.8;
        margin: 0;
    }

    .prayer-card {
        background: linear-gradient(135deg, rgba(90, 125, 78, 0.05) 0%, var(--card-bg) 100%);
        border: 2px solid var(--primary);
    }

    .prayer-icon {
        font-size: 3rem;
    }

    .prayer-text {
        max-width: 800px;
        margin: 0 auto;
    }

    .prayer-item {
        padding: 1.25rem;
        margin-bottom: 1rem;
        background-color: rgba(255, 255, 255, 0.5);
        border-right: 4px solid var(--primary);
        border-radius: 8px;
        transition: var(--transition);
    }

    [data-theme="dark"] .prayer-item {
        background-color: rgba(0, 0, 0, 0.2);
    }

    .prayer-item:hover {
        background-color: rgba(90, 125, 78, 0.1);
        transform: translateX(-5px);
    }

    .prayer-item p {
        color: var(--text);
        font-size: 1.05rem;
        line-height: 2;
        margin: 0;
        font-family: var(--font-ar);
    }

    /* ============================================
       Video Section Styles (Simple, Centered & Clean)
       ============================================ */
    .developer-video-card {
        border: 2px solid var(--primary);
        border-radius: var(--radius);
        background: linear-gradient(135deg, var(--card-bg) 0%, var(--muted-bg) 100%);
        overflow: hidden;
    }

    .video-showcase-wrapper {
        position: relative;
        max-width: 320px;
        margin: 0 auto;
    }

    /* Vertical Mobile Frame (9:16 Aspect Ratio) */
    .video-phone-frame {
        position: relative;
        width: 100%;
        aspect-ratio: 9 / 16;
        background: #000;
        border-radius: 26px;
        border: 5px solid var(--border);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18), 0 0 0 1px rgba(90, 125, 78, 0.2);
        overflow: hidden;
        transition: var(--transition);
    }

    [data-theme="dark"] .video-phone-frame {
        border-color: #3A4139;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(157, 179, 123, 0.3);
    }

    .video-phone-frame:hover {
        border-color: var(--primary);
        transform: translateY(-3px);
        box-shadow: 0 16px 36px rgba(90, 125, 78, 0.25);
    }

    .video-phone-notch {
        position: absolute;
        top: 8px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 5px;
        background: rgba(255, 255, 255, 0.25);
        border-radius: 4px;
        z-index: 5;
        pointer-events: none;
    }

    .developer-video-player {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        background: #000;
    }

    /* Custom Play Button Overlay */
    .video-custom-play-btn {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: rgba(90, 125, 78, 0.92);
        color: #FFFFFF;
        border: 3px solid rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        z-index: 4;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
    }

    .video-custom-play-btn:hover {
        background: var(--primary);
        transform: translate(-50%, -50%) scale(1.08);
    }

    .play-btn-icon {
        font-size: 1.8rem;
        line-height: 1;
        margin-right: -3px;
        /* visual center for triangle */
    }

    .play-btn-ripple {
        position: absolute;
        inset: -8px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.5);
        animation: pulsePlay 2s infinite ease-out;
        pointer-events: none;
    }

    @keyframes pulsePlay {
        0% {
            transform: scale(0.95);
            opacity: 0.8;
        }

        70% {
            transform: scale(1.3);
            opacity: 0;
        }

        100% {
            transform: scale(1.3);
            opacity: 0;
        }
    }

    .video-custom-play-btn.is-hidden {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }

    .video-quote-simple blockquote {
        font-family: var(--font-ar);
        line-height: 1.7;
    }

    /* ============================================
       Media Queries (Phones, Tablets & Small Screens)
       ============================================ */
    @media (max-width: 768px) {
        .developer-page-icon {
            font-size: 3rem;
        }

        .developer-photo-placeholder {
            width: 150px;
            height: 150px;
        }

        .photo-placeholder-icon {
            font-size: 4rem;
        }

        .skill-badge {
            font-size: 0.85rem;
            padding: 0.4rem 0.8rem;
        }

        .video-showcase-wrapper {
            max-width: 280px;
        }

        .video-custom-play-btn {
            width: 66px;
            height: 66px;
        }

        .play-btn-icon {
            font-size: 1.5rem;
        }

        .feature-card {
            padding: 1.5rem;
        }

        .feature-icon {
            font-size: 2.5rem;
        }

        .prayer-item {
            padding: 1rem;
        }

        .prayer-item p {
            font-size: 0.95rem;
            line-height: 1.8;
        }
    }

    /* Small Phones (iPhone SE, Galaxy A, screens <= 360px) */
    @media (max-width: 360px) {
        .video-showcase-wrapper {
            max-width: 240px;
        }

        .video-custom-play-btn {
            width: 58px;
            height: 58px;
        }

        .play-btn-icon {
            font-size: 1.3rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const video = document.getElementById('developerVideo');
        const customPlayBtn = document.getElementById('customPlayBtn');

        if (!video) return;

        function hidePlayOverlay() {
            if (customPlayBtn) {
                customPlayBtn.classList.add('is-hidden');
            }
        }

        function showPlayOverlay() {
            if (customPlayBtn && video.paused) {
                customPlayBtn.classList.remove('is-hidden');
            }
        }

        if (customPlayBtn) {
            customPlayBtn.addEventListener('click', function () {
                if (video.paused) {
                    video.play();
                } else {
                    video.pause();
                }
            });
        }

        video.addEventListener('play', hidePlayOverlay);
        video.addEventListener('playing', hidePlayOverlay);
        video.addEventListener('pause', showPlayOverlay);
        video.addEventListener('ended', showPlayOverlay);
    });
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
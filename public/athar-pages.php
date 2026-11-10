<?php
/**
 * Athar Pages Intro Page
 * Static page explaining what memorial pages are and how they work
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

// Page metadata
$pageTitle = 'ما هي صفحات أثر طيب؟ وكيف تعمل؟ — ' . SITE_NAME;
$pageDescription = 'تعريف مبسّط بفكرة الصفحات التذكارية في أثر طيب وكيف تجمع الدعاء والتسبيح والقرآن في مكان واحد.';

// SEO Structured Data for Video
$structuredData = getVideoStructuredData(site_url('athar-pages'));

include __DIR__ . '/../includes/header.php';
?>

<div class='container my-5'>
    <div class='row'>
        <div class='col-lg-10 mx-auto'>

            <div class='text-center mb-5'>
                <h1 class='fw-bold'>ما هي صفحات «أثر طيب»؟ وكيف تعمل؟ 🌿</h1>
                <p class='lead text-muted'>
                    هذه الصفحة موجهة لك إن كانت هذه أول مرة تسمع فيها عن «أثر طيب» أو عن فكرة الصفحات التذكارية الرقمية.
                </p>
            </div>

            <!-- Video Showcase Section -->
            <?php
            $videoTitle = 'كيف تعمل صفحات «أثر طيب»؟ 🌿';
            $videoSubtitle = 'شاهد هذا الشرح السريع (دقيقة واحدة) لتتعرّف عمليًا على تجربة الصفحة التذكارية ومزاياها لمن تحب.';
            $videoShowCta = false;
            $videoSectionId = 'athar-pages-video';
            include __DIR__ . '/../includes/video_showcase.php';
            ?>

            <div class='card shadow-sm mb-4'>
                <div class='card-body p-4'>
                    <h2 class='h4 mb-3'>يعني إيه صفحة تذكارية؟</h2>
                    <p>
                        صفحة تذكارية هي صفحة خاصة بمتوفى واحد، تجمع في مكان واحد: الدعاء، التسبيح، قراءة القرآن،
                        والأذكار، بحيث يكون كل من يدخلها شريكًا في الأجر بإذن الله.
                    </p>
                    <p>
                        بدل ما يكون الدعاء مرة واحدة في بوست على السوشيال، الصفحة تفضل موجودة، ويمكن الرجوع لها في أي
                        وقت،
                        ومشاركتها مع العائلة والأصدقاء بسهولة.
                    </p>
                </div>
            </div>

            <div class='row g-4 mb-4'>
                <div class='col-md-6'>
                    <div class='card h-100 shadow-sm'>
                        <div class='card-body p-4'>
                            <h3 class='h5 mb-3'>كيف تجمع الصفحة بين الدعاء والتسبيح والقرآن؟ 📿📖</h3>
                            <ul class='mb-0'>
                                <li>عدادات تسبيح إلكترونية يمكن للجميع استخدامها للذكر بنية المتوفى.</li>
                                <li>أدعية مختارة للميت مع إمكانية قراءتها أو الاستماع إليها.</li>
                                <li>قسم قرآن وأذكار الصباح والمساء لتسهيل الطاعة وربطها بذكرى من نحب.</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class='col-md-6'>
                    <div class='card h-100 shadow-sm'>
                        <div class='card-body p-4'>
                            <h3 class='h5 mb-3'>إيه الفرق بينها وبين بوست دعاء عادي على السوشيال؟ 🔄</h3>
                            <ul class='mb-0'>
                                <li>البوست يختفي مع الوقت في التايم لاين، لكن الصفحة التذكارية ثابتة ويمكن الرجوع لها
                                    دائمًا.</li>
                                <li>الصفحة مصممة بالكامل للدعاء والذكر، بدون تشتيت أو إعلانات أو تعليقات خارجة.</li>
                                <li>يمكن مشاركتها بسهولة في أي وقت، وجمع كل الدعاء والتسبيح في مكان واحد منظم.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class='mb-4'>
                <h2 class='h4 mb-3'>أمثلة على استخدام الصفحات التذكارية 💚</h2>
                <div class='row g-3'>
                    <div class='col-md-4'>
                        <div class='card h-100 border-0 shadow-sm bg-light'>
                            <div class='card-body'>
                                <h3 class='h6 fw-bold mb-2'>صفحة لأب أو أم</h3>
                                <p class='small text-muted mb-0'>
                                    يجتمع الأبناء والأحفاد على صفحة واحدة يدعون فيها باستمرار لوالديهم، ويتذكرونهم
                                    بكلمات طيبة
                                    وصور محترمة.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class='col-md-4'>
                        <div class='card h-100 border-0 shadow-sm bg-light'>
                            <div class='card-body'>
                                <h3 class='h6 fw-bold mb-2'>صفحة لصديق عزيز</h3>
                                <p class='small text-muted mb-0'>
                                    ينشئها الأصدقاء معًا لصديقهم المتوفى، ويتفقون على قراءة أذكار أو تسبيح كل جمعة
                                    بنية إهداء الأجر له.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class='col-md-4'>
                        <div class='card h-100 border-0 shadow-sm bg-light'>
                            <div class='card-body'>
                                <h3 class='h6 fw-bold mb-2'>صفحة لشهيد أو قريب غالٍ</h3>
                                <p class='small text-muted mb-0'>
                                    تكون بمثابة ذاكرة طيبة يجتمع فيها الدعاء وذكر المواقف الجميلة، بعيدًا عن الجدل أو
                                    النقاشات
                                    التي قد تحدث على مواقع التواصل.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='card memorial-cta-section mt-4'>
                <div class='card-body p-4 text-center'>
                    <h2 class='h4 mb-3'>جرّب إنشاء أول صفحة تذكارية الآن 🌱</h2>
                    <p class='mb-4 text-muted'>
                        لا تحتاج لتسجيل أو خطوات معقدة، فقط دقائق بسيطة لتكتب اسم من تحب وبعض المعلومات عنه،
                        وستحصل على صفحة يمكن مشاركتها مع من تريد.
                    </p>
                    <a href='<?= site_url('create') ?>' class='btn btn-primary btn-lg px-5'>
                        أنشئ صفحة تذكارية جديدة
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
<?php
/**
 * Video Showcase Component - Athar Tayeb
 * Reusable vertical video showcase with phone frame mockup and lazy-load facade
 */

$videoTitle = $videoTitle ?? 'شاهد كيف تعمل منصة «أثر طيب» في دقيقة واحدة 🌿';
$videoSubtitle = $videoSubtitle ?? 'تعرّف بالصوت والصورة على فكرة الصفحات التذكارية وكيف تجمع الأجر والدعاء والتسبيح والقرآن لمن تحب.';
$videoShowCta = $videoShowCta ?? true;
$videoSectionId = $videoSectionId ?? 'video-showcase';
?>
<section class="athar-video-section my-4" id="<?= e($videoSectionId) ?>"
    aria-labelledby="athar-video-heading-<?= e($videoSectionId) ?>">
    <div class="athar-video-inner">
        <div class="row align-items-center g-4 g-lg-5">
            <!-- Text & Info Column -->
            <div class="col-lg-7 text-center text-lg-start">
                <div class="athar-video-badge">
                    <span aria-hidden="true">🎬</span>
                    <span>فيديو تعريفي سريع</span>
                </div>
                <h2 id="athar-video-heading-<?= e($videoSectionId) ?>" class="fw-bold mb-3 athar-video-title">
                    <?= e($videoTitle) ?>
                </h2>
                <p class="lead text-muted mb-4 athar-video-desc">
                    <?= e($videoSubtitle) ?>
                </p>

                <ul class="athar-video-features">
                    <li>
                        <span class="feat-icon" aria-hidden="true">✓</span>
                        <div>
                            <strong>صفحة تذكارية دائمة:</strong> تجمع الدعاء والقرآن والأذكار في مكان واحد منظم وثابت
                            بدون تشتيت أو إعلانات.
                        </div>
                    </li>
                    <li>
                        <span class="feat-icon" aria-hidden="true">✓</span>
                        <div>
                            <strong>تسبيح إلكتروني تفاعلي:</strong> يشارك فيه كل زائر بنية المتوفى، وتُحفظ الحسنات كصدقة
                            جارية مستمرة.
                        </div>
                    </li>
                    <li>
                        <span class="feat-icon" aria-hidden="true">✓</span>
                        <div>
                            <strong>سهل ومجاني بالكامل:</strong> تُنشأ الصفحة في أقل من دقيقتين بدون الحاجة لتسجيل حساب
                            أو خطوات معقدة.
                        </div>
                    </li>
                </ul>

                <?php if ($videoShowCta): ?>
                    <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start mt-4">
                        <a href="<?= site_url('create') ?>" class="btn btn-primary btn-lg px-4 py-2"
                            aria-label="أنشئ صفحة تذكارية جديدة الآن">
                            أنشئ صفحة تذكارية الآن 💚
                        </a>
                        <a href="<?= site_url('athar-pages') ?>" class="btn btn-outline-secondary btn-lg px-4 py-2">
                            اعرف المزيد عن الصفحات
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Video Player & Phone Mockup Column -->
            <div class="col-lg-5">
                <div class="athar-phone-mockup">
                    <div class="athar-phone-speaker" aria-hidden="true"></div>
                    <div class="athar-phone-screen athar-video-player-container" data-video-id="athar-intro-video">

                        <!-- Facade (Poster + Play Button) - Zero video data loaded initially -->
                        <div class="athar-video-facade" role="button" tabindex="0"
                            aria-label="تشغيل الفيديو التعريفي لمنصة أثر طيب (مدته دقيقة واحدة)">
                            <img src="<?= BASE_URL ?>/assets/images/video-poster.png"
                                alt="غلاف الفيديو التعريفي لمنصة أثر طيب" class="athar-video-poster-img" loading="lazy"
                                width="1080" height="1920">

                            <div class="athar-play-button-wrap">
                                <div class="athar-play-button" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" focusable="false" aria-hidden="true">
                                        <path d="M8 5v14l11-7z" />
                                    </svg>
                                </div>
                                <span class="athar-play-text">تشغيل الشرح (0:58 د)</span>
                            </div>

                            <span class="athar-video-duration-badge" aria-hidden="true">
                                ⏱️ 0:58 د
                            </span>
                        </div>

                        <!-- Loading Spinner -->
                        <div class="athar-video-spinner" aria-hidden="true"></div>

                        <!-- Actual HTML5 Video - src attached ONLY upon user click -->
                        <video class="athar-video-element" data-src="<?= BASE_URL ?>/assets/videos/athar-tayeb.mp4"
                            controls playsinline preload="none" aria-label="فيديو تعريفي عن فكرة منصة أثر طيب">
                            متصفحك لا يدعم تشغيل الفيديو. يمكنك تحميله من <a
                                href="<?= BASE_URL ?>/assets/videos/athar-tayeb.mp4">هذا الرابط</a>.
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
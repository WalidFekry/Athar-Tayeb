</main>

<!-- Footer -->
<footer class="footer mt-5 py-4">
    <div class="container">
        <div class="row g-4">
            <!-- About Section -->
            <div class="col-lg-3 col-md-6 footer-section">
                <h5 class="fw-bold mb-3">
                    <span class="footer-icon">🌿</span>
                    <?= SITE_NAME ?>
                </h5>
                <p class="small">
                    <?= SITE_TAGLINE ?>
                </p>
                <p class="small">
                    منصة رقمية لإنشاء صفحات تذكارية للمتوفين - صدقة جارية تبقى بعد الرحيل
                </p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 footer-section">
                <h6 class="fw-bold mb-3">روابط سريعة</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= site_url('') ?>" class="footer-link small">الرئيسية</a></li>
                    <li><a href="<?= site_url('create') ?>" class="footer-link small">أنشئ صفحة تذكارية</a></li>
                    <li><a href="<?= site_url('all') ?>" class="footer-link small">جميع الصفحات</a></li>
                    <li><a href="<?= site_url('how-to-benefit') ?>" class="footer-link small">كيف تستفيد؟</a></li>
                </ul>
            </div>

            <!-- Apps Section -->
            <div class="col-lg-3 col-md-6 footer-section">
                <h6 class="fw-bold mb-3">تطبيقاتنا</h6>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= APP_MAKTBTI ?>" target="_blank" class="app-link">
                        📱 تطبيق مكتبتي
                    </a>
                    <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank" class="app-link">
                        📱 مكتبتي بلس
                    </a>
                </div>
            </div>

            <!-- Open Source Section -->
            <div class="col-lg-4 col-md-6 footer-section">
                <h6 class="fw-bold mb-3">مفتوح المصدر</h6>
                <div class="opensource-card">
                    <p class="small mb-3">
                        مشروع <strong>أثر طيب</strong> متاح مجاناً ومفتوح المصدر على GitHub
                    </p>
                    <a href="https://github.com/WalidFekry/Athar-Tayeb" target="_blank" rel="noopener noreferrer"
                        class="github-btn">
                        <svg class="github-icon" viewBox="0 0 24 24" fill="currentColor"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                        </svg>
                        <span class="github-btn-text">عرض على GitHub</span>
                        <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M5 12h14M12 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Global Statistics Section -->
        <?php
        $stats = getGlobalStatistics();
        ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="global-stats-section">
                    <h6 class="fw-bold text-center mb-3">إحصائيات أثر طيب 📊</h6>
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-icon">📿</div>
                                <div class="stat-value"><?= toArabicNumerals(number_format($stats['tasbeeh'])) ?></div>
                                <div class="stat-label">إجمالي التسبيحات</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-icon">🌿</div>
                                <div class="stat-value"><?= toArabicNumerals($stats['memorials']) ?></div>
                                <div class="stat-label">الصفحات التذكارية</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item">
                                <div class="stat-icon">👁️</div>
                                <div class="stat-value"><?= toArabicNumerals(number_format($stats['visits'])) ?></div>
                                <div class="stat-label">إجمالي الزيارات</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-4">

        <!-- Developer Page Link -->
        <div class="text-center mb-3">
            <a href="<?= site_url('developer') ?>" class="developer-page-link">
                <span class="developer-link-icon">👨‍💻</span>
                <span class="developer-link-text">عن المطوّر</span>
            </a>
        </div>

        <!-- Copyright -->
        <div class="row align-items-center">
            <div class="col-md-4 text-center text-md-start mb-2 mb-md-0">
                <p class="small mb-0">
                    © <?= date('Y') ?> <?= SITE_NAME ?> — صدقة جارية رقمية
                </p>
            </div>
            <div class="col-md-4 text-center mb-2 mb-md-0">
                <div class="d-flex justify-content-center gap-3">
                    <a href="<?= site_url('privacy') ?>" class="text-muted small text-decoration-none">سياسة
                        الخصوصية</a>
                    <span class="text-muted small">•</span>
                    <a href="<?= site_url('terms') ?>" class="text-muted small text-decoration-none">الشروط والأحكام</a>
                </div>
            </div>
            <div class="col-md-4 text-center text-md-end">
                <p class="small mb-0">
                    تصميم وتطوير: <a href="<?= DEVELOPER_URL ?>" target="_blank"
                        class="developer-link"><?= DEVELOPER_NAME ?></a>
                </p>
            </div>
        </div>
    </div>
</footer>

<!-- Back to Top Button -->
<button id="backToTop" aria-label="العودة للأعلى" title="العودة للأعلى">
    ↑
</button>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>

<!-- PWA Install Manager -->
<script src="<?= BASE_URL ?>/assets/js/pwa.js" defer></script>
</body>

</html>
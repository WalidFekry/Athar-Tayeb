    </main>
    
    <!-- Footer -->
    <footer class="footer mt-5 py-4 bg-light">
        <div class="container">
            <div class="row g-4">
                <!-- About -->
                <div class="col-md-4">
                    <h5 class="fw-bold text-primary mb-3">🌿 <?= SITE_NAME ?></h5>
                    <p class="text-muted small">
                        <?= SITE_TAGLINE ?>
                    </p>
                    <p class="text-muted small">
                        منصة رقمية لإنشاء صفحات تذكارية للمتوفين - صدقة جارية تبقى بعد الرحيل
                    </p>
                </div>
                
                <!-- Quick Links -->
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">روابط سريعة</h6>
                    <ul class="list-unstyled">
                        <li><a href="<?= BASE_URL ?>" class="text-muted text-decoration-none small">الرئيسية</a></li>
                        <li><a href="<?= BASE_URL ?>/create.php" class="text-muted text-decoration-none small">أنشئ صفحة تذكارية</a></li>
                        <li><a href="<?= BASE_URL ?>/all.php" class="text-muted text-decoration-none small">جميع الصفحات</a></li>
                        <li><a href="<?= BASE_URL ?>/contact.php" class="text-muted text-decoration-none small">تواصل معنا</a></li>
                    </ul>
                </div>
                
                <!-- Apps -->
                <div class="col-md-4">
                    <h6 class="fw-bold mb-3">تطبيقاتنا</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= APP_MAKTBTI ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            📱 تطبيق مكتبتي
                        </a>
                        <a href="<?= APP_MAKTBTI_PLUS ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            📱 مكتبتي بلس
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <!-- Copyright -->
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-muted small mb-0">
                        © <?= date('Y') ?> <?= SITE_NAME ?> — صدقة جارية رقمية
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-muted small mb-0">
                        تصميم وتطوير: <a href="<?= DEVELOPER_URL ?>" target="_blank" class="text-decoration-none"><?= DEVELOPER_NAME ?></a>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>

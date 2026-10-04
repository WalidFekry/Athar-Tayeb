<?php
/**
 * Public Header Template
 * Includes HTML head, navigation, and common assets
 */

// Default values if not set
$pageTitle = $pageTitle ?? SITE_NAME;
$pageDescription = $pageDescription ?? SITE_DESCRIPTION;
$pageImage = $pageImage ?? BASE_URL . '/assets/images/placeholder-memorial.png';
$pageUrl = $pageUrl ?? BASE_URL . $_SERVER['REQUEST_URI'];
$ogTags = $ogTags ?? '';
$structuredData = $structuredData ?? '';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($pageDescription) ?>">

    <!-- Open Graph / Social Media -->
    <?php if ($ogTags): ?>
        <?= $ogTags ?>
    <?php else: ?>
        <?= generateOGTags($pageTitle, $pageDescription, $pageImage, $pageUrl) ?>
    <?php endif; ?>

    <!-- Canonical URL -->
    <link rel="canonical" href="<?= e($pageUrl) ?>">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&family=Tajawal:wght@300;400;500;700&display=swap"
        rel="stylesheet">

    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/footer-styles.css">

    <!-- Structured Data -->
    <?= $structuredData ?>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/assets/images/icon-192.png">

    <!-- PWA Manifest -->
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#5A7D4E">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="أثر طيب">

    <!-- PWA Styles -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/pwa.css">

    <!-- Base URL for JavaScript -->
    <script>
        const BASEURL = '<?= BASE_URL ?>';
    </script>

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-6XFFFR8JXF"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());

        gtag('config', 'G-6XFFFR8JXF');
    </script>
</head>

<body>
    <!-- Skip to main content link for keyboard users -->
    <a href="#main-content" class="skip-link visually-hidden-focusable">انتقل إلى المحتوى الرئيسي</a>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top" role="navigation"
        aria-label="القائمة الرئيسية">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="<?= site_url('') ?>">
                <span class="fs-4 fw-bold text-primary">🌿 <?= SITE_NAME ?></span>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="فتح قائمة التنقل">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('') ?>">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('create') ?>">أنشئ صفحة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('all') ?>">جميع الصفحات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('guide') ?>">دليل الاستخدام</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('contact') ?>">تواصل معنا</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <!-- PWA Install Button -->
                    <button id="pwaInstallBtn"
                        type="button"
                        aria-label="تثبيت تطبيق أثر طيب"
                        title="ثبّت التطبيق على جهازك">
                        <span class="pwa-btn-icon" aria-hidden="true">📲</span>
                        <span class="pwa-btn-text">ثبّت التطبيق</span>
                    </button>

                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="btn btn-outline-secondary btn-sm"
                        aria-label="تبديل بين الوضع الليلي والنهاري" aria-pressed="false">
                        <span class="theme-icon" aria-hidden="true">🌙</span>
                    </button>

                    <!-- Search Icon (Mobile) -->
                    <button class="btn btn-outline-primary btn-sm d-lg-none" data-bs-toggle="modal"
                        data-bs-target="#searchModal" aria-label="فتح نافذة البحث">
                        <span aria-hidden="true">🔍</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Search Modal (for mobile) -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">البحث عن متوفى</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="إغلاق نافذة البحث"></button>
                </div>
                <div class="modal-body">
                    <form action="<?= site_url('search') ?>" method="GET" role="search">
                        <div class="input-group">
                            <label for="mobileSearchInput" class="visually-hidden">ابحث عن اسم المتوفى</label>
                            <input type="text" id="mobileSearchInput" name="q" class="form-control"
                                placeholder="ابحث عن اسم..." required aria-required="true">
                            <button class="btn btn-primary" type="submit">بحث</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Global Loading Overlay -->
    <div id="globalLoader" class="global-loader">
        <div class="loader-content">
            <div class="loader-logo">
                <span class="logo-icon">🌿</span>
                <span class="logo-text"><?= SITE_NAME ?></span>
            </div>
            <div class="loader-spinner">
                <div class="spinner-ring"></div>
            </div>
            <div class="loader-message">جاري التحميل..</div>
        </div>
    </div>

    <main id="main-content" role="main">
<?php
/**
 * Admin Settings
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$success = '';
$cleanupResult = '';
$orphanedCleanupResult = '';
$sitemapResult = '';

// Handle sitemap generation action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'generate_sitemap') {
    checkCSRF();

    try {
        // Get the site URL from config (remove /public if present)
        $siteUrl = preg_replace('~/public/?$~', '', BASE_URL);

        // Get current timestamp in ISO 8601 format
        $nowIso = date('c'); // ISO 8601 format with timezone

        // Start building sitemap XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '                            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";
        $xml .= '  <!-- Generated automatically on ' . date('Y-m-d H:i:s') . ' -->' . "\n";

        // Static pages with priorities
        $staticPages = [
            '' => ['priority' => '1.00'],
            'create' => ['priority' => '0.95'],
            'all' => ['priority' => '0.85'],
            'how-to-benefit' => ['priority' => '0.75'],
            'guide' => ['priority' => '0.75'],
            'contact' => ['priority' => '0.65'],
            'share-guide' => ['priority' => '0.60'],
            'mobile-guide' => ['priority' => '0.60'],
            'memorial-guide' => ['priority' => '0.60'],
            'duaa-etiquette' => ['priority' => '0.50'],
            'search' => ['priority' => '0.50'],
            'faq' => ['priority' => '0.45'],
            'athar-pages' => ['priority' => '0.40'],
            'privacy' => ['priority' => '0.35'],
            'terms' => ['priority' => '0.35'],
            'developer' => ['priority' => '0.20'],
        ];

        // Add static pages to sitemap
        foreach ($staticPages as $page => $config) {
            $url = rtrim($siteUrl, '/') . '/' . $page;
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($url) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $nowIso . '</lastmod>' . "\n";
            $xml .= '    <priority>' . $config['priority'] . '</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }

        // Get all published memorial pages (with updated_at)
        $stmt = $pdo->query("
            SELECT id, created_at, updated_at 
            FROM memorials 
            WHERE status = 1 
            ORDER BY id ASC
        ");
        $memorials = $stmt->fetchAll();

        $memorialCount = 0;
        foreach ($memorials as $memorial) {
            $memorialUrl = rtrim($siteUrl, '/') . '/m/' . $memorial['id'];

            // Use updated_at if available, otherwise created_at, otherwise current time
            if (!empty($memorial['updated_at'])) {
                $timestamp = $memorial['updated_at'];
            } elseif (!empty($memorial['created_at'])) {
                $timestamp = $memorial['created_at'];
            } else {
                $timestamp = null;
            }

            $memorialLastmod = $timestamp
                ? date('c', strtotime($timestamp))
                : $nowIso;

            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . htmlspecialchars($memorialUrl) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . $memorialLastmod . '</lastmod>' . "\n";
            $xml .= '    <priority>0.80</priority>' . "\n";
            $xml .= '  </url>' . "\n";

            $memorialCount++;
        }

        $xml .= '</urlset>' . "\n";

        // Delete old sitemap if exists
        $sitemapPath = __DIR__ . '/../sitemap.xml';
        if (file_exists($sitemapPath)) {
            unlink($sitemapPath);
        }

        // Write new sitemap
        $bytesWritten = file_put_contents($sitemapPath, $xml);

        if ($bytesWritten !== false) {
            $sitemapResult = [
                'success' => true,
                'static_pages' => count($staticPages),
                'memorial_pages' => $memorialCount,
                'total_urls' => count($staticPages) + $memorialCount,
                'file_size' => $bytesWritten,
                'generated_at' => date('Y-m-d H:i:s')
            ];
        } else {
            throw new Exception('فشل في كتابة ملف sitemap.xml');
        }

    } catch (Exception $e) {
        $sitemapResult = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}


// Handle orphaned images cleanup action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cleanup_orphaned') {
    checkCSRF();

    // Get all image filenames from database
    $stmt = $pdo->query("SELECT image FROM memorials WHERE image IS NOT NULL AND image != ''");
    $dbImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Convert to array for faster lookup
    $dbImagesArray = array_flip($dbImages);

    $uploadsPath = __DIR__ . '/../public/uploads/memorials/';
    $duaaPath = __DIR__ . '/../public/uploads/duaa_images/';

    $totalFound = 0;
    $deletedMain = 0;
    $deletedThumbs = 0;
    $deletedDuaa = 0;
    $failedCount = 0;

    // Scan uploads/memorials/ directory
    if (is_dir($uploadsPath)) {
        $files = scandir($uploadsPath);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..' || !is_file($uploadsPath . $file)) {
                continue;
            }

            // Skip thumbnail files for now, we'll handle them separately
            if (strpos($file, '_thumb.') !== false) {
                continue;
            }

            // Check if it's an image file
            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
                continue;
            }

            $totalFound++;

            // Check if this image exists in database
            if (!isset($dbImagesArray[$file])) {
                try {
                    // Delete main image
                    $mainImagePath = $uploadsPath . $file;
                    if (file_exists($mainImagePath)) {
                        unlink($mainImagePath);
                        $deletedMain++;
                    }

                    // Delete corresponding thumbnail
                    $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $mainImagePath);
                    if (file_exists($thumbPath)) {
                        unlink($thumbPath);
                        $deletedThumbs++;
                    }

                    // Delete corresponding duaa card image
                    $duaaImagePath = $duaaPath . $file;
                    if (file_exists($duaaImagePath)) {
                        unlink($duaaImagePath);
                        $deletedDuaa++;
                    }

                } catch (Exception $e) {
                    $failedCount++;
                }
            }
        }
    }

    $orphanedCleanupResult = [
        'total_found' => $totalFound,
        'deleted_main' => $deletedMain,
        'deleted_thumbs' => $deletedThumbs,
        'deleted_duaa' => $deletedDuaa,
        'failed' => $failedCount
    ];
}

// Handle cleanup action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cleanup') {
    checkCSRF();

    $days = (int) ($_POST['cleanup_days'] ?? 30);
    if ($days < 1)
        $days = 30;

    $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$days} days"));

    // Find inactive pages
    $stmt = $pdo->prepare("SELECT id, name, image FROM memorials WHERE last_visit < ? OR last_visit IS NULL");
    $stmt->execute([$cutoffDate]);
    $inactivePages = $stmt->fetchAll();

    $totalFound = count($inactivePages);
    $deletedCount = 0;
    $failedCount = 0;

    foreach ($inactivePages as $page) {
        try {
            // Delete image file if exists
            if ($page['image']) {
                $imagePath = __DIR__ . '/../public/uploads/memorials/' . $page['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                // Delete thumbnail if exists
                $ext = pathinfo($page['image'], PATHINFO_EXTENSION);
                $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }

                // Delete Duaa card if exists
                $duaaImagePath = __DIR__ . '/../public/uploads/duaa_images/' . $page['image'];
                if (file_exists($duaaImagePath)) {
                    unlink($duaaImagePath);
                }
            }

            // Delete record
            $deleteStmt = $pdo->prepare("DELETE FROM memorials WHERE id = ?");
            $deleteStmt->execute([$page['id']]);
            $deletedCount++;

        } catch (Exception $e) {
            $failedCount++;
        }
    }

    $cleanupResult = [
        'total' => $totalFound,
        'deleted' => $deletedCount,
        'failed' => $failedCount
    ];
}

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['action']) || ($_POST['action'] !== 'cleanup' && $_POST['action'] !== 'cleanup_orphaned' && $_POST['action'] !== 'generate_sitemap'))) {
    checkCSRF();

    $settings = [
        'auto_approval' => isset($_POST['auto_approval']) && $_POST['auto_approval'] === '1' ? '1' : '0',
        'maintenance_mode' => isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] === '1' ? '1' : '0',
        'auto_approve_messages' => isset($_POST['auto_approve_messages']) && $_POST['auto_approve_messages'] === '1' ? '1' : '0',
        'auto_approve_images' => isset($_POST['auto_approve_images']) && $_POST['auto_approve_images'] === '1' ? '1' : '0',
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    $success = 'تم حفظ الإعدادات بنجاح';
}


// Fetch current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$autoApproval = isset($settingsData['auto_approval']) ? (int) $settingsData['auto_approval'] : 0;
$maintenanceMode = isset($settingsData['maintenance_mode']) ? (int) $settingsData['maintenance_mode'] : 0;
$autoApproveMessages = isset($settingsData['auto_approve_messages']) ? (int) $settingsData['auto_approve_messages'] : 0;
$autoApproveImages = isset($settingsData['auto_approve_images']) ? (int) $settingsData['auto_approve_images'] : 0;


// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM memorials");
$totalMemorials = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(visits) FROM memorials");
$totalVisits = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) FROM memorials");
$totalTasbeeh = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات — <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">🌿 <?= SITE_NAME ?> — الإدارة</a>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="btn btn-sm btn-light">← العودة</a>
        </div>
    </nav>

    <div class="container my-5">

        <h1 class="mb-4">إعدادات الموقع</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($cleanupResult): ?>
            <div class="alert alert-info">
                <h5 class="alert-heading">🧹 نتائج التنظيف</h5>
                <ul class="mb-0">
                    <li><strong>إجمالي الصفحات الموجودة:</strong> <?= $cleanupResult['total'] ?></li>
                    <li><strong>تم حذفها بنجاح:</strong> <?= $cleanupResult['deleted'] ?></li>
                    <?php if ($cleanupResult['failed'] > 0): ?>
                        <li><strong>فشل في الحذف:</strong> <?= $cleanupResult['failed'] ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($orphanedCleanupResult): ?>
            <div class="alert alert-success">
                <h5 class="alert-heading">🗂️ نتائج تنظيف الصور المهجورة</h5>
                <ul class="mb-0">
                    <li><strong>إجمالي الصور الموجودة:</strong> <?= $orphanedCleanupResult['total_found'] ?></li>
                    <li><strong>الصور الرئيسية المحذوفة:</strong> <?= $orphanedCleanupResult['deleted_main'] ?></li>
                    <li><strong>الصور المصغرة المحذوفة:</strong> <?= $orphanedCleanupResult['deleted_thumbs'] ?></li>
                    <li><strong>صور الدعاء المحذوفة:</strong> <?= $orphanedCleanupResult['deleted_duaa'] ?></li>
                    <?php if ($orphanedCleanupResult['failed'] > 0): ?>
                        <li><strong>فشل في الحذف:</strong> <?= $orphanedCleanupResult['failed'] ?></li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($sitemapResult): ?>
            <?php if ($sitemapResult['success']): ?>
                <div class="alert alert-success">
                    <h5 class="alert-heading">🗺️ تم إنشاء خريطة الموقع بنجاح</h5>
                    <ul class="mb-0">
                        <li><strong>الصفحات الثابتة:</strong> <?= $sitemapResult['static_pages'] ?></li>
                        <li><strong>صفحات التذكار:</strong> <?= $sitemapResult['memorial_pages'] ?></li>
                        <li><strong>إجمالي الروابط:</strong> <?= $sitemapResult['total_urls'] ?></li>
                        <li><strong>حجم الملف:</strong> <?= number_format($sitemapResult['file_size']) ?> بايت</li>
                        <li><strong>تاريخ الإنشاء:</strong> <?= $sitemapResult['generated_at'] ?></li>
                    </ul>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    <h5 class="alert-heading">❌ فشل في إنشاء خريطة الموقع</h5>
                    <p class="mb-0"><strong>الخطأ:</strong> <?= e($sitemapResult['error']) ?></p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= number_format($totalMemorials) ?></h3>
                        <p class="text-muted mb-0">إجمالي الصفحات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= number_format($totalVisits) ?></h3>
                        <p class="text-muted mb-0">إجمالي الزيارات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?= number_format($totalTasbeeh) ?></h3>
                        <p class="text-muted mb-0">إجمالي التسبيحات</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">⚙️ إعدادات عامة</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_approval" name="auto_approval"
                            value="1" <?= $autoApproval === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="auto_approval">السماح بالموافقة التلقائية على الصفحات
                            الجديدة</label>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_approve_messages"
                            name="auto_approve_messages" value="1" <?= $autoApproveMessages === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="auto_approve_messages">
                            السماح بالموافقة التلقائية على الرسائل الجديدة
                        </label>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_approve_images"
                            name="auto_approve_images" value="1" <?= $autoApproveImages === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="auto_approve_images">
                            الموافقة التلقائية على <strong>صور</strong> الصفحات الجديدة
                        </label>
                    </div>

                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode"
                            value="1" <?= $maintenanceMode === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="maintenance_mode">
                            <strong>وضع الصيانة</strong> — عند التفعيل، لن يتمكن الزوار من الوصول للموقع (المشرفون فقط)
                        </label>
                    </div>



                    <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                </form>
            </div>
        </div>

        <!-- Inactive Pages Cleaner -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">🧹 تنظيف الصفحات غير النشطة</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    احذف الصفحات التي لم تتم زيارتها منذ فترة طويلة لتوفير مساحة التخزين.
                </p>

                <form method="POST"
                    onsubmit="return confirm('هل أنت متأكد من حذف الصفحات غير النشطة؟ هذا الإجراء لا يمكن التراجع عنه.')">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="cleanup">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label for="cleanup_days" class="form-label">عدد الأيام</label>
                            <input type="number" class="form-control" id="cleanup_days" name="cleanup_days" value="30"
                                min="1" max="365" required>
                            <small class="form-text text-muted">احذف الصفحات التي لم تتم زيارتها منذ X يومًا</small>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-broom"></i> تنظيف الآن
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Orphaned Images Cleaner -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">🗂️ تنظيف الصور المهجورة</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    احذف الصور الموجودة على الخادم والتي لا تنتمي لأي صفحة تذكارية في قاعدة البيانات.
                    سيتم حذف الصورة الرئيسية والمصغرة وصورة الدعاء المقترنة بها إن وجدت.
                </p>

                <form method="POST"
                    onsubmit="return confirm('هل أنت متأكد من حذف الصور المهجورة؟ سيتم حذف جميع الصور التي لا تنتمي لصفحات موجودة في قاعدة البيانات. هذا الإجراء لا يمكن التراجع عنه.')">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="cleanup_orphaned">

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-danger">
                            🗑️ حذف الصور المهجورة
                        </button>
                        <small class="text-muted">
                            سيتم فحص مجلد uploads/memorials/ ومقارنته مع قاعدة البيانات
                        </small>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sitemap Generator -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">🗺️ إنشاء خريطة الموقع (Sitemap)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">
                    قم بإنشاء أو تحديث ملف sitemap.xml الذي يساعد محركات البحث في فهرسة صفحات الموقع.
                    سيتم تضمين جميع الصفحات الثابتة وصفحات التذكار المنشورة.
                </p>

                <form method="POST"
                    onsubmit="return confirm('هل تريد إنشاء خريطة موقع جديدة؟ سيتم استبدال الملف الحالي إن وجد.')">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="generate_sitemap">

                    <div class="d-flex align-items-center gap-3">
                        <button type="submit" class="btn btn-success">
                            🗺️ إنشاء خريطة الموقع
                        </button>
                        <small class="text-muted">
                            سيتم إنشاء ملف sitemap.xml في جذر الموقع
                        </small>
                    </div>
                </form>

                <?php
                $sitemapPath = __DIR__ . '/../sitemap.xml';
                if (file_exists($sitemapPath)):
                    $sitemapSize = filesize($sitemapPath);
                    $sitemapDate = date('Y-m-d H:i:s', filemtime($sitemapPath));
                    ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="mb-2">📄 معلومات خريطة الموقع الحالية:</h6>
                        <ul class="mb-0 small">
                            <li><strong>حجم الملف:</strong> <?= number_format($sitemapSize) ?> بايت</li>
                            <li><strong>آخر تحديث:</strong> <?= $sitemapDate ?></li>
                            <li><strong>الرابط:</strong> <a href="<?= BASE_URL ?>/sitemap.xml" target="_blank">عرض خريطة
                                    الموقع</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded">
                        <small class="text-warning">⚠️ لم يتم إنشاء خريطة الموقع بعد</small>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- System Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">📊 معلومات النظام</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>إصدار PHP:</th>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <th>قاعدة البيانات:</th>
                        <td><?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?></td>
                    </tr>
                    <tr>
                        <th>الرابط الأساسي:</th>
                        <td><?= BASE_URL ?></td>
                    </tr>
                    <tr>
                        <th>وضع التطوير:</th>
                        <td><?= DEBUG_MODE ? '<span class="badge bg-warning">مفعّل</span>' : '<span class="badge bg-success">معطّل</span>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
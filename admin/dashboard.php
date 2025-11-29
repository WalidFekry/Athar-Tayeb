<?php
/**
 * Admin Dashboard
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

// Fetch statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM memorials");
$totalMemorials = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM memorials WHERE status = 1");
$publishedMemorials = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM memorials WHERE image_status = 0 AND image IS NOT NULL");
$pendingImages = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM memorials WHERE quote_status = 0 AND quote IS NOT NULL");
$pendingQuotes = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$unreadContactMessages = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(visits) FROM memorials");
$totalVisits = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) FROM memorials");
$totalTasbeeh = $stmt->fetchColumn();

// Count actual image files on server
$uploadsPath = __DIR__ . '/../public/uploads/memorials/';
$uploadsCardsPath = __DIR__ . '/../public/uploads/duaa_images/';
$mainImagesCount = 0;
$thumbnailsCount = 0;
$cardsCount = 0;

if (is_dir($uploadsPath)) {
    $files = scandir($uploadsPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($uploadsPath . $file)) {
            if (strpos($file, '_thumb.') !== false) {
                $thumbnailsCount++;
            } else {
                // Check if it's an image file (not a thumbnail)
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    $mainImagesCount++;
                }
            }
        }
    }
}

if (is_dir($uploadsCardsPath)) {
    $files = scandir($uploadsCardsPath);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($uploadsCardsPath . $file)) {
            $cardsCount++;
        }
    }
}

// Latest memorials
$stmt = $pdo->query("SELECT id, name, created_at, status FROM memorials ORDER BY created_at DESC LIMIT 10");
$latestMemorials = $stmt->fetchAll();

$pageTitle = 'لوحة التحكم';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> — <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>

<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">
                🌿 <?= SITE_NAME ?> — الإدارة
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= ADMIN_URL ?>/dashboard.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/memorials.php">الصفحات التذكارية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/images_moderation.php">
                            الصور <?php if ($pendingImages > 0): ?><span
                                    class="badge bg-warning"><?= $pendingImages ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/messages_moderation.php">
                            الرسائل <?php if ($pendingQuotes > 0): ?><span
                                    class="badge bg-warning"><?= $pendingQuotes ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/contact_messages.php">
                            البريد <?php if ($unreadContactMessages > 0): ?><span
                                    class="badge bg-warning"><?= $unreadContactMessages ?></span><?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/settings.php">الإعدادات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/blocked_ips.php">المستخدمون المحظورون</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/admins.php">المديرون</a>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>" target="_blank">عرض الموقع</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/logout.php">تسجيل الخروج</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <h1 class="mb-4">مرحباً، <?= e($_SESSION['admin_username']) ?> 👋</h1>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <!-- First Row -->
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= toArabicNumerals($totalMemorials) ?></h3>
                        <p class="text-muted mb-0">إجمالي الصفحات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= toArabicNumerals($publishedMemorials) ?></h3>
                        <p class="text-muted mb-0">صفحات منشورة</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-warning"><?= toArabicNumerals($pendingImages) ?></h3>
                        <p class="text-muted mb-0">صور قيد المراجعة</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?= toArabicNumerals($totalVisits) ?></h3>
                        <p class="text-muted mb-0">إجمالي الزيارات</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row for Additional Statistics -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-secondary"><?= toArabicNumerals($totalTasbeeh) ?></h3>
                        <p class="text-muted mb-0">📿 إجمالي التسبيحات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-dark"><?= toArabicNumerals($mainImagesCount) ?></h3>
                        <p class="text-muted mb-0">🖼️ الصور الرئيسية</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-muted"><?= toArabicNumerals($thumbnailsCount) ?></h3>
                        <p class="text-muted mb-0">🔍 الصور المصغرة</p>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-muted"><?= toArabicNumerals($cardsCount) ?></h3>
                        <p class="text-muted mb-0">📜 بطاقات الدعاء</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <a href="<?= ADMIN_URL ?>/images_moderation.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">🖼️</div>
                        <h5>مراجعة الصور</h5>
                        <?php if ($pendingImages > 0): ?>
                            <span class="badge bg-warning"><?= toArabicNumerals($pendingImages) ?> قيد الانتظار</span>
                        <?php else: ?>
                            <span class="text-muted">لا توجد صور قيد المراجعة</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= ADMIN_URL ?>/messages_moderation.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">💬</div>
                        <h5>مراجعة الرسائل</h5>
                        <?php if ($pendingQuotes > 0): ?>
                            <span class="badge bg-warning"><?= toArabicNumerals($pendingQuotes) ?> قيد الانتظار</span>
                        <?php else: ?>
                            <span class="text-muted">لا توجد رسائل قيد المراجعة</span>
                        <?php endif; ?>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= ADMIN_URL ?>/memorials.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">📄</div>
                        <h5>إدارة الصفحات</h5>
                        <span class="text-muted">عرض وتعديل جميع الصفحات</span>
                    </div>
                </a>
            </div>

            <div class="col-md-3">
                <a href="<?= ADMIN_URL ?>/duaa_cards.php" class="card text-decoration-none h-100">
                    <div class="card-body text-center">
                        <div class="fs-1 mb-3">📜</div>
                        <h5>بطاقات الدعاء</h5>
                        <span class="text-muted">عرض بطاقات الدعاء المتاحة</span>
                    </div>
                </a>
            </div>
        </div>

        <!-- Latest Memorials -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">آخر الصفحات المضافة</h5>
            </div>
            <div class="card-body">
                <?php if (count($latestMemorials) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>تاريخ الإضافة</th>
                                    <th>الحالة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($latestMemorials as $memorial): ?>
                                    <tr>
                                        <td><?= e($memorial['name']) ?></td>
                                        <td><?= date('Y-m-d H:i', strtotime($memorial['created_at'])) ?></td>
                                        <td>
                                            <?php if ($memorial['status'] == 1): ?>
                                                <span class="badge bg-success">منشور</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">قيد المراجعة</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= ADMIN_URL ?>/memorial_view.php?id=<?= $memorial['id'] ?>"
                                                class="btn btn-sm btn-primary">
                                                عرض
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted mb-0">لا توجد صفحات بعد</p>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
/**
 * Duaa Cards Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

// Pagination settings
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 12;

// Scan for images
$uploadsCardsPath = __DIR__ . '/../public/uploads/duaa_images/';
$allImages = [];

if (is_dir($uploadsCardsPath)) {
    $files = scandir($uploadsCardsPath);
    $filesWithTime = [];
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && is_file($uploadsCardsPath . $file)) {
            $filesWithTime[$file] = filemtime($uploadsCardsPath . $file);
        }
    }
    // Sort by time descending (newest first)
    arsort($filesWithTime);
    $allImages = array_keys($filesWithTime);
}

// Total items and pages
$totalImages = count($allImages);
$totalPages = ceil($totalImages / $perPage);
// Slice array for current page
$offset = ($page - 1) * $perPage;
$currentImages = array_slice($allImages, $offset, $perPage);

// Fetch details from database for these images
$imageDetails = [];
if (!empty($currentImages)) {
    $placeholders = str_repeat('?,', count($currentImages) - 1) . '?';
    $stmt = $pdo->prepare("SELECT id, name, image FROM memorials WHERE image IN ($placeholders)");
    $stmt->execute($currentImages);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Key by image name for easy lookup
    foreach ($results as $row) {
        $imageDetails[$row['image']] = $row;
    }
}

$pageTitle = 'بطاقات الدعاء';
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
    <style>
        .duaa-card-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }

        .card {
            transition: transform 0.2s;
        }

        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>

<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">🌿 <?= SITE_NAME ?> — الإدارة</a>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="btn btn-sm btn-light">← العودة للوحة التحكم</a>
        </div>
    </nav>

    <div class="container my-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>بطاقات الدعاء</h1>
            <span class="badge bg-primary fs-6"><?= $totalImages ?> بطاقة</span>
        </div>

        <?php if (empty($allImages)): ?>
            <div class="alert alert-info">
                لا توجد بطاقات دعاء متاحة حالياً.
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($currentImages as $image): ?>
                    <?php
                    $details = $imageDetails[$image] ?? null;
                    $imageUrl = BASE_URL . '/uploads/duaa_images/' . $image;
                    ?>
                    <div class="col-md-3">
                        <div class="card h-100 shadow-sm">
                            <a href="<?= $imageUrl ?>" target="_blank">
                                <img src="<?= $imageUrl ?>" class="card-img-top duaa-card-img" alt="بطاقة دعاء">
                            </a>
                            <div class="card-body">
                                <?php if ($details): ?>
                                    <h5 class="card-title h6 mb-2"><?= e($details['name']) ?></h5>
                                    <a href="<?= ADMIN_URL ?>/memorial_view.php?id=<?= $details['id'] ?>"
                                        class="btn btn-sm btn-outline-primary w-100">
                                        عرض الصفحة
                                    </a>
                                <?php else: ?>
                                    <h5 class="card-title h6 mb-2 text-muted">غير مرتبط بصفحة</h5>
                                    <span class="text-muted small"><?= e($image) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <!-- Previous page -->
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page - 1 ?>">السابق</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">السابق</span>
                            </li>
                        <?php endif; ?>

                        <!-- Page numbers -->
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <!-- Next page -->
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page + 1 ?>">التالي</a>
                            </li>
                        <?php else: ?>
                            <li class="page-item disabled">
                                <span class="page-link">التالي</span>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
/**
 * All Memorials Page
 * Paginated listing of all published memorials
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Get total count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM memorials WHERE status = 1 AND image_status = 1");
$stmt->execute();
$totalMemorials = $stmt->fetchColumn();
$totalPages = ceil($totalMemorials / ITEMS_PER_PAGE);

// Fetch memorials for current page
$stmt = $pdo->prepare("
    SELECT id, name, death_date, image, visits, gender, from_name
    FROM memorials 
    WHERE status = 1 AND image_status = 1
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");
$stmt->execute([ITEMS_PER_PAGE, $offset]);
$memorials = $stmt->fetchAll();

$pageTitle = 'جميع الصفحات التذكارية — ' . SITE_NAME;

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    
    <div class="text-center mb-5">
        <h1> جميع الصفحات التذكارية 🤲</h1>
        <p class="lead text-muted">
            <?= toArabicNumerals($totalMemorials) ?> صفحة تذكارية
        </p>
    </div>
    
    <?php if (count($memorials) > 0): ?>
        
        <div class="row g-4 mb-5">
            <?php foreach ($memorials as $memorial): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card memorial-card h-100">
                        <div class="card-body text-center">
                            <img 
                                src="<?= getImageUrl($memorial['image'], true) ?>" 
                                alt="<?= e($memorial['name']) ?>"
                                class="memorial-image"
                                loading="lazy"
                            >
                            
                            <?php if ($memorial['from_name']): ?>
                                <p class="text-muted small mb-2">
                                    إهداء من: <?= e($memorial['from_name']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <h5 class="memorial-name"><?= e($memorial['name']) ?></h5>
                            
                            <?php if ($memorial['death_date']): ?>
                                <p class="memorial-date">
                                    📅 <?= formatArabicDate($memorial['death_date']) ?>
                                </p>
                            <?php endif; ?>
                            
                            <p class="memorial-visits">
                                👁️ زارها <?= toArabicNumerals($memorial['visits']) ?> شخصاً
                            </p>
                            
                            <a href="<?= BASE_URL ?>/memorial.php?id=<?= $memorial['id'] ?>" class="btn btn-primary w-100">
                                عرض الصفحة
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="تنقل الصفحات">
                <ul class="pagination justify-content-center">
                    
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">السابق</a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= toArabicNumerals($i) ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">التالي</a>
                        </li>
                    <?php endif; ?>
                    
                </ul>
            </nav>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="alert alert-info text-center">
            <p class="mb-0">لا توجد صفحات تذكارية حالياً</p>
        </div>
    <?php endif; ?>
    
    <div class="text-center mt-5">
        <a href="<?= BASE_URL ?>/create.php" class="btn btn-primary btn-lg">
             أنشئ صفحة تذكارية 💚
        </a>
    </div>
    
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

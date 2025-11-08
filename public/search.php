<?php
/**
 * Search Page
 * Search for memorials by name
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/maintenance_check.php';


$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$results = [];

if (!empty($query)) {
    // Search in memorials (only published ones)
    $searchTerm = '%' . $query . '%';
    $stmt = $pdo->prepare("
        SELECT id, name, death_date, image, visits, gender
        FROM memorials 
        WHERE status = 1 
        AND (name LIKE ? OR from_name LIKE ?)
        ORDER BY visits DESC, created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$searchTerm, $searchTerm]);
    $results = $stmt->fetchAll();
}

$pageTitle = 'البحث' . ($query ? ' — ' . $query : '') . ' — ' . SITE_NAME;

include __DIR__ . '/../includes/header.php';
?>

<div class="container my-5">
    
    <div class="row">
        <div class="col-lg-8 mx-auto">
            
            <h1 class="text-center mb-4">🔍 البحث عن صفحة تذكارية</h1>
            
            <!-- Search Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="">
                        <div class="input-group input-group-lg">
                            <input 
                                type="text" 
                                name="q" 
                                class="form-control" 
                                placeholder="ابحث عن اسم..."
                                value="<?= e($query) ?>"
                                required
                                autofocus
                            >
                            <button class="btn btn-primary px-4" type="submit">بحث</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Results -->
            <?php if (!empty($query)): ?>
                
                <?php if (count($results) > 0): ?>
                    <h3 class="mb-4">
                        النتائج (<?= toArabicNumerals(count($results)) ?>)
                    </h3>
                    
                    <div class="row g-3">
                        <?php foreach ($results as $memorial): ?>
                            <div class="col-md-6">
                                <div class="card memorial-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <img 
                                                src="<?= getImageUrl($memorial['image'], true) ?>" 
                                                alt="<?= e($memorial['name']) ?>"
                                                class="rounded-circle me-3"
                                                style="width: 60px; height: 60px; object-fit: cover;"
                                            >
                                            <div class="flex-grow-1">
                                                <h5 class="mb-1"><?= e($memorial['name']) ?></h5>
                                                <?php if ($memorial['death_date']): ?>
                                                    <small class="text-muted">
                                                        <?= formatArabicDate($memorial['death_date']) ?>
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <small class="text-muted">
                                                👁️ <?= toArabicNumerals($memorial['visits']) ?> زيارة
                                            </small>
                                        </div>
                                        
                                        <a href="<?= site_url('m/' . $memorial['id']) ?>" class="btn btn-primary btn-sm w-100 mt-3">
                                            عرض الصفحة
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                <?php else: ?>
                    <div class="alert alert-warning text-center">
                        <h4>لا توجد نتائج</h4>
                        <p class="mb-0">
                            لم نجد أي صفحات تذكارية تطابق بحثك عن "<strong><?= e($query) ?></strong>"
                        </p>
                    </div>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="text-center text-muted">
                    <p>أدخل اسماً للبحث عن صفحة تذكارية</p>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="<?= site_url('all') ?>" class="btn btn-outline-primary">
                    عرض جميع الصفحات
                </a>
            </div>
            
        </div>
    </div>
    
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

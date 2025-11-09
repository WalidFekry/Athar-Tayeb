<?php
/**
 * Images Moderation
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

// Handle moderation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $memorialId = (int)$_POST['memorial_id'];
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE memorials SET image_status = 1 WHERE id = ?");
        $stmt->execute([$memorialId]);
        invalidateMemorialCache($memorialId);
        $success = 'تمت الموافقة على الصورة';
    } elseif ($action === 'reject') {
        // Get memorial to delete image file
        $stmt = $pdo->prepare("SELECT image FROM memorials WHERE id = ?");
        $stmt->execute([$memorialId]);
        $memorial = $stmt->fetch();
        
        if ($memorial && $memorial['image']) {
            // Delete original image
            $imagePath = UPLOAD_PATH . '/' . $memorial['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            
            // Delete thumbnail if exists
            $ext = pathinfo($memorial['image'], PATHINFO_EXTENSION);
            $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
            if (file_exists($thumbPath)) {
                unlink($thumbPath);
            }
            
            // Update database: set image to NULL and status to rejected
            $stmt = $pdo->prepare("UPDATE memorials SET image = NULL, image_status = 2 WHERE id = ?");
            $stmt->execute([$memorialId]);
        } else {
            // No image to delete, just update status
            $stmt = $pdo->prepare("UPDATE memorials SET image_status = 2 WHERE id = ?");
            $stmt->execute([$memorialId]);
        }
        
        invalidateMemorialCache($memorialId);
        $success = 'تم رفض الصورة وحذفها من الخادم';
    }
}

// Fetch pending images
$stmt = $pdo->query("
    SELECT id, name, image, from_name, created_at
    FROM memorials 
    WHERE image_status = 0 AND image IS NOT NULL
    ORDER BY created_at ASC
");
$pendingImages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراجعة الصور — <?= SITE_NAME ?></title>
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
        
        <h1 class="mb-4">مراجعة الصور (<?= count($pendingImages) ?>)</h1>
        
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        
        <?php if (count($pendingImages) > 0): ?>
            <div class="row g-4">
                <?php foreach ($pendingImages as $memorial): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card">
                            <img src="<?= getImageUrl($memorial['image']) ?>" class="card-img-top" alt="صورة">
                            <div class="card-body">
                                <h5 class="card-title"><?= e($memorial['name']) ?></h5>
                                <?php if ($memorial['from_name']): ?>
                                    <p class="text-muted small">من: <?= e($memorial['from_name']) ?></p>
                                <?php endif; ?>
                                <p class="text-muted small">
                                    <?= date('Y-m-d H:i', strtotime($memorial['created_at'])) ?>
                                </p>
                                
                                <div class="d-grid gap-2">
                                    <form method="POST">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn btn-success w-100">✓ موافقة</button>
                                    </form>
                                    
                                    <form method="POST">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn btn-danger w-100" onclick="return confirm('رفض هذه الصورة؟')">✗ رفض</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p class="mb-0">لا توجد صور قيد المراجعة 🎉</p>
            </div>
        <?php endif; ?>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

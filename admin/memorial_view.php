<?php
/**
 * Admin Memorial View
 * View full details of a memorial page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$memorialId = (int)($_GET['id'] ?? 0);

if (!$memorialId) {
    redirect(ADMIN_URL . '/memorials.php');
}

$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

if (!$memorial) {
    redirect(ADMIN_URL . '/memorials.php');
}

$pageTitle = 'عرض الصفحة: ' . $memorial['name'];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/admin.css">
</head>
<body>
    
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">🌿 <?= SITE_NAME ?> — الإدارة</a>
            <a href="<?= ADMIN_URL ?>/memorials.php" class="btn btn-sm btn-light">← العودة للصفحات</a>
        </div>
    </nav>
    
    <div class="container my-5">
        
        <h1 class="mb-4">عرض الصفحة التذكارية</h1>
        
        <!-- Memorial Info Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">📋 معلومات الصفحة</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">رقم الصفحة:</th>
                                <td><?= $memorial['id'] ?></td>
                            </tr>
                            <tr>
                                <th>الاسم:</th>
                                <td><strong><?= e($memorial['name']) ?></strong></td>
                            </tr>
                            <tr>
                                <th>إهداء من:</th>
                                <td><?= e($memorial['from_name'] ?: '—') ?></td>
                            </tr>
                            <tr>
                                <th>النوع:</th>
                                <td><?= $memorial['gender'] === 'female' ? 'أنثى' : 'ذكر' ?></td>
                            </tr>
                            <tr>
                                <th>تاريخ الوفاة:</th>
                                <td><?= $memorial['death_date'] ? formatArabicDate($memorial['death_date']) : '—' ?></td>
                            </tr>
                            <tr>
                                <th>واتساب:</th>
                                <td><?= e($memorial['whatsapp'] ?: '—') ?></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <th width="40%">حالة الصفحة:</th>
                                <td>
                                    <?php if ($memorial['status'] == 1): ?>
                                        <span class="badge bg-success">منشور</span>
                                    <?php elseif ($memorial['status'] == 2): ?>
                                        <span class="badge bg-danger">مرفوض</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>حالة الصورة:</th>
                                <td>
                                    <?php if ($memorial['image_status'] == 1): ?>
                                        <span class="badge bg-success">موافق عليها</span>
                                    <?php elseif ($memorial['image_status'] == 2): ?>
                                        <span class="badge bg-danger">مرفوضة</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>حالة الرسالة:</th>
                                <td>
                                    <?php if (!$memorial['quote']): ?>
                                        <span class="text-muted">لا توجد رسالة</span>
                                    <?php elseif ($memorial['quote_status'] == 1): ?>
                                        <span class="badge bg-success">موافق عليها</span>
                                    <?php elseif ($memorial['quote_status'] == 2): ?>
                                        <span class="badge bg-danger">مرفوضة</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">قيد المراجعة</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th>عدد الزيارات:</th>
                                <td><?= number_format($memorial['visits']) ?></td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td><?= formatArabicDate($memorial['created_at']) ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Image Card -->
        <?php if ($memorial['image']): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">🖼️ الصورة</h5>
                </div>
                <div class="card-body text-center">
                    <img 
                        src="<?= getImageUrl($memorial['image']) ?>" 
                        alt="<?= e($memorial['name']) ?>"
                        class="img-fluid rounded"
                        style="max-width: 400px;"
                    >
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Quote Card -->
        <?php if ($memorial['quote']): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">💬 الرسالة</h5>
                </div>
                <div class="card-body">
                    <p style="white-space: pre-wrap;"><?= e($memorial['quote']) ?></p>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Tasbeeh Stats -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">📿 إحصائيات التسبيح</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4 class="text-primary"><?= number_format($memorial['tasbeeh_subhan']) ?></h4>
                        <p class="text-muted">سبحان الله</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-success"><?= number_format($memorial['tasbeeh_alham']) ?></h4>
                        <p class="text-muted">الحمد لله</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-info"><?= number_format($memorial['tasbeeh_lailaha']) ?></h4>
                        <p class="text-muted">لا إله إلا الله</p>
                    </div>
                    <div class="col-md-3">
                        <h4 class="text-warning"><?= number_format($memorial['tasbeeh_allahu']) ?></h4>
                        <p class="text-muted">الله أكبر</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">⚙️ الإجراءات</h5>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?= BASE_URL ?>/m/<?= $memorial['id'] ?>" 
                       target="_blank" 
                       class="btn btn-primary">
                        👁️ عرض الصفحة
                    </a>
                    <a href="<?= ADMIN_URL ?>/memorials.php?action=edit&id=<?= $memorial['id'] ?>" 
                       class="btn btn-warning">
                        ✏️ تعديل
                    </a>
                    <a href="<?= ADMIN_URL ?>/memorials.php" 
                       class="btn btn-secondary">
                        ← العودة للقائمة
                    </a>
                </div>
            </div>
        </div>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

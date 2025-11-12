<?php
/**
 * Messages/Quotes Moderation
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
    $action = $_POST['action'];
    if ($action === 'approve') {
        $memorialId = (int) $_POST['memorial_id'];
        $stmt = $pdo->prepare("UPDATE memorials SET quote_status = 1 WHERE id = ?");
        $stmt->execute([$memorialId]);
        $success = 'تمت الموافقة على الرسالة';
    } elseif ($action === 'reject') {
        $memorialId = (int) $_POST['memorial_id'];
        $stmt = $pdo->prepare("UPDATE memorials SET quote_status = 2 WHERE id = ?");
        $stmt->execute([$memorialId]);
        $success = 'تم رفض الرسالة';
    } elseif ($action === 'approve_all') {
        $stmt = $pdo->prepare("UPDATE memorials SET quote_status = 1 WHERE quote_status = 0 AND quote IS NOT NULL AND quote != ''");
        $stmt->execute();
        $count = $stmt->rowCount();
        $success = 'تمت الموافقة على ' . $count . ' رسالة';
    }
}

// Fetch pending quotes
$stmt = $pdo->query("
    SELECT id, name, quote, from_name, created_at
    FROM memorials 
    WHERE quote_status = 0 AND quote IS NOT NULL AND quote != ''
    ORDER BY created_at ASC
");
$pendingQuotes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مراجعة الرسائل — <?= SITE_NAME ?></title>
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

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="mb-0">مراجعة الرسائل (<?= count($pendingQuotes) ?>)</h1>
            <?php if (count($pendingQuotes) > 0): ?>
                <form method="POST" onsubmit="return confirm('هل أنت متأكد من الموافقة على جميع الرسائل؟')">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="approve_all">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle"></i> موافقة على الكل
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if (count($pendingQuotes) > 0): ?>
            <div class="row g-4">
                <?php foreach ($pendingQuotes as $memorial): ?>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?= e($memorial['name']) ?></h5>
                                <?php if ($memorial['from_name']): ?>
                                    <p class="text-muted small">من: <?= e($memorial['from_name']) ?></p>
                                <?php endif; ?>

                                <div class="alert alert-light">
                                    <p class="mb-0" style="white-space: pre-wrap;"><?= e($memorial['quote']) ?></p>
                                </div>

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
                                        <button type="submit" class="btn btn-danger w-100"
                                            onclick="return confirm('رفض هذه الرسالة؟')">✗ رفض</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <p class="mb-0">لا توجد رسائل قيد المراجعة 🎉</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
<?php
/**
 * Blocked IPs Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$success = '';
$error = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();

    $action = $_POST['action'] ?? '';

    if ($action === 'unblock_single') {
        $blockedId = (int)($_POST['blocked_id'] ?? 0);
        if ($blockedId > 0) {
            $stmt = $pdo->prepare("DELETE FROM blocked_ips WHERE id = ?");
            $stmt->execute([$blockedId]);
            if ($stmt->rowCount() > 0) {
                $success = 'تم إلغاء الحظر عن هذا المستخدم بنجاح.';
            } else {
                $error = 'لم يتم العثور على هذا السجل أو تم حذفه مسبقاً.';
            }
        }
    } elseif ($action === 'unblock_all') {
        $stmt = $pdo->prepare("DELETE FROM blocked_ips");
        $stmt->execute();
        $deleted = $stmt->rowCount();
        if ($deleted > 0) {
            $success = 'تم إلغاء الحظر عن جميع المستخدمين المحظورين (' . $deleted . ' عنوان IP).';
        } else {
            $success = 'لا توجد عناوين IP محظورة حالياً.';
        }
    } elseif ($action === 'unblock_by_days') {
        $days = (int)($_POST['days'] ?? 0);
        if ($days < 1) {
            $error = 'يرجى إدخال عدد أيام صحيح (1 أو أكثر).';
        } else {
            $cutoffDate = date('Y-m-d H:i:s', strtotime('-' . $days . ' days'));
            $stmt = $pdo->prepare("DELETE FROM blocked_ips WHERE created_at <= ?");
            $stmt->execute([$cutoffDate]);
            $deleted = $stmt->rowCount();
            if ($deleted > 0) {
                $success = 'تم إلغاء الحظر عن ' . $deleted . ' عنوان IP تجاوزت مدة حظرها ' . $days . ' يوماً.';
            } else {
                $success = 'لا توجد عناوين IP تجاوزت مدة الحظر المحددة.';
            }
        }
    }
}

// Fetch all blocked IPs
$stmt = $pdo->query("SELECT * FROM blocked_ips ORDER BY created_at DESC");
$blockedIps = $stmt->fetchAll();

$pageTitle = 'إدارة المستخدمين المحظورين';
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
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">🌿 <?= SITE_NAME ?> — الإدارة</a>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="btn btn-sm btn-light">← العودة</a>
        </div>
    </nav>

    <div class="container my-5">
        <h1 class="mb-4">المستخدمون المحظورون</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <!-- Global Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">⚙️ إجراءات عامة</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <form method="POST" onsubmit="return confirm('هل أنت متأكد من إلغاء الحظر عن جميع المستخدمين؟ هذا الإجراء لا يمكن التراجع عنه.')">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="unblock_all">
                            <button type="submit" class="btn btn-danger w-100">إلغاء الحظر للجميع</button>
                        </form>
                    </div>
                    <div class="col-md-8">
                        <form method="POST" onsubmit="return confirm('سيتم إلغاء الحظر عن كل من تجاوزت مدة حظرهم عدد الأيام المحدد. هل أنت متأكد؟')">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="unblock_by_days">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label for="days" class="form-label">عدد الأيام</label>
                                    <input type="number" name="days" id="days" class="form-control" value="30" min="1" max="365" required>
                                </div>
                                <div class="col-md-8">
                                    <button type="submit" class="btn btn-warning w-100">إلغاء الحظر لمن تجاوز حظرهم عدد الأيام المحدد</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Blocked IPs Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">قائمة عناوين IP المحظورة (<?= count($blockedIps) ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (count($blockedIps) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>عنوان IP</th>
                                    <th>السبب</th>
                                    <th>تاريخ الحظر</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($blockedIps as $row): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= e($row['ip_address']) ?></td>
                                        <td><?= e($row['reason'] ?? '—') ?></td>
                                        <td><?= $row['created_at'] ? date('Y-m-d H:i', strtotime($row['created_at'])) : '—' ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('إلغاء الحظر عن هذا المستخدم؟')">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="action" value="unblock_single">
                                                <input type="hidden" name="blocked_id" value="<?= $row['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success">إلغاء الحظر</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mb-0">
                        لا توجد عناوين IP محظورة حالياً.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

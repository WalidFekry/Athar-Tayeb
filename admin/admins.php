<?php
/**
 * Admin Users Management
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
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'moderator';
        
        if (empty($username) || empty($password)) {
            $error = 'اسم المستخدم وكلمة المرور مطلوبان';
        } else {
            try {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $role]);
                $success = 'تم إضافة المدير بنجاح';
            } catch (PDOException $e) {
                $error = 'فشل إضافة المدير: اسم المستخدم موجود بالفعل';
            }
        }
    } elseif ($action === 'delete') {
        $adminId = (int)$_POST['admin_id'];
        if ($adminId != $_SESSION['admin_id']) {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$adminId]);
            $success = 'تم حذف المدير بنجاح';
        } else {
            $error = 'لا يمكنك حذف حسابك الخاص';
        }
    }
}

// Fetch all admins
$stmt = $pdo->query("SELECT * FROM admins ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إدارة المديرين — <?= SITE_NAME ?></title>
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
        
        <h1 class="mb-4">إدارة المديرين</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>
        
        <!-- Add New Admin -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">➕ إضافة مدير جديد</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="create">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">الدور</label>
                            <select name="role" class="form-select">
                                <option value="admin">مدير</option>
                                <option value="moderator" selected>مشرف</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">إضافة</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Admins List -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">قائمة المديرين (<?= count($admins) ?>)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>اسم المستخدم</th>
                                <th>الدور</th>
                                <th>تاريخ الإضافة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= $admin['id'] ?></td>
                                    <td>
                                        <?= e($admin['username']) ?>
                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                            <span class="badge bg-info">أنت</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($admin['role'] === 'admin'): ?>
                                            <span class="badge bg-danger">مدير</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">مشرف</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($admin['created_at'])) ?></td>
                                    <td>
                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('حذف هذا المدير؟')">حذف</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

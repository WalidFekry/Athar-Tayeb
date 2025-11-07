<?php
/**
 * Admin Login Page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect(ADMIN_URL . '/dashboard.php');
}

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'يرجى إدخال اسم المستخدم وكلمة المرور';
    } else {
        // Fetch admin
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful
            setAdminSession($admin['id'], $admin['username'], $admin['role']);
            logAuthAttempt($username, true, $_SERVER['REMOTE_ADDR']);
            redirect(ADMIN_URL . '/dashboard.php');
        } else {
            // Login failed
            $error = 'اسم المستخدم أو كلمة المرور غير صحيحة';
            logAuthAttempt($username, false, $_SERVER['REMOTE_ADDR']);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول — لوحة التحكم</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css">
</head>
<body class="bg-light">
    
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5">
                
                <div class="text-center mb-4">
                    <h1 class="display-5 fw-bold text-primary">🌿 <?= SITE_NAME ?></h1>
                    <p class="text-muted">لوحة التحكم</p>
                </div>
                
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-4">تسجيل الدخول</h3>
                        
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <?= e($error) ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <?php csrfField(); ?>
                            
                            <div class="mb-3">
                                <label for="username" class="form-label">اسم المستخدم</label>
                                <input 
                                    type="text" 
                                    class="form-control" 
                                    id="username" 
                                    name="username"
                                    required
                                    autofocus
                                    value="<?= e($_POST['username'] ?? '') ?>"
                                >
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">كلمة المرور</label>
                                <input 
                                    type="password" 
                                    class="form-control" 
                                    id="password" 
                                    name="password"
                                    required
                                >
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    دخول
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-3">
                    <a href="<?= BASE_URL ?>" class="text-muted text-decoration-none">
                        ← العودة للموقع
                    </a>
                </div>
                
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

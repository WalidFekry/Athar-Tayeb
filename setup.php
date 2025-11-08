<?php
/**
 * Setup Script for Athar Tayeb
 * Run this once to initialize the application
 */

// Security: Delete this file after setup
$setupComplete = file_exists(__DIR__ . '/.setup_complete');

if ($setupComplete) {
    die('Setup already completed. Delete .setup_complete file to run again.');
}

$errors = [];
$warnings = [];
$success = [];

// Check PHP version
if (version_compare(PHP_VERSION, '7.4.0', '<')) {
    $errors[] = 'PHP 7.4 or higher required. Current: ' . PHP_VERSION;
} else {
    $success[] = 'PHP version: ' . PHP_VERSION . ' ✓';
}

// Check required extensions
$requiredExtensions = ['pdo', 'pdo_mysql', 'gd', 'mbstring', 'json'];
foreach ($requiredExtensions as $ext) {
    if (!extension_loaded($ext)) {
        $errors[] = "PHP extension '$ext' is not loaded";
    } else {
        $success[] = "Extension '$ext' loaded ✓";
    }
}

// Check directories
$directories = [
    'public/uploads/memorials',
    'cache',
    'logs'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        if (@mkdir($path, 0755, true)) {
            $success[] = "Created directory: $dir ✓";
        } else {
            $errors[] = "Failed to create directory: $dir";
        }
    } else {
        $success[] = "Directory exists: $dir ✓";
    }
    
    // Check if writable
    if (is_dir($path) && !is_writable($path)) {
        $warnings[] = "Directory not writable: $dir (chmod 755 recommended)";
    }
}

// Check config file
if (!file_exists(__DIR__ . '/includes/config.php')) {
    $errors[] = 'config.php not found in includes/';
} else {
    require_once __DIR__ . '/includes/config.php';
    
    // Check if config was modified
    if (DB_HOST === 'localhost' && DB_USER === 'root' && DB_PASS === '') {
        $warnings[] = 'Database credentials appear to be default. Update config.php if needed.';
    }
    
    $success[] = 'config.php loaded ✓';
}

// Try database connection
if (empty($errors)) {
    try {
        require_once __DIR__ . '/includes/db.php';
        $success[] = 'Database connection successful ✓';
        
        // Check if tables exist
        $stmt = $pdo->query("SHOW TABLES LIKE 'memorials'");
        if ($stmt->rowCount() > 0) {
            $success[] = 'Database tables found ✓';
            
            // Check admin user
            $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
            $adminCount = $stmt->fetchColumn();
            if ($adminCount > 0) {
                $success[] = "Admin users found: $adminCount ✓";
            } else {
                $warnings[] = 'No admin users found. Import SQL file first.';
            }
        } else {
            $errors[] = 'Database tables not found. Import sql/athartayeb_schema.sql';
        }
    } catch (Exception $e) {
        $errors[] = 'Database connection failed: ' . $e->getMessage();
    }
}

// Handle form submission to create admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $errors[] = 'Username and password required';
    } else {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashedPassword]);
            $success[] = "Admin user '$username' created successfully!";
        } catch (Exception $e) {
            $errors[] = 'Failed to create admin: ' . $e->getMessage();
        }
    }
}

// Mark setup as complete if no errors
if (empty($errors) && isset($_POST['complete_setup'])) {
    file_put_contents(__DIR__ . '/.setup_complete', date('Y-m-d H:i:s'));
    $success[] = 'Setup completed! You can now delete setup.php';
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup — أثر طيب</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; background: #f5f5f5; padding: 2rem 0; }
        .setup-container { max-width: 800px; margin: 0 auto; }
        .card { margin-bottom: 1.5rem; }
        .check-item { padding: 0.5rem 0; border-bottom: 1px solid #eee; }
        .check-item:last-child { border-bottom: none; }
    </style>
</head>
<body>
    <div class="setup-container">
        
        <div class="text-center mb-4">
            <h1 class="display-4">🌿 أثر طيب</h1>
            <p class="lead">معالج الإعداد الأولي</p>
        </div>
        
        <!-- Errors -->
        <?php if (!empty($errors)): ?>
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0">❌ أخطاء يجب إصلاحها</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($errors as $error): ?>
                        <div class="check-item text-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Warnings -->
        <?php if (!empty($warnings)): ?>
            <div class="card border-warning">
                <div class="card-header bg-warning">
                    <h5 class="mb-0">⚠️ تحذيرات</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($warnings as $warning): ?>
                        <div class="check-item text-warning"><?= htmlspecialchars($warning) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Success -->
        <?php if (!empty($success)): ?>
            <div class="card border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">✅ نجح</h5>
                </div>
                <div class="card-body">
                    <?php foreach ($success as $item): ?>
                        <div class="check-item text-success"><?= htmlspecialchars($item) ?></div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Create Admin Form -->
        <?php if (empty($errors) && isset($pdo)): ?>
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">إنشاء مستخدم إداري جديد (اختياري)</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">اسم المستخدم</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">كلمة المرور</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="create_admin" class="btn btn-primary">إنشاء مستخدم</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <!-- Complete Setup -->
        <?php if (empty($errors)): ?>
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 class="text-success mb-3">🎉 الإعداد جاهز!</h5>
                    <p>يمكنك الآن استخدام الموقع.</p>
                    
                    <form method="POST" class="d-inline">
                        <button type="submit" name="complete_setup" class="btn btn-success btn-lg">
                            إتمام الإعداد
                        </button>
                    </form>
                    
                    <hr class="my-4">
                    
                    <div class="d-grid gap-2">
                        <a href="public/" class="btn btn-primary">🏠 الذهاب للموقع</a>
                        <a href="admin/login.php" class="btn btn-outline-primary">🔐 تسجيل دخول الإدارة</a>
                    </div>
                    
                    <p class="text-muted mt-3 small">
                        <strong>بيانات الدخول الافتراضية:</strong><br>
                        اسم المستخدم: admin<br>
                        كلمة المرور: admin123
                    </p>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                <strong>لا يمكن إتمام الإعداد.</strong> يرجى إصلاح الأخطاء أعلاه أولاً.
            </div>
        <?php endif; ?>
        
        <!-- Instructions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📖 التعليمات</h5>
            </div>
            <div class="card-body">
                <ol>
                    <li>تأكد من استيراد ملف <code>sql/athartayeb_schema.sql</code> إلى قاعدة البيانات</li>
                    <li>عدّل ملف <code>includes/config.php</code> بإعدادات قاعدة البيانات الصحيحة</li>
                    <li>تأكد من أذونات الكتابة للمجلدات المطلوبة</li>
                    <li>احذف ملف <code>setup.php</code> بعد الانتهاء</li>
                </ol>
            </div>
        </div>
        
        <div class="text-center text-muted mt-4">
            <p>© 2025 أثر طيب — صدقة جارية رقمية</p>
        </div>
        
    </div>
</body>
</html>

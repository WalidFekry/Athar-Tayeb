<?php
/**
 * Admin Settings
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$success = '';

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRF();

    $settings = [
        'auto_approval' => isset($_POST['auto_approval']) && $_POST['auto_approval'] === '1' ? '1' : '0',
        'maintenance_mode' => isset($_POST['maintenance_mode']) && $_POST['maintenance_mode'] === '1' ? '1' : '0'
    ];

    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->execute([$key, $value, $value]);
    }

    $success = 'تم حفظ الإعدادات بنجاح';
}


// Fetch current settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

$autoApproval = isset($settingsData['auto_approval']) ? (int) $settingsData['auto_approval'] : 0;
$maintenanceMode = isset($settingsData['maintenance_mode']) ? (int) $settingsData['maintenance_mode'] : 0;


// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM memorials");
$totalMemorials = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(visits) FROM memorials");
$totalVisits = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT SUM(tasbeeh_subhan + tasbeeh_alham + tasbeeh_lailaha + tasbeeh_allahu) FROM memorials");
$totalTasbeeh = $stmt->fetchColumn();

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الإعدادات — <?= SITE_NAME ?></title>
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

        <h1 class="mb-4">إعدادات الموقع</h1>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= number_format($totalMemorials) ?></h3>
                        <p class="text-muted mb-0">إجمالي الصفحات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= number_format($totalVisits) ?></h3>
                        <p class="text-muted mb-0">إجمالي الزيارات</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info"><?= number_format($totalTasbeeh) ?></h3>
                        <p class="text-muted mb-0">إجمالي التسبيحات</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Form -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">⚙️ إعدادات عامة</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>
                    
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="auto_approval" name="auto_approval"
                            value="1" <?= $autoApproval === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="auto_approval">السماح بالموافقة التلقائية على الصفحات
                            الجديدة</label>
                    </div>
                    
                    <div class="mb-3 form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode"
                            value="1" <?= $maintenanceMode === 1 ? 'checked' : '' ?>>
                        <label class="form-check-label" for="maintenance_mode">
                            <strong>وضع الصيانة</strong> — عند التفعيل، لن يتمكن الزوار من الوصول للموقع (المشرفون فقط)
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">حفظ الإعدادات</button>
                </form>
            </div>
        </div>

        <!-- System Info -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">📊 معلومات النظام</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>إصدار PHP:</th>
                        <td><?= phpversion() ?></td>
                    </tr>
                    <tr>
                        <th>قاعدة البيانات:</th>
                        <td><?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?></td>
                    </tr>
                    <tr>
                        <th>الرابط الأساسي:</th>
                        <td><?= BASE_URL ?></td>
                    </tr>
                    <tr>
                        <th>وضع التطوير:</th>
                        <td><?= DEBUG_MODE ? '<span class="badge bg-warning">مفعّل</span>' : '<span class="badge bg-success">معطّل</span>' ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
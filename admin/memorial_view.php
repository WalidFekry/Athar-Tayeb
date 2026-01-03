<?php
/**
 * Admin Memorial View
 * View full details of a memorial page
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$success = '';
$error = '';

$memorialId = (int) ($_GET['id'] ?? 0);

if (!$memorialId) {
    redirect(ADMIN_URL . '/memorials.php');
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    checkCSRF();

    $action = $_POST['action'];

    if ($action === 'delete') {
        $deleteId = (int) $_POST['memorial_id'];

        if ($deleteId === $memorialId) {
            // Get memorial data for file cleanup
            $stmt = $pdo->prepare("SELECT image FROM memorials WHERE id = ?");
            $stmt->execute([$deleteId]);
            $memorialToDelete = $stmt->fetch();

            if ($memorialToDelete && $memorialToDelete['image']) {
                // Delete main image
                $imagePath = UPLOAD_PATH . '/' . $memorialToDelete['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                // Delete thumbnail
                $ext = pathinfo($memorialToDelete['image'], PATHINFO_EXTENSION);
                $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }

                // Delete Duaa card if exists
                $duaaImagePath = __DIR__ . '/../public/uploads/duaa_images/' . $memorialToDelete['image'];
                if (file_exists($duaaImagePath)) {
                    unlink($duaaImagePath);
                }
            }

            // Delete memorial record from database
            $stmt = $pdo->prepare("DELETE FROM memorials WHERE id = ?");
            $stmt->execute([$deleteId]);

            // Redirect back to memorials list with success message
            redirect(ADMIN_URL . '/memorials.php?deleted=1');
        }
    } elseif ($action === 'delete_image') {
        $deleteImageId = (int) $_POST['memorial_id'];

        if ($deleteImageId === $memorialId) {
            // Get memorial data for file cleanup
            $stmt = $pdo->prepare("SELECT image FROM memorials WHERE id = ?");
            $stmt->execute([$deleteImageId]);
            $memorialData = $stmt->fetch();

            if ($memorialData && $memorialData['image']) {
                // Delete main image
                $imagePath = UPLOAD_PATH . '/' . $memorialData['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }

                // Delete thumbnail
                $ext = pathinfo($memorialData['image'], PATHINFO_EXTENSION);
                $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }

                // Delete Duaa card if exists
                $duaaImagePath = __DIR__ . '/../public/uploads/duaa_images/' . $memorialData['image'];
                if (file_exists($duaaImagePath)) {
                    unlink($duaaImagePath);
                }

                // Update database: set image to NULL and image_status to 0
                $stmt = $pdo->prepare("UPDATE memorials SET image = NULL, image_status = 0 WHERE id = ?");
                $stmt->execute([$deleteImageId]);

                $success = 'تم حذف الصورة وبطاقة الدعاء بنجاح.';

                // Refresh memorial data
                $stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
                $stmt->execute([$memorialId]);
                $memorial = $stmt->fetch();
            } else {
                $error = 'لا توجد صورة لحذفها.';
            }
        }
    } elseif ($action === 'block_ip') {
        $blockId = (int) $_POST['memorial_id'];

        if ($blockId === $memorialId) {
            // Get IP address for this memorial
            $stmt = $pdo->prepare("SELECT ip_address FROM memorials WHERE id = ?");
            $stmt->execute([$blockId]);
            $memorialIpRow = $stmt->fetch();

            if ($memorialIpRow && !empty($memorialIpRow['ip_address'])) {
                $ipToBlock = $memorialIpRow['ip_address'];

                // Check if already blocked
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM blocked_ips WHERE ip_address = ?");
                $stmt->execute([$ipToBlock]);
                $alreadyBlocked = (int) $stmt->fetchColumn() > 0;

                if ($alreadyBlocked) {
                    $error = 'تم حظر هذا العنوان من قبل.';
                } else {
                    $reason = 'حظر من الصفحة التذكارية رقم ' . $blockId;
                    $blockedBy = isset($_SESSION['admin_id']) ? (int) $_SESSION['admin_id'] : null;

                    $stmt = $pdo->prepare("INSERT INTO blocked_ips (ip_address, reason, blocked_by) VALUES (?, ?, ?)");
                    $stmt->execute([$ipToBlock, $reason, $blockedBy]);

                    $success = 'تم حظر هذا المستخدم بنجاح.';
                }
            } else {
                $error = 'لا يوجد عنوان IP صالح لهذه الصفحة.';
            }
        }
    }
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

        <?php if ($success): ?>
            <div class="alert alert-success"><?= e($success) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

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
                                <td><?= $memorial['death_date'] ? formatArabicDate($memorial['death_date']) : '—' ?>
                                </td>
                            </tr>
                            <tr>
                                <th>واتساب:</th>
                                <td><?= e($memorial['whatsapp'] ?: '—') ?></td>
                            </tr>
                            <tr>
                                <th>عنوان IP:</th>
                                <td><?= e($memorial['ip_address'] ?: '—') ?></td>
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
                                    <?php elseif (!$memorial['image'] && $memorial['image_status'] == 0): ?>
                                        <span class="badge bg-danger">لا توجد صورة</span>
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
                                <th>حالة بطاقة الدعاء:</th>
                                <td>
                                    <?php
                                    $isDuaaEnabled = !empty($memorial['generate_duaa_image']);
                                    ?>
                                    <?php if ($isDuaaEnabled): ?>
                                        <span class="badge bg-success">مفعّلة ✅</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">غير مفعّلة ❌</span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <th>عدد الزيارات:</th>
                                <td><?= number_format($memorial['visits']) ?></td>
                            </tr>
                            <tr>
                                <th>آخر زيارة:</th>
                                <td><?= $memorial['last_visit'] ? timeAgoInArabic($memorial['last_visit']) : '—' ?></td>
                            </tr>
                            <tr>
                                <th>تاريخ الإنشاء:</th>
                                <td><?= formatArabicDate($memorial['created_at']) ?></td>
                            </tr>
                            <tr>
                                <th>تاريخ التحديث:</th>
                                <td><?= $memorial['updated_at'] ?></td>
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
                    <img src="<?= getImageUrl($memorial['image']) ?>" alt="<?= e($memorial['name']) ?>"
                        class="img-fluid rounded" style="max-width: 400px;">
                </div>
            </div>
        <?php endif; ?>

        <?php
        // Get duaa card URL once
        $duaaCardUrl = getDuaaCardUrl($memorial['image'] ?? null);
        $hasDuaaCard = !empty($duaaCardUrl);
        ?>

        <?php if ($hasDuaaCard): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        📜 بطاقة الدعاء
                    </h5>
                </div>

                <div class="card-body">
                    <div class="text-center">
                        <img src="<?= htmlspecialchars($duaaCardUrl, ENT_QUOTES, 'UTF-8') ?>"
                            alt="بطاقة الدعاء <?= e($memorial['name']) ?>" class="img-fluid rounded"
                            style="max-width: 400px;" loading="lazy">
                    </div>
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
                    <a href="<?= BASE_URL ?>/m/<?= $memorial['id'] ?>" target="_blank" class="btn btn-primary">
                        👁️ عرض الصفحة
                    </a>
                    <a href="<?= ADMIN_URL ?>/memorials.php?action=edit&id=<?= $memorial['id'] ?>"
                        class="btn btn-warning">
                        ✏️ تعديل
                    </a>
                    <form method="POST" style="display: inline;"
                        onsubmit="return confirm('هل أنت متأكد من حظر هذا المستخدم؟ سيتم منعه من إنشاء صفحات تذكارية جديدة من هذا العنوان.')">
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="block_ip">
                        <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            ☠️ حظر المستخدم
                        </button>
                    </form>
                    <?php if ($memorial['image']): ?>
                        <form method="POST" style="display: inline;"
                            onsubmit="return confirm('هل أنت متأكد من حذف الصورة وبطاقة الدعاء؟ سيتم حذف الصورة الأصلية والمصغرة وبطاقة الدعاء إن وجدت. الصفحة التذكارية ستبقى موجودة بدون صورة.')">
                            <?php csrfField(); ?>
                            <input type="hidden" name="action" value="delete_image">
                            <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                            <button type="submit" class="btn btn-warning">
                                🖼️ حذف الصورة فقط
                            </button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" style="display: inline;"
                        onsubmit="return confirm('هل أنت متأكد من حذف هذه الصفحة نهائياً؟ سيتم حذف جميع الصور والبيانات المرتبطة بها. هذا الإجراء لا يمكن التراجع عنه.')">
                        <?php csrfField(); ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            🗑️ حذف الصفحة
                        </button>
                    </form>
                    <a href="<?= ADMIN_URL ?>/memorials.php" class="btn btn-secondary">
                        ← العودة للقائمة
                    </a>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
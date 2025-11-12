<?php
/**
 * Admin Memorials Management
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

// Handle edit form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    checkCSRF();
    
    $memorialId = (int)$_POST['memorial_id'];
    $name = trim($_POST['name'] ?? '');
    $fromName = trim($_POST['from_name'] ?? '');
    $deathDate = trim($_POST['death_date'] ?? '');
    $gender = trim($_POST['gender'] ?? 'male');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $quote = trim($_POST['quote'] ?? '');
    $status = (int)($_POST['status'] ?? 0);
    $imageStatus = (int)($_POST['image_status'] ?? 0);
    $quoteStatus = (int)($_POST['quote_status'] ?? 0);
    
    if (!empty($name)) {
        $stmt = $pdo->prepare("
            UPDATE memorials 
            SET name = ?, from_name = ?, death_date = ?, gender = ?, whatsapp = ?, 
                quote = ?, status = ?, image_status = ?, quote_status = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $name,
            $fromName ?: null,
            $deathDate ?: null,
            $gender,
            $whatsapp ?: null,
            $quote ?: null,
            $status,
            $imageStatus,
            $quoteStatus,
            $memorialId
        ]);
        invalidateMemorialCache($memorialId);
        $success = 'تم تحديث الصفحة بنجاح';
    }
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] !== 'update') {
    checkCSRF();
    
    $memorialId = (int)$_POST['memorial_id'];
    $action = $_POST['action'];
    
    switch ($action) {
        case 'publish':
            $stmt = $pdo->prepare("UPDATE memorials SET status = 1 WHERE id = ?");
            $stmt->execute([$memorialId]);
            invalidateMemorialCache($memorialId);
            $success = 'تم نشر الصفحة بنجاح';
            break;
            
        case 'unpublish':
            $stmt = $pdo->prepare("UPDATE memorials SET status = 0 WHERE id = ?");
            $stmt->execute([$memorialId]);
            invalidateMemorialCache($memorialId);
            $success = 'تم إلغاء نشر الصفحة';
            break;
            
        case 'delete':
            // Get memorial to delete image
            $stmt = $pdo->prepare("SELECT image FROM memorials WHERE id = ?");
            $stmt->execute([$memorialId]);
            $memorial = $stmt->fetch();
            
            if ($memorial && $memorial['image']) {
                $imagePath = UPLOAD_PATH . '/' . $memorial['image'];
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                // Delete thumbnail
                $ext = pathinfo($memorial['image'], PATHINFO_EXTENSION);
                $thumbPath = str_replace('.' . $ext, '_thumb.' . $ext, $imagePath);
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }
            
            $stmt = $pdo->prepare("DELETE FROM memorials WHERE id = ?");
            $stmt->execute([$memorialId]);
            invalidateMemorialCache($memorialId);
            $success = 'تم حذف الصفحة بنجاح';
            break;
    }
}

// Check if we're in edit mode
$editMode = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
$editMemorial = null;

if ($editMode) {
    $editId = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
    $stmt->execute([$editId]);
    $editMemorial = $stmt->fetch();
    
    if (!$editMemorial) {
        $editMode = false;
    }
}

// Filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$idSearch = $_GET['id_search'] ?? '';

// Build query
$where = [];
$params = [];

if ($filter === 'pending') {
    $where[] = 'status = 0';
} elseif ($filter === 'published') {
    $where[] = 'status = 1';
} elseif ($filter === 'rejected') {
    $where[] = 'status = 2';
}

if (!empty($search)) {
    $where[] = '(name LIKE ? OR from_name LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

if (!empty($idSearch) && is_numeric($idSearch)) {
    $where[] = 'id = ?';
    $params[] = (int)$idSearch;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * ITEMS_PER_PAGE;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM memorials $whereClause");
$stmt->execute($params);
$totalMemorials = $stmt->fetchColumn();
$totalPages = ceil($totalMemorials / ITEMS_PER_PAGE);

// Fetch memorials
$stmt = $pdo->prepare("
    SELECT * FROM memorials 
    $whereClause
    ORDER BY created_at DESC
    LIMIT " . ITEMS_PER_PAGE . " OFFSET $offset
");
$stmt->execute($params);
$memorials = $stmt->fetchAll();

$pageTitle = 'إدارة الصفحات التذكارية';
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
    
    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">🌿 <?= SITE_NAME ?> — الإدارة</a>
            <a href="<?= ADMIN_URL ?>/dashboard.php" class="btn btn-sm btn-light">← العودة للوحة التحكم</a>
        </div>
    </nav>


<div class="container my-5">
    
    <h1 class="mb-4">إدارة الصفحات التذكارية</h1>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    
    <?php if ($editMode && $editMemorial): ?>
        <!-- Edit Form -->
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <h5 class="mb-0">✏️ تعديل الصفحة التذكارية: <?= e($editMemorial['name']) ?></h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <?php csrfField(); ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="memorial_id" value="<?= $editMemorial['id'] ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">اسم المتوفى <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= e($editMemorial['name']) ?>" required>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">إهداء من</label>
                                <input type="text" name="from_name" class="form-control" value="<?= e($editMemorial['from_name'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">تاريخ الوفاة</label>
                                <input type="date" name="death_date" class="form-control" value="<?= e($editMemorial['death_date'] ?? '') ?>">
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">النوع</label>
                                <select name="gender" class="form-select">
                                    <option value="male" <?= $editMemorial['gender'] === 'male' ? 'selected' : '' ?>>ذكر</option>
                                    <option value="female" <?= $editMemorial['gender'] === 'female' ? 'selected' : '' ?>>أنثى</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">رقم واتساب</label>
                        <input type="text" name="whatsapp" class="form-control" value="<?= e($editMemorial['whatsapp'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">الرسالة / الاقتباس</label>
                        <textarea name="quote" class="form-control" rows="4"><?= e($editMemorial['quote'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">حالة الصفحة</label>
                                <select name="status" class="form-select">
                                    <option value="0" <?= $editMemorial['status'] == 0 ? 'selected' : '' ?>>قيد المراجعة</option>
                                    <option value="1" <?= $editMemorial['status'] == 1 ? 'selected' : '' ?>>منشور</option>
                                    <option value="2" <?= $editMemorial['status'] == 2 ? 'selected' : '' ?>>مرفوض</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">حالة الصورة</label>
                                <select name="image_status" class="form-select">
                                    <option value="0" <?= $editMemorial['image_status'] == 0 ? 'selected' : '' ?>>قيد المراجعة</option>
                                    <option value="1" <?= $editMemorial['image_status'] == 1 ? 'selected' : '' ?>>موافق عليها</option>
                                    <option value="2" <?= $editMemorial['image_status'] == 2 ? 'selected' : '' ?>>مرفوضة</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">حالة الرسالة</label>
                                <select name="quote_status" class="form-select">
                                    <option value="0" <?= $editMemorial['quote_status'] == 0 ? 'selected' : '' ?>>قيد المراجعة</option>
                                    <option value="1" <?= $editMemorial['quote_status'] == 1 ? 'selected' : '' ?>>موافق عليها</option>
                                    <option value="2" <?= $editMemorial['quote_status'] == 2 ? 'selected' : '' ?>>مرفوضة</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success">💾 حفظ التعديلات</button>
                        <a href="<?= ADMIN_URL ?>/memorials.php" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="filter" class="form-label">الحالة</label>
                    <select name="filter" id="filter" class="form-select">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>الكل</option>
                        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                        <option value="published" <?= $filter === 'published' ? 'selected' : '' ?>>منشور</option>
                        <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="id_search" class="form-label">بحث برقم الصفحة</label>
                    <input type="number" name="id_search" id="id_search" class="form-control" placeholder="رقم الصفحة" value="<?= e($idSearch) ?>" min="1">
                </div>
                
                <div class="col-md-4">
                    <label for="search" class="form-label">بحث بالاسم</label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="بحث بالاسم..." value="<?= e($search) ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">بحث</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Memorials Table -->
    <div class="card">
        <div class="card-body">
            <?php if (count($memorials) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>الاسم</th>
                                <th>من</th>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th>الزيارات</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($memorials as $memorial): ?>
                                <tr>
                                    <td><?= $memorial['id'] ?></td>
                                    <td><?= e($memorial['name']) ?></td>
                                    <td><?= e($memorial['from_name'] ?? '-') ?></td>
                                    <td><?= date('Y-m-d', strtotime($memorial['created_at'])) ?></td>
                                    <td>
                                        <?php if ($memorial['status'] == 1): ?>
                                            <span class="badge bg-success">منشور</span>
                                        <?php elseif ($memorial['status'] == 2): ?>
                                            <span class="badge bg-danger">مرفوض</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">قيد المراجعة</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $memorial['visits'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= ADMIN_URL ?>/memorial_view.php?id=<?= $memorial['id'] ?>" class="btn btn-info">عرض</a>
                                            
                                            <?php if ($memorial['status'] != 1): ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                                                    <input type="hidden" name="action" value="publish">
                                                    <button type="submit" class="btn btn-success" onclick="return confirm('نشر هذه الصفحة؟')">نشر</button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <?php csrfField(); ?>
                                                    <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                                                    <input type="hidden" name="action" value="unpublish">
                                                    <button type="submit" class="btn btn-warning" onclick="return confirm('إلغاء نشر هذه الصفحة؟')">إلغاء النشر</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" style="display: inline;">
                                                <?php csrfField(); ?>
                                                <input type="hidden" name="memorial_id" value="<?= $memorial['id'] ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-danger" onclick="return confirm('حذف هذه الصفحة نهائياً؟')">حذف</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-3">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>&id_search=<?= urlencode($idSearch) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <p class="text-muted text-center">لا توجد نتائج</p>
            <?php endif; ?>
        </div>
    </div>
    
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

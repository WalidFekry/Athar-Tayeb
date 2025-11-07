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

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
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

// Filters
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

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
include __DIR__ . '/dashboard.php'; // Reuse header
?>

<div class="container my-5">
    
    <h1 class="mb-4">إدارة الصفحات التذكارية</h1>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= e($success) ?></div>
    <?php endif; ?>
    
    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="filter" class="form-select">
                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>الكل</option>
                        <option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>قيد المراجعة</option>
                        <option value="published" <?= $filter === 'published' ? 'selected' : '' ?>>منشور</option>
                        <option value="rejected" <?= $filter === 'rejected' ? 'selected' : '' ?>>مرفوض</option>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم..." value="<?= e($search) ?>">
                </div>
                
                <div class="col-md-2">
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
                                    <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
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

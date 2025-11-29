<?php
/**
 * Admin Contact Messages
 * View and manage contact form submissions
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

// Handle GET AJAX request for message details
if (isset($_GET['action']) && $_GET['action'] === 'get_message' && isset($_GET['id'])) {
    $messageId = (int) $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->execute([$messageId]);
    $message = $stmt->fetch();

    if ($message) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => [
                'name' => $message['name'],
                'whatsapp' => $message['whatsapp'],
                'email' => $message['email'],
                'message' => $message['message'],
                'created_at' => date('Y-m-d H:i', strtotime($message['created_at'])),
                'ip_address' => $message['ip_address'],
                'is_read' => (bool) $message['is_read']
            ]
        ]);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Message not found']);
        exit;
    }
}

// Handle POST AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }

    $action = $_POST['action'];
    $messageId = (int) ($_POST['message_id'] ?? 0);

    if ($messageId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID']);
        exit;
    }

    try {
        if ($action === 'toggle_read') {
            // Toggle read status
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = NOT is_read WHERE id = ?");
            $stmt->execute([$messageId]);

            // Get new status
            $stmt = $pdo->prepare("SELECT is_read FROM contact_messages WHERE id = ?");
            $stmt->execute([$messageId]);
            $newStatus = $stmt->fetchColumn();

            // Get unread count
            $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
            $unreadCount = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'is_read' => (bool) $newStatus,
                'unread_count' => $unreadCount
            ]);

        } elseif ($action === 'delete') {
            // Delete message
            $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
            $stmt->execute([$messageId]);

            // Get unread count
            $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
            $unreadCount = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'unread_count' => $unreadCount
            ]);

        } elseif ($action === 'mark_read') {
            // Mark as read (when viewing message detail)
            $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$messageId]);

            // Get unread count
            $stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
            $unreadCount = $stmt->fetchColumn();

            echo json_encode([
                'success' => true,
                'unread_count' => $unreadCount
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Unknown action']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error']);
    }

    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Filter
$filter = $_GET['filter'] ?? 'all';
$whereClause = $filter === 'unread' ? 'WHERE is_read = 0' : '';

// Get total count
$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages $whereClause");
$totalMessages = $stmt->fetchColumn();
$totalPages = ceil($totalMessages / $perPage);

// Get messages
$stmt = $pdo->prepare("
    SELECT id, name, whatsapp, email, message, is_read, created_at, ip_address
    FROM contact_messages
    $whereClause
    ORDER BY created_at DESC
    LIMIT $perPage OFFSET $offset
");
$stmt->execute();
$messages = $stmt->fetchAll();

// Get statistics
$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages");
$totalCount = $stmt->fetchColumn();

$stmt = $pdo->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$unreadCount = $stmt->fetchColumn();

$pageTitle = 'رسائل التواصل';
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
    <style>
        .message-row.unread {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .badge-status {
            font-size: 0.75rem;
        }
    </style>
</head>

<body>

    <!-- Admin Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= ADMIN_URL ?>/dashboard.php">
                🌿 <?= SITE_NAME ?> — الإدارة
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="adminNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/dashboard.php">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="<?= ADMIN_URL ?>/contact_messages.php">
                            البريد <span class="badge bg-warning" id="navUnreadBadge"><?= $unreadCount ?></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/settings.php">الإعدادات</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>" target="_blank">عرض الموقع</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= ADMIN_URL ?>/logout.php">تسجيل الخروج</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">

        <h1 class="mb-4">رسائل التواصل 📧</h1>

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?= toArabicNumerals($totalCount) ?></h3>
                        <p class="text-muted mb-0">إجمالي الرسائل</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-info" id="unreadCountDisplay"><?= toArabicNumerals($unreadCount) ?></h3>
                        <p class="text-muted mb-0">رسائل غير مقروءة</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h3 class="text-success"><?= toArabicNumerals($totalCount - $unreadCount) ?></h3>
                        <p class="text-muted mb-0">رسائل مقروءة</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="btn-group" role="group">
                    <a href="?filter=all" class="btn btn-<?= $filter === 'all' ? 'primary' : 'outline-primary' ?>">
                        جميع الرسائل
                    </a>
                    <a href="?filter=unread"
                        class="btn btn-<?= $filter === 'unread' ? 'primary' : 'outline-primary' ?>">
                        غير المقروءة فقط
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages Table -->
        <div class="card">
            <div class="card-body">
                <?php if (count($messages) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>الاسم</th>
                                    <th>واتساب</th>
                                    <th>البريد</th>
                                    <th>الرسالة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>إجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                    <tr class="message-row <?= $msg['is_read'] ? '' : 'unread' ?>"
                                        data-message-id="<?= $msg['id'] ?>">
                                        <td><?= e($msg['name']) ?></td>
                                        <td><?= $msg['whatsapp'] ? e($msg['whatsapp']) : '<span class="text-muted">—</span>' ?>
                                        </td>
                                        <td><?= $msg['email'] ? e($msg['email']) : '<span class="text-muted">—</span>' ?></td>
                                        <td>
                                            <span
                                                class="message-preview"><?= e(mb_substr($msg['message'], 0, 50)) ?><?= mb_strlen($msg['message']) > 50 ? '...' : '' ?></span>
                                        </td>
                                        <td><?= date('Y-m-d H:i', strtotime($msg['created_at'])) ?></td>
                                        <td>
                                            <?php if ($msg['is_read']): ?>
                                                <span class="badge bg-success badge-status">مقروءة</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning badge-status">غير مقروءة</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary view-btn" data-id="<?= $msg['id'] ?>">
                                                عرض
                                            </button>
                                            <button
                                                class="btn btn-sm btn-<?= $msg['is_read'] ? 'secondary' : 'info' ?> toggle-read-btn"
                                                data-id="<?= $msg['id'] ?>">
                                                <?= $msg['is_read'] ? 'غير مقروءة' : 'مقروءة' ?>
                                            </button>
                                            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $msg['id'] ?>">
                                                حذف
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav aria-label="تنقل بين الصفحات" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link"
                                            href="?page=<?= $i ?>&filter=<?= $filter ?>"><?= toArabicNumerals($i) ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-5">
                        <p class="mb-0">لا توجد رسائل<?= $filter === 'unread' ? ' غير مقروءة' : '' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <!-- Message Detail Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">تفاصيل الرسالة</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body">
                    <div id="messageContent">
                        <!-- Content will be loaded via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                    <button type="button" class="btn btn-info" id="modalToggleReadBtn">تبديل الحالة</button>
                    <button type="button" class="btn btn-danger" id="modalDeleteBtn">حذف</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const csrfToken = '<?= getCSRFToken() ?>';
        let currentMessageId = null;
        const modal = new bootstrap.Modal(document.getElementById('messageModal'));

        // View message detail
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const messageId = this.dataset.id;
                currentMessageId = messageId;
                viewMessage(messageId);
            });
        });

        function viewMessage(messageId) {
            // Make AJAX request to get full message details
            fetch(`contact_messages.php?action=get_message&id=${messageId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const msg = data.message;
                        document.getElementById('messageContent').innerHTML = `
                            <div class="mb-3">
                                <strong>الاسم:</strong> ${escapeHtml(msg.name)}
                            </div>
                            <div class="mb-3">
                                <strong>رقم الواتساب:</strong> ${msg.whatsapp || '<span class="text-muted">غير محدد</span>'}
                            </div>
                            <div class="mb-3">
                                <strong>البريد الإلكتروني:</strong> ${msg.email || '<span class="text-muted">غير محدد</span>'}
                            </div>
                            <div class="mb-3">
                                <strong>الرسالة:</strong>
                                <div class="border rounded p-3 bg-light mt-2">
                                    ${escapeHtml(msg.message).replace(/\n/g, '<br>')}
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>تاريخ الإرسال:</strong> ${msg.created_at}
                            </div>
                            <div class="mb-3">
                                <strong>عنوان IP:</strong> <code>${msg.ip_address || 'غير معروف'}</code>
                            </div>
                        `;

                        modal.show();

                        // Mark as read if unread
                        if (!msg.is_read) {
                            markAsRead(messageId);
                        }
                    } else {
                        alert('حدث خطأ في تحميل الرسالة');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('حدث خطأ في تحميل الرسالة');
                });
        }

        // Toggle read status
        document.querySelectorAll('.toggle-read-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                toggleRead(this.dataset.id);
            });
        });

        document.getElementById('modalToggleReadBtn').addEventListener('click', function () {
            if (currentMessageId) {
                toggleRead(currentMessageId);
            }
        });

        function toggleRead(messageId) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=toggle_read&message_id=${messageId}&csrf_token=${csrfToken}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function markAsRead(messageId) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_read&message_id=${messageId}&csrf_token=${csrfToken}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateUnreadCount(data.unread_count);
                        // Update row styling
                        const row = document.querySelector(`tr[data-message-id="${messageId}"]`);
                        if (row) {
                            row.classList.remove('unread');
                            const badge = row.querySelector('.badge-status');
                            badge.className = 'badge bg-success badge-status';
                            badge.textContent = 'مقروءة';
                            const toggleBtn = row.querySelector('.toggle-read-btn');
                            toggleBtn.className = 'btn btn-sm btn-secondary toggle-read-btn';
                            toggleBtn.textContent = 'غير مقروءة';
                        }
                    }
                });
        }

        // Delete message
        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (confirm('هل أنت متأكد من حذف هذه الرسالة؟')) {
                    deleteMessage(this.dataset.id);
                }
            });
        });

        document.getElementById('modalDeleteBtn').addEventListener('click', function () {
            if (currentMessageId && confirm('هل أنت متأكد من حذف هذه الرسالة؟')) {
                deleteMessage(currentMessageId);
                modal.hide();
            }
        });

        function deleteMessage(messageId) {
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete&message_id=${messageId}&csrf_token=${csrfToken}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }

        function updateUnreadCount(count) {
            document.getElementById('navUnreadBadge').textContent = count;
            document.getElementById('unreadCountDisplay').textContent = toArabicNumerals(count);
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function toArabicNumerals(num) {
            const western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
            const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            return String(num).split('').map(c => {
                const idx = western.indexOf(c);
                return idx >= 0 ? arabic[idx] : c;
            }).join('');
        }
    </script>
</body>

</html>
<?php
/**
 * Admin Memorial View
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$memorialId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM memorials WHERE id = ?");
$stmt->execute([$memorialId]);
$memorial = $stmt->fetch();

if (!$memorial) {
    redirect(ADMIN_URL . '/memorials.php');
}

echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>عرض الصفحة</title></head><body>';
echo '<h1>عرض الصفحة: ' . e($memorial['name']) . '</h1>';
echo '<p>ID: ' . $memorial['id'] . '</p>';
echo '<p>الحالة: ' . ($memorial['status'] == 1 ? 'منشور' : 'قيد المراجعة') . '</p>';
echo '<a href="' . ADMIN_URL . '/memorials.php">العودة</a>';
echo '</body></html>';

<?php
/**
 * Database Backups
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

echo '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><title>النسخ الاحتياطي</title></head><body>';
echo '<h1>النسخ الاحتياطي</h1>';
echo '<p>صفحة النسخ الاحتياطي - يمكن تصدير قاعدة البيانات والملفات</p>';
echo '<a href="' . ADMIN_URL . '/dashboard.php">العودة للوحة التحكم</a>';
echo '</body></html>';

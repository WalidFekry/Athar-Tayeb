# دليل التثبيت — أثر طيب

## المتطلبات الأساسية

قبل البدء، تأكد من توفر:

- ✅ PHP 7.4 أو أحدث
- ✅ MySQL 5.7 أو أحدث (أو MariaDB)
- ✅ Apache مع mod_rewrite (أو Nginx)
- ✅ PHP GD Library (لمعالجة الصور)
- ✅ 50MB مساحة قرص على الأقل

## خطوات التثبيت

### 1. رفع الملفات

**أ) على الاستضافة المشتركة (cPanel):**

1. ارفع جميع الملفات إلى المجلد الرئيسي
2. تأكد من أن مجلد `public` هو web root
3. إذا لم يكن كذلك، انقل محتويات `public` إلى `public_html`

**ب) على السيرفر المحلي (XAMPP/AMPPS):**

1. انسخ المجلد `athar-tayeb` إلى:
   - XAMPP: `C:\xampp\htdocs\`
   - AMPPS: `C:\Program Files\Ampps\www\`
2. افتح المتصفح على: `http://localhost/athar-tayeb/public/`

### 2. إنشاء قاعدة البيانات

**أ) عبر phpMyAdmin:**

1. افتح phpMyAdmin
2. اضغط "New" لإنشاء قاعدة بيانات جديدة
3. اسم القاعدة: `athartayeb_db`
4. Collation: `utf8mb4_unicode_ci`
5. اضغط "Create"
6. اختر القاعدة ثم اضغط "Import"
7. اختر ملف `sql/athartayeb_schema.sql`
8. اضغط "Go"

**ب) عبر سطر الأوامر:**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE athartayeb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

```bash
mysql -u root -p athartayeb_db < sql/athartayeb_schema.sql
```

### 3. تحديث إعدادات الاتصال

افتح ملف `includes/config.php` وعدّل:

```php
// Database Configuration
define('DB_HOST', 'localhost');          // عادة localhost
define('DB_NAME', 'athartayeb_db');      // اسم قاعدة البيانات
define('DB_USER', 'root');               // اسم المستخدم
define('DB_PASS', '');                   // كلمة المرور

// Base URL
define('BASE_URL', 'http://localhost/athar-tayeb/public');  // عدّل حسب موقعك
define('ADMIN_URL', 'http://localhost/athar-tayeb/admin');
```

**مثال للاستضافة المشتركة:**

```php
define('BASE_URL', 'https://yoursite.com');
define('ADMIN_URL', 'https://yoursite.com/admin');
```

### 4. إنشاء مجلدات الرفع والكاش

**على Linux/Mac:**

```bash
mkdir -p public/uploads/memorials
mkdir -p cache
mkdir -p logs
chmod 755 public/uploads/memorials
chmod 755 cache
chmod 755 logs
```

**على Windows (أو يدوياً):**

1. أنشئ مجلد `public/uploads/memorials`
2. أنشئ مجلد `cache`
3. أنشئ مجلد `logs`
4. تأكد من أن PHP يمكنه الكتابة في هذه المجلدات

### 5. تفعيل mod_rewrite (Apache)

**على XAMPP/AMPPS:**

1. افتح `httpd.conf`
2. ابحث عن: `#LoadModule rewrite_module modules/mod_rewrite.so`
3. احذف `#` من بداية السطر
4. أعد تشغيل Apache

**على cPanel:**

- عادة mod_rewrite مفعّل افتراضياً
- تأكد من وجود ملف `.htaccess` في المجلد الرئيسي

### 6. اختبار التثبيت

1. افتح المتصفح على: `http://yoursite.com` (أو `http://localhost/athar-tayeb/public/`)
2. يجب أن تظهر الصفحة الرئيسية
3. جرّب إنشاء صفحة تذكارية تجريبية

### 7. تسجيل الدخول للإدارة

1. اذهب إلى: `http://yoursite.com/admin/login.php`
2. **اسم المستخدم:** `admin`
3. **كلمة المرور:** `admin123`

⚠️ **مهم جداً:** غيّر كلمة المرور فوراً!

## تغيير كلمة مرور الإدارة

### الطريقة 1: عبر phpMyAdmin

1. افتح phpMyAdmin
2. اختر قاعدة `athartayeb_db`
3. افتح جدول `admins`
4. عدّل سجل `admin`
5. في حقل `password`، ضع:

```php
$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
```

(هذا hash لكلمة المرور `admin123`)

### الطريقة 2: عبر PHP

أنشئ ملف `change_password.php` في المجلد الرئيسي:

```php
<?php
$newPassword = 'your_new_password_here';
$hash = password_hash($newPassword, PASSWORD_DEFAULT);
echo "New password hash: " . $hash;
// انسخ الـ hash وضعه في قاعدة البيانات
?>
```

## إعدادات إضافية

### تفعيل HTTPS (موصى به)

في `includes/config.php`:

```php
define('BASE_URL', 'https://yoursite.com');
```

### تعديل حدود الرفع

في `includes/config.php`:

```php
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
```

أو في `php.ini`:

```ini
upload_max_filesize = 5M
post_max_size = 5M
```

### تفعيل الكاش

في `includes/config.php`:

```php
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // ساعة واحدة
```

## استكشاف الأخطاء

### خطأ: "Database connection failed"

- تأكد من صحة بيانات الاتصال في `config.php`
- تأكد من تشغيل MySQL
- تأكد من وجود قاعدة البيانات

### خطأ: "404 Not Found" للصفحات التذكارية

- تأكد من تفعيل mod_rewrite
- تأكد من وجود ملف `.htaccess`
- تحقق من `RewriteBase` في `.htaccess`

### الصور لا تُرفع

- تأكد من وجود مجلد `public/uploads/memorials`
- تأكد من أذونات الكتابة (755 أو 777)
- تحقق من حدود الرفع في PHP

### الجلسات لا تعمل

- تأكد من أن PHP يمكنه الكتابة في مجلد الجلسات
- تحقق من إعدادات `session.save_path` في `php.ini`

## الأمان بعد التثبيت

1. ✅ غيّر كلمة مرور الإدارة
2. ✅ احذف ملف `change_password.php` إن أنشأته
3. ✅ تأكد من أن مجلدات `includes`, `sql`, `logs` غير قابلة للوصول عبر المتصفح
4. ✅ فعّل HTTPS إن أمكن
5. ✅ راجع أذونات الملفات والمجلدات
6. ✅ فعّل النسخ الاحتياطي الدوري

## النسخ الاحتياطي

### قاعدة البيانات:

```bash
mysqldump -u root -p athartayeb_db > backup_$(date +%Y%m%d).sql
```

### الملفات:

```bash
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz public/uploads/
```

## الدعم

إذا واجهت مشاكل:

1. راجع ملف `logs/auth.log` للأخطاء
2. فعّل وضع التطوير في `config.php`: `define('ENV', 'development');`
3. تواصل معنا: support@athartayeb.com

---

© 2025 أثر طيب

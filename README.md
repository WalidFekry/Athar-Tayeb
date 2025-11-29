# أثر طيب (Athar Tayeb) — Digital Memorial Platform

**منصة رقمية لإنشاء صفحات تذكارية للمتوفين — صدقة جارية**

## 📖 نظرة عامة

أثر طيب هي منصة ويب مجانية تتيح للمستخدمين إنشاء صفحات تذكارية دائمة لأحبائهم المتوفين. كل صفحة تحتوي على:

- ✨ معلومات المتوفى وصورته
- 🤲 أدعية مخصصة مع الصوت
- 📿 عدادات تسبيح إلكترونية تفاعلية
- 📖 صفحة قرآن عشوائية مع الصوت
- 🕌 أذكار الصباح والمساء
- ✨ أسماء الله الحسنى
- 📤 أزرار مشاركة على وسائل التواصل
- 📱 أدلة إرشادية للمستخدمين (آداب الدعاء، كيفية الانتفاع، إلخ)

## 🛠️ التقنيات المستخدمة

- **Backend:** PHP 8.0+ (vanilla), PDO, MySQL
- **Frontend:** HTML5, CSS3, JavaScript (vanilla)
- **Framework:** Bootstrap 5 RTL
- **Database:** MySQL with utf8mb4 (full Arabic support)
- **Server:** Apache/Nginx (LAMP/LEMP)

## 📁 هيكل المشروع

```
athartayeb/
├── public/                # Web root
│   ├── index.php          # Home page
│   ├── create.php         # Create memorial
│   ├── memorial.php       # Memorial view (by ID)
│   ├── search.php         # Search page
│   ├── all.php            # All memorials listing
│   ├── contact.php        # Contact page
│   ├── guide.php          # General guide
│   ├── duaa-etiquette.php # Duaa etiquette guide
│   ├── how-to-benefit.php # How to benefit guide
│   ├── mobile-guide.php   # Mobile usage guide
│   ├── share-guide.php    # Sharing guide
│   ├── developer.php      # Developer info
│   ├── assets/            # CSS, JS, images
│   ├── uploads/           # User-uploaded images
│   └── api/               # API endpoints
├── admin/                 # Admin panel
│   ├── dashboard.php      # Main dashboard
│   ├── memorials.php      # Manage memorials
│   ├── contact_messages.php # View contact messages
│   ├── admins.php         # Manage admins
│   ├── blocked_ips.php    # Manage blocked IPs
│   ├── settings.php       # Site settings
│   ├── images_moderation.php # Moderate images
│   └── messages_moderation.php # Moderate messages
├── includes/              # Core PHP files
│   ├── config.php         # Configuration
│   ├── db.php             # Database connection
│   ├── functions.php      # Helper functions
│   ├── session.php        # Session management
│   ├── csrf.php           # Security
│   ├── generate_duaa_image.php # Image generation logic
│   ├── yaseen_modal.php   # Yaseen surah modal
│   └── header.php / footer.php
├── sql/                   # Database schema
│   └── athartayeb_schema.sql
├── setup.php              # Installation wizard
├── .htaccess             # Apache rewrite rules
└── README.md
```

## ✨ الميزات الرئيسية

### للمستخدمين:
- إنشاء صفحات تذكارية مجاناً
- رفع صور المتوفين (مع نظام موافقة)
- إضافة رسائل ودعاء مخصص
- مشاركة الصفحات عبر WhatsApp, Facebook, Telegram
- عدادات تسبيح تفاعلية مع حفظ الإحصائيات
- قرآن وأذكار بالصوت
- واجهة عربية كاملة RTL
- وضع ليلي (Dark Mode)
- أدلة إرشادية شاملة
- نموذج تواصل مع الإدارة

### للإدارة:
- لوحة تحكم شاملة للإحصائيات
- مراجعة الصور والرسائل قبل النشر
- إدارة رسائل التواصل (Contact Messages)
- حظر عناوين IP المسيئة
- إعدادات الموقع العامة (تفعيل/تعطيل الصيانة، الموافقة التلقائية)
- إدارة المديرين والصلاحيات
- سجلات الأنشطة

## 🔒 الأمان

- ✅ PDO prepared statements (حماية من SQL Injection)
- ✅ CSRF token protection
- ✅ XSS prevention (htmlspecialchars)
- ✅ Secure file upload validation
- ✅ Rate limiting (للتسبيح، التعديل، ونموذج التواصل)
- ✅ Session security (httponly, secure cookies)
- ✅ Password hashing (bcrypt)
- ✅ IP Blocking system

## 🚀 التثبيت

### الطريقة الأولى: معالج الإعداد (موصى به)

1. قم برفع الملفات إلى الخادم.
2. أنشئ قاعدة بيانات فارغة.
3. قم باستيراد ملف `sql/athartayeb_schema.sql` إلى قاعدة البيانات.
4. افتح المتصفح واذهب إلى `http://your-domain.com/setup.php`.
5. اتبع التعليمات للتحقق من المتطلبات وإنشاء حساب المدير.
6. **هام:** احذف ملف `setup.php` بعد الانتهاء.

### الطريقة الثانية: التثبيت اليدوي

1. **استيراد قاعدة البيانات:**
   ```bash
   mysql -u root -p < sql/athartayeb_schema.sql
   ```

2. **تحديث الإعدادات:**
   - انسخ `includes/config.php` وعدّل إعدادات قاعدة البيانات (`DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`).

3. **إنشاء المجلدات:**
   ```bash
   mkdir -p public/uploads/memorials
   chmod 755 public/uploads/memorials
   ```

4. **بيانات الدخول الافتراضية:**
   - **الرابط:** `admin/login.php`
   - **اسم المستخدم:** `admin`
   - **كلمة المرور:** `admin123`

   ⚠️ **مهم:** غيّر كلمة المرور فوراً من لوحة التحكم!

## 🎨 التخصيص

### الألوان والثيم
عدّل متغيرات CSS في `public/assets/css/main.css`:

```css
:root {
    --bg: #F9F6F2;          /* خلفية فاتحة */
    --primary: #5A7D4E;     /* اللون الأساسي (زيتوني) */
    --accent: #9DB37B;      /* لون ثانوي */
    /* ... */
}
```

### معلومات الموقع
عدّل الثوابت في `includes/config.php`:

```php
define('SITE_NAME', 'أثر طيب');
define('SITE_TAGLINE', 'لكي يبقى الأثر طيبًا بعد الرحيل 🌿');
define('BASE_URL', 'https://your-domain.com');
```

## 🤝 المساهمة

هذا المشروع مفتوح المصدر. يمكنك المساهمة بـ:
- الإبلاغ عن الأخطاء
- اقتراح ميزات جديدة
- تحسين الكود

## 📄 الترخيص

هذا المشروع مجاني للاستخدام الشخصي والتجاري.

---

© 2025 أثر طيب — صدقة جارية رقمية

<?php
/**
 * Athar Tayeb - Configuration File
 * Contains database credentials, paths, and environment settings
 */

// Environment
define('ENV', 'production'); // 'development' or 'production'
define('DEBUG_MODE', ENV === 'development');

// Base URL (update this to your domain)
define('BASE_URL', 'http://localhost/athar-tayeb/public');
define('ADMIN_URL', 'http://localhost/athar-tayeb/admin');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'athartayeb_db');
define('DB_USER', 'root');
define('DB_PASS', 'mysql');
define('DB_CHARSET', 'utf8mb4');

// Paths
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads/memorials');
define('CACHE_PATH', ROOT_PATH . '/cache');
define('LOGS_PATH', ROOT_PATH . '/logs');

// Upload Settings
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Cache Settings
define('CACHE_ENABLED', true);
define('CACHE_TTL', 3600); // 1 hour

// Rate Limiting
define('TASBEEH_RATE_LIMIT', 60); // max clicks per minute per field
define('CREATE_RATE_LIMIT', 5); // max creates per hour per IP

// Session Settings
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'athartayeb_session');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Site Information
define('SITE_NAME', 'أثر طيب');
define('SITE_TAGLINE', 'لكي يبقى الأثر طيبًا بعد الرحيل 🌿');
define('SITE_DESCRIPTION', 'منصة رقمية لإنشاء صفحات تذكارية للمتوفين - صدقة جارية');

// App Links
define('APP_MAKTBTI', 'https://play.google.com/store/apps/details?id=com.walid.maktbti');
define('APP_MAKTBTI_PLUS', 'https://play.google.com/store/apps/details?id=com.maktbti.plus');

// Developer Info
define('DEVELOPER_NAME', 'Walid Fekry');
define('DEVELOPER_URL', 'https://walid-fekry.com');
// Support Email
define('SUPPORT_EMAIL', 'walid_fekry@hotmail.com');

// Error Reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} 

// Timezone
date_default_timezone_set('Africa/Cairo');

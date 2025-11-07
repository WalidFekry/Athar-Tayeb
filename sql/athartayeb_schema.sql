-- Athar Tayeb Database Schema
-- UTF-8 Arabic support with utf8mb4 collation

-- Create Database
CREATE DATABASE IF NOT EXISTS athartayeb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE athartayeb_db;

-- Admins Table
CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'admin',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Memorials Table
CREATE TABLE IF NOT EXISTS memorials (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  from_name VARCHAR(255) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  image_status TINYINT(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
  quote TEXT DEFAULT NULL,
  quote_status TINYINT(1) DEFAULT 0 COMMENT '0=pending, 1=approved, 2=rejected',
  death_date DATE DEFAULT NULL,
  gender ENUM('male','female') DEFAULT 'male',
  whatsapp VARCHAR(50) DEFAULT NULL,
  visits INT DEFAULT 0,
  tasbeeh_subhan INT DEFAULT 0,
  tasbeeh_alham INT DEFAULT 0,
  tasbeeh_lailaha INT DEFAULT 0,
  tasbeeh_allahu INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  status TINYINT(1) DEFAULT 0 COMMENT '0=pending, 1=published, 2=rejected',
  rejected_reason TEXT DEFAULT NULL,
  INDEX idx_slug (slug),
  INDEX idx_status (status),
  INDEX idx_image_status (image_status),
  INDEX idx_quote_status (quote_status),
  INDEX idx_created_at (created_at),
  FULLTEXT idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings Table (for admin panel configuration)
CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Admin (password: admin123)
-- Password hash generated with: password_hash('admin123', PASSWORD_DEFAULT)
INSERT INTO admins (username, password, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Memorials for Testing
INSERT INTO memorials (name, slug, from_name, image, image_status, quote, quote_status, death_date, gender, status, visits, tasbeeh_subhan, tasbeeh_alham, tasbeeh_lailaha, tasbeeh_allahu) VALUES
('محمد أحمد السيد', 'محمد-أحمد-السيد-1', 'عائلة السيد', NULL, 1, 'كان رجلاً صالحاً محباً للخير، اللهم ارحمه واغفر له وأسكنه فسيح جناتك', 1, '2024-01-15', 'male', 1, 245, 1250, 890, 670, 1100),
('فاطمة محمود علي', 'فاطمة-محمود-علي-2', 'أبناء المرحومة', NULL, 1, 'أم حنونة وقلب طيب، رحمها الله وجعل الجنة مثواها', 1, '2023-12-20', 'female', 1, 189, 980, 750, 520, 890),
('عبدالله خالد', 'عبدالله-خالد-3', NULL, NULL, 0, 'في انتظار المراجعة', 0, '2024-02-01', 'male', 0, 12, 45, 30, 25, 40);

-- Insert Default Settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'أثر طيب'),
('site_tagline', 'لكي يبقى الأثر طيبًا بعد الرحيل 🌿'),
('site_description', 'منصة رقمية لإنشاء صفحات تذكارية للمتوفين - صدقة جارية'),
('maintenance_mode', '0'),
('memorials_require_approval', '1');

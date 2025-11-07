<?php
/**
 * Helper Functions
 * Utility functions for the application
 */

/**
 * Sanitize output for HTML display
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}



/**
 * Format date in Arabic
 */
function formatArabicDate($date, $format = 'long') {
    if (!$date) return '';
    
    $timestamp = is_numeric($date) ? $date : strtotime($date);
    
    $arabicMonths = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
        5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
        9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
    ];
    
    $day = date('j', $timestamp);
    $month = $arabicMonths[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    
    if ($format === 'short') {
        return "$day $month $year";
    }
    
    return "$day $month $year";
}

/**
 * Convert Arabic-Indic numerals to Arabic (Eastern Arabic numerals)
 */
function toArabicNumerals($number) {
    $western = ['0','1','2','3','4','5','6','7','8','9'];
    $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    return str_replace($western, $arabic, $number);
}

/**
 * Generate Open Graph meta tags
 */
function generateOGTags($title, $description, $image = null, $url = null) {
    $tags = [];
    $tags[] = '<meta property="og:title" content="' . e($title) . '">';
    $tags[] = '<meta property="og:description" content="' . e($description) . '">';
    $tags[] = '<meta property="og:type" content="website">';
    
    if ($image) {
        $tags[] = '<meta property="og:image" content="' . e($image) . '">';
    }
    
    if ($url) {
        $tags[] = '<meta property="og:url" content="' . e($url) . '">';
    }
    
    $tags[] = '<meta name="twitter:card" content="summary_large_image">';
    $tags[] = '<meta name="twitter:title" content="' . e($title) . '">';
    $tags[] = '<meta name="twitter:description" content="' . e($description) . '">';
    
    if ($image) {
        $tags[] = '<meta name="twitter:image" content="' . e($image) . '">';
    }
    
    return implode("\n    ", $tags);
}

/**
 * Get cached memorial HTML
 */
function getCachedMemorial($id) {
    if (!CACHE_ENABLED) return null;
    
    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";
    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_TTL) {
        return file_get_contents($cacheFile);
    }
    
    return null;
}

/**
 * Save memorial to cache
 */
function cacheMemorial($id, $html) {
    if (!CACHE_ENABLED) return false;
    
    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }
    
    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";
    return file_put_contents($cacheFile, $html) !== false;
}

/**
 * Invalidate memorial cache
 */
function invalidateMemorialCache($id) {
    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * Process uploaded image
 */
function processUploadedImage($file, $memorial_id) {
    // Validate file exists and no errors
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'خطأ في رفع الملف'];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'حجم الملف كبير جداً (الحد الأقصى 2 ميجابايت)'];
    }
    
    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['success' => false, 'error' => 'نوع الملف غير مسموح (فقط JPG و PNG)'];
    }
    
    // Validate extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'امتداد الملف غير مسموح'];
    }
    
    // Generate unique filename
    $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    $destination = UPLOAD_PATH . '/' . $filename;
    
    // Ensure upload directory exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => false, 'error' => 'فشل حفظ الملف'];
    }
    
    // Create thumbnail
    createThumbnail($destination, $extension);
    
    return ['success' => true, 'filename' => $filename];
}

/**
 * Create thumbnail for image
 */
function createThumbnail($sourcePath, $extension) {
    $thumbPath = str_replace('.' . $extension, '_thumb.' . $extension, $sourcePath);
    $maxWidth = 400;
    $maxHeight = 400;
    
    // Get original dimensions
    list($width, $height) = getimagesize($sourcePath);
    
    // Calculate new dimensions
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int)($width * $ratio);
    $newHeight = (int)($height * $ratio);
    
    // Create image resource
    if ($extension === 'jpg' || $extension === 'jpeg') {
        $source = imagecreatefromjpeg($sourcePath);
    } else {
        $source = imagecreatefrompng($sourcePath);
    }
    
    // Create thumbnail
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG
    if ($extension === 'png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save thumbnail
    if ($extension === 'jpg' || $extension === 'jpeg') {
        imagejpeg($thumb, $thumbPath, 85);
    } else {
        imagepng($thumb, $thumbPath, 8);
    }
    
    imagedestroy($source);
    imagedestroy($thumb);
}

/**
 * Get image URL (with thumbnail option)
 */
function getImageUrl($filename, $thumbnail = false) {
    if (!$filename) return BASE_URL . '/assets/images/placeholder-memorial.svg';
    
    if ($thumbnail) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $thumbFilename = str_replace('.' . $ext, '_thumb.' . $ext, $filename);
        $thumbPath = UPLOAD_PATH . '/' . $thumbFilename;
        
        if (file_exists($thumbPath)) {
            return BASE_URL . '/uploads/memorials/' . $thumbFilename;
        }
    }
    
    return BASE_URL . '/uploads/memorials/' . $filename;
}

/**
 * Check rate limit for IP
 */
function checkRateLimit($key, $limit, $period = 3600) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $rateLimitKey = "ratelimit_{$key}_{$ip}";
    
    if (!isset($_SESSION[$rateLimitKey])) {
        $_SESSION[$rateLimitKey] = ['count' => 0, 'start' => time()];
    }
    
    $data = $_SESSION[$rateLimitKey];
    
    // Reset if period expired
    if (time() - $data['start'] > $period) {
        $_SESSION[$rateLimitKey] = ['count' => 1, 'start' => time()];
        return true;
    }
    
    // Check limit
    if ($data['count'] >= $limit) {
        return false;
    }
    
    // Increment
    $_SESSION[$rateLimitKey]['count']++;
    return true;
}

/**
 * Log authentication attempts
 */
function logAuthAttempt($username, $success, $ip) {
    if (!is_dir(LOGS_PATH)) {
        mkdir(LOGS_PATH, 0755, true);
    }
    
    $logFile = LOGS_PATH . '/auth.log';
    $timestamp = date('Y-m-d H:i:s');
    $status = $success ? 'SUCCESS' : 'FAILED';
    $message = "[$timestamp] $status - Username: $username - IP: $ip\n";
    
    file_put_contents($logFile, $message, FILE_APPEND);
}

/**
 * Generate structured data (JSON-LD) for memorial page
 */
function generateStructuredData($memorial) {
    $data = [
        "@context" => "https://schema.org",
        "@type" => "Person",
        "name" => $memorial['name'],
        "description" => $memorial['quote'] ?? "صفحة تذكارية للمغفور له " . $memorial['name'],
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => BASE_URL . "/memorial.php?id=" . $memorial['id']
        ]
    ];
    
    if ($memorial['image'] && $memorial['image_status'] == 1) {
        $data['image'] = getImageUrl($memorial['image']);
    }
    
    if ($memorial['death_date']) {
        $data['deathDate'] = $memorial['death_date'];
    }
    
    return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Get pronoun based on gender
 */
function getPronoun($gender, $type = 'له') {
    if ($gender === 'female') {
        $pronouns = [
            'له' => 'لها',
            'المغفور له' => 'المغفور لها',
            'عنه' => 'عنها',
            'روحه' => 'روحها',
            'قبره' => 'قبرها',
            'ميزانه' => 'ميزانها'
        ];
        return $pronouns[$type] ?? $type;
    }
    return $type;
}

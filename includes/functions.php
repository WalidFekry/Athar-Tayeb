<?php
/**
 * Helper Functions
 * Utility functions for the application
 */

/**
 * Sanitize output for HTML display
 */
function e($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Format date in Arabic
 */
function formatArabicDate($date, $format = 'long')
{
    if (!$date)
        return '';

    $timestamp = is_numeric($date) ? $date : strtotime($date);

    $arabicMonths = [
        1 => 'يناير',
        2 => 'فبراير',
        3 => 'مارس',
        4 => 'أبريل',
        5 => 'مايو',
        6 => 'يونيو',
        7 => 'يوليو',
        8 => 'أغسطس',
        9 => 'سبتمبر',
        10 => 'أكتوبر',
        11 => 'نوفمبر',
        12 => 'ديسمبر'
    ];

    $day = date('j', $timestamp);
    $month = $arabicMonths[(int) date('n', $timestamp)];
    $year = date('Y', $timestamp);

    if ($format === 'short') {
        return "$day $month $year";
    }

    return "$day $month $year";
}

/**
 * Convert Arabic-Indic numerals to Arabic (Eastern Arabic numerals)
 */
function toArabicNumerals($number)
{
    $western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    return str_replace($western, $arabic, $number);
}

/**
 * Generate Open Graph meta tags
 */
function generateOGTags($title, $description, $image = null, $url = null)
{
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
function getCachedMemorial($id)
{
    if (!CACHE_ENABLED)
        return null;

    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";

    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < CACHE_TTL) {
        return file_get_contents($cacheFile);
    }

    return null;
}

/**
 * Save memorial to cache
 */
function cacheMemorial($id, $html)
{
    if (!CACHE_ENABLED)
        return false;

    if (!is_dir(CACHE_PATH)) {
        mkdir(CACHE_PATH, 0755, true);
    }

    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";
    return file_put_contents($cacheFile, $html) !== false;
}

/**
 * Invalidate memorial cache
 */
function invalidateMemorialCache($id)
{
    $cacheFile = CACHE_PATH . "/memorial_{$id}.html";
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
}

/**
 * Process uploaded image
 */
function processUploadedImage($file, $memorial_id)
{
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

    // ↓↓↓ Resize original image (reduce size and dimensions)
    resizeImage($destination, $extension, 1280, 1280);

    // ↓↓↓ Create optimized thumbnail (smaller)
    createThumbnail($destination, $extension, 300, 300);

    return ['success' => true, 'filename' => $filename];
}

/**
 * Resize image to specified max dimensions
 */
function resizeImage($filePath, $extension, $maxWidth, $maxHeight)
{
    if (!extension_loaded('gd'))
        return false;

    list($width, $height) = getimagesize($filePath);

    // Check if image is smaller than max dimensions
    if ($width <= $maxWidth && $height <= $maxHeight)
        return false;

    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int) ($width * $ratio);
    $newHeight = (int) ($height * $ratio);

    // Create image resource
    if ($extension === 'jpg' || $extension === 'jpeg') {
        $src = imagecreatefromjpeg($filePath);
    } else {
        $src = imagecreatefrompng($filePath);
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if ($extension === 'png') {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Save resized image
    if ($extension === 'jpg' || $extension === 'jpeg') {
        imagejpeg($dst, $filePath, 80); // 80% quality for good quality and smaller size
    } else {
        imagepng($dst, $filePath, 8);
    }

    imagedestroy($src);
    imagedestroy($dst);
    return true;
}

/**
 * Create thumbnail for image
 */
function createThumbnail($sourcePath, $extension, $maxWidth = 300, $maxHeight = 300)
{
    if (!extension_loaded('gd'))
        return false;

    $thumbPath = str_replace('.' . $extension, '_thumb.' . $extension, $sourcePath);
    list($width, $height) = getimagesize($sourcePath);
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = (int) ($width * $ratio);
    $newHeight = (int) ($height * $ratio);

    if ($extension === 'jpg' || $extension === 'jpeg') {
        $source = imagecreatefromjpeg($sourcePath);
    } else {
        $source = imagecreatefrompng($sourcePath);
    }

    $thumb = imagecreatetruecolor($newWidth, $newHeight);

    if ($extension === 'png') {
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }

    imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    if ($extension === 'jpg' || $extension === 'jpeg') {
        imagejpeg($thumb, $thumbPath, 75); // 75% quality for thumbnails
    } else {
        imagepng($thumb, $thumbPath, 8);
    }

    imagedestroy($source);
    imagedestroy($thumb);
}


/**
 * Get image URL (with thumbnail option)
 */
function getImageUrl($filename, $thumbnail = false, $default_image = '/assets/images/placeholder-memorial.svg')
{
    $default = BASE_URL . $default_image;

    if (!$filename)
        return $default;

    if ($thumbnail) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $thumbFilename = str_replace('.' . $ext, '_thumb.' . $ext, $filename);
        $thumbPath = UPLOAD_PATH . '/' . $thumbFilename;

        if (file_exists($thumbPath)) {
            return BASE_URL . '/uploads/memorials/' . $thumbFilename;
        } else {
            $originalPath = UPLOAD_PATH . '/' . $filename;
            if (file_exists($originalPath)) {
                return BASE_URL . '/uploads/memorials/' . $filename;
            }
            return $default;
        }
    } else {
        $originalPath = UPLOAD_PATH . '/' . $filename;
        if (file_exists($originalPath)) {
            return BASE_URL . '/uploads/memorials/' . $filename;
        }
        return $default;
    }
}


/**
 * Check rate limit for IP
 */
function checkRateLimit($key, $limit, $period = 3600)
{
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
function logAuthAttempt($username, $success, $ip)
{
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
function generateStructuredData($memorial)
{
    $data = [
        "@context" => "https://schema.org",
        "@type" => "Person",
        "name" => $memorial['name'],
        "description" => $memorial['quote'] ?? "صفحة تذكارية للمغفور له " . $memorial['name'],
        "mainEntityOfPage" => [
            "@type" => "WebPage",
            "@id" => site_url('m/' . $memorial['id'])
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
function redirect($url)
{
    header("Location: $url");
    exit;
}

/**
 * Get pronoun based on gender
 */
function getPronoun($gender, $type = 'له')
{
    if ($gender === 'female') {
        $pronouns = [
            'للمرحوم' => 'للمرحومة',
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
/**
 * Summary of getPrayers
 * @param mixed $gender
 * @param mixed $name
 * @return string[]
 */
function getPrayers($gender, $name)
{
    if ($gender === 'female') {
        return [
            "اللهم يا حنان يا منان يا واسع الغفران اغفر لـ $name وارحمها وعافها واعف عنها، وأكرم نزلها ووسع مدخلها، وأغسلها بالماء والثلج والبرد، ونقها من الذنوب والخطايا كما ينقى الثوب الأبيض من الدنس.",
            "اللهم اجعل قبر $name روضة من رياض الجنة، ولا تجعله حفرة من حفر النار، اللهم أسكنها الفردوس الأعلى من الجنة، وأنر قبرها ووسع مدخلها يا حليم يا غفار.",
            "اللهم اجعل عن يمين $name نورًا، حتى تبعثها آمنة مطمئنة في نورٍ من نورك. اللهمّ انظر إليها نظرة رضا، فإنّ من تنظُر إليه نظرة رضًا لا تعذّبه أبدًا. اللهمّ أسكنها فسيح الجنان، واغفر لها يا رحمن، وارحمها يا رحيم، وتجاوز عمّا تعلم يا عليم. اللهمّ اعف عنها، فإنّك القائل ويعفو عن كثير.",
            "اللهم يمّن كتاب $name ويسّر حسابها، وثقّل بالحسنات ميزانها، وثبّت على الصراط أقدامها، وأسكنها في أعلى الجنات بجوار حبيبك ومصطفاك صلّى الله عليه وسلّم.",
            "اللهمّ أدخلها الجنة من غير مناقشة حساب، ولا سابقة عذاب. اللهمّ آنسها في وحدتها، وفي وحشتها، وفي غربتها. اللهمّ أنزلها منزلاً مباركاً، وأنت خير المنزلين. اللهمّ أنزلها منازل الصدّيقين، والشهداء، والصالحين، وحسُن أولئك رفيقاً. اللهمّ اجعل قبرها روضةً من رياض الجنّة، ولا تجعلها حفرةً من حفر النّار.",
            "اللّهم ارحمنا إذا حُمِلنا على الأعناقِ، وبلغتِ التراقِ، وقيل من راق، وظنّ أنّه الفراق، والتفَّتِ السَّاقُ بالسَّاقِ، إليك يا ربَّنا يومئذٍ المساق. اللهمّ ارحمنا إذا ورينا التّراب، وغلّقت القبور والأبواب، وانفضّ الأهل والأحباب. اللهمّ ارحمنا إذا فارقنا النّعيم، وانقطع النّسيم، وقيل ما غرّك بربّك الكريم."
        ];
    } else {
        return [
            "اللهم يا حنان يا منان يا واسع الغفران اغفر لـ $name وارحمه وعافه واعف عنه، وأكرم نزله ووسع مدخله، وأغسله بالماء والثلج والبرد، ونقه من الذنوب والخطايا كما ينقى الثوب الأبيض من الدنس.",
            "اللهم اجعل قبر $name روضة من رياض الجنة، ولا تجعله حفرة من حفر النار، اللهم أسكنه الفردوس الأعلى من الجنة، وأنر قبره ووسع مدخله.",
            "اللهم اجعل عن يمين $name نورًا، حتى تبعثه آمنًا مطمئنًا في نورٍ من نورك. اللهمّ انظر إليه نظرة رضا، فإنّ من تنظُر إليه نظرة رضًا لا تعذّبه أبدًا. اللهمّ أسكنه فسيح الجنان، واغفر له يا رحمن، وارحمه يا رحيم، وتجاوز عمّا تعلم يا عليم. اللهمّ اعف عنه، فإنّك القائل ويعفو عن كثير.",
            "اللهمّ يمّن كتاب $name ويسّر حسابه، وثقّل بالحسنات ميزانه، وثبّت على الصّراط أقدامه، وأسكنه في أعلى الجنّات، بجوار حبيبك ومصطفاك صلّى الله عليه وسلّم.",
            "اللهمّ أدخله الجنة من غير مناقشة حساب، ولا سابقة عذاب. اللهمّ آنسه في وحدته، وفي وحشته، وفي غربته. اللهمّ أنزله منزلاً مباركاً، وأنت خير المنزلين. اللهمّ أنزله منازل الصدّيقين، والشهداء، والصالحين، وحسُن أولئك رفيقاً. اللهمّ اجعل قبره روضةً من رياض الجنّة، ولا تجعله حفرةً من حفر النّار.",
            "اللّهم ارحمنا إذا حُمِلنا على الأعناقِ، وبلغتِ التراقِ، وقيل من راق، وظنّ أنّه الفراق، والتفَّتِ السَّاقُ بالسَّاقِ، إليك يا ربَّنا يومئذٍ المساق. اللهمّ ارحمنا إذا ورينا التّراب، وغلّقت القبور والأبواب، وانفضّ الأهل والأحباب. اللهمّ ارحمنا إذا فارقنا النّعيم، وانقطع النّسيم، وقيل ما غرّك بربّك الكريم."
        ];
    }
}



/**
 * Get time ago in Arabic
 */
function timeAgoInArabic($datetime)
{
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'منذ ثوانٍ';
    }

    $minute = 60;
    $hour = 3600;
    $day = 86400;
    $week = 604800;
    $month = 2592000;
    $year = 31536000;

    if ($diff < $hour) {
        $minutes = floor($diff / $minute);
        return 'منذ ' . formatArabicCount($minutes, 'دقيقة', 'دقيقتين', 'دقائق');
    } elseif ($diff < $day) {
        $hours = floor($diff / $hour);
        return 'منذ ' . formatArabicCount($hours, 'ساعة', 'ساعتين', 'ساعات');
    } elseif ($diff < $week) {
        $days = floor($diff / $day);
        return 'منذ ' . formatArabicCount($days, 'يوم', 'يومين', 'أيام');
    } elseif ($diff < $month) {
        $weeks = floor($diff / $week);
        return 'منذ ' . formatArabicCount($weeks, 'أسبوع', 'أسبوعين', 'أسابيع');
    } elseif ($diff < $year) {
        $months = floor($diff / $month);
        return 'منذ ' . formatArabicCount($months, 'شهر', 'شهرين', 'أشهر');
    } else {
        $years = floor($diff / $year);
        return 'منذ ' . formatArabicCount($years, 'سنة', 'سنتين', 'سنوات');
    }
}
function formatArabicCount($num, $singular, $dual, $plural)
{
    $numArabic = toArabicNumerals($num);
    if ($num == 1)
        return " $numArabic $singular";
    if ($num == 2)
        return " $dual";
    if ($num <= 10)
        return " $numArabic $plural";
    return " $numArabic $plural";
}

/**
 * Generate clean URLs without /public/ and .php extension
 * Converts BASE_URL paths to SEO-friendly format
 * 
 * @param string $path The path relative to project root (e.g., 'create', 'm/2', 'all')
 * @return string The full clean URL
 */
function site_url($path = '')
{
    // Remove trailing /public from BASE_URL to get project root
    $root = preg_replace('~/public/?$~', '', BASE_URL);

    // Ensure proper path formatting
    $path = ltrim($path, '/');

    // Return clean URL
    return rtrim($root, '/') . '/' . $path;
}

/**
 * Generate a secure edit key for memorial pages
 * @return string A unique, secure edit key
 */
function generateEditKey()
{
    // Generate a secure random string (16 bytes = 128 bits)
    $randomBytes = random_bytes(16);
    
    // Base64 encode
    $base64 = base64_encode($randomBytes);
    
    // Convert to URL-safe base64 (base64url) and remove padding '='
    $base64url = rtrim(strtr($base64, '+/', '-_'), '=');
    
    return $base64url;
}

/**
 * Validate edit key format
 * @param string $key The edit key to validate
 * @return bool True if valid format, false otherwise
 */
function isValidEditKeyFormat($key)
{
    // Base64url characters only: A-Z, a-z, 0-9, -, _
    // Length between 20 and 24
    return preg_match('/^[A-Za-z0-9\-_]{20,24}$/', $key) === 1;
}



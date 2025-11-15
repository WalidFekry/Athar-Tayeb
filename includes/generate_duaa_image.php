<?php
/**
 * Duaa Image Generation System
 * Generates high-quality prayer images for memorial pages
 */


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/Arabic/ArabicGlyphs.php';

/**
 * Generate a duaa image for a memorial
 * 
 * @param int $memorialId Memorial ID
 * @param string $name Deceased person's name
 * @param string $gender Gender (male/female)
 * @param string|null $imagePath Path to deceased's image (optional)
 * @return array Result with success status and file path
 */
function generateDuaaImage($imageName, $name, $gender, $imagePath = null, $death_date)
{
    try {
        // Ensure output directory exists
        $outputDir = PUBLIC_PATH . '/uploads/duaa_images';
        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $outputPath = $outputDir . '/' . $imageName;

        // Image dimensions
        $width = 1080;
        $height = 1080;

        // Create canvas
        $canvas = imagecreatetruecolor($width, $height);
        if (!$canvas) {
            throw new Exception('Failed to create image canvas');
        }

        // Enable alpha blending
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // Load background
        $backgroundPath = getRandomBackground();
        if ($backgroundPath && file_exists($backgroundPath)) {
            $background = loadImageFromFile($backgroundPath);
            if ($background) {
                // Apply slight blur to background for better text visibility
                imagefilter($background, IMG_FILTER_GAUSSIAN_BLUR);
                // Resize background to fit canvas
                $bgResized = imagecreatetruecolor($width, $height);
                imagecopyresampled($bgResized, $background, 0, 0, 0, 0, $width, $height, imagesx($background), imagesy($background));
                imagecopy($canvas, $bgResized, 0, 0, 0, 0, $width, $height);
                imagedestroy($background);
                imagedestroy($bgResized);
            }
        }

        // Get random duaa
        $duaa = getRandomDuaa($gender);

        // Colors
        $textColor = imagecolorallocate($canvas, 255, 255, 255); // White
        $nameColor = imagecolorallocate($canvas, 46, 139, 87); // Green
        $shadowColor = imagecolorallocatealpha($canvas, 0, 0, 0, 50); // Semi-transparent black

        // Font paths
        $titleFont = PUBLIC_PATH . '/assets/fonts/' . 'NotoSansArabic-Bold.ttf';
        $duaaFont =  PUBLIC_PATH . '/assets/fonts/' . 'NotoSansArabic-Regular.ttf';

        // Draw deceased's static title at top
        $gender === 'male' ? $staticName = 'المغفور له بإذن الله تعالى' : $staticName = 'المغفور لها بإذن الله تعالى';
        $staticName = ArabicGlyphs::processText($staticName);
        drawArabicTextWithShadow($canvas, 25, 0, $width / 2, 150, $shadowColor, $textColor, $titleFont, $staticName, true);
        
        
        
        // 1. Draw deceased's name at top
        $nameText = ArabicGlyphs::processText($name);
        $nameFontSize = 50;
        $nameY = 250;
        // Add text shadow for name
        drawArabicTextWithShadow($canvas, $nameFontSize, 0, $width / 2, $nameY, $shadowColor, $nameColor, $titleFont, $nameText, true);

        // 2. Draw duaa in middle
        $processedDuaa = ArabicGlyphs::processText($duaa);
        $duaaFontSize = 35;
        $duaaY = 350;
        $maxDuaaWidth = $width - 120; // 60px margin on each side

        // Wrap duaa text with proper Arabic support
        $duaaLines = wrapArabicTextRTL($processedDuaa, $duaaFont, $duaaFontSize, $maxDuaaWidth);
        $lineHeight = $duaaFontSize + 20;
        $startY = $duaaY;

        foreach ($duaaLines as $i => $line) {
            $currentY = $startY + ($i * $lineHeight);
            drawArabicTextWithShadow($canvas, $duaaFontSize, 0, $width / 2, $currentY + 2, $shadowColor, $textColor, $duaaFont, $line, true);
        }

        // 3. Add deceased's image at bottom (if provided)
        if ($imagePath && file_exists($imagePath)) {
            $personImage = loadImageFromFile($imagePath);
            if ($personImage) {
                $imageSize = 300;
                $imageX = ($width - $imageSize) / 2;
                $imageY = $height - $imageSize - 250;

                // Create circular mask
                $circularImage = createCircularImage($personImage, $imageSize);
                if ($circularImage) {
                    // Place circular image
                    imagecopy($canvas, $circularImage, $imageX, $imageY, 0, 0, $imageSize, $imageSize);
                    imagedestroy($circularImage);
                }
                imagedestroy($personImage);
            }
        }

        if($death_date){
            // 4. Draw death date at bottom
            $dateText = ArabicGlyphs::processText("تاريخ الوفاة / " . $death_date);
            $dateFontSize = 25;
            $dateY = $height - 175;
            drawArabicTextWithShadow($canvas, $dateFontSize, 0, $width / 2, $dateY, $shadowColor, $textColor, $duaaFont, $dateText, true);
        }

        // Add decorative elements
        addDecorativeElements($canvas, $width, $height);

        // Add footer text (branding)
        $footerText = ArabicGlyphs::processText("تم إنشاء الصورة بواسطة موقع أثر طيب");        $footerSize = 24;
        $footerX = 80; //Space from left
        $footerY = $height - 80; //Space from bottom
        drawArabicTextWithShadow($canvas, $footerSize, 0, $footerX, $footerY, $shadowColor, $nameColor, $duaaFont, $footerText, false);

        // Save image
        if (!imagepng($canvas, $outputPath, 9)) {
            throw new Exception('Failed to save image');
        }

        imagedestroy($canvas);

        return [
            'success' => true,
            'path' => $outputPath,
            'url' => BASE_URL . '/uploads/duaa_images/' . $imageName
        ];

    } catch (Exception $e) {
        error_log("Duaa image generation error: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get random background image path
 */
function getRandomBackground()
{
    $backgroundDir = PUBLIC_PATH . '/assets/duaa_backgrounds';

    if (!is_dir($backgroundDir)) {
        return null;
    }

    $backgrounds = glob($backgroundDir . '/*.{jpg,jpeg,png}', GLOB_BRACE);

    if (empty($backgrounds)) {
        return null;
    }

    return $backgrounds[array_rand($backgrounds)];
}


/**
 * Get random duaa based on gender
 */
function getRandomDuaa($gender)
{
    if ($gender === 'female') {
        $duaas = [
            "اللهم اغفر لها وارحمها وأسكنها فسيح جناتك .. اللهم اجعل قبرها روضة من رياض الجنة",
            "اللهم ارحمها رحمةً واسعة وتجاوز عن سيئاتها .. اللهم اجعل عملها الصالح شفيعًا لها يوم القيامة",
            "اللهم أنر قبرها ووسع مدخلها واجعلها من أهل الجنة .. اللهم اجعلها في الفردوس الأعلى مع النبيين والصديقين"
        ];
    } else {
        $duaas = [
            "اللهم اغفر له وارحمه وأسكنه فسيح جناتك .. اللهم اجعل قبره روضة من رياض الجنة",
            "اللهم ارحمه رحمةً واسعة وتجاوز عن سيئاته .. اللهم اجعل عمله الصالح شفيعًا له يوم القيامة",
            "اللهم أنر قبره ووسع مدخله واجعله من أهل الجنة .. اللهم اجعله في الفردوس الأعلى مع النبيين والصديقين"
        ];
    }

    return $duaas[array_rand($duaas)];
}


/**
 * Load image from file with format detection
 */
function loadImageFromFile($path)
{
    $imageInfo = getimagesize($path);
    if (!$imageInfo) {
        return false;
    }

    switch ($imageInfo[2]) {
        case IMAGETYPE_JPEG:
            return imagecreatefromjpeg($path);
        case IMAGETYPE_PNG:
            return imagecreatefrompng($path);
        case IMAGETYPE_GIF:
            return imagecreatefromgif($path);
        default:
            return false;
    }
}

/**
 * Draw Arabic text with shadow effect and proper RTL support
 */
function drawArabicTextWithShadow($canvas, $fontSize, $angle, $x, $y, $shadowColor, $textColor, $fontPath, $text, $center = false)
{
    // Fix RTL issue: reverse words order for proper Arabic rendering in GD
    $text = implode(' ', array_reverse(explode(' ', $text)));
    
    // Use TTF font
    if ($center) {
        $bbox = imagettfbbox($fontSize, $angle, $fontPath, $text);
        $textWidth = $bbox[4] - $bbox[0];
        $x = $x - ($textWidth / 2);
    }

    // Draw shadow (offset by 2 pixels)
    imagettftext($canvas, $fontSize, $angle, $x + 2, $y + 2, $shadowColor, $fontPath, $text);

    // Draw main text
    imagettftext($canvas, $fontSize, $angle, $x, $y, $textColor, $fontPath, $text);

}

/**
 * Wrap Arabic text to fit within specified width with proper RTL support
 */
function wrapArabicTextRTL($text, $fontPath, $fontSize, $maxWidth)
{
    // Split by spaces but preserve Arabic word boundaries
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $lines = [];
    $currentLine = '';

    foreach ($words as $word) {
        $testLine = empty($currentLine) ? $word : $currentLine . ' ' . $word;

        // Test the width of the line
        if ($fontPath && file_exists($fontPath)) {
            // Use TTF font for measurement
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            $lineWidth = abs($bbox[4] - $bbox[0]);
        } else {
            // Fallback measurement for built-in fonts
            $builtinFontSize = min(5, max(1, (int) ($fontSize / 10)));
            $lineWidth = strlen($testLine) * imagefontwidth($builtinFontSize);
        }

        if ($lineWidth <= $maxWidth) {
            $currentLine = $testLine;
        } else {
            if (!empty($currentLine)) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                // Word is too long, try to break it intelligently
                if (mb_strlen($word, 'UTF-8') > 20) {
                    // Break very long words
                    $chunks = mb_str_split($word, 15, 'UTF-8');
                    foreach ($chunks as $chunk) {
                        $lines[] = $chunk;
                    }
                } else {
                    $lines[] = $word;
                }
            }
        }
    }

    if (!empty($currentLine)) {
        $lines[] = $currentLine;
    }

    return $lines;
}

/**
 * Create circular image with transparency and anti-aliasing
 */
function createCircularImage($sourceImage, $size)
{
    $srcWidth = imagesx($sourceImage);
    $srcHeight = imagesy($sourceImage);

    // Create square crop
    $cropSize = min($srcWidth, $srcHeight);
    $cropX = ($srcWidth - $cropSize) / 2;
    $cropY = ($srcHeight - $cropSize) / 2;

    // Create circular image with higher resolution for anti-aliasing
    $antiAliasSize = $size * 2;
    $circular = imagecreatetruecolor($antiAliasSize, $antiAliasSize);
    imagealphablending($circular, false);
    imagesavealpha($circular, true);

    // Fill with transparent
    $transparent = imagecolorallocatealpha($circular, 0, 0, 0, 127);
    imagefill($circular, 0, 0, $transparent);

    // Resize source image to higher resolution
    $resized = imagecreatetruecolor($antiAliasSize, $antiAliasSize);
    imagecopyresampled($resized, $sourceImage, 0, 0, $cropX, $cropY, $antiAliasSize, $antiAliasSize, $cropSize, $cropSize);

    // Create circular mask with anti-aliasing
    $centerX = $antiAliasSize / 2;
    $centerY = $antiAliasSize / 2;
    $radius = $antiAliasSize / 2;

    for ($x = 0; $x < $antiAliasSize; $x++) {
        for ($y = 0; $y < $antiAliasSize; $y++) {
            $distance = sqrt(pow($x - $centerX, 2) + pow($y - $centerY, 2));

            if ($distance <= $radius) {
                // Calculate alpha for anti-aliasing
                $alpha = 0;
                if ($distance > $radius - 1) {
                    $alpha = (int) ((($distance - ($radius - 1)) / 1) * 127);
                }

                $sourcePixel = imagecolorat($resized, $x, $y);
                $r = ($sourcePixel >> 16) & 0xFF;
                $g = ($sourcePixel >> 8) & 0xFF;
                $b = $sourcePixel & 0xFF;

                $newColor = imagecolorallocatealpha($circular, $r, $g, $b, $alpha);
                imagesetpixel($circular, $x, $y, $newColor);
            }
        }
    }

    // Scale down to final size for anti-aliasing effect
    $final = imagecreatetruecolor($size, $size);
    imagealphablending($final, false);
    imagesavealpha($final, true);
    imagefill($final, 0, 0, $transparent);

    imagecopyresampled($final, $circular, 0, 0, 0, 0, $size, $size, $antiAliasSize, $antiAliasSize);

    imagedestroy($circular);
    imagedestroy($resized);

    return $final;
}

/**
 * Add decorative Islamic elements
 */
function addDecorativeElements($canvas, $width, $height)
{
    $decorColor = imagecolorallocatealpha($canvas, 255, 255, 255, 100);

    // Add corner decorations
    $cornerSize = 100;

    // Top corners
    drawCornerDecoration($canvas, 50, 50, $cornerSize, $decorColor, 'top-left');
    drawCornerDecoration($canvas, $width - 50, 50, $cornerSize, $decorColor, 'top-right');

    // Bottom corners  
    drawCornerDecoration($canvas, 50, $height - 50, $cornerSize, $decorColor, 'bottom-left');
    drawCornerDecoration($canvas, $width - 50, $height - 50, $cornerSize, $decorColor, 'bottom-right');
}

/**
 * Draw corner decoration
 */
function drawCornerDecoration($canvas, $x, $y, $size, $color, $position)
{
    $thickness = 3;

    switch ($position) {
        case 'top-left':
            // Horizontal line
            imagefilledrectangle($canvas, $x, $y, $x + $size, $y + $thickness, $color);
            // Vertical line
            imagefilledrectangle($canvas, $x, $y, $x + $thickness, $y + $size, $color);
            break;

        case 'top-right':
            // Horizontal line
            imagefilledrectangle($canvas, $x - $size, $y, $x, $y + $thickness, $color);
            // Vertical line
            imagefilledrectangle($canvas, $x - $thickness, $y, $x, $y + $size, $color);
            break;

        case 'bottom-left':
            // Horizontal line
            imagefilledrectangle($canvas, $x, $y - $thickness, $x + $size, $y, $color);
            // Vertical line
            imagefilledrectangle($canvas, $x, $y - $size, $x + $thickness, $y, $color);
            break;

        case 'bottom-right':
            // Horizontal line
            imagefilledrectangle($canvas, $x - $size, $y - $thickness, $x, $y, $color);
            // Vertical line
            imagefilledrectangle($canvas, $x - $thickness, $y - $size, $x, $y, $color);
            break;
    }
}

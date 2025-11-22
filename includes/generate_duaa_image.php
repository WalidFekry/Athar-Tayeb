<?php
/**
 * Duaa Image Generation System with ImageMagick
 * Generates high-quality prayer images for memorial pages
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

/**
 * Generate a duaa image for a memorial
 * 
 * @param string $imageName Output image filename
 * @param string $name Deceased person's name
 * @param string $gender Gender (male/female)
 * @param string|null $imagePath Path to deceased's image (optional)
 * @param string|null $death_date Death date
 * @return array Result with success status and file path
 */
function generateDuaaImage($imageName, $name, $gender, $imagePath = null, $death_date = null)
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
        $canvas = new Imagick();
        
        // Load background
        $backgroundPath = getRandomBackground();
        if ($backgroundPath && file_exists($backgroundPath)) {
            $canvas->readImage($backgroundPath);
            $canvas->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1);
            // Apply blur for better text visibility
            $canvas->blurImage(0, 1);
        } else {
            // Create solid background if no image found
            $canvas->newImage($width, $height, new ImagickPixel('#2C3E50'));
        }
        
        $canvas->setImageFormat('png');

        // Font paths
        $titleFont = PUBLIC_PATH . '/assets/fonts/NotoSansArabic-Bold.ttf';
        $duaaFont = PUBLIC_PATH . '/assets/fonts/NotoSansArabic-Regular.ttf';

        // Colors
        $whiteColor = new ImagickPixel('white');
        $greenColor = new ImagickPixel('rgba(46, 139, 87, 1)'); // #2E8B57
        $shadowColor = new ImagickPixel('rgba(0, 0, 0, 0.5)');

        // 1. Draw static title at top
        $staticName = ($gender === 'male') 
            ? 'المغفور له بإذن الله تعالى' 
            : 'المغفور لها بإذن الله تعالى';
        
        drawTextWithShadow($canvas, $staticName, $titleFont, 40, $whiteColor, $shadowColor, $width / 2, 150, true);

        // 2. Draw deceased's name
        drawTextWithShadow($canvas, $name, $titleFont, 70, $greenColor, $shadowColor, $width / 2, 250, true);

        // 3. Draw duaa in middle
        $duaa = getRandomDuaa($gender);
        $duaaY = 350;
        $maxWidth = $width - 120; // 60px margin on each side
        
        $duaaLines = wrapTextForImageMagick($canvas, $duaa, $duaaFont, 50, $maxWidth);
        $lineHeight = 55; // 35 + 20
        
        foreach ($duaaLines as $i => $line) {
            $currentY = $duaaY + ($i * $lineHeight);
            drawTextWithShadow($canvas, $line, $duaaFont, 50, $whiteColor, $shadowColor, $width / 2, $currentY, true);
        }

        // 4. Add deceased's image at bottom (if provided)
        if ($imagePath && file_exists($imagePath)) {
            addCircularImage($canvas, $imagePath, $width, $height);
        }

        // 5. Draw death date at bottom
        if ($death_date) {
            $dateText = "تاريخ الوفاة: " . $death_date;
            drawTextWithShadow($canvas, $dateText, $duaaFont, 35, $whiteColor, $shadowColor, $width / 2, $height - 175, true);
        }

        // 6. Add decorative elements
        addDecorativeElements($canvas, $width, $height);

        // 7. Add footer text (branding)
        $footerText = "تم إنشاء الصورة بواسطة موقع أثر طيب";
        drawTextWithShadow($canvas, $footerText, $duaaFont, 30, $greenColor, $shadowColor, 80, $height - 80, false);

        // Save image
        $canvas->writeImage($outputPath);
        $canvas->clear();
        $canvas->destroy();

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
 * Draw text with shadow effect using ImageMagick
 */
function drawTextWithShadow($canvas, $text, $font, $fontSize, $textColor, $shadowColor, $x, $y, $center = false)
{
    // Create drawing object for shadow
    $shadowDraw = new ImagickDraw();
    $shadowDraw->setFont($font);
    $shadowDraw->setFontSize($fontSize);
    $shadowDraw->setFillColor($shadowColor);
    
    if ($center) {
        $shadowDraw->setTextAlignment(Imagick::ALIGN_CENTER);
    }
    
    // Draw shadow (offset by 2 pixels)
    $shadowDraw->annotation($x + 2, $y + 2, $text);
    $canvas->drawImage($shadowDraw);
    
    // Create drawing object for main text
    $textDraw = new ImagickDraw();
    $textDraw->setFont($font);
    $textDraw->setFontSize($fontSize);
    $textDraw->setFillColor($textColor);
    
    if ($center) {
        $textDraw->setTextAlignment(Imagick::ALIGN_CENTER);
    }
    
    // Draw main text
    $textDraw->annotation($x, $y, $text);
    $canvas->drawImage($textDraw);
}

/**
 * Wrap text to fit within specified width using ImageMagick
 */
function wrapTextForImageMagick($canvas, $text, $font, $fontSize, $maxWidth)
{
    $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
    $lines = [];
    $currentLine = '';

    $draw = new ImagickDraw();
    $draw->setFont($font);
    $draw->setFontSize($fontSize);

    foreach ($words as $word) {
        $testLine = empty($currentLine) ? $word : $currentLine . ' ' . $word;
        
        // Measure text width
        $metrics = $canvas->queryFontMetrics($draw, $testLine);
        $lineWidth = $metrics['textWidth'];

        if ($lineWidth <= $maxWidth) {
            $currentLine = $testLine;
        } else {
            if (!empty($currentLine)) {
                $lines[] = $currentLine;
                $currentLine = $word;
            } else {
                // Word is too long, break it
                if (mb_strlen($word, 'UTF-8') > 20) {
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
 * Add circular image to canvas
 */
function addCircularImage($canvas, $imagePath, $canvasWidth, $canvasHeight)
{
    try {
        $imageSize = 300;
        $imageX = ($canvasWidth - $imageSize) / 2;
        $imageY = $canvasHeight - $imageSize - 250;

        // Load person's image
        $personImage = new Imagick($imagePath);
        
        // Get dimensions
        $width = $personImage->getImageWidth();
        $height = $personImage->getImageHeight();
        
        // Crop to square (center crop)
        $cropSize = min($width, $height);
        $cropX = ($width - $cropSize) / 2;
        $cropY = ($height - $cropSize) / 2;
        
        $personImage->cropImage($cropSize, $cropSize, $cropX, $cropY);
        
        // Resize to target size
        $personImage->resizeImage($imageSize, $imageSize, Imagick::FILTER_LANCZOS, 1);
        
        // Set image format to PNG
        $personImage->setImageFormat('png');
        
        // Add white border
        $borderWidth = 5;
        $borderDraw = new ImagickDraw();
        $borderDraw->setStrokeColor(new ImagickPixel('white'));
        $borderDraw->setStrokeWidth($borderWidth);
        $borderDraw->setFillColor(new ImagickPixel('transparent'));
        $borderDraw->rectangle(
            $borderWidth / 2, 
            $borderWidth / 2, 
            $imageSize - $borderWidth / 2, 
            $imageSize - $borderWidth / 2
        );
        $personImage->drawImage($borderDraw);
        
        // Composite onto canvas
        $canvas->compositeImage($personImage, Imagick::COMPOSITE_OVER, $imageX, $imageY);
        
        // Cleanup
        $personImage->clear();
        $personImage->destroy();
        
    } catch (Exception $e) {
        error_log("Error adding square image: " . $e->getMessage());
    }
}

/**
 * Add decorative Islamic elements
 */
function addDecorativeElements($canvas, $width, $height)
{
    $decorColor = new ImagickPixel('rgba(255, 255, 255, 0.4)');
    $cornerSize = 100;
    $thickness = 3;

    $draw = new ImagickDraw();
    $draw->setStrokeColor($decorColor);
    $draw->setStrokeWidth($thickness);
    $draw->setFillColor(new ImagickPixel('transparent'));

    // Top-left corner
    $draw->line(50, 50, 50 + $cornerSize, 50); // Horizontal
    $draw->line(50, 50, 50, 50 + $cornerSize); // Vertical

    // Top-right corner
    $draw->line($width - 50, 50, $width - 50 - $cornerSize, 50); // Horizontal
    $draw->line($width - 50, 50, $width - 50, 50 + $cornerSize); // Vertical

    // Bottom-left corner
    $draw->line(50, $height - 50, 50 + $cornerSize, $height - 50); // Horizontal
    $draw->line(50, $height - 50, 50, $height - 50 - $cornerSize); // Vertical

    // Bottom-right corner
    $draw->line($width - 50, $height - 50, $width - 50 - $cornerSize, $height - 50); // Horizontal
    $draw->line($width - 50, $height - 50, $width - 50, $height - 50 - $cornerSize); // Vertical

    $canvas->drawImage($draw);
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
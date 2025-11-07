<?php
/**
 * QR Code Generator API
 * Generates QR code for memorial URLs
 */

require_once __DIR__ . '/../../includes/config.php';

// Get URL parameter
$url = isset($_GET['url']) ? trim($_GET['url']) : '';

if (empty($url)) {
    header('HTTP/1.0 400 Bad Request');
    die('URL parameter required');
}

// Validate URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid URL');
}

// Use Google Chart API as a simple fallback (no external library needed)
// Note: This is a simple implementation. For production, consider using a PHP QR library
$qrApiUrl = 'https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . urlencode($url) . '&choe=UTF-8';

// Redirect to QR image
header('Location: ' . $qrApiUrl);
exit;

// Alternative: If you want to serve the image directly, uncomment below:
/*
$imageData = @file_get_contents($qrApiUrl);
if ($imageData) {
    header('Content-Type: image/png');
    header('Content-Disposition: inline; filename="qr-code.png"');
    echo $imageData;
} else {
    header('HTTP/1.0 500 Internal Server Error');
    die('Failed to generate QR code');
}
*/

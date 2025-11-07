<?php
/**
 * Maintenance Mode Page
 * Displayed when the site is under maintenance
 */

require_once __DIR__ . '/../includes/config.php';

// Set 503 header
http_response_code(503);

$pageTitle = 'الموقع قيد الصيانة — ' . SITE_NAME;
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .maintenance-container {
            max-width: 600px;
            padding: 2rem;
        }
        .maintenance-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }
        .maintenance-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        .lead {
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-card">
            <div class="maintenance-icon">🔧</div>
            <h1>الموقع قيد الصيانة</h1>
            <p class="lead">
                نعتذر عن الإزعاج. نقوم حالياً بإجراء بعض التحسينات على الموقع لتقديم تجربة أفضل لك.
            </p>
            
            <div class="info-box">
                <h5 class="mb-3">⏰ متى سيعود الموقع؟</h5>
                <p class="mb-0">
                    نتوقع أن يعود الموقع للعمل قريباً. يُرجى المحاولة مرة أخرى بعد قليل.
                </p>
            </div>
            
            <div class="mt-4">
                <p class="text-muted small mb-0">
                    شكراً لصبركم وتفهمكم 🌿
                </p>
            </div>
        </div>
    </div>
</body>
</html>

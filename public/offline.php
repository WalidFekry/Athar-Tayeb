<?php
/**
 * Offline Page - Athar Tayeb
 * Shown when the user is offline and visits an uncached page
 */

// Minimal includes — avoid DB calls when offline
$pageTitle = 'لا يوجد اتصال - أثر طيب';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?></title>
    <meta name="robots" content="noindex, nofollow">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary: #5A7D4E;
            --primary-hover: #4a6d3e;
            --bg: #F9F6F2;
            --card-bg: #FFFFFF;
            --text: #2B2B2B;
            --muted-text: #58624B;
            --border: #E5E5E5;
            --radius: 12px;
            --shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            --shadow-hover: 0 8px 30px rgba(90, 125, 78, 0.25);
            --font-ar: 'Cairo', sans-serif;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: var(--font-ar);
            background-color: var(--bg);
            color: var(--text);
            direction: rtl;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .offline-container {
            text-align: center;
            padding: 2rem;
            max-width: 480px;
            width: 100%;
        }

        .offline-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 3rem 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
        }

        .offline-icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            display: block;
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .offline-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .offline-message {
            color: var(--muted-text);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        .btn-retry {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.85rem 2.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            font-family: var(--font-ar);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-retry:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
            color: white;
        }

        .site-brand {
            margin-top: 2rem;
            color: var(--muted-text);
            font-size: 0.9rem;
        }

        .site-brand a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .connection-tips {
            background: #f0f7ed;
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            margin: 1.5rem 0;
            text-align: right;
        }

        .connection-tips p {
            font-size: 0.875rem;
            color: var(--muted-text);
            margin: 0.25rem 0;
        }

        .connection-tips p::before {
            content: '• ';
            color: var(--primary);
        }
    </style>
</head>

<body>
    <div class="offline-container">
        <div class="offline-card">
            <span class="offline-icon" role="img" aria-label="نبتة">🌿</span>

            <h1 class="offline-title">لا يوجد اتصال بالإنترنت</h1>

            <p class="offline-message">
                يبدو أنك غير متصل بالإنترنت حالياً.<br>
                تحقق من اتصالك وأعد المحاولة.
            </p>

            <div class="connection-tips" role="note" aria-label="اقتراحات">
                <p>تأكد من تشغيل الواي فاي أو بيانات الهاتف</p>
                <p>جرّب تشغيل وضع الطيران ثم إيقافه</p>
                <p>أعد تشغيل الموجّه اللاسلكي إذا لزم</p>
            </div>

            <button class="btn-retry" onclick="window.location.reload()" type="button" aria-label="إعادة المحاولة">
                <span aria-hidden="true">🔄</span>
                إعادة المحاولة
            </button>

            <p class="site-brand">
                <a href="/">🌿 أثر طيب</a> — لكي يبقى الأثر طيبًا بعد الرحيل
            </p>
        </div>
    </div>
</body>

</html>

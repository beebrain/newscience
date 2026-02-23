<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($page_title) ?> - คณะวิทยาศาสตร์</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 2rem;
            background: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .verify-card {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            max-width: 480px;
            width: 100%;
            text-align: center;
        }
        .status-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fee2e2;
            color: #991b1b;
        }
        .status-icon svg { width: 32px; height: 32px; }
        h1 { margin: 0 0 0.5rem; font-size: 1.5rem; color: #1f2937; }
        .message { color: #6b7280; margin-top: 1rem; }
    </style>
</head>
<body>
    <div class="verify-card">
        <div class="status-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </div>
        <h1>ใบรับรองไม่ถูกต้อง</h1>
        <p class="message"><?= esc($message ?? 'ไม่พบข้อมูลใบรับรองในระบบ') ?></p>
    </div>
</body>
</html>

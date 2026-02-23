<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dev Tools - E-Certificate Test</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 2rem;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dev-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        h1 {
            margin: 0 0 0.5rem;
            color: #1f2937;
            font-size: 1.75rem;
        }
        .subtitle {
            color: #6b7280;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
        .warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            color: #92400e;
            font-size: 0.875rem;
        }
        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .btn {
            padding: 0.875rem 1.25rem;
            border-radius: 8px;
            border: none;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .btn-student { background: #10b981; color: white; }
        .btn-staff { background: #3b82f6; color: white; }
        .btn-approver { background: #8b5cf6; color: white; }
        .btn-dean { background: #f59e0b; color: white; }
        .btn-logout { background: #ef4444; color: white; }
        .btn-test { background: #6b7280; color: white; }
        .session-info {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #f3f4f6;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        .session-info h3 {
            margin: 0 0 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        .session-info pre {
            margin: 0;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="dev-card">
        <h1>Dev Tools</h1>
        <p class="subtitle">E-Certificate System Test Mode</p>
        
        <div class="warning">
            ⚠️ โหมดนี้ใช้สำหรับการทดสอบเท่านั้น (Development Environment)
        </div>

        <div class="btn-group">
            <a href="<?= base_url('dev/login-as-student') ?>" class="btn btn-student">
                🎓 Login เป็นนักศึกษา
            </a>
            <a href="<?= base_url('dev/login-as-staff') ?>" class="btn btn-staff">
                👔 Login เป็นเจ้าหน้าที่
            </a>
            <a href="<?= base_url('dev/login-as-approver/chair') ?>" class="btn btn-approver">
                ✅ Login เป็นประธานหลักสูตร
            </a>
            <a href="<?= base_url('dev/login-as-approver/dean') ?>" class="btn btn-dean">
                🎖️ Login เป็นคณบดี
            </a>
            <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 0.5rem 0;">
            <a href="<?= base_url('dev/create-test-request') ?>" class="btn btn-test">
                📝 สร้างคำขอทดสอบ (ต้อง Login เป็นนักศึกษาก่อน)
            </a>
            <a href="<?= base_url('dev/logout') ?>" class="btn btn-logout">
                🚪 Logout
            </a>
        </div>

        <div class="session-info">
            <h3>Session Info:</h3>
            <pre><?php print_r(session()->get()); ?></pre>
        </div>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Admin Login | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">

    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">

    <style>
        :root {
            --primary: #f59e0b;
            /* Amber 500 */
            --primary-hover: #d97706;
            /* Amber 600 */
            --bg-dark: #0f172a;
            /* Slate 900 */
            --bg-card: rgba(30, 41, 59, 0.7);
            /* Slate 800/70 */
            --text-main: #f8fafc;
            /* Slate 50 */
            --text-muted: #94a3b8;
            /* Slate 400 */
            --border: rgba(255, 255, 255, 0.1);
            --border-focus: rgba(245, 158, 11, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-dark);
            background-image:
                radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%),
                radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%),
                radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 1) 0, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--text-main);
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            perspective: 1000px;
        }

        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo-area {
            display: flex;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
        }

        .logo-box svg {
            width: 36px;
            height: 36px;
            color: white;
        }

        .header-text {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .header-text h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #fff, #cbd5e1);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .header-text p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #cbd5e1;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-focus);
            background: rgba(15, 23, 42, 0.8);
            box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.1);
        }

        .form-control::placeholder {
            color: #475569;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, var(--primary), var(--primary-hover));
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(245, 158, 11, 0.2);
            margin-top: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(245, 158, 11, 0.3);
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2rem 0 1.5rem;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--border);
        }

        .divider span {
            padding: 0 1rem;
        }

        .sso-grid {
            display: grid;
            gap: 1rem;
        }

        .btn-sso {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--border);
            padding: 0.875rem;
            border-radius: 12px;
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-sso:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            gap: 0.75rem;
            align-items: start;
            line-height: 1.5;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.1);
            border: 1px solid rgba(34, 197, 94, 0.2);
            color: #86efac;
        }

        .back-link {
            text-align: center;
            margin-top: 2rem;
        }

        .back-link a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .back-link a:hover {
            color: white;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="glass-card">
            <div class="logo-area">
                <div class="logo-box">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5" />
                        <path d="M2 12l10 5 10-5" />
                    </svg>
                </div>
            </div>

            <div class="header-text">
                <h1>Admin Login</h1>
                <p>Access your university management dashboard</p>
            </div>

            <!-- Alerts -->
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= session()->getFlashdata('error') ?></span>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= session()->getFlashdata('success') ?></span>
                </div>
            <?php endif; ?>

            <!-- Silent Logout Logic -->
            <?php if (isset($_GET['logout']) && $_GET['logout'] === '1'): ?>
                <div class="alert alert-success">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>ออกจากระบบแล้ว</span>
                </div>
                <?php
                $edocSso = config(\Config\EdocSso::class);
                if ($edocSso->enabled && $edocSso->logoutUrl !== '') {
                    echo '<iframe src="' . esc($edocSso->logoutUrl) . '" style="width:0;height:0;border:0;position:absolute;visibility:hidden;"></iframe>';
                }
                $researchSso = config(\Config\ResearchRecordSso::class);
                if ($researchSso->enabled && $researchSso->logoutUrl !== '') {
                    echo '<iframe src="' . esc($researchSso->logoutUrl) . '" style="width:0;height:0;border:0;position:absolute;visibility:hidden;"></iframe>';
                }
                ?>
            <?php endif; ?>

            <div style="margin-top: 1.5rem;">
                <?php
                $uruOAuth = config(\Config\UruPortalOAuth::class);
                if ($uruOAuth->enabled):
                ?>
                    <a href="<?= base_url('oauth/login') ?>" class="btn-primary" style="text-decoration: none; display: flex; justify-content: center; align-items: center; gap: 0.75rem;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        เข้าสู่ระบบด้วย URU Portal
                    </a>
                <?php else: ?>
                    <div class="alert alert-error">
                        <span>การเข้าสู่ระบบผ่าน URU Portal ยังไม่เปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="back-link">
                <a href="<?= base_url() ?>">← Back to Website</a>
            </div>
        </div>
    </div>
</body>

</html>
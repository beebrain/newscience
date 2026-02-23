<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0f172a">
    <title>Student Portal - Login | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo250.png') ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <style>
        :root {
            --primary: #eab308;
            --primary-hover: #ca8a04;
            --bg-dark: #0f172a;
            --bg-card: rgba(30, 41, 59, 0.85);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border: rgba(255, 255, 255, 0.1);
            --border-focus: rgba(234, 179, 8, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--bg-dark);
            background-image: radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 1) 0, transparent 50%), radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 1) 0, transparent 50%);
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
        }

        .glass-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        }

        .logo-area {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .logo-box {
            width: 64px;
            height: 64px;
            margin: 0 auto;
            background: linear-gradient(135deg, var(--primary), var(--primary-hover));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box svg {
            width: 36px;
            height: 36px;
            color: #1a1a1a;
        }

        .header-text {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .header-text h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-main);
        }

        .header-text p {
            color: var(--text-muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            color: #cbd5e1;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: white;
            font-size: 1rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--border-focus);
            box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.15);
        }

        .form-control::placeholder {
            color: #64748b;
        }

        .btn-primary {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(to right, var(--primary), var(--primary-hover));
            border: none;
            border-radius: 12px;
            color: #1a1a1a;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 15px -3px rgba(234, 179, 8, 0.3);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .back-link a {
            color: var(--text-muted);
            text-decoration: none;
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
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
            </div>
            <div class="header-text">
                <h1>Student Portal</h1>
                <p>เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน</p>
            </div>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= base_url('student/login') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="login">อีเมล</label>
                    <input type="text" id="login" name="login" class="form-control" value="<?= esc(old('login')) ?>" placeholder="อีเมล@example.com" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label class="form-label" for="password">รหัสผ่าน</label>
                    <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password" placeholder="••••••••">
                </div>
                <button type="submit" class="btn-primary">เข้าสู่ระบบ</button>
            </form>

            <?php
            $uruOAuth = config(\Config\UruPortalOAuth::class);
            if ($uruOAuth->enabled):
            ?>
                <div style="margin-top: 1.5rem; text-align: center;">
                    <div style="color: var(--text-muted); font-size: 0.875rem; margin-bottom: 0.75rem;">หรือเข้าสู่ระบบด้วย</div>
                    <a href="<?= base_url('oauth/login') ?>" class="btn-primary" style="text-decoration: none; display: inline-flex; justify-content: center; align-items: center; gap: 0.75rem; width: 100%;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        เข้าสู่ระบบด้วย URU Portal
                    </a>
                </div>
            <?php endif; ?>
            <div class="back-link">
                <a href="<?= base_url() ?>">← กลับหน้าแรก</a>
            </div>
        </div>
    </div>
</body>

</html>
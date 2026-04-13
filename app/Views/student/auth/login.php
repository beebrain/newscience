<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0d9488">
    <title>Student Portal - Login | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <style>
        :root {
            --primary: #0d9488;
            --primary-hover: #0f766e;
            --primary-light: #ccfbf1;
            --accent: #f59e0b;
            --bg: #f0fdfa;
            --card: #ffffff;
            --text: #134e4a;
            --text-muted: #5eead4;
            --border: #99f6e4;
            --shadow: 0 4px 6px -1px rgba(13, 148, 136, 0.08), 0 10px 20px -5px rgba(13, 148, 136, 0.06);
            --shadow-lg: 0 20px 40px -10px rgba(13, 148, 136, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            color: var(--text);
            background: var(--bg);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(13, 148, 136, 0.25), transparent),
                radial-gradient(ellipse 60% 40% at 100% 100%, rgba(245, 158, 11, 0.12), transparent),
                radial-gradient(ellipse 50% 30% at 0% 80%, rgba(13, 148, 136, 0.15), transparent);
        }

        .page-wrap {
            width: 100%;
            max-width: 440px;
        }

        .card {
            background: var(--card);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border);
            animation: cardIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(24px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .brand {
            text-align: center;
            margin-bottom: 1.75rem;
        }

        .brand-icon {
            width: 56px;
            height: 56px;
            margin: 0 auto 1rem;
            background: linear-gradient(145deg, var(--primary), var(--primary-hover));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px -4px rgba(13, 148, 136, 0.35);
            animation: iconIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        .brand-icon svg {
            width: 28px;
            height: 28px;
            color: white;
            animation: iconPulse 2.5s ease-in-out infinite;
        }

        @keyframes iconIn {
            from { opacity: 0; transform: scale(0.5); }
            to { opacity: 1; transform: scale(1); }
        }

        @keyframes iconPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(1.05); }
        }

        .brand h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 0.25rem;
            letter-spacing: -0.02em;
            animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
        }

        .brand p {
            font-size: 0.9rem;
            color: #64748b;
            animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            margin-bottom: 1.25rem;
            font-size: 0.875rem;
            display: flex;
            gap: 0.625rem;
            align-items: flex-start;
            line-height: 1.5;
        }

        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }

        .alert-success {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #15803d;
        }

        .alert svg {
            width: 18px;
            height: 18px;
            flex-shrink: 0;
            margin-top: 1px;
        }

        .btn-portal {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.625rem;
            padding: 1rem 1.25rem;
            background: linear-gradient(145deg, var(--primary), var(--primary-hover));
            border: none;
            border-radius: 14px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
            box-shadow: 0 4px 14px -2px rgba(13, 148, 136, 0.4);
            animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.45s both;
        }

        .btn-portal:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 8px 24px -4px rgba(13, 148, 136, 0.45);
        }

        .btn-portal:active {
            transform: translateY(0) scale(0.99);
            transition-duration: 0.1s;
        }

        .btn-portal svg {
            width: 22px;
            height: 22px;
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 1.5rem 0 1rem;
            color: #94a3b8;
            font-size: 0.8rem;
            animation: fadeIn 0.5s ease-out 0.5s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }

        .divider span {
            padding: 0 0.75rem;
        }

        .toggle-link {
            display: block;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--primary);
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s, transform 0.2s;
            animation: fadeIn 0.5s ease-out 0.55s both;
        }

        .toggle-link:hover {
            transform: scale(1.02);
        }

        .toggle-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .email-form {
            margin-top: 0.5rem;
            animation: fadeSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        .email-form.hidden {
            display: none;
        }

        .field {
            margin-bottom: 1.125rem;
        }

        .field label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.375rem;
        }

        .field input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            color: var(--text);
            background: #f8fafc;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field input::placeholder {
            color: #94a3b8;
        }

        .field input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem 1rem;
            background: linear-gradient(145deg, var(--primary), var(--primary-hover));
            border: none;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.25rem;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-submit:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 4px 14px -2px rgba(13, 148, 136, 0.4);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .back {
            text-align: center;
            margin-top: 1.75rem;
            animation: fadeIn 0.5s ease-out 0.6s both;
        }

        .back a {
            color: #64748b;
            font-size: 0.875rem;
            text-decoration: none;
            transition: color 0.2s, transform 0.2s;
            display: inline-block;
        }

        .back a:hover {
            color: var(--primary);
            transform: translateX(-2px);
        }

        .features {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            animation: fadeIn 0.5s ease-out 0.7s both;
        }

        .features span {
            font-size: 0.75rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            transition: transform 0.2s, color 0.2s;
        }

        .features span:hover {
            color: var(--primary);
            transform: scale(1.05);
        }

        .features span::before {
            content: '';
            width: 6px;
            height: 6px;
            background: var(--primary);
            border-radius: 50%;
        }

        .alert {
            animation: fadeSlideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }
    </style>
</head>

<body>
    <div class="page-wrap">
        <div class="card">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                </div>
                <h1>Student Portal</h1>
                <p>เข้าสู่ระบบนักศึกษา คณะวิทยาศาสตร์และเทคโนโลยี</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span><?= esc(session()->getFlashdata('success')) ?></span>
                </div>
            <?php endif; ?>

            <?php
            $uruOAuth = config(\Config\UruPortalOAuth::class);
            if ($uruOAuth->enabled):
            ?>
                <a href="<?= base_url('oauth/login?intent=student') ?>" class="btn-portal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                        <polyline points="10 17 15 12 10 7"></polyline>
                        <line x1="15" y1="12" x2="3" y2="12"></line>
                    </svg>
                    เข้าสู่ระบบด้วย URU Portal
                </a>

                <div class="divider"><span>หรือ</span></div>
                <a href="#" class="toggle-link" id="toggleEmailForm" aria-expanded="false">เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน</a>
            <?php endif; ?>

            <div class="email-form <?= $uruOAuth->enabled ? 'hidden' : '' ?>" id="emailFormWrap">
                <form method="post" action="<?= base_url('student/login') ?>">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="login">อีเมล</label>
                        <input type="text" id="login" name="login" value="<?= esc(old('login')) ?>" placeholder="อีเมล@example.com" required autocomplete="username">
                    </div>
                    <div class="field">
                        <label for="password">รหัสผ่าน</label>
                        <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn-submit">เข้าสู่ระบบ</button>
                </form>
            </div>

            <?php if (!$uruOAuth->enabled): ?>
                <div class="alert alert-error" style="margin-top: 1rem;">
                    <span>การเข้าสู่ระบบผ่าน URU Portal ยังไม่เปิดใช้งาน กรุณาใช้อีเมลและรหัสผ่านด้านบน หรือติดต่อผู้ดูแลระบบ</span>
                </div>
            <?php endif; ?>

            <div class="back">
                <a href="<?= base_url() ?>">← กลับหน้าแรก</a>
            </div>

            <?php if (defined('ENVIRONMENT') && ENVIRONMENT === 'development'): ?>
                <p style="margin-top: 1rem; font-size: 0.85rem; color: var(--text-muted); text-align: center;">
                    <a href="<?= base_url('dev/student-test') ?>" style="color: var(--primary);">ทดสอบ (dev): บัญชี dummy u59/u69</a>
                </p>
            <?php endif; ?>

            <div class="features">
                <span>E-Certificate</span>
                <span>E-Barcode</span>
            </div>
        </div>
    </div>

    <script>
        (function() {
            var toggle = document.getElementById('toggleEmailForm');
            var wrap = document.getElementById('emailFormWrap');
            if (toggle && wrap) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    wrap.classList.toggle('hidden');
                    toggle.setAttribute('aria-expanded', wrap.classList.contains('hidden') ? 'false' : 'true');
                    toggle.textContent = wrap.classList.contains('hidden') ? 'เข้าสู่ระบบด้วยอีเมลและรหัสผ่าน' : 'ซ่อนแบบฟอร์มอีเมล';
                });
            }
        })();
    </script>
</body>

</html>

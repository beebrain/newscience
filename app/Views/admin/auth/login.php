<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#b8860b">
    <title>Teacher and Staff Login | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <style>
        :root {
            --primary: #b8860b;
            --primary-hover: #996500;
            --gold: #d4af37;
            --gold-light: #f5ecd8;
            --bg: #faf6ef;
            --card: #fffef9;
            --text: #4a3c20;
            --border: #e5d4a1;
            --shadow: 0 4px 6px -1px rgba(184, 134, 11, 0.1), 0 10px 24px -5px rgba(184, 134, 11, 0.08);
            --shadow-lg: 0 20px 40px -10px rgba(184, 134, 11, 0.15);
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
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(212, 175, 55, 0.22), transparent),
                radial-gradient(ellipse 60% 40% at 100% 100%, rgba(184, 134, 11, 0.12), transparent),
                radial-gradient(ellipse 50% 30% at 0% 80%, rgba(212, 175, 55, 0.15), transparent);
        }

        .page-wrap {
            width: 100%;
            max-width: 440px;
        }

        .card {
            background: var(--card);
            border-radius: 20px;
            padding: 2.25rem 2rem;
            box-shadow: var(--shadow), 0 0 0 1px rgba(212, 175, 55, 0.06);
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
            background: linear-gradient(145deg, var(--gold), var(--primary), var(--primary-hover));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 24px -4px rgba(184, 134, 11, 0.4), 0 0 0 1px rgba(212, 175, 55, 0.2) inset;
            animation: iconIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        .brand-icon svg {
            width: 28px;
            height: 28px;
            color: #1a1510;
            filter: drop-shadow(0 1px 0 rgba(255,255,255,0.4));
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
            color: #6b5b45;
            animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            background: linear-gradient(145deg, var(--gold), var(--primary), var(--primary-hover));
            border: none;
            border-radius: 14px;
            color: #1a1510;
            font-weight: 600;
            text-shadow: 0 1px 0 rgba(255,255,255,0.25);
            font-size: 1rem;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease;
            box-shadow: 0 4px 16px -2px rgba(184, 134, 11, 0.4), 0 0 0 1px rgba(212, 175, 55, 0.25) inset;
            animation: fadeSlideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.45s both;
        }

        .btn-portal:hover {
            transform: translateY(-3px) scale(1.01);
            box-shadow: 0 10px 28px -4px rgba(184, 134, 11, 0.5), 0 0 0 1px rgba(212, 175, 55, 0.3) inset;
        }

        .btn-portal:active {
            transform: translateY(0) scale(0.99);
            transition-duration: 0.1s;
        }

        .btn-portal svg {
            width: 22px;
            height: 22px;
        }

        .back {
            text-align: center;
            margin-top: 1.75rem;
            animation: fadeIn 0.5s ease-out 0.55s both;
        }

        .back a {
            color: #6b5b45;
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
            border-top: 1px solid var(--border);
            animation: fadeIn 0.5s ease-out 0.65s both;
        }

        .features span {
            font-size: 0.75rem;
            color: #8b7355;
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

        .logout-iframes {
            width: 0;
            height: 0;
            overflow: hidden;
            position: absolute;
            visibility: hidden;
        }
    </style>
</head>

<body>
    <div class="page-wrap">
        <div class="card">
            <div class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z" />
                        <path d="M2 17l10 5 10-5" />
                        <path d="M2 12l10 5 10-5" />
                    </svg>
                </div>
                <h1>Teacher and Staff Login</h1>
                <p>เข้าสู่ระบบอาจารย์และบุคลากร คณะวิทยาศาสตร์และเทคโนโลยี</p>
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

            <?php if (isset($_GET['logout']) && $_GET['logout'] === '1'): ?>
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                    <span>ออกจากระบบแล้ว</span>
                </div>
                <div class="logout-iframes" aria-hidden="true">
                    <?php
                    $edocSso = config(\Config\EdocSso::class);
                    if ($edocSso->enabled && $edocSso->logoutUrl !== '') {
                        echo '<iframe src="' . esc($edocSso->logoutUrl) . '" title="Edoc logout"></iframe>';
                    }
                    $researchSso = config(\Config\ResearchRecordSso::class);
                    if ($researchSso->enabled && $researchSso->logoutUrl !== '') {
                        echo '<iframe src="' . esc($researchSso->logoutUrl) . '" title="Research logout"></iframe>';
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php
            $uruOAuth = config(\Config\UruPortalOAuth::class);
            if ($uruOAuth->enabled):
            ?>
                <a href="<?= base_url('oauth/login') ?>" class="btn-portal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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

            <div class="back">
                <a href="<?= base_url() ?>">← กลับหน้าแรก</a>
            </div>

            <div class="features">
                <span>Dashboard</span>
                <span>E-Document</span>
                <span>E-Certificate</span>
            </div>
        </div>
    </div>
</body>

</html>

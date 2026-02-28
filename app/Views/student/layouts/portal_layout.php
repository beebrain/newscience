<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= esc($page_title ?? 'Student Portal') ?> | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <style>
        .portal-wrap { min-height: 100vh; background: var(--color-gray-100); }
        .portal-topbar {
            background: var(--color-white);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .portal-topbar .portal-brand {
            font-weight: 700;
            font-size: 1.125rem;
            color: var(--color-gray-800);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .portal-topbar .portal-brand:hover { color: var(--primary-dark); }
        .portal-topbar .portal-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
        }
        .portal-topbar .portal-brand-icon svg { width: 20px; height: 20px; }
        .portal-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .portal-nav a {
            padding: 0.5rem 1rem;
            color: var(--color-gray-600);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.9375rem;
        }
        .portal-nav a:hover { background: var(--color-gray-100); color: var(--color-gray-800); }
        .portal-nav .portal-user {
            color: var(--color-gray-500);
            font-size: 0.875rem;
            margin-left: 0.5rem;
            padding-left: 1rem;
            border-left: 1px solid var(--color-gray-200);
        }
        .portal-main { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
    </style>
</head>
<body>
<div class="portal-wrap">
    <header class="portal-topbar">
        <a href="<?= base_url('student') ?>" class="portal-brand">
            <div class="portal-brand-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            </div>
            Student Portal
        </a>
        <nav class="portal-nav">
            <a href="<?= base_url() ?>">กลับหน้าหลัก</a>
            <span class="portal-user"><?= esc(session()->get('student_name') ?: session()->get('student_email')) ?></span>
            <a href="<?= base_url('student/logout') ?>">ออกจากระบบ</a>
        </nav>
    </header>
    <main class="portal-main">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" role="status"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>
</div>
</body>
</html>

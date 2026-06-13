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
        .portal-header {
            background: var(--color-white);
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .portal-topbar {
            max-width: 900px;
            margin: 0 auto;
            padding: 0.75rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .portal-topbar .portal-brand:hover { color: var(--secondary); }
        .portal-topbar .portal-brand-icon {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1a1a1a;
        }
        .portal-topbar .portal-brand-icon svg { width: 18px; height: 18px; }
        .portal-user-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .portal-user-nav .portal-link-back {
            color: var(--color-gray-500);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .portal-user-nav .portal-link-back:hover {
            color: var(--color-gray-800);
        }
        .portal-user-nav .portal-user {
            color: var(--color-gray-700);
            font-size: 0.875rem;
            font-weight: 600;
            background: var(--color-gray-100);
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            border: 1px solid var(--color-gray-200);
        }
        .portal-user-nav .portal-link-logout {
            color: var(--color-error);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .portal-user-nav .portal-link-logout:hover {
            text-decoration: underline;
        }
        .portal-subnav {
            background: var(--color-white);
            border-top: 1px solid var(--color-gray-100);
        }
        .portal-subnav-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            gap: 1rem;
            overflow-x: auto;
            scrollbar-width: none; /* Firefox */
        }
        .portal-subnav-container::-webkit-scrollbar {
            display: none; /* Safari and Chrome */
        }
        .portal-subnav-container a {
            padding: 0.875rem 0.25rem;
            color: var(--color-gray-600);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .portal-subnav-container a:hover {
            color: var(--secondary);
        }
        .portal-subnav-container a.active {
            color: var(--secondary);
            border-bottom-color: var(--secondary);
            font-weight: 600;
        }
        .portal-subnav-container a.portal-admin-tab {
            color: var(--color-gray-700);
        }
        .portal-subnav-container a.portal-admin-tab:hover {
            color: var(--color-primary-dark);
        }
        .portal-subnav-container a.portal-admin-tab.active {
            color: var(--color-primary-dark);
            border-bottom-color: var(--color-primary-dark);
        }
        .portal-flash { margin-bottom: 1rem; }
        .portal-main { max-width: 900px; margin: 0 auto; padding: 2rem 1.5rem; }
    </style>
</head>
<body>
<?php if (session()->get('is_impersonating')): ?>
    <div style="background: var(--color-warning, #f59e0b); color: #1a1a1a; padding: 0.75rem 1.5rem; text-align: center; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 1rem; position: relative; width: 100%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); z-index: 1001;">
        <span>กำลังใช้งานแทนนักศึกษา: <?= esc(session()->get('student_name')) ?> (รหัสนักศึกษา: <?= esc(session()->get('student_uid')) ?>)</span>
        <a href="<?= base_url('student/stop-impersonate') ?>" style="background: #1a1a1a; color: white; border: none; padding: 0.35rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.85rem; font-weight: 500; transition: background 0.2s;">
            กลับสู่ระบบ Admin
        </a>
    </div>
<?php endif; ?>
<div class="portal-wrap">
    <header class="portal-header">
        <div class="portal-topbar">
            <a href="<?= base_url('student') ?>" class="portal-brand">
                <div class="portal-brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                Student Portal
            </a>
            <div class="portal-user-nav">
                <a href="<?= base_url() ?>" class="portal-link-back">กลับหน้าแรกเว็บ</a>
                <span class="portal-user"><?= esc(session()->get('student_name') ?: session()->get('student_email') ?: session()->get('admin_name') ?: session()->get('admin_email') ?: 'User') ?></span>
                <a href="<?= session()->get('is_impersonating') ? base_url('student/logout') : (session()->get('admin_logged_in') ? base_url('admin/logout') : base_url('student/logout')) ?>" class="portal-link-logout">ออกจากระบบ</a>
            </div>
        </div>
        <nav class="portal-subnav">
            <div class="portal-subnav-container">
                <a href="<?= base_url('student') ?>" class="<?= uri_string() === 'student' || uri_string() === 'student/dashboard' ? 'active' : '' ?>">หน้าแรก Portal</a>
                <a href="<?= base_url('student/barcodes') ?>" class="<?= str_starts_with(uri_string(), 'student/barcodes') ? 'active' : '' ?>">กิจกรรม / รับรหัส</a>
                <a href="<?= base_url('student/certificates') ?>" class="<?= str_starts_with(uri_string(), 'student/certificates') ? 'active' : '' ?>">ใบประกาศของฉัน</a>
                <a href="<?= base_url('student/events') ?>" class="<?= str_starts_with(uri_string(), 'student/events') ? 'active' : '' ?>">ข่าว / Event</a>
                <?php if (session()->get('admin_logged_in') || in_array(session()->get('student_role'), ['club', 'admin_student'], true)): ?>
                    <a href="<?= base_url('student-admin/barcode-events') ?>" class="<?= str_starts_with(uri_string(), 'student-admin') ? 'active' : '' ?> portal-admin-tab">Student Admin</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <main class="portal-main" id="portal-main">
        <div class="portal-flash" aria-live="polite" aria-atomic="true">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success" role="status"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" role="alert"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>
        </div>
        <?= $this->renderSection('content') ?>
    </main>
</div>
</body>
</html>

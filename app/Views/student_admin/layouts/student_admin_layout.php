<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= esc($page_title ?? 'Student Admin') ?> | คณะวิทยาศาสตร์และเทคโนโลยี</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo250.png') ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body>
<a href="#student-admin-main" class="admin-skip-link">ข้ามไปเนื้อหาหลัก</a>
<div class="admin-container">
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?= base_url('student-admin/barcode-events') ?>" class="sidebar-logo" aria-label="Student Admin">
                <div class="sidebar-logo-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                    </svg>
                </div>
                <span>Student Admin</span>
            </a>
        </div>
        <nav class="sidebar-nav" aria-label="เมนูหลัก">
            <a href="<?= base_url('student-admin/barcode-events') ?>" class="<?= (strpos(uri_string(), 'student-admin/barcode-events') === 0) ? 'active' : '' ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                จัดการบาร์โค้ด
            </a>
            <?php if (session()->get('admin_logged_in')): ?>
                <a href="<?= base_url('dashboard') ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    กลับ Dashboard
                </a>
            <?php elseif (session()->get('student_logged_in')): ?>
                <a href="<?= base_url('student') ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    กลับ Student Portal
                </a>
            <?php endif; ?>
            <a href="<?= base_url() ?>" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                หน้าแรกเว็บ
            </a>
        </nav>
    </aside>
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    <main id="student-admin-main" class="main-content" role="main">
        <header class="topbar">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <button class="topbar-menu-btn btn-secondary" onclick="toggleSidebar()" style="display: none; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--color-gray-300); background: white; cursor: pointer;" aria-label="เปิดเมนู">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <h1 class="topbar-title" style="margin: 0;"><?= esc($page_title ?? 'Student Admin') ?></h1>
            </div>
            <div class="topbar-user">
                <span><?= esc(session()->get('admin_name') ?? session()->get('student_name') ?? session()->get('student_email') ?? 'User') ?></span>
                <a href="<?= session()->get('admin_logged_in') ? base_url('admin/logout') : base_url('student/logout') ?>">ออกจากระบบ</a>
            </div>
        </header>
        <div class="content">
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success" role="status">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <?= esc(session()->getFlashdata('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error" role="status">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>
            <?= $this->renderSection('content') ?>
        </div>
    </main>
</div>
<script>
function toggleSidebar() {
    var w = window.innerWidth;
    if (w < 1024) {
        document.querySelector('.sidebar').classList.toggle('active');
        document.querySelector('.sidebar-overlay').classList.toggle('active');
    } else {
        document.querySelector('.sidebar').classList.toggle('desktop-closed');
        document.querySelector('.main-content').classList.toggle('desktop-closed');
    }
}
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>

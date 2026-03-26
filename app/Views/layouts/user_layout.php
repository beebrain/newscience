<?php

use App\Libraries\AccessControl;

$uid = (int) session()->get('admin_id');
$userRole = session()->get('admin_role') ?? 'user';
$userName = session()->get('admin_name') ?? session()->get('admin_email') ?? 'User';
$isAdmin = in_array($userRole, ['admin', 'editor', 'super_admin', 'faculty_admin'], true);
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= esc($page_title ?? 'Dashboard') ?> | คณะวิทยาศาสตร์ฯ</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sarabun': ['Sarabun', 'Noto Sans Thai', 'sans-serif'],
                    },
                    colors: {
                        primary: { DEFAULT: '#eab308', dark: '#ca8a04', light: '#fef9c3' },
                        secondary: { DEFAULT: '#2d7d46', dark: '#1e5631' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Sarabun', 'Noto Sans Thai', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    <?= $this->renderSection('styles') ?>
</head>

<body class="bg-gray-50 min-h-screen flex flex-col font-sarabun">

    <!-- Topbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">

                <!-- Logo -->
                <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-3 text-gray-800 hover:text-primary-dark transition-colors shrink-0">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" /><path d="M2 17l10 5 10-5" /><path d="M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-sm font-bold leading-tight">คณะวิทยาศาสตร์ฯ</div>
                        <div class="text-xs text-gray-500 leading-tight">ระบบบริการบุคลากร</div>
                    </div>
                </a>

                <!-- Desktop Nav -->
                <nav class="hidden md:flex items-center gap-1">
                    <?php
                    $currentUri = uri_string();
                    $navItems = [
                        ['url' => 'dashboard', 'label' => 'หน้าหลัก', 'icon' => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'],
                        ['url' => 'edoc', 'label' => 'E-Document', 'icon' => '<path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/>'],
                        ['url' => 'evaluate', 'label' => 'ประเมินการสอน', 'icon' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>'],
                        ['url' => 'exam', 'label' => 'ตารางสอบ', 'icon' => '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>'],
                    ];
                    foreach ($navItems as $item):
                        $isActive = ($currentUri === $item['url'] || strpos($currentUri, $item['url'] . '/') === 0);
                    ?>
                        <a href="<?= base_url($item['url']) ?>"
                           class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-colors <?= $isActive ? 'bg-yellow-50 text-yellow-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-800' ?>">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
                            <?= $item['label'] ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <!-- Right: User + Admin link -->
                <div class="flex items-center gap-2">
                    <?php if ($isAdmin): ?>
                        <a href="<?= base_url('admin/news') ?>" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-yellow-700 bg-yellow-50 border border-yellow-200 rounded-full hover:bg-yellow-100 transition-colors">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 004.561 21h14.878a2 2 0 001.94-1.515L22 17"/></svg>
                            Admin Panel
                        </a>
                    <?php endif; ?>

                    <!-- User Dropdown -->
                    <div class="relative" id="user-menu">
                        <button onclick="document.getElementById('user-dropdown').classList.toggle('hidden')"
                                class="flex items-center gap-2 pl-3 pr-2 py-1.5 rounded-full hover:bg-gray-100 transition-colors border border-transparent hover:border-gray-200">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center text-white text-sm font-bold">
                                <?= strtoupper(mb_substr($userName, 0, 1)) ?>
                            </div>
                            <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate"><?= esc($userName) ?></span>
                            <svg class="w-4 h-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="text-sm font-semibold text-gray-800"><?= esc($userName) ?></div>
                                <div class="text-xs text-gray-500"><?= esc(session()->get('admin_email') ?? '') ?></div>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-600"><?= esc(ucfirst($userRole)) ?></span>
                            </div>
                            <a href="<?= base_url() ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                                กลับเว็บไซต์หลัก
                            </a>
                            <?php if ($isAdmin): ?>
                                <a href="<?= base_url('admin/news') ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                                    Admin Panel
                                </a>
                            <?php endif; ?>
                            <div class="border-t border-gray-100 mt-1 pt-1">
                                <a href="<?= base_url('admin/logout') ?>" class="flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                    ออกจากระบบ
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile menu toggle -->
                    <button id="mobile-menu-btn" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                            class="md:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 space-y-1">
                <?php foreach ($navItems as $item):
                    $isActive = ($currentUri === $item['url'] || strpos($currentUri, $item['url'] . '/') === 0);
                ?>
                    <a href="<?= base_url($item['url']) ?>"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium <?= $isActive ? 'bg-yellow-50 text-yellow-700' : 'text-gray-600 hover:bg-gray-50' ?>">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><?= $item['icon'] ?></svg>
                        <?= $item['label'] ?>
                    </a>
                <?php endforeach; ?>
                <?php if ($isAdmin): ?>
                    <div class="pt-2 mt-2 border-t border-gray-100">
                        <a href="<?= base_url('admin/news') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-yellow-700 bg-yellow-50">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                            เข้าสู่ Admin Panel
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 mt-4">
        <?php if (session()->getFlashdata('success')): ?>
            <div class="flex items-center gap-3 p-4 mb-4 rounded-xl bg-green-50 border border-green-200 text-green-800" role="status">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span class="text-sm"><?= session()->getFlashdata('success') ?></span>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flex items-center gap-3 p-4 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800" role="status">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span class="text-sm"><?= session()->getFlashdata('error') ?></span>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="flex gap-3 p-4 mb-4 rounded-xl bg-red-50 border border-red-200 text-red-800" role="status">
                <svg class="w-5 h-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <ul class="text-sm space-y-1">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= $error ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <!-- SSO auto-login iframes -->
    <?php if (session()->getFlashdata('sso_autologin_urls')): ?>
        <div class="sr-only">
            <?php foreach (session()->getFlashdata('sso_autologin_urls') as $ssoUrl): ?>
                <iframe src="<?= esc($ssoUrl) ?>" class="w-0 h-0 border-0"></iframe>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="flex-1">
        <?= $this->renderSection('content') ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-gray-500">
                <span>&copy; <?= date('Y') + 543 ?> คณะวิทยาศาสตร์และเทคโนโลยี ม.ราชภัฏอุตรดิตถ์</span>
                <a href="<?= base_url() ?>" class="hover:text-gray-700 transition-colors">กลับเว็บไซต์หลัก</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.BASE_URL = '<?= base_url() ?>';

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            var menu = document.getElementById('user-menu');
            var dropdown = document.getElementById('user-dropdown');
            if (menu && dropdown && !menu.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        window.swalAlert = function(msg, type) {
            type = type || 'info';
            var icon = { success: 'success', error: 'error', warning: 'warning', info: 'info' }[type] || 'info';
            return (typeof Swal !== 'undefined') ? Swal.fire({ icon: icon, title: type === 'error' ? 'เกิดข้อผิดพลาด' : (type === 'success' ? 'สำเร็จ' : ''), text: msg }) : Promise.resolve(alert(msg));
        };
        window.swalConfirm = function(opts) {
            var title = (typeof opts === 'string') ? opts : (opts.title || 'ยืนยัน');
            var text = (typeof opts === 'object' && opts.text) ? opts.text : '';
            var confirmText = (typeof opts === 'object' && opts.confirmText) ? opts.confirmText : 'ตกลง';
            var cancelText = (typeof opts === 'object' && opts.cancelText) ? opts.cancelText : 'ยกเลิก';
            if (typeof Swal === 'undefined') return Promise.resolve(window.confirm(title + (text ? '\n' + text : '')));
            return Swal.fire({ title: title, text: text, icon: 'question', showCancelButton: true, confirmButtonText: confirmText, cancelButtonText: cancelText, confirmButtonColor: '#d33', cancelButtonColor: '#6c757d' }).then(function(r) { return r.isConfirmed; });
        };
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>

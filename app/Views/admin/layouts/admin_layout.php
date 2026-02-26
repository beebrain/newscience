<?php

use App\Libraries\AccessControl; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= $page_title ?? 'Admin' ?> | University Admin</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/images/logo250.png') ?>" sizes="32x32">

    <!-- ฟอนต์ Sarabun (โหลดจากโปรเจกต์) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <!-- Central CSS: theme + admin (ธีมสีเดียวกับหน้าแรก) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>

<body>
    <a href="#admin-main" class="admin-skip-link">ข้ามไปเนื้อหาหลัก</a>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <a href="<?= base_url() ?>" class="sidebar-logo" aria-label="กลับหน้าหลัก University Admin">
                    <div class="sidebar-logo-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="24" height="24" aria-hidden="true">
                            <path d="M12 2L2 7l10 5 10-5-10-5z" />
                            <path d="M2 17l10 5 10-5" />
                            <path d="M2 12l10 5 10-5" />
                        </svg>
                    </div>
                    <span>University Admin</span>
                </a>
            </div>

            <nav class="sidebar-nav" aria-label="เมนูหลัก">
                <?php $sidebarAdminId = session()->get('admin_id'); $sid = (int) $sidebarAdminId; ?>
                <a href="<?= base_url('dashboard') ?>" class="<?= (uri_string() == 'dashboard') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    สลับไปหน้า Dashboard
                </a>

                <!-- E-Document -->
                <?php if ($sidebarAdminId && (AccessControl::hasAccess($sid, 'edoc') || AccessControl::hasAccess($sid, 'edoc_admin'))): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">E-Document</div>
                    <?php if (AccessControl::hasAccess($sid, 'edoc')): ?>
                    <a href="<?= base_url('edoc') ?>" class="<?= (strpos(uri_string(), 'edoc') === 0 && strpos(uri_string(), 'edoc/admin') !== 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /><polyline points="10 9 9 9 8 9" /></svg>
                        E-Document (ดูเอกสาร)
                    </a>
                    <?php endif; ?>
                    <?php if (AccessControl::hasAccess($sid, 'edoc_admin')): ?>
                    <a href="<?= base_url('edoc/admin') ?>" class="<?= (strpos(uri_string(), 'edoc/admin') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        E-Document (จัดการ)
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- จัดการเนื้อหา (admin_core) -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'admin_core')): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">จัดการเนื้อหา</div>
                    <a href="<?= base_url('admin/news') ?>" class="<?= (uri_string() == 'admin/news' || strpos(uri_string(), 'admin/news') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" /></svg>
                        News Management
                    </a>
                    <a href="<?= base_url('admin/organization') ?>" class="<?= (uri_string() == 'admin/organization' || strpos(uri_string(), 'admin/organization') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87" /><path d="M16 3.13a4 4 0 010 7.75" /></svg>
                        โครงสร้างองค์กร
                    </a>
                    <a href="<?= base_url('admin/programs') ?>" class="<?= (uri_string() == 'admin/programs' || strpos(uri_string(), 'admin/programs') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 016.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" /><line x1="8" y1="6" x2="16" y2="6" /><line x1="8" y1="10" x2="16" y2="10" /></svg>
                        จัดการหลักสูตร
                    </a>
                    <a href="<?= base_url('admin/hero-slides') ?>" class="<?= (uri_string() == 'admin/hero-slides' || strpos(uri_string(), 'admin/hero-slides') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2" ry="2" /><circle cx="8.5" cy="8.5" r="1.5" /><polyline points="21 15 16 10 5 21" /></svg>
                        Hero Slides
                    </a>
                    <a href="<?= base_url('admin/events') ?>" class="<?= (uri_string() == 'admin/events' || strpos(uri_string(), 'admin/events') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                        Events Coming Up
                    </a>
                </div>
                <?php endif; ?>

                <!-- Content Builder (program_admin) -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'program_admin')): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">Content Builder</div>
                    <a href="<?= base_url('program-admin') ?>" class="<?= (strpos(uri_string(), 'program-admin') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 19l7-7 3 3-7 7-3-3z" /><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z" /><path d="M2 2l7.586 7.586" /><circle cx="11" cy="11" r="2" /></svg>
                        แก้ไขเว็บหลักสูตร
                    </a>
                </div>
                <?php endif; ?>

                <!-- E-Certificate -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'ecert')): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">E-Certificate</div>
                    <a href="<?= base_url('admin/cert-events') ?>" class="<?= (strpos(uri_string(), 'admin/cert-events') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" /></svg>
                        กิจกรรม/อบรม
                    </a>
                    <a href="<?= base_url('admin/cert-templates') ?>" class="<?= (strpos(uri_string(), 'admin/cert-templates') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /></svg>
                        เทมเพลตใบรับรอง
                    </a>
                    <a href="<?= base_url('admin/certificates') ?>" class="<?= (strpos(uri_string(), 'admin/certificates') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        คำขอใบรับรอง (เดิม)
                    </a>
                </div>
                <?php endif; ?>

                <!-- อนุมัติใบรับรอง (cert_approve) -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'cert_approve')): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">อนุมัติใบรับรอง</div>
                    <a href="<?= base_url('approve/certificates') ?>" class="<?= (strpos(uri_string(), 'approve') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12l2 2 4-4" /><circle cx="12" cy="12" r="10" /></svg>
                        อนุมัติใบรับรอง
                    </a>
                </div>
                <?php endif; ?>

                <!-- ผู้ดูแลระบบ -->
                <?php if ($sidebarAdminId && (AccessControl::hasAccess($sid, 'user_management') || AccessControl::hasAccess($sid, 'site_settings') || AccessControl::hasAccess($sid, 'utility'))): ?>
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">ผู้ดูแลระบบ</div>
                    <?php if (AccessControl::hasAccess($sid, 'user_management')): ?>
                    <a href="<?= base_url('admin/users') ?>" class="<?= (uri_string() == 'admin/users' || strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87" /><path d="M16 3.13a4 4 0 010 7.75" /></svg>
                        จัดการผู้ใช้
                    </a>
                    <?php endif; ?>
                    <?php if (AccessControl::hasAccess($sid, 'site_settings')): ?>
                    <a href="<?= base_url('admin/settings') ?>" class="<?= (uri_string() == 'admin/settings' || strpos(uri_string(), 'admin/settings') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="3" /><path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" /><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" /></svg>
                        ตั้งค่าเว็บไซต์
                    </a>
                    <?php endif; ?>
                    <?php if (AccessControl::hasAccess($sid, 'utility')): ?>
                    <a href="<?= base_url('utility/import-data') ?>" class="<?= (strpos(uri_string(), 'utility') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" /><polyline points="17 8 12 3 7 8" /><line x1="12" y1="3" x2="12" y2="15" /></svg>
                        เครื่องมือผู้ดูแล (Utility)
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- ลิงก์ภายนอก -->
                <div class="sidebar-submenu" style="margin: 0.5rem 0;">
                    <div class="submenu-header" style="padding: 0.5rem 1rem; color: var(--color-gray-500); font-size: 12px; text-transform: uppercase;">ลิงก์ภายนอก</div>
                    <?php
                    $researchSso = config(\Config\ResearchRecordSso::class);
                    $showResearch = $researchSso->enabled && $researchSso->baseUrl !== '' && $sidebarAdminId && AccessControl::hasAccess($sid, 'research_record');
                    if ($showResearch):
                    ?>
                    <a href="<?= esc(rtrim($researchSso->baseUrl, '/') . '/index.php/dashboard') ?>" target="_blank" rel="noopener noreferrer" aria-label="เข้าสู่หน้าการจัดการงานวิจัย Research Record (เปิดในแท็บใหม่)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
                        Research Record
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url() ?>" target="_blank" rel="noopener noreferrer" aria-label="ดูเว็บไซต์ (เปิดในแท็บใหม่)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><polyline points="15 3 21 3 21 9" /><line x1="10" y1="14" x2="21" y2="3" /></svg>
                        View Website
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <!-- Main Content -->
        <main id="admin-main" class="main-content" role="main">
            <header class="topbar">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- Hamburger Menu -->
                    <button class="topbar-menu-btn btn-secondary" onclick="toggleSidebar()" style="display: none; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--color-gray-300); background: white; cursor: pointer;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="topbar-title" style="margin: 0;"><?= $page_title ?? 'Dashboard' ?></h1>
                </div>

                <div class="topbar-user">
                    <span><?= session()->get('admin_name') ?? 'Admin' ?></span>
                    <a href="<?= base_url('admin/logout') ?>" aria-label="ออกจากระบบ (รวมทุกแอป)">Logout</a>
                </div>
            </header>

            <div class="content">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="alert alert-success" role="status" aria-live="polite">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="alert alert-error" role="status" aria-live="polite">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="15" y1="9" x2="9" y2="15" />
                            <line x1="9" y1="9" x2="15" y2="15" />
                        </svg>
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-error" role="status" aria-live="polite">
                        <ul style="margin: 0; padding-left: 1rem;">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Silent Auto-Login Iframes (trigger login on other apps) -->
                <?php if (session()->getFlashdata('sso_autologin_urls')): ?>
                    <div style="width:0;height:0;overflow:hidden;position:absolute;">
                        <?php foreach (session()->getFlashdata('sso_autologin_urls') as $ssoUrl): ?>
                            <iframe src="<?= esc($ssoUrl) ?>" style="width:0;height:0;border:0;"></iframe>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleSidebar() {
            const width = window.innerWidth;
            if (width < 1024) {
                document.querySelector('.sidebar').classList.toggle('active');
                document.querySelector('.sidebar-overlay').classList.toggle('active');
            } else {
                document.querySelector('.sidebar').classList.toggle('desktop-closed');
                document.querySelector('.main-content').classList.toggle('desktop-closed');
            }
        }
        window.swalAlert = function(msg, type) {
            type = type || 'info';
            var icon = { success: 'success', error: 'error', warning: 'warning', info: 'info' }[type] || 'info';
            return (typeof Swal !== 'undefined') ? Swal.fire({ icon: icon, title: type === 'error' ? 'เกิดข้อผิดพลาด' : (type === 'success' ? 'สำเร็จ' : ''), text: msg }) : Promise.resolve(alert(msg));
        };
        window.swalConfirm = function(opts) {
            var title = (typeof opts === 'string') ? opts : (opts.title || 'ยืนยัน');
            var text = (typeof opts === 'string') ? '' : (opts.text || '');
            var confirmText = (typeof opts === 'object' && opts.confirmText) ? opts.confirmText : 'ตกลง';
            var cancelText = (typeof opts === 'object' && opts.cancelText) ? opts.cancelText : 'ยกเลิก';
            if (typeof Swal === 'undefined') return Promise.resolve(window.confirm(title + (text ? '\n' + text : '')));
            return Swal.fire({
                title: title,
                text: text,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: cancelText,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d'
            }).then(function(r) { return r.isConfirmed; });
        };
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>
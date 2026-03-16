<?php

use App\Libraries\AccessControl; ?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#1a1a1a">
    <title><?= $page_title ?? 'Admin' ?> | University Admin</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">

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
                <?php $sidebarAdminId = session()->get('admin_id');
                $sid = (int) $sidebarAdminId; ?>
                
                <!-- ตารางคุมสอบ (exam) -->
                <?php
                $hasExam = $sidebarAdminId && (AccessControl::hasAccess($sid, 'exam') || AccessControl::hasAccess($sid, 'exam_admin'));
                ?>
                <?php if ($hasExam): ?>
                    <div class="sidebar-submenu" data-submenu="exam">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>ตารางคุมสอบ</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('exam') ?>" class="<?= (uri_string() == 'exam' || strpos(uri_string(), 'exam') === 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                ดูตารางคุมสอบ
                            </a>
                            <?php if (\App\Libraries\AccessControl::hasAccess($sid, 'exam_admin')): ?>
                                <div class="nav-section">
                                    <div class="nav-section-title">จัดการตารางสอบ</div>
                                </div>
                                <a href="<?= base_url('admin/exam') ?>" class="<?= (uri_string() == 'admin/exam' || strpos(uri_string(), 'admin/exam') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                    จัดการตารางคุมสอบ (Excel)
                                </a>
                                <a href="<?= base_url('admin/exam-json') ?>" class="<?= (uri_string() == 'admin/exam-json' || strpos(uri_string(), 'admin/exam-json') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                    จัดการตารางคุมสอบ (JSON)
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- จัดการผู้ใช้ -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'admin_core')): ?>
                    <a href="<?= base_url('admin/users') ?>" class="<?= (uri_string() == 'admin/users' || strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M23 21v-2a4 4 0 00-3-3.87" />
                            <path d="M16 3.13a4 4 0 010 7.75" />
                        </svg>
                        จัดการผู้ใช้
                    </a>
                <?php endif; ?>

                <!-- จัดการเนื้อหา -->
                <?php
                $hasAdminCore = $sidebarAdminId && AccessControl::hasAccess($sid, 'admin_core');
                $hasAdminNews = $sidebarAdminId && AccessControl::hasAccess($sid, 'admin_news');
                $showContentMenu = $hasAdminCore || $hasAdminNews;
                ?>
                <?php if ($showContentMenu): ?>
                    <div class="sidebar-submenu" data-submenu="content">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>จัดการเนื้อหา</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <?php if ($hasAdminCore || $hasAdminNews): ?>
                                <a href="<?= base_url('admin/news') ?>" class="<?= (uri_string() == 'admin/news' || strpos(uri_string(), 'admin/news') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                    </svg>
                                    ประกาศข่าว / News
                                </a>
                            <?php endif; ?>
                            <?php if ($hasAdminCore): ?>
                                <a href="<?= base_url('admin/hero-slides') ?>" class="<?= (uri_string() == 'admin/hero-slides' || strpos(uri_string(), 'admin/hero-slides') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                    Hero Slides
                                </a>
                                <a href="<?= base_url('admin/events') ?>" class="<?= (uri_string() == 'admin/events' || strpos(uri_string(), 'admin/events') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    Events Coming Up
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- E-Document (เฉพาะสำหรับผู้จัดการ) -->
                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'edoc_admin')): ?>
                    <div class="sidebar-submenu" data-submenu="edoc">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>E-Document</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('edoc') ?>" class="<?= (strpos(uri_string(), 'edoc') === 0 && strpos(uri_string(), 'edoc/admin') !== 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                                E-Document (ดูเอกสาร)
                            </a>
                            <a href="<?= base_url('edoc/admin') ?>" class="<?= (strpos(uri_string(), 'edoc/admin') === 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                E-Document (จัดการ)
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </nav>
        </aside>    <polyline points="22 4 12 14.01 9 11.01" />
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
            var icon = {
                success: 'success',
                error: 'error',
                warning: 'warning',
                info: 'info'
            } [type] || 'info';
            return (typeof Swal !== 'undefined') ? Swal.fire({
                icon: icon,
                title: type === 'error' ? 'เกิดข้อผิดพลาด' : (type === 'success' ? 'สำเร็จ' : ''),
                text: msg
            }) : Promise.resolve(alert(msg));
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
            }).then(function(r) {
                return r.isConfirmed;
            });
        };

        // Sidebar collapsible submenus
        (function() {
            const STORAGE_KEY = 'admin.sidebar.submenus.v1';
            const root = document.querySelector('.sidebar');
            if (!root) return;

            let state = {};
            try {
                state = JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}') || {};
            } catch (e) {
                state = {};
            }

            const submenus = Array.from(root.querySelectorAll('.sidebar-submenu[data-submenu]'));
            submenus.forEach((submenu) => {
                const key = submenu.getAttribute('data-submenu') || '';
                const btn = submenu.querySelector('.submenu-header');
                const items = submenu.querySelector('.submenu-items');
                if (!btn || !items || !key) return;

                const hasActive = !!items.querySelector('a.active');
                const savedOpen = state[key];
                /* ยังไม่เคยบันทึก = เปิดทุกกลุ่มให้เห็นรายการ; มี active = เปิดกลุ่มนั้น; เก่าบันทึกแล้ว = ใช้ค่าที่บันทึก */
                const open = (typeof savedOpen === 'boolean') ? savedOpen : (hasActive || true);
                submenu.classList.toggle('open', open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');

                btn.addEventListener('click', function() {
                    const nowOpen = !submenu.classList.contains('open');
                    submenu.classList.toggle('open', nowOpen);
                    btn.setAttribute('aria-expanded', nowOpen ? 'true' : 'false');
                    state[key] = nowOpen;
                    try {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                    } catch (e) {}
                });
            });
        })();
    </script>
    <?= $this->renderSection('scripts') ?>
</body>

</html>
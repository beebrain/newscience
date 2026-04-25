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

    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            important: false,
            corePlugins: { preflight: false },
            theme: {
                extend: {
                    fontFamily: { 'sarabun': ['Sarabun', 'Noto Sans Thai', 'sans-serif'] },
                    colors: {
                        primary: { DEFAULT: '#eab308', dark: '#ca8a04', light: '#fef9c3' },
                    }
                }
            }
        }
    </script>
    <?= $this->renderSection('styles') ?>
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
                $sid = (int) $sidebarAdminId;
                $sidebarRole = session()->get('admin_role');
                $hasAdminCore = $sidebarAdminId && AccessControl::hasAccess($sid, 'admin_core');
                $hasAdminNews = $sidebarAdminId && AccessControl::hasAccess($sid, 'admin_news');
                $hasAdminDownloads = $sidebarAdminId && (AccessControl::hasAccess($sid, 'admin_downloads') || AccessControl::hasAccess($sid, 'admin_core'));
                $hasAdminUrgentPopup = $sidebarAdminId && (AccessControl::hasAccess($sid, 'admin_urgent_popup') || AccessControl::hasAccess($sid, 'admin_core'));
                $hasAcademicService = $sidebarAdminId && (AccessControl::hasAccess($sid, 'academic_service') || AccessControl::hasAccess($sid, 'admin_core'));
                $hasExam = $sidebarAdminId && (AccessControl::hasAccess($sid, 'exam') || AccessControl::hasAccess($sid, 'exam_admin'));
                $showContentMenu = $hasAdminCore || $hasAdminNews || $hasAdminDownloads || $hasAdminUrgentPopup;
                $canManageEvaluate = $sidebarAdminId && in_array($sidebarRole, ['super_admin', 'faculty_admin'], true);
                ?>
                <a href="<?= base_url('dashboard') ?>" class="<?= (uri_string() == 'dashboard') ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    สลับไปหน้า Dashboard
                </a>

                <?php if ($sidebarAdminId && ($sidebarRole === 'admin' || $sidebarRole === 'super_admin')): ?>
                    <a href="<?= base_url('admin/executive-dashboard') ?>" class="<?= (uri_string() == 'admin/executive-dashboard') ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 3v18h18" />
                            <path d="M18 9v4" />
                            <path d="M13 3v12" />
                            <path d="M8 7v8" />
                        </svg>
                        Dashboard ผู้บริหาร
                    </a>
                <?php endif; ?>

                <?php if ($sidebarAdminId && $sidebarRole === 'super_admin'): ?>
                    <a href="<?= base_url('admin/complaints') ?>" class="<?= (strpos(uri_string(), 'admin/complaints') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z" />
                            <path d="M8 9h8" />
                            <path d="M8 13h5" />
                        </svg>
                        รายการร้องเรียน
                    </a>
                <?php endif; ?>

                <?php if ($sidebarAdminId): ?>
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
                            <?php if (AccessControl::hasAccess($sid, 'edoc_admin')): ?>
                                <a href="<?= base_url('edoc/admin') ?>" class="<?= (strpos(uri_string(), 'edoc/admin') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    E-Document (จัดการ)
                                </a>
                            <?php endif; ?>
                            <a href="<?= base_url('edoc/analysis') ?>" class="<?= (strpos(uri_string(), 'edoc/analysis') === 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M3 3v18h18" />
                                    <path d="M18 9v4" />
                                    <path d="M13 3v12" />
                                    <path d="M8 7v8" />
                                </svg>
                                วิเคราะห์เอกสาร
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'calendar')): ?>
                    <a href="<?= base_url('admin/calendar') ?>" class="<?= (uri_string() == 'admin/calendar' || strpos(uri_string(), 'admin/calendar') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                        ปฏิทินนัดหมาย
                    </a>
                <?php endif; ?>

                <?php if ($canManageEvaluate): ?>
                    <div class="sidebar-submenu" data-submenu="evaluate">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>ประเมินผลการสอน</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('evaluate/admin') ?>" class="<?= (uri_string() === 'evaluate/admin') ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M9 11l3 3L22 4" />
                                    <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11" />
                                </svg>
                                จัดการระบบประเมินผลการสอน (Admin)
                            </a>
                            <a href="<?= base_url('evaluate/admin/referees') ?>" class="<?= (uri_string() == 'evaluate/admin/referees') ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                                จัดการผู้ทรงคุณวุฒิ
                            </a>
                            <a href="<?= base_url('evaluate/admin/settings') ?>" class="<?= (uri_string() == 'evaluate/admin/settings') ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" />
                                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
                                </svg>
                                ตั้งค่าระบบประเมิน
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

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
                                <a href="<?= base_url('admin/organization') ?>" class="<?= (uri_string() == 'admin/organization' || strpos(uri_string(), 'admin/organization') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                        <path d="M16 3.13a4 4 0 010 7.75" />
                                    </svg>
                                    โครงสร้างองค์กร
                                </a>
                                <a href="<?= base_url('admin/programs') ?>" class="<?= (uri_string() == 'admin/programs' || strpos(uri_string(), 'admin/programs') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                        <line x1="8" y1="6" x2="16" y2="6" />
                                        <line x1="8" y1="10" x2="16" y2="10" />
                                    </svg>
                                    จัดการหลักสูตร
                                </a>
                                <a href="<?= base_url('admin/hero-slides') ?>" class="<?= (uri_string() == 'admin/hero-slides' || strpos(uri_string(), 'admin/hero-slides') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                        <circle cx="8.5" cy="8.5" r="1.5" />
                                        <polyline points="21 15 16 10 5 21" />
                                    </svg>
                                    Hero Slides
                                </a>
                                <?php if ($hasAdminUrgentPopup): ?>
                                    <a href="<?= base_url('admin/urgent-popups') ?>" class="<?= (uri_string() == 'admin/urgent-popups' || strpos(uri_string(), 'admin/urgent-popups') === 0) ? 'active' : '' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                            <line x1="12" y1="9" x2="12" y2="13" />
                                            <line x1="12" y1="17" x2="12.01" y2="17" />
                                        </svg>
                                        ประกาศด่วน (ป๊อปอัป)
                                    </a>
                                    <a href="<?= base_url('admin/executive-posters') ?>" class="<?= (uri_string() == 'admin/executive-posters' || strpos(uri_string(), 'admin/executive-posters') === 0) ? 'active' : '' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2" />
                                            <circle cx="12" cy="9" r="3" />
                                            <path d="M7 18c0-2.5 2.5-4 5-4s5 1.5 5 4" />
                                        </svg>
                                        โปสเตอร์ผู้บริหาร
                                    </a>
                                <?php endif; ?>
                                <a href="<?= base_url('admin/events') ?>" class="<?= (uri_string() == 'admin/events' || strpos(uri_string(), 'admin/events') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                        <line x1="16" y1="2" x2="16" y2="6" />
                                        <line x1="8" y1="2" x2="8" y2="6" />
                                        <line x1="3" y1="10" x2="21" y2="10" />
                                    </svg>
                                    Events Coming Up
                                </a>
                                <?php if ($hasAdminDownloads): ?>
                                    <a href="<?= base_url('admin/downloads') ?>" class="<?= (uri_string() == 'admin/downloads' || strpos(uri_string(), 'admin/downloads') === 0) ? 'active' : '' ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                            <polyline points="7 10 12 15 17 10" />
                                            <line x1="12" y1="15" x2="12" y2="3" />
                                        </svg>
                                        จัดการดาวน์โหลดคณะ
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($hasAcademicService): ?>
                    <div class="sidebar-submenu" data-submenu="academic_service">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>บริการวิชาการ</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('admin/academic-services') ?>" class="<?= ((uri_string() == 'admin/academic-services' || strpos(uri_string(), 'admin/academic-services') === 0) && strpos(uri_string(), 'admin/academic-services/report') !== 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                </svg>
                                จัดการบริการวิชาการ
                            </a>
                            <a href="<?= base_url('admin/academic-services/report') ?>" class="<?= (uri_string() == 'admin/academic-services/report' || strpos(uri_string(), 'admin/academic-services/report') === 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                    <polyline points="10 9 9 9 8 9" />
                                </svg>
                                แบบรายงานสรุป
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

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
                            <?php if (AccessControl::hasAccess($sid, 'exam_admin')): ?>
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

                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'program_admin')): ?>
                    <div class="sidebar-submenu" data-submenu="program_admin">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>Content Builder</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('program-admin') ?>" class="<?= (strpos(uri_string(), 'program-admin') === 0) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 19l7-7 3 3-7 7-3-3z" />
                                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z" />
                                    <path d="M2 2l7.586 7.586" />
                                    <circle cx="11" cy="11" r="2" />
                                </svg>
                                แก้ไขเว็บหลักสูตร
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($sidebarAdminId && in_array($sidebarRole, ['admin', 'editor', 'super_admin', 'faculty_admin'], true)): ?>
                    <a href="<?= base_url('student-admin/barcode-events') ?>" class="<?= (strpos(uri_string(), 'student-admin') === 0) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2" />
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                        </svg>
                        จัดการบาร์โค้ด
                    </a>
                <?php endif; ?>

                <?php if ($sidebarAdminId && AccessControl::hasAccess($sid, 'ecert')): ?>
                    <div class="sidebar-submenu" data-submenu="ecert">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>E-Certificate</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <a href="<?= base_url('admin/cert-events') ?>" class="<?= (strpos(uri_string(), 'admin/cert-events') === 0 && strpos(uri_string(), 'issued-report') === false) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                    <line x1="16" y1="2" x2="16" y2="6" />
                                    <line x1="8" y1="2" x2="8" y2="6" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                กิจกรรม/อบรม
                            </a>
                            <a href="<?= base_url('admin/cert-events/issued-report') ?>" class="<?= (strpos(uri_string(), 'admin/cert-events/issued-report') !== false) ? 'active' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M9 17H7v-7h2v7zm4 0h-2V7h2v10zm4 0h-2v-4h2v4z" />
                                </svg>
                                รายงานใบที่ออกแล้ว
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($sidebarAdminId && (AccessControl::hasAccess($sid, 'user_management') || AccessControl::hasAccess($sid, 'site_settings') || AccessControl::hasAccess($sid, 'utility'))): ?>
                    <div class="sidebar-submenu" data-submenu="sysadmin">
                        <button type="button" class="submenu-header" aria-expanded="false">
                            <span>ผู้ดูแลระบบ</span>
                            <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <polyline points="6 9 12 15 18 9" />
                            </svg>
                        </button>
                        <div class="submenu-items">
                            <?php if (AccessControl::hasAccess($sid, 'user_management')): ?>
                                <a href="<?= base_url('admin/users') ?>" class="<?= (uri_string() == 'admin/users' || strpos(uri_string(), 'admin/users') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                        <path d="M16 3.13a4 4 0 010 7.75" />
                                    </svg>
                                    จัดการผู้ใช้
                                </a>
                                <a href="<?= base_url('admin/club-representatives') ?>" class="<?= (uri_string() == 'admin/club-representatives' || strpos(uri_string(), 'admin/club-representatives') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                        <circle cx="9" cy="7" r="4" />
                                        <path d="M22 11l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    ตัวแทนสโมสรนักศึกษา
                                </a>
                                <a href="<?= base_url('admin/user-faculty') ?>" class="<?= (uri_string() == 'admin/user-faculty' || strpos(uri_string(), 'admin/user-faculty') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    จัดการคณะผู้ใช้
                                </a>
                            <?php endif; ?>
                            <?php if (AccessControl::hasAccess($sid, 'site_settings')): ?>
                                <a href="<?= base_url('admin/settings') ?>" class="<?= (uri_string() == 'admin/settings' || strpos(uri_string(), 'admin/settings') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" />
                                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
                                    </svg>
                                    ตั้งค่าเว็บไซต์
                                </a>
                            <?php endif; ?>
                            <?php if ($canManageEvaluate): ?>
                                <a href="<?= base_url('evaluate/admin/settings') ?>" class="<?= (uri_string() == 'evaluate/admin/settings') ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <circle cx="12" cy="12" r="3" />
                                        <path d="M12 1v6m0 6v6m11-7h-6m-6 0H1" />
                                        <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z" />
                                    </svg>
                                    ตั้งค่าระบบประเมิน
                                </a>
                            <?php endif; ?>
                            <?php if (AccessControl::hasAccess($sid, 'utility')): ?>
                                <a href="<?= base_url('utility/import-data') ?>" class="<?= (uri_string() === 'utility/import-data' || strpos(uri_string(), 'utility/import-data') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                        <polyline points="17 8 12 3 7 8" />
                                        <line x1="12" y1="3" x2="12" y2="15" />
                                    </svg>
                                    Import Data
                                </a>
                                <a href="<?= base_url('utility/categorize-news') ?>" class="<?= (uri_string() === 'utility/categorize-news' || strpos(uri_string(), 'utility/categorize-news') === 0) ? 'active' : '' ?>">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z" />
                                        <polyline points="10 9 9 9 8 9" />
                                    </svg>
                                    จัดหมวดข่าว
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="sidebar-submenu" data-submenu="external">
                    <button type="button" class="submenu-header" aria-expanded="false">
                        <span>ลิงก์ภายนอก</span>
                        <svg class="submenu-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9" />
                        </svg>
                    </button>
                    <div class="submenu-items">
                        <?php
                        $researchSso = config(\Config\ResearchRecordSso::class);
                        $showResearch = $researchSso->enabled && $researchSso->baseUrl !== '' && $sidebarAdminId && AccessControl::hasAccess($sid, 'research_record');
                        if ($showResearch):
                        ?>
                            <a href="<?= esc(rtrim($researchSso->baseUrl, '/') . '/index.php/dashboard') ?>" target="_blank" rel="noopener noreferrer" aria-label="เข้าสู่หน้าการจัดการงานวิจัย กบศ (เปิดในแท็บใหม่)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                                กบศ
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url() ?>" target="_blank" rel="noopener noreferrer" aria-label="ดูเว็บไซต์ (เปิดในแท็บใหม่)">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" />
                                <polyline points="15 3 21 3 21 9" />
                                <line x1="10" y1="14" x2="21" y2="3" />
                            </svg>
                            View Website
                        </a>
                    </div>
                </div>
            </nav>
        </aside>

        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <main id="admin-main" class="main-content" role="main">
            <header class="topbar" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <button class="topbar-menu-btn btn-secondary" onclick="toggleSidebar()" style="display: none; padding: 0.5rem; border-radius: 8px; border: 1px solid var(--color-gray-300); background: white; cursor: pointer;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                    </button>
                    <h1 class="topbar-title" style="margin: 0;"><?= $page_title ?? 'Dashboard' ?></h1>
                </div>

                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <a href="<?= base_url('dashboard') ?>" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; font-size: 0.75rem; font-weight: 600; border-radius: 9999px; border: 1px solid #e5e7eb; color: #374151; background: #f9fafb; text-decoration: none; transition: all 0.15s;" onmouseover="this.style.background='#f3f4f6';this.style.borderColor='#d1d5db'" onmouseout="this.style.background='#f9fafb';this.style.borderColor='#e5e7eb'">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                        Dashboard
                    </a>
                    <?php
                    $topbarRole = session()->get('admin_role') ?? 'user';
                    $roleBadgeColors = [
                        'super_admin' => 'background:#fef2f2;color:#991b1b;border-color:#fecaca;',
                        'admin' => 'background:#eff6ff;color:#1e40af;border-color:#bfdbfe;',
                        'faculty_admin' => 'background:#f0fdf4;color:#166534;border-color:#bbf7d0;',
                        'editor' => 'background:#faf5ff;color:#6b21a8;border-color:#e9d5ff;',
                    ];
                    $badgeStyle = $roleBadgeColors[$topbarRole] ?? 'background:#f3f4f6;color:#374151;border-color:#e5e7eb;';
                    ?>
                    <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; font-size: 0.6875rem; font-weight: 600; border-radius: 9999px; border: 1px solid; <?= $badgeStyle ?>"><?= esc(ucfirst($topbarRole)) ?></span>
                    <div class="topbar-user">
                        <span><?= esc(session()->get('admin_name') ?? 'Admin') ?></span>
                        <a href="<?= base_url('admin/logout') ?>" aria-label="ออกจากระบบ (รวมทุกแอป)">Logout</a>
                    </div>
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
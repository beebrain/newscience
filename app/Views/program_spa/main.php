<?php $id = (int) ($id ?? 0); $dataUrl = base_url('p/' . $id . '/data'); ?>
<!DOCTYPE html>
<html class="light scroll-smooth" lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หลักสูตร | รายละเอียดหลักสูตร</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>?v=<?= (defined('FCPATH') && is_file(FCPATH . 'assets/css/fonts.css')) ? filemtime(FCPATH . 'assets/css/fonts.css') : '1' ?>">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#002D72',
                        'primary-hover': '#001f52',
                        gold: '#C5941A',
                        'gold-hover': '#a87d15',
                        background: '#FAFAFA',
                        surface: '#FFFFFF',
                        'surface-alt': '#F5F5F5',
                        'on-background': '#1A1A1A',
                        'on-surface-variant': '#4A4A4A',
                        'outline-variant': '#E0E0E0',
                        'red-accent': '#C8102E'
                    },
                    fontFamily: {
                        sarabun: ['Sarabun', 'Noto Sans Thai', 'sans-serif']
                    },
                    boxShadow: { elegant: '0 10px 40px -10px rgba(0,0,0,0.04)' }
                }
            }
        };
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        body { font-family: 'Sarabun', 'Noto Sans Thai', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Sarabun', 'Noto Sans Thai', sans-serif; }
        .section-rule { width: 3rem; height: 2px; background: #C5941A; }
        .year-tab.is-active { border-color: #C8102E; color: #C8102E; background: #fff; }
        .sem-tab.is-active { background: #C8102E; color: #fff; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.08); }
        #loading { transition: opacity 0.3s ease; }
    </style>
</head>
<body class="bg-background text-on-background font-sarabun antialiased pt-24">

<div id="loading" class="fixed inset-0 z-[70] flex items-center justify-center bg-background">
    <div class="text-center px-6">
        <div class="mx-auto h-12 w-12 rounded-full border-2 border-gold border-t-transparent animate-spin" role="status" aria-live="polite"></div>
    </div>
</div>

<nav class="fixed top-0 w-full z-50 bg-surface/90 backdrop-blur-md border-b border-gold/20 shadow-sm transition-all duration-300 ease-in-out">
    <div class="flex items-center justify-between px-8 py-5 max-w-[1280px] mx-auto">
        <a href="#hero" id="nav-brand" class="text-2xl font-bold tracking-tight text-primary truncate max-w-[12rem] sm:max-w-none">หลักสูตร</a>
        <button id="nav-toggle" class="md:hidden text-primary inline-flex items-center justify-center" type="button" aria-label="เปิดเมนู">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
        <div id="nav-desktop" class="hidden md:flex items-center gap-8 lg:gap-10 text-sm font-semibold"></div>
    </div>
    <div id="nav-mobile" class="hidden md:hidden border-t border-outline-variant/40 bg-surface px-8 py-5 space-y-4 text-sm"></div>
</nav>

<main id="app" class="hidden">
    <header id="hero" class="relative w-full h-[85vh] min-h-[650px] flex items-center justify-center overflow-hidden bg-primary text-white">
        <div class="absolute inset-0 z-0">
            <div id="hero-bg" class="absolute inset-0 z-0 w-full h-full bg-cover bg-center bg-no-repeat opacity-[0.42] mix-blend-overlay pointer-events-none hidden" role="presentation"></div>
            <div class="absolute inset-0 z-[1] bg-gradient-to-b from-primary/78 to-primary/95 pointer-events-none" aria-hidden="true"></div>
        </div>
        <div class="relative z-10 max-w-[1280px] mx-auto px-8 text-center flex flex-col items-center">
            <div id="hero-level-wrap" class="inline-flex items-center gap-2 px-5 py-2 rounded-sm border border-gold/50 bg-white/5 backdrop-blur-sm mb-8 hidden">
                <svg class="w-5 h-5 text-gold shrink-0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                <span id="hero-level" class="text-xs tracking-wide text-gold font-semibold"></span>
            </div>
            <p id="hero-name-th" class="text-sm md:text-base text-white/75 font-semibold mb-4"></p>
            <h1 id="hero-title" class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 max-w-5xl mx-auto leading-tight"></h1>
            <p id="hero-description" class="text-lg md:text-xl text-white/85 max-w-3xl mx-auto mb-12 font-light leading-relaxed hidden"></p>
            <div id="hero-actions" class="flex flex-col sm:flex-row justify-center gap-6"></div>
        </div>
    </header>

    <section id="about" class="py-32 bg-white w-full">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-16 items-start">
                <div id="about-philosophy-col" class="md:col-span-5 md:sticky md:top-32">
                    <h2 id="about-philosophy-heading" class="text-4xl font-bold text-primary mb-8 leading-tight hidden">ปรัชญาและวัตถุประสงค์</h2>
                    <div id="about-philosophy-rule" class="section-rule mb-8 hidden"></div>
                    <p id="philosophy-text" class="text-lg text-on-surface-variant leading-relaxed mb-6 font-light hidden"></p>
                </div>
                <div id="objective-cards" class="md:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-8"></div>
            </div>
        </div>
    </section>

    <section id="plo-elo" class="py-28 md:py-32 bg-white w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-primary mb-6">มาตรฐานการเรียนรู้ &amp; PLO / ELO</h2>
                <div class="section-rule mx-auto mb-6"></div>
                <p class="text-on-surface-variant text-sm md:text-base max-w-3xl mx-auto font-light">Programme Learning Outcomes (PLO/ELOs) และมาตรฐานการเรียนรู้ — OBE / AUN-QA</p>
            </div>
            <div id="plo-elo-empty" class="hidden"></div>
            <div id="plo-elo-body" class="space-y-16">
                <div id="plo-intro-wrap" class="hidden max-w-4xl mx-auto text-on-surface-variant leading-relaxed text-lg font-light border border-outline-variant/40 rounded-sm bg-surface p-8 md:p-10 shadow-elegant"></div>
                <div id="ls-section" class="hidden">
                    <h3 class="text-2xl font-bold text-primary text-center mb-10">มาตรฐานการเรียนรู้ (Learning Standards)</h3>
                    <div id="ls-grid" class="grid grid-cols-1 md:grid-cols-2 gap-8"></div>
                </div>
                <div id="mapping-section" class="hidden">
                    <h3 class="text-2xl font-bold text-primary text-center mb-8">ความเชื่อมโยงมาตรฐานการเรียนรู้ – PLO</h3>
                    <div class="rounded-sm border border-outline-variant/40 bg-surface shadow-elegant overflow-x-auto">
                        <div id="mapping-table-host"></div>
                    </div>
                </div>
                <div id="elo-section" class="hidden">
                    <h3 id="elo-heading" class="text-2xl font-bold text-primary text-center mb-10">PLO / ผลลัพธ์การเรียนรู้ระดับหลักสูตร (Programme Learning Outcomes)</h3>
                    <div id="elo-grid" class="grid grid-cols-1 md:grid-cols-2 gap-8"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="faculty" class="py-24 bg-[#f8fafc] w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">คณาจารย์และบุคลากร</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div id="faculty-empty" class="hidden"></div>
            <div id="faculty-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8"></div>
        </div>
    </section>

    <section id="graduate-journey" class="py-24 bg-[#eff6ff] w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">โปรไฟล์ผู้สำเร็จการศึกษาระหว่างหลักสูตร</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div id="graduate-empty" class="hidden"></div>
            <div id="graduate-grid" class="grid grid-cols-1 md:grid-cols-4 gap-6"></div>
        </div>
    </section>

    <section id="curriculum" class="py-32 bg-white w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">โครงสร้างหลักสูตรและรายวิชา</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div class="bg-surface rounded-sm shadow-elegant border border-outline-variant/40 overflow-hidden">
                <div id="curriculum-empty" class="hidden p-12 md:p-16"></div>
                <div id="curriculum-loaded">
                    <div id="year-tabs" class="flex flex-wrap border-b border-outline-variant/30 bg-surface-alt"></div>
                    <div id="year-panels" class="p-8 md:p-12"></div>
                </div>
            </div>
        </div>
    </section>

    <section id="teaching-methods" class="py-32 bg-[#f8fafc] w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">รูปแบบการเรียนการสอน</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div id="teaching-empty" class="hidden"></div>
            <div id="teaching-grid" class="grid grid-cols-1 md:grid-cols-3 gap-10"></div>
        </div>
    </section>

    <section id="graduation" class="bg-[#eff6ff] py-32 w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">เกณฑ์การจบการศึกษา</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div class="bg-surface p-12 rounded-sm shadow-elegant border border-outline-variant/40 max-w-4xl mx-auto">
                <div id="graduation-empty" class="hidden"></div>
                <ul id="graduation-list" class="space-y-10"></ul>
            </div>
        </div>
    </section>

    <section id="alumni" class="py-28 md:py-32 bg-[#f8fafc] w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-20">
                <h2 class="text-4xl font-bold text-primary mb-6">ข้อความจากศิษย์เก่า</h2>
                <div class="section-rule mx-auto mb-6"></div>
            </div>
            <div id="alumni-empty" class="hidden"></div>
            <div id="alumni-grid" class="grid grid-cols-1 lg:grid-cols-2 gap-10"></div>
        </div>
    </section>

    <section id="learning-supports" class="bg-white py-32 w-full border-t border-outline-variant/30">
        <div class="max-w-[1280px] mx-auto px-8">
            <div id="learning-supports-head" class="text-center mb-20">
                <h2 id="learning-supports-title" class="text-4xl font-bold text-primary mb-6">สิ่งสนับสนุนการเรียนการสอน</h2>
                <div id="learning-supports-rule" class="section-rule mx-auto mb-6"></div>
            </div>
            <div id="admission-info-block" class="max-w-4xl mx-auto mb-12 text-on-surface-variant leading-relaxed hidden"></div>
            <div id="support-empty" class="hidden"></div>
            <div id="support-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10"></div>
        </div>
    </section>

    <section id="assessment" class="bg-primary text-white py-32 w-full">
        <div class="max-w-[1280px] mx-auto px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-white mb-6">การวัดและประเมินผล</h2>
                <div class="section-rule mx-auto mb-6"></div>
                <div id="assessment-empty" class="hidden"></div>
                <div id="assessment-loaded" class="hidden">
                    <p id="assessment-text" class="text-lg text-white/85 max-w-3xl mx-auto font-light leading-relaxed"></p>
                </div>
            </div>
        </div>
    </section>

    <?php
    $footer_phone           = $footer_phone ?? '';
    $footer_email           = $footer_email ?? '';
    $footer_fax             = $footer_fax ?? '';
    $footer_address_th      = $footer_address_th ?? '';
    $footer_facebook        = $footer_facebook ?? '';
    $footer_website         = $footer_website ?? '';
    $footer_site_name       = $footer_site_name ?? '';
    $footer_university_name = $footer_university_name ?? '';
    $footer_contact_page_url = $footer_contact_page_url ?? base_url('contact');
    $footer_telHref         = ($footer_phone !== '' && preg_replace('/[^\d+]/', '', $footer_phone) !== '')
        ? 'tel:' . preg_replace('/[^\d+]/', '', $footer_phone)
        : '';
    ?>
    <footer id="contact" class="border-t border-gold/25 bg-[#00214a] text-white w-full mt-0">
        <div class="max-w-[1280px] mx-auto px-8 py-16 md:py-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                <div class="lg:col-span-6">
                    <h2 class="text-xl font-bold text-gold mb-2 tracking-wide">การติดต่อหลักสูตร</h2>
                    <div class="section-rule mb-6"></div>
                    <div id="footer-program-contact" class="text-base leading-relaxed text-white/90 [&_a]:text-gold [&_a]:underline hover:[&_a]:opacity-90"></div>
                </div>
                <div class="lg:col-span-6">
                    <h2 class="text-xl font-bold text-gold mb-2 tracking-wide">ติดต่อคณะ / มหาวิทยาลัย</h2>
                    <div class="section-rule mb-6"></div>
                    <div class="space-y-5 text-white/85 text-base leading-relaxed">
                        <?php if ($footer_address_th !== '') : ?>
                            <p class="flex gap-4">
                                <span class="shrink-0 text-gold font-semibold w-28">ที่อยู่</span>
                                <span><?= nl2br(esc($footer_address_th)) ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if ($footer_phone !== '') : ?>
                            <p class="flex gap-4 items-start">
                                <span class="shrink-0 text-gold font-semibold w-28">โทรศัพท์</span>
                                <?php if ($footer_telHref !== '') : ?>
                                    <span><a class="text-gold underline underline-offset-2 hover:opacity-90" href="<?= esc($footer_telHref, 'attr') ?>"><?= esc($footer_phone) ?></a></span>
                                <?php else : ?>
                                    <span><?= esc($footer_phone) ?></span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if ($footer_fax !== '') : ?>
                            <p class="flex gap-4">
                                <span class="shrink-0 text-gold font-semibold w-28">โทรสาร</span>
                                <span><?= esc($footer_fax) ?></span>
                            </p>
                        <?php endif; ?>
                        <?php if ($footer_email !== '') : ?>
                            <p class="flex gap-4 items-start">
                                <span class="shrink-0 text-gold font-semibold w-28">อีเมล</span>
                                <a class="text-gold underline underline-offset-2 hover:opacity-90 break-all" href="mailto:<?= esc($footer_email, 'attr') ?>"><?= esc($footer_email) ?></a>
                            </p>
                        <?php endif; ?>
                        <p class="flex flex-wrap gap-3 pt-2">
                            <a href="<?= esc($footer_contact_page_url, 'attr') ?>" class="inline-flex items-center px-4 py-2 rounded-sm bg-gold text-white text-sm font-semibold hover:bg-gold-hover">หน้าติดต่อคณะ</a>
                            <?php if ($footer_website !== '') : ?>
                                <a href="<?= esc($footer_website, 'attr') ?>" class="inline-flex items-center px-4 py-2 rounded-sm border border-white/30 text-sm hover:bg-white/10" target="_blank" rel="noopener noreferrer">เว็บไซต์คณะ</a>
                            <?php endif; ?>
                            <?php if ($footer_facebook !== '') : ?>
                                <a href="<?= esc($footer_facebook, 'attr') ?>" class="inline-flex items-center px-4 py-2 rounded-sm border border-white/30 text-sm hover:bg-white/10" target="_blank" rel="noopener noreferrer">Facebook</a>
                            <?php endif; ?>
                        </p>
                    </div>
                    <?php if ($footer_phone === '' && $footer_email === '' && $footer_address_th === '' && $footer_fax === '') : ?>
                        <p class="mt-6 text-sm text-white/55">ยังไม่ได้ตั้งค่าที่อยู่ เบอร์โทร หรืออีเมลในเมนูตั้งค่าเว็บไซต์ — ผู้เข้าชมสามารถใช้ลิงก์ &quot;หน้าติดต่อคณะ&quot;</p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="border-t border-white/10 mt-14 pt-8 text-center text-sm text-white/60">
                <p>
                    © พ.ศ. <?= (int) date('Y') + 543 ?>
                    <?= $footer_site_name !== '' ? ' · ' . esc($footer_site_name) : '' ?><?php if ($footer_university_name !== '') : ?> · <?= esc($footer_university_name) ?><?php endif; ?>
                </p>
            </div>
        </div>
    </footer>
</main>

<script>
(function () {
    var dataUrl = <?= json_encode($dataUrl) ?>;

    var SVG_OPEN = '<svg xmlns="http://www.w3.org/2000/svg" class="%CLASS%" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">';
    function svg(paths, cls) {
        cls = cls || 'w-8 h-8';
        var p = SVG_OPEN.replace('%CLASS%', cls) + paths + '</svg>';
        return p;
    }
    var ICON_PATHS = {
        objectiveBulb: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17h6M10 21h4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.94 1.6 4.5 5.2 4 9a7 7 0 01-14 1c-.5-3.8 1.06-7.4 4-9z"/>',
        objectiveSettings: '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>',
        objectiveAnalyze: '<path stroke-linecap="round" stroke-linejoin="round" d="M4 18V6"/><path stroke-linecap="round" stroke-linejoin="round" d="M11 18V11"/><path stroke-linecap="round" stroke-linejoin="round" d="M18 18v-7"/><circle cx="4" cy="6" r="2"/><circle cx="11" cy="11" r="2"/><circle cx="18" cy="17" r="2"/>',
        objectiveEthics: '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>',
        person: '<path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>',
        quotes: '<path stroke-linecap="round" stroke-linejoin="round" d="M7.867 15.867L6 21l4.867-3.867M18 21H6a4 4 0 01-4-4V9a4 4 0 014-4h12a4 4 0 014 4v8a4 4 0 01-4 4z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 13h8"/>',
        journey1: '<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m-.001-4a3 3 0 013-3h.008a3 3 0 013 3v12a9 9 0 01-9 9m11-12a9 9 0 00-11-11"/>',
        journey2: '<ellipse cx="12" cy="5" rx="9" ry="3"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>',
        journey3: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 12v10h14V12"/>',
        journey4: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v4h6v-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M14 21h-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 16c-5 0-8-5-9-11h18c-1 6-4 11-9 11z"/>',
        study: '<path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-7.058A4 4 0 0114 6H10a4 4 0 00-4 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 17H4l2-11h14"/>',
        lab: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 01-9-9c2.5 0 4 2 9 9 5-7 6.5-9 9-9a9 9 0 01-9 9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18"/>',
        cloud: '<path stroke-linecap="round" stroke-linejoin="round" d="M6 17h13a5 5 0 004-8 5 5 0 00-9.5-.5 4 4 0 00-8 3 4 4 0 001 9z"/>',
        check: '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>',
        grade: '<path stroke-linecap="round" stroke-linejoin="round" d="M14 14l7-8M8 21l11-13"/><path stroke-linecap="round" stroke-linejoin="round" d="M5 21V7l14-4v13"/>',
        work: '<rect x="2" y="7" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M16 7V5a2 2 0 10-8 0v2"/>',
        verified: '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 21s8-4 8-10V8l-8-5-8 5v3c0 6 8 10 8 10z"/>',
        tuition: '<rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/>',
        wallet: '<rect x="3" y="6" width="18" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 11h14a2 2 0 012 2v8"/>',
        loan: '<path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V9l-8-7-8 7v3c0 6 8 10 8 10z"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 11h6"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v8"/>',
        card: '<rect x="2" y="5" width="20" height="14" rx="2"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 10h12"/>',
        rocket: '<path stroke-linecap="round" stroke-linejoin="round" d="M14 21l7-14-7 4"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 7L7 21l2-10"/>',
        building: '<rect x="5" y="3" width="14" height="18" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" d="M9 21V9h2v12M13 21V12h2v9"/><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18"/>'
    };

    function esc(v) {
        var d = document.createElement('div');
        d.textContent = v == null ? '' : String(v);
        return d.innerHTML;
    }
    function text(v) { return v == null ? '' : String(v).trim(); }
    function firstText() { for (var i = 0; i < arguments.length; i++) { var v = text(arguments[i]); if (v) return v; } return ''; }
    function nonEmpty(a) { return (Array.isArray(a) ? a : []).map(text).filter(Boolean); }
    function listFromString(v) {
        var s = text(v);
        if (!s) return [];
        if (s.charAt(0) === '[') {
            try { var p = JSON.parse(s); if (Array.isArray(p)) return nonEmpty(p); } catch (e) {}
        }
        return nonEmpty(s.replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]+>/g, '').split(/\r\n|\n|\r/));
    }
    function rich(v) {
        var s = text(v);
        return /<[^>]+>/.test(s) ? s : esc(s).replace(/\n/g, '<br>');
    }
    function svgIcon(kind, cls) {
        var p = ICON_PATHS[kind] || ICON_PATHS.check;
        return svg(p, cls);
    }

    function iconCardBodyOnly(iconKey, bodyText) {
        return '<div class="bg-surface p-10 rounded-sm shadow-elegant border border-outline-variant/40 hover:border-gold/30 transition-all duration-300">' +
            '<div class="text-gold mb-6">' + svgIcon(iconKey, 'w-10 h-10') + '</div>' +
            '<div class="text-on-surface-variant leading-relaxed font-light">' + rich(bodyText) + '</div></div>';
    }
    function textCardCentered(iconKey, bodyText) {
        return '<div class="bg-surface p-12 rounded-sm shadow-elegant border border-outline-variant/40 text-center hover:border-gold/30 transition-all duration-300">' +
            '<div class="w-16 h-16 rounded-full bg-surface-alt flex items-center justify-center mx-auto mb-6 border border-gold/20 text-primary">' +
            svgIcon(iconKey, 'w-8 h-8') +
            '</div><div class="text-on-surface-variant leading-relaxed">' + rich(bodyText) + '</div></div>';
    }
    function supportTitleCard(iconKey, titleOnly) {
        return '<div class="bg-surface p-10 rounded-sm shadow-elegant border border-outline-variant/40 hover:border-gold/30 transition-all duration-300 flex flex-col items-center text-center">' +
            '<div class="w-20 h-20 rounded-full bg-surface-alt flex items-center justify-center mb-6 border border-gold/20 text-primary">' +
            svgIcon(iconKey, 'w-9 h-9') +
            '</div><h3 class="text-xl font-bold text-primary">' + esc(titleOnly) + '</h3></div>';
    }

    function showEl(id, on) {
        var el = document.getElementById(id);
        if (!el) return;
        el.classList.toggle('hidden', !on);
    }

    var LABEL_NO_DATA = 'ยังไม่มีข้อมูล';
    function badgeNoData(variant) {
        var light = variant === 'light';
        var boxClass = light
            ? 'border border-white/35 bg-white/10 text-white/95'
            : 'border border-outline-variant bg-surface-alt text-on-surface-variant shadow-elegant';
        return '<div class="flex justify-center py-14 px-6"><span class="inline-flex items-center rounded-sm px-8 py-4 text-lg font-semibold ' +
            boxClass + '">' + esc(LABEL_NO_DATA) + '</span></div>';
    }

    function renderNavLinks() {
        var spec = [
            ['#about', 'ปรัชญาและวัตถุประสงค์'],
            ['#plo-elo', 'มาตรฐานการเรียนรู้ & PLO/ELO'],
            ['#curriculum', 'โครงสร้างหลักสูตรและรายวิชา'],
            ['#faculty', 'คณาจารย์และบุคลากร'],
            ['#graduate-journey', 'โปรไฟล์ผู้สำเร็จการศึกษาระหว่างหลักสูตร'],
            ['#teaching-methods', 'รูปแบบการเรียนการสอน'],
            ['#graduation', 'เกณฑ์การจบการศึกษา'],
            ['#alumni', 'ข้อความจากศิษย์เก่า'],
            ['#learning-supports', 'การรับสมัครและสิ่งสนับสนุน'],
            ['#assessment', 'การวัดและประเมินผล'],
            ['#contact', 'การติดต่อ']
        ];
        var desk = document.getElementById('nav-desktop');
        var mob = document.getElementById('nav-mobile');
        var deskHtml = '';
        var mobHtml = '';
        for (var i = 0; i < spec.length; i++) {
            var tgt = document.querySelector(spec[i][0]);
            if (!tgt || tgt.classList.contains('hidden')) continue;
            var href = spec[i][0];
            var lab = esc(spec[i][1]);
            deskHtml += '<a class="text-on-surface-variant hover:text-gold transition-colors" href="' + href + '">' + lab + '</a>';
            mobHtml += '<a class="block text-on-surface-variant hover:text-gold transition-colors" href="' + href + '">' + lab + '</a>';
        }
        desk.innerHTML = deskHtml;
        mob.innerHTML = mobHtml;
    }

    function renderHero(d) {
        var title = firstText(d.name_th, d.name_en);
        document.title = (title ? title + ' | ' : '') + 'รายละเอียดหลักสูตร';
        document.getElementById('nav-brand').textContent = title || 'หลักสูตร';
        document.getElementById('hero-title').textContent = title;
        document.getElementById('hero-name-th').textContent = text(d.name_th) && text(d.name_th) !== title ? text(d.name_th) : '';

        var desc = firstText(d.description_en, d.description);
        var heroDescEl = document.getElementById('hero-description');
        if (desc) {
            heroDescEl.textContent = desc;
            heroDescEl.classList.remove('hidden');
        } else {
            heroDescEl.textContent = '';
            heroDescEl.classList.add('hidden');
        }

        var lvl = firstText(d.degree_th, d.degree_en, d.level);
        var lvlWrap = document.getElementById('hero-level-wrap');
        if (lvl) {
            document.getElementById('hero-level').textContent = lvl;
            lvlWrap.classList.remove('hidden');
        } else {
            lvlWrap.classList.add('hidden');
        }

        var bgEl = document.getElementById('hero-bg');
        var hi = text(d.hero_image || '');
        if (bgEl) {
            if (hi !== '') {
                bgEl.style.backgroundImage = 'url(' + JSON.stringify(hi) + ')';
                bgEl.classList.remove('hidden');
            } else {
                bgEl.style.backgroundImage = '';
                bgEl.classList.add('hidden');
            }
        }

        document.getElementById('hero-actions').innerHTML = '';
    }

    function updateHeroAnchors() {
        var parts = [];
        function add(sel, txt) {
            var el = document.querySelector(sel);
            if (!el || el.classList.contains('hidden')) return;
            parts.push('<a class="bg-gold text-white px-10 py-4 rounded-sm text-sm hover:bg-gold-hover transition-colors shadow-lg font-semibold" href="' +
                sel + '">' + esc(txt) + '</a>');
        }
        function addGhost(sel, txt) {
            var el = document.querySelector(sel);
            if (!el || el.classList.contains('hidden')) return;
            parts.push('<a class="bg-transparent border border-white/35 text-white px-10 py-4 rounded-sm text-sm hover:bg-white/10 transition-colors font-semibold" href="' +
                sel + '">' + esc(txt) + '</a>');
        }
        add('#curriculum', 'โครงสร้างหลักสูตรและรายวิชา');
        addGhost('#plo-elo', 'มาตรฐาน & PLO/ELO');
        addGhost('#about', 'ปรัชญาและวัตถุประสงค์');
        addGhost('#learning-supports', 'การรับสมัครและสิ่งสนับสนุน');
        addGhost('#contact', 'การติดต่อ');

        var wurl = (dRef && text(dRef.website)) ? String(dRef.website).trim() : '';
        if (wurl !== '') {
            var hrefEsc = wurl.replace(/&/g, '&amp;').replace(/"/g, '&quot;');
            parts.push('<a class="bg-transparent border border-white/30 text-white px-10 py-4 rounded-sm text-sm hover:bg-white/10 transition-colors font-semibold" href="' +
                hrefEsc + '" target="_blank" rel="noopener noreferrer">' +
                esc(wurl) + '</a>');
        }

        document.getElementById('hero-actions').innerHTML = parts.join('');
    }

    function graduatePairs(d) {
        var p = nonEmpty(d.graduate_profile_list);
        if (!p.length) p = listFromString(d.graduate_profile);
        var out = [];
        for (var i = 0; i + 1 < p.length; i += 2) {
            out.push({ headline: text(p[i]), detail: text(p[i + 1]) });
        }
        return out;
    }

    var dRef = null;

    function renderAbout(d) {
        var phi = text(d.philosophy);
        showEl('about-philosophy-heading', !!phi);
        showEl('about-philosophy-rule', !!phi);
        var phEl = document.getElementById('philosophy-text');
        if (phi) {
            phEl.innerHTML = rich(phi);
            phEl.classList.remove('hidden');
        } else {
            phEl.innerHTML = '';
            phEl.classList.add('hidden');
        }

        var objSource = nonEmpty(d.objectives_list);
        if (!objSource.length) objSource = listFromString(d.vision);
        var iconKeys = ['objectiveBulb', 'objectiveSettings', 'objectiveAnalyze', 'objectiveEthics'];
        var cards = [];
        objSource.forEach(function (item) {
            if (!text(item)) return;
            cards.push(iconCardBodyOnly(iconKeys[cards.length % iconKeys.length], item));
        });
        document.getElementById('objective-cards').innerHTML = cards.join('');

        var showAbout = !!phi || cards.length > 0;
        showEl('about', showAbout);
    }

    function renderFaculty(d) {
        var grid = document.getElementById('faculty-grid');
        var emp = document.getElementById('faculty-empty');
        var staff = (Array.isArray(d.staff) ? d.staff : []).filter(function (s) { return text(s.name); });
        if (!staff.length) {
            grid.innerHTML = '';
            grid.classList.add('hidden');
            emp.innerHTML = badgeNoData();
            emp.classList.remove('hidden');
            return;
        }
        emp.innerHTML = '';
        emp.classList.add('hidden');
        grid.classList.remove('hidden');
        grid.innerHTML = staff.slice(0, 48).map(function (s) {
            var avatar = s.image
                ? '<img src="' + esc(s.image) + '" alt="" class="w-36 h-36 rounded-full object-cover mb-6 border border-gold/20">'
                : '<div class="w-36 h-36 rounded-full bg-surface-alt flex items-center justify-center mb-6 border border-gold/20 text-primary">' + svgIcon('person', 'w-[3.75rem] h-[3.75rem]') + '</div>';
            var roleTxt = firstText(s.role, s.position);
            var roleHtml = roleTxt ? '<p class="text-sm text-gold">' + esc(roleTxt) + '</p>' : '';
            var body =
                '<div class="bg-surface p-10 rounded-sm shadow-elegant border border-outline-variant/40 flex flex-col items-center text-center hover:-translate-y-1 transition-transform duration-300">' +
                avatar +
                '<h3 class="text-lg font-semibold text-primary mb-2">' + esc(s.name) + '</h3>' +
                roleHtml + '</div>';
            return s.cv_url ? '<a href="' + esc(s.cv_url) + '" target="_blank" rel="noopener">' + body + '</a>' : body;
        }).join('');
    }

    function renderJourney(d) {
        var emp = document.getElementById('graduate-empty');
        var gridEl = document.getElementById('graduate-grid');
        var pairs = graduatePairs(d);
        if (!pairs.length) {
            gridEl.innerHTML = '';
            gridEl.classList.add('hidden');
            emp.innerHTML = badgeNoData();
            emp.classList.remove('hidden');
            return;
        }
        emp.innerHTML = '';
        emp.classList.add('hidden');
        gridEl.classList.remove('hidden');
        var jIcons = ['journey1', 'journey2', 'journey3', 'journey4'];
        gridEl.innerHTML = pairs.map(function (pair, i) {
            var raw = pair.headline;
            var yearLabel = '';
            var mYear = /^ชั้นปีที่\s*\d+/i.exec(raw);
            if (mYear) yearLabel = mYear[0].trim();
            var titleRest = raw.replace(/^ชั้นปีที่\s*\d+\s*/i, '').trim() || raw;
            return '<div class="relative bg-surface p-10 rounded-sm shadow-elegant border border-outline-variant/40 flex flex-col items-center text-center">' +
                '<div class="w-20 h-20 rounded-full bg-surface-alt border border-outline-variant text-primary flex items-center justify-center mb-6">' +
                svgIcon(jIcons[i % jIcons.length], 'w-9 h-9') + '</div>' +
                (yearLabel ? '<span class="text-xs text-gold mb-3 font-semibold">' + esc(yearLabel) + '</span>' : '') +
                '<h3 class="text-xl font-bold text-primary mb-4">' + esc(titleRest) + '</h3>' +
                '<p class="text-sm text-on-surface-variant leading-relaxed font-light">' + esc(pair.detail) + '</p></div>';
        }).join('');
    }

    function curriculumValid(d) {
        return Array.isArray(d.curriculum) && d.curriculum.length > 0;
    }

    function renderCurriculum(d) {
        var emptyEl = document.getElementById('curriculum-empty');
        var wrap = document.getElementById('curriculum-loaded');
        if (!curriculumValid(d)) {
            emptyEl.innerHTML = badgeNoData();
            emptyEl.classList.remove('hidden');
            wrap.classList.add('hidden');
            document.getElementById('year-tabs').innerHTML = '';
            document.getElementById('year-panels').innerHTML = '';
            return;
        }
        emptyEl.innerHTML = '';
        emptyEl.classList.add('hidden');
        wrap.classList.remove('hidden');
        var plan = d.curriculum;
        document.getElementById('year-tabs').innerHTML = plan.map(function (y, i) {
            return '<button type="button" class="year-tab flex-1 py-6 px-6 text-sm font-semibold text-center border-b-2 transition-all ' +
                (i === 0 ? 'is-active' : 'border-transparent text-on-surface-variant hover:text-red-accent') +
                '" data-year-index="' + i + '">' +
                esc(y.title || ('ปีที่ ' + (y.year || i + 1))) + '</button>';
        }).join('');
        document.getElementById('year-panels').innerHTML = plan.map(function (y, yi) {
            var sems = Array.isArray(y.semesters) && y.semesters.length ? y.semesters : [{ name: '', courses: [] }];
            var tabs = sems.map(function (s, si) {
                var semNm = text(s.name) || ('ภาคการศึกษาที่ ' + (si + 1));
                return '<button type="button" class="sem-tab sem-tab-' + yi + ' px-8 py-3 rounded-sm text-xs font-semibold transition-all ' +
                    (si === 0 ? 'is-active' : 'bg-surface-alt text-on-surface-variant hover:bg-red-accent/5') +
                    '" data-year-index="' + yi + '" data-sem-index="' + si + '">' +
                    esc(semNm) + '</button>';
            }).join('');
            var panels = sems.map(function (s, si) {
                var courses = Array.isArray(s.courses) ? s.courses : [];
                var rows = courses.length ? courses.map(function (c, ci) {
                    return '<tr class="' + (ci < courses.length - 1 ? 'border-b border-outline-variant/20' : '') +
                        ' hover:bg-surface-alt/50 transition-colors"><td class="py-6 text-red-accent font-semibold pr-4">' +
                        esc(c.code || '') + '</td><td class="py-6">' +
                        esc(c.name || c.title || '') + '</td><td class="py-6 text-right text-on-surface-variant">' +
                        esc(c.credits != null && c.credits !== '' ? c.credits : '—') + '</td></tr>';
                }).join('') : '<tr><td colspan="3" class="py-8 text-center text-on-surface-variant">—</td></tr>';
                return '<div class="sem-panel sem-panel-' + yi + ' ' + (si === 0 ? 'block' : 'hidden') +
                    '" data-year-index="' + yi + '" data-sem-index="' + si + '">' +
                    '<div class="overflow-x-auto"><table class="w-full text-left">' +
                    '<thead><tr class="border-b border-outline-variant/30 text-xs text-on-surface-variant">' +
                    '<th class="pb-6 font-semibold">รหัสวิชา</th><th class="pb-6 font-semibold">ชื่อรายวิชา</th>' +
                    '<th class="pb-6 font-semibold text-right">หน่วยกิจ</th></tr></thead>' +
                    '<tbody class="text-base font-light">' + rows + '</tbody></table></div></div>';
            }).join('');
            return '<div class="year-panel ' + (yi === 0 ? 'block' : 'hidden') +
                '" data-year-index="' + yi + '"><div class="flex flex-wrap gap-4 mb-10 border-b border-outline-variant/30 pb-6">' +
                tabs + '</div>' + panels + '</div>';
        }).join('');
    }

    function renderTeaching(d) {
        var m = listFromString(d.teaching_methods);
        var grid = document.getElementById('teaching-grid');
        var emp = document.getElementById('teaching-empty');
        if (!m.length) {
            grid.innerHTML = '';
            grid.classList.add('hidden');
            emp.innerHTML = badgeNoData();
            emp.classList.remove('hidden');
            return;
        }
        emp.innerHTML = '';
        emp.classList.add('hidden');
        grid.classList.remove('hidden');
        var deco = ['study', 'lab', 'cloud', 'study', 'lab', 'cloud'];
        grid.innerHTML = m.map(function (line, i) {
            return textCardCentered(deco[i % deco.length], line);
        }).join('');
    }

    function renderGraduation(d) {
        var items = listFromString(d.graduation_requirements);
        var emp = document.getElementById('graduation-empty');
        var ul = document.getElementById('graduation-list');
        if (!items.length) {
            ul.innerHTML = '';
            ul.classList.add('hidden');
            emp.innerHTML = badgeNoData();
            emp.classList.remove('hidden');
            return;
        }
        emp.innerHTML = '';
        emp.classList.add('hidden');
        ul.classList.remove('hidden');
        var iconKeys = ['check', 'grade', 'work', 'verified'];
        ul.innerHTML = items.slice(0, 50).map(function (item, i) {
            var ik = iconKeys[i % iconKeys.length];
            return '<li class="flex items-start gap-6">' +
                '<div class="w-10 h-10 rounded-full bg-surface-alt flex items-center justify-center shrink-0 border border-gold/20 text-gold">' +
                svgIcon(ik, 'w-6 h-6') +
                '</div><div class="text-on-surface-variant leading-relaxed font-light">' +
                rich(item) + '</div></li>';
        }).join('');
    }

    function renderAlumni(d) {
        var grid = document.getElementById('alumni-grid');
        var emp = document.getElementById('alumni-empty');
        if (!grid || !emp) return;
        var raw = Array.isArray(d.alumni) ? d.alumni : [];
        var list = raw.filter(function (a) {
            if (!a) return false;
            return !!(text(a.message) || text(a.position) || text(a.workplace) || text(a.graduation_year) || text(a.photo_url));
        });
        if (!list.length) {
            grid.innerHTML = '';
            grid.classList.add('hidden');
            emp.innerHTML = badgeNoData();
            emp.classList.remove('hidden');
            return;
        }
        emp.innerHTML = '';
        emp.classList.add('hidden');
        grid.classList.remove('hidden');
        grid.innerHTML = list.map(function (a) {
            var photo = text(a.photo_url);
            var avatar = photo
                ? '<img src="' + esc(photo) + '" alt="" class="w-24 h-24 rounded-full object-cover shrink-0 border-2 border-gold/35 shadow-md">'
                : '<div class="w-24 h-24 rounded-full bg-surface-alt flex items-center justify-center shrink-0 border-2 border-gold/25 text-primary">' +
                svgIcon('person', 'w-11 h-11') + '</div>';
            var msgInner = rich(a.message || '');
            var metaParts = [];
            if (text(a.position)) metaParts.push(esc(a.position));
            if (text(a.workplace)) metaParts.push(esc(a.workplace));
            if (text(a.graduation_year)) metaParts.push(esc(a.graduation_year));
            var metaLine = metaParts.join(' · ');
            var msgBlock = msgInner
                ? '<div class="text-on-surface-variant leading-relaxed font-light text-base [&_p]:mb-3 last:[&_p]:mb-0">' + msgInner + '</div>'
                : '';
            return '<article class="relative bg-surface p-8 md:p-10 rounded-sm shadow-elegant border border-outline-variant/40 border-t-[3px] border-t-gold hover:border-outline-variant hover:shadow-[0_12px_40px_-12px_rgba(0,45,114,0.12)] transition-all duration-300 flex flex-col sm:flex-row gap-8">' +
                '<div class="shrink-0 flex items-start">' + avatar + '</div>' +
                '<div class="flex-1 min-w-0 flex flex-col">' +
                '<div class="text-gold mb-4 opacity-90">' + svgIcon('quotes', 'w-10 h-10') + '</div>' +
                msgBlock +
                (metaLine ? '<p class="mt-6 pt-4 border-t border-outline-variant/35 text-sm text-primary font-semibold">' + metaLine + '</p>' : '') +
                '</div></article>';
        }).join('');
    }

    function renderPloElo(d) {
        var bodyEl = document.getElementById('plo-elo-body');
        var emptyEl = document.getElementById('plo-elo-empty');
        if (!bodyEl || !emptyEl) return;

        var ls = d && d.learning_standards;
        ls = ls && typeof ls === 'object' ? ls : {};
        var intro = text(ls.intro || '');
        var standards = Array.isArray(ls.standards) ? ls.standards : [];
        var mapping = Array.isArray(ls.mapping) ? ls.mapping : [];
        var elos = Array.isArray(d && d.elos) ? d.elos : [];

        standards = standards.filter(function (st) {
            return !!(st && (text(st.code) || text(st.title) || text(st.category) || text(st.summary) || text(st.detail)));
        });
        mapping = mapping.filter(function (row) {
            return !!(row && (text(row.standard_code) || text(row.plo_refs)));
        });
        elos = elos.filter(function (e) {
            return !!(e && (text(e.category) || text(e.title) || text(e.summary) || text(e.detail)));
        });

        var hasStandardsBlock = intro.length > 0 || standards.length > 0 || mapping.length > 0;
        var hasElos = elos.length > 0;

        if (!hasStandardsBlock && !hasElos) {
            emptyEl.innerHTML = badgeNoData();
            emptyEl.classList.remove('hidden');
            bodyEl.classList.add('hidden');
            return;
        }

        emptyEl.innerHTML = '';
        emptyEl.classList.add('hidden');
        bodyEl.classList.remove('hidden');

        var introWrap = document.getElementById('plo-intro-wrap');
        if (introWrap) {
            if (intro) {
                introWrap.innerHTML = rich(intro);
                introWrap.classList.remove('hidden');
            } else {
                introWrap.innerHTML = '';
                introWrap.classList.add('hidden');
            }
        }

        var lsSec = document.getElementById('ls-section');
        var lsGrid = document.getElementById('ls-grid');
        if (lsSec && lsGrid) {
            if (standards.length > 0) {
                lsSec.classList.remove('hidden');
                lsGrid.innerHTML = standards.map(function (st, i) {
                    var title = firstText(st.title, st.category) || ('มาตรฐานการเรียนรู้ ' + (i + 1));
                    var code = text(st.code) || ('LS' + (i + 1));
                    var catTxt = text(st.category);
                    var catLabel = catTxt ? catTxt : 'มาตรฐานการเรียนรู้';
                    var sum = text(st.summary);
                    var det = text(st.detail);
                    var summaryHtml = '';
                    if (sum) {
                        summaryHtml = '<p class="text-on-surface-variant mt-3 leading-relaxed">' + rich(sum) + '</p>';
                    } else if (det) {
                        summaryHtml = '<p class="text-on-surface-variant mt-3 leading-relaxed">' + rich(det) + '</p>';
                    }
                    var detailExtra = '';
                    if (det && det !== sum) {
                        detailExtra = '<div class="text-sm text-on-surface-variant mt-4 pt-4 border-t border-outline-variant/30 leading-relaxed">' + rich(det) + '</div>';
                    }
                    return '<article class="bg-surface p-8 rounded-sm shadow-elegant border border-outline-variant/40 hover:border-gold/30 transition-colors">' +
                        '<div class="text-gold mb-5">' + svgIcon('study', 'w-10 h-10') + '</div>' +
                        '<span class="inline-block text-xs font-bold tracking-wide uppercase text-gold mb-2">' + esc(code) + '</span>' +
                        '<p class="text-sm font-semibold text-primary">' + esc(catLabel) + '</p>' +
                        '<h4 class="text-xl font-bold text-primary mt-1">' + esc(title) + '</h4>' +
                        summaryHtml + detailExtra + '</article>';
                }).join('');
            } else {
                lsSec.classList.add('hidden');
                lsGrid.innerHTML = '';
            }
        }

        var mapSec = document.getElementById('mapping-section');
        var mapHost = document.getElementById('mapping-table-host');
        if (mapSec && mapHost) {
            if (mapping.length > 0) {
                mapSec.classList.remove('hidden');
                var rows = mapping.map(function (row) {
                    var refs = text(row.plo_refs);
                    return '<tr class="border-b border-outline-variant/25 last:border-b-0">' +
                        '<td class="px-6 py-3 align-top text-on-surface-variant font-medium whitespace-nowrap">' + esc(text(row.standard_code) || '—') + '</td>' +
                        '<td class="px-6 py-3 align-top text-on-surface-variant leading-relaxed">' + (refs ? esc(refs).replace(/\n/g, '<br>') : '—') + '</td>' +
                        '</tr>';
                }).join('');
                mapHost.innerHTML =
                    '<table class="min-w-full text-left border-collapse" role="table">' +
                    '<thead><tr class="bg-primary/5 text-primary border-b border-outline-variant/40">' +
                    '<th scope="col" class="px-6 py-4 font-semibold">รหัส / มาตรฐานการเรียนรู้</th>' +
                    '<th scope="col" class="px-6 py-4 font-semibold">PLO ที่เกี่ยวข้อง</th></tr></thead>' +
                    '<tbody>' + rows + '</tbody></table>';
            } else {
                mapSec.classList.add('hidden');
                mapHost.innerHTML = '';
            }
        }

        var eloSec = document.getElementById('elo-section');
        var eloGrid = document.getElementById('elo-grid');
        var eloHd = document.getElementById('elo-heading');
        if (eloSec && eloGrid) {
            if (hasElos) {
                eloSec.classList.remove('hidden');
                if (eloHd) eloHd.classList.toggle('hidden', !(hasStandardsBlock && hasElos));
                eloGrid.innerHTML = elos.map(function (elo) {
                    var sum = text(elo.summary);
                    var det = text(elo.detail);
                    var summaryHtml = '';
                    if (sum) {
                        summaryHtml = '<p class="text-on-surface-variant mt-3 leading-relaxed">' + rich(sum) + '</p>';
                    } else if (det) {
                        summaryHtml = '<p class="text-on-surface-variant mt-3 leading-relaxed">' + rich(det) + '</p>';
                    }
                    var detailExtra = '';
                    if (det && det !== sum) {
                        detailExtra = '<div class="text-sm text-on-surface-variant mt-4 pt-4 border-t border-outline-variant/30 leading-relaxed">' + rich(det) + '</div>';
                    }
                    var catTxt = text(elo.category);
                    var titleTxt = text(elo.title);
                    var headTitle = firstText(titleTxt, catTxt);
                    if (!headTitle) headTitle = 'PLO / ELO';
                    var catHtml = '';
                    if (catTxt && (!titleTxt || catTxt !== titleTxt)) {
                        catHtml = '<p class="text-sm font-semibold text-primary">' + esc(catTxt) + '</p>';
                    }
                    var headCls = catHtml ? 'text-xl font-bold text-primary mt-2' : 'text-xl font-bold text-primary mt-0';
                    return '<article class="bg-surface p-8 rounded-sm shadow-elegant border border-outline-variant/40 hover:border-gold/30 transition-colors">' +
                        '<div class="text-gold mb-5">' + svgIcon('verified', 'w-10 h-10') + '</div>' +
                        catHtml +
                        '<h4 class="' + headCls + '">' + esc(headTitle) + '</h4>' +
                        summaryHtml + detailExtra + '</article>';
                }).join('');
            } else {
                eloSec.classList.add('hidden');
                eloGrid.innerHTML = '';
            }
        }
    }

    function renderSupports(d) {
        var sup = ((d.admission_details || {}).supports) || {};
        var rowsMeta = [
            ['scholarship', 'ทุนการศึกษา', 'tuition'],
            ['first_term_loan', 'กองทุนยืมเงินค่าเทอมแรกเข้า', 'wallet'],
            ['ksl_loan', 'กองทุนกู้ยืมเพื่อการศึกษา (กยศ.)', 'loan'],
            ['study_scholarship', 'ทุนการศึกษาระหว่างเรียน', 'card'],
            ['entrepreneur_fund', 'ทุนสนับสนุนการเป็นผู้ประกอบการ', 'rocket'],
            ['dormitory', 'หอพักนักศึกษาของมหาวิทยาลัย', 'building']
        ];

        var enabled = rowsMeta.filter(function (row) {
            return sup[row[0]] === true;
        });
        var admissionInfo = text(d.admission_info || '');
        var infoEl = document.getElementById('admission-info-block');
        var supGrid = document.getElementById('support-grid');
        var supEmp = document.getElementById('support-empty');

        if (admissionInfo) {
            infoEl.innerHTML = rich(d.admission_info);
            infoEl.classList.remove('hidden');
        } else {
            infoEl.innerHTML = '';
            infoEl.classList.add('hidden');
        }

        if (enabled.length) {
            supEmp.innerHTML = '';
            supEmp.classList.add('hidden');
            supGrid.classList.remove('hidden');
            supGrid.innerHTML = enabled.map(function (x) {
                return supportTitleCard(x[2], x[1]);
            }).join('');
        } else {
            supGrid.innerHTML = '';
            supGrid.classList.add('hidden');
            supEmp.innerHTML = badgeNoData();
            supEmp.classList.remove('hidden');
        }
    }

    function renderAssessment(d) {
        var a = text(d.assessment_methods);
        var loaded = document.getElementById('assessment-loaded');
        var empt = document.getElementById('assessment-empty');
        var txt = document.getElementById('assessment-text');
        if (!a) {
            loaded.classList.add('hidden');
            txt.innerHTML = '';
            empt.innerHTML = badgeNoData('light');
            empt.classList.remove('hidden');
            return;
        }
        empt.innerHTML = '';
        empt.classList.add('hidden');
        loaded.classList.remove('hidden');
        txt.innerHTML = rich(d.assessment_methods);
    }

    function bindTabs() {
        document.querySelectorAll('.year-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var yi = tab.getAttribute('data-year-index');
                document.querySelectorAll('.year-tab').forEach(function (x) {
                    x.classList.remove('is-active');
                    x.classList.add('border-transparent', 'text-on-surface-variant');
                });
                tab.classList.add('is-active');
                tab.classList.remove('border-transparent', 'text-on-surface-variant');
                document.querySelectorAll('.year-panel').forEach(function (p) {
                    var a = p.getAttribute('data-year-index') === yi;
                    p.classList.toggle('hidden', !a);
                    p.classList.toggle('block', a);
                });
            });
        });
        document.querySelectorAll('.sem-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var yi = tab.getAttribute('data-year-index');
                var si = tab.getAttribute('data-sem-index');
                document.querySelectorAll('.sem-tab-' + yi).forEach(function (x) {
                    x.classList.remove('is-active');
                    x.classList.add('bg-surface-alt', 'text-on-surface-variant');
                });
                tab.classList.add('is-active');
                tab.classList.remove('bg-surface-alt', 'text-on-surface-variant');
                document.querySelectorAll('.sem-panel-' + yi).forEach(function (p) {
                    var a = p.getAttribute('data-sem-index') === si;
                    p.classList.toggle('hidden', !a);
                    p.classList.toggle('block', a);
                });
            });
        });
    }

    var LABEL_NO_PROGRAM_CONTACT = 'ยังไม่มีข้อมูลการติดต่อหลักสูตร';

    function renderFooterContact(d) {
        var el = document.getElementById('footer-program-contact');
        if (!el) return;
        if (text(d.contact_info)) {
            el.innerHTML = rich(d.contact_info);
        } else {
            el.innerHTML = '<p class="text-white/60 leading-relaxed">' + esc(LABEL_NO_PROGRAM_CONTACT) + '</p>';
        }
    }

    function renderData(d) {
        dRef = d;
        renderAbout(d);
        renderPloElo(d);
        renderHero(d);
        renderFaculty(d);
        renderJourney(d);
        renderCurriculum(d);
        renderTeaching(d);
        renderGraduation(d);
        renderAlumni(d);
        renderSupports(d);
        renderAssessment(d);
        updateHeroAnchors();
        bindTabs();
        renderNavLinks();
        renderFooterContact(d);
    }

    document.getElementById('nav-toggle').addEventListener('click', function () {
        document.getElementById('nav-mobile').classList.toggle('hidden');
    });
    document.getElementById('nav-mobile').addEventListener('click', function (ev) {
        if (ev.target && ev.target.closest && ev.target.closest('a')) {
            document.getElementById('nav-mobile').classList.add('hidden');
        }
    });

    $.ajax({ url: dataUrl, method: 'GET', dataType: 'json' })
        .done(function (res) {
            if (!res || !res.success || !res.data) { throw new Error((res && res.message) || 'ไม่พบข้อมูลหลักสูตร'); }
            renderData(res.data);
            document.getElementById('app').classList.remove('hidden');
            var l = document.getElementById('loading');
            l.style.opacity = '0';
            setTimeout(function () { l.style.display = 'none'; }, 320);
        })
        .fail(function () {
            document.getElementById('loading').innerHTML =
                '<div class="max-w-md mx-auto px-8 py-10 bg-white border border-outline-variant shadow-elegant text-center">' +
                '<h1 class="text-2xl font-bold text-primary mb-4">โหลดข้อมูลไม่สำเร็จ</h1>' +
                '<p class="text-on-surface-variant mb-6">ระบบไม่สามารถเชื่อมต่อกับข้อมูลหลักสูตรได้ในขณะนี้</p>' +
                '<button type="button" class="bg-gold text-white px-8 py-3 rounded-sm text-sm font-semibold" onclick="location.reload()">ลองอีกครั้ง</button></div>';
        });
})();
</script>
</body>
</html>

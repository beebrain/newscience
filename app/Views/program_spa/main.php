<?php $id = (int) ($id ?? 0); $dataUrl = base_url('p/' . $id . '/data'); ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หลักสูตร | Program SPA</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 900: '#0a0f1e', 800: '#0f172a', 700: '#1a2340' },
                    gold: { 400: '#daa520', 500: '#c5941a', 600: '#b8860b' }
                },
                fontFamily: { display: ['Inter', 'Noto Sans Thai', 'sans-serif'] }
            }
        }
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <style>
        :root { --navy: #0a0f1e; --gold: #daa520; --theme: #daa520; --theme-rgb: 218, 165, 32; }
        * { font-family: 'Inter', 'Noto Sans Thai', sans-serif; }
        /* โทนสว่าง: สีข้อความ/พื้นหลังกำหนดจาก Admin > การตั้งค่าเว็บไซต์ (--website-text, --website-bg) */
        body { background: var(--website-bg, color-mix(in srgb, var(--theme) 8%, #f8fafc)); color: var(--website-text, #1e293b); overflow-x: hidden; transition: background 0.6s ease, color 0.3s ease; }
        .page-bg { background: var(--website-bg, color-mix(in srgb, var(--theme) 8%, #f8fafc)); transition: background 0.6s ease; }

        /* Neural network canvas */
        #neural-canvas { position: absolute; inset: 0; z-index: 0; }

        /* Animated gradient orbs */
        .orb {
            position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.3;
            animation: orbFloat 12s ease-in-out infinite alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(var(--theme-rgb), 0.15), transparent 70%); top: -10%; left: -5%; animation-delay: 0s; }
        .orb-2 { width: 400px; height: 400px; background: radial-gradient(circle, rgba(var(--theme-rgb), 0.1), transparent 70%); bottom: -10%; right: -5%; animation-delay: -4s; }
        .orb-3 { width: 300px; height: 300px; background: radial-gradient(circle, rgba(var(--theme-rgb), 0.08), transparent 70%); top: 40%; left: 50%; animation-delay: -8s; }
        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -40px) scale(1.15); }
        }

        /* Reveal animations */
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .reveal-left { opacity: 0; transform: translateX(-60px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-left.visible { opacity: 1; transform: translateX(0); }
        .reveal-right { opacity: 0; transform: translateX(60px); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-right.visible { opacity: 1; transform: translateX(0); }
        .reveal-scale { opacity: 0; transform: scale(0.85); transition: all 0.8s cubic-bezier(0.16, 1, 0.3, 1); }
        .reveal-scale.visible { opacity: 1; transform: scale(1); }

        /* Stagger children */
        .stagger-children > * { opacity: 0; transform: translateY(30px); transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
        .stagger-children.visible > *:nth-child(1) { transition-delay: 0.05s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(2) { transition-delay: 0.1s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(3) { transition-delay: 0.15s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(4) { transition-delay: 0.2s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(5) { transition-delay: 0.25s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(6) { transition-delay: 0.3s; opacity: 1; transform: translateY(0); }
        .stagger-children.visible > *:nth-child(n+7) { transition-delay: 0.35s; opacity: 1; transform: translateY(0); }

        /* Hero text typing */
        .hero-title-char { display: inline-block; opacity: 0; transform: translateY(20px); animation: charIn 0.5s forwards; }
        @keyframes charIn { to { opacity: 1; transform: translateY(0); } }

        /* Glowing CTA - uses --theme from program */
        .glow-btn {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, var(--theme), color-mix(in srgb, var(--theme) 85%, black)) !important;
            box-shadow: 0 0 20px rgba(var(--theme-rgb), 0.3), 0 0 60px rgba(var(--theme-rgb), 0.1);
            transition: all 0.4s;
        }
        .glow-btn:hover { box-shadow: 0 0 30px rgba(var(--theme-rgb), 0.5), 0 0 80px rgba(var(--theme-rgb), 0.2); transform: translateY(-2px); }
        .glow-btn::after {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.15) 50%, transparent 60%);
            animation: btnShine 3s ease-in-out infinite;
        }
        @keyframes btnShine { 0%,100% { transform: translateX(-100%) rotate(45deg); } 50% { transform: translateX(100%) rotate(45deg); } }

        /* Card - โทนสว่าง */
        .luxury-card {
            background: color-mix(in srgb, var(--theme) 6%, #ffffff); border: 1px solid rgba(var(--theme-rgb), 0.2);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .luxury-card:hover {
            border-color: rgba(var(--theme-rgb), 0.45); transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08), 0 0 20px rgba(var(--theme-rgb), 0.1);
        }

        /* Faculty card — เล็กลง ~30% แบบ Meet the team (Major Tom) */
        .faculty-grid-compact .faculty-card { max-width: 180px; margin: 0 auto; }
        .faculty-card { position: relative; overflow: hidden; }
        .faculty-card .faculty-overlay {
            position: absolute; inset: 0; background: linear-gradient(to top, rgba(255,255,255,0.95) 0%, transparent 55%);
            opacity: 0; transition: opacity 0.4s;
        }
        .faculty-card:hover .faculty-overlay { opacity: 1; }
        .faculty-card .faculty-info { transform: translateY(20px); transition: transform 0.4s; color: #1e293b; }
        .faculty-card:hover .faculty-info { transform: translateY(0); }

        /* News carousel */
        .news-track { display: flex; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1); }

        /* Lightbox */
        .lightbox { position: fixed; inset: 0; z-index: 100; background: rgba(0,0,0,0.92); display: none; align-items: center; justify-content: center; }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90vw; max-height: 85vh; border-radius: 8px; box-shadow: 0 0 60px rgba(var(--theme-rgb), 0.15); }

        /* Document icon glow */
        .doc-icon { transition: all 0.3s; }
        .doc-row:hover .doc-icon { filter: drop-shadow(0 0 8px rgba(var(--theme-rgb), 0.5)); transform: scale(1.1); }
        .doc-row:hover { border-color: rgba(var(--theme-rgb), 0.5) !important; }

        /* Section divider */
        .section-title::after {
            content: ''; display: block; width: 60px; height: 2px; margin-top: 12px;
            background: linear-gradient(90deg, var(--theme), transparent);
        }
        .section-accent { color: var(--theme); }

        /* Scroll to top */
        #scroll-top {
            position: fixed; bottom: 2rem; right: 2rem; z-index: 50;
            opacity: 0; transform: translateY(20px); transition: all 0.4s;
            pointer-events: none;
        }
        #scroll-top.show { opacity: 1; transform: translateY(0); pointer-events: auto; }

        /* Glass - โทนสว่าง */
        .glass { background: color-mix(in srgb, var(--theme) 4%, #ffffff); backdrop-filter: blur(12px); border: 1px solid rgba(var(--theme-rgb), 0.15); }

        /* Loading screen */
        .loader-ring { width: 60px; height: 60px; border: 3px solid rgba(var(--theme-rgb), 0.15); border-top-color: var(--theme); border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .scroll-top-btn { background: rgba(var(--theme-rgb), 0.85) !important; }
        .scroll-top-btn:hover { background: rgba(var(--theme-rgb), 1) !important; }
        /* Hero — ให้รูปเด่นขึ้นแบบ Major Tom (opacity สูงขึ้น, overlay เบาลง) */
        .hero-bg-image { opacity: 0.45; }
        .hero-overlay { background: linear-gradient(to bottom, rgba(255,255,255,0.15), color-mix(in srgb, var(--theme) 15%, white) 40%, color-mix(in srgb, var(--theme) 35%, white) 75%); }
        /* Section tint โทนสว่าง */
        .section-theme-tint { background: linear-gradient(to bottom, transparent, rgba(var(--theme-rgb), 0.04), transparent); }
        /* แต่ละ Section พื้นหลังคนละโทน สอดคล้องกับสีที่ผู้ใช้เลือก (--website-bg, --theme) */
        .section-bg-base { background: var(--website-bg, color-mix(in srgb, var(--theme) 8%, #f8fafc)); }
        .section-bg-tint-1 { background: color-mix(in srgb, var(--theme) 5%, var(--website-bg, #f8fafc)); }
        .section-bg-tint-2 { background: color-mix(in srgb, var(--theme) 10%, var(--website-bg, #f8fafc)); }
        .section-bg-tint-3 { background: color-mix(in srgb, var(--theme) 7%, var(--website-bg, #f8fafc)); }
        .section-bg-tint-4 { background: color-mix(in srgb, var(--theme) 12%, var(--website-bg, #f8fafc)); }
        /* ศิษย์เก่า testimonial — ไม่มีกรอบ/การ์ด กลืนกับพื้นหลังแบบ Clean (Major Tom style) */
        .alumni-testimonial-card { background: transparent; border: none; box-shadow: none; }
        .nav-light { background: rgba(255,255,255,0.92); backdrop-filter: blur(12px); }
        .nav-brand-theme { color: var(--theme); }
        .nav-link-theme:hover { color: var(--theme); }
        .hero-title { color: #0f172a; text-shadow: 0 1px 2px rgba(255,255,255,0.8); }
        .hero-sub { color: #334155; }
        .hero-arrow { color: var(--theme); opacity: 0.9; }
        .elo-num { background: rgba(var(--theme-rgb), 0.15); color: var(--theme); }
        .faculty-role { color: var(--theme); }
        .news-date { color: var(--theme); }
        .activity-thumb { --tw-ring-color: var(--theme); }
        .facility-placeholder { color: rgba(var(--theme-rgb), 0.25); }
        .doc-icon-bg { background: rgba(var(--theme-rgb), 0.1); }
        .doc-icon-fg { color: var(--theme); }
        .doc-row:hover .doc-arrow { color: var(--theme); }
    </style>
</head>
<body class="min-h-screen antialiased">

<!-- Loading -->
<div id="loading" class="fixed inset-0 z-[60] flex items-center justify-center page-bg">
    <div class="text-center">
        <div class="loader-ring mx-auto mb-6"></div>
        <p class="text-slate-600 text-sm tracking-widest uppercase">Loading Program</p>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="lightbox" onclick="this.classList.remove('active')">
    <img id="lightbox-img" src="" alt="">
</div>

<!-- Scroll to top -->
<button id="scroll-top" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="scroll-top-btn w-12 h-12 rounded-full backdrop-blur text-white flex items-center justify-center shadow-lg transition">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/></svg>
</button>

<!-- Navbar - โทนสว่าง -->
<nav id="navbar" class="fixed top-0 left-0 right-0 z-40 transition-all duration-500" style="transform:translateY(-100%)">
    <div class="nav-light border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">
            <a href="#hero" id="nav-brand" class="font-bold text-slate-800 tracking-wide text-lg nav-brand-theme">หลักสูตร</a>
            <button id="nav-toggle" class="md:hidden text-slate-600 p-2" aria-label="เมนู">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div id="nav-links" class="hidden md:flex items-center gap-8">
                <a href="#about" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">เกี่ยวกับ</a>
                <a href="#faculty" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">คณาจารย์</a>
                <a href="#news" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">ข่าวสาร</a>
                <a href="#alumni" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">ศิษย์เก่า</a>
                <a href="#activities" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">กิจกรรม</a>
                <a href="#facilities" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">สิ่งอำนวยความสะดวก</a>
                <a href="#documents" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">เอกสาร</a>
                <a href="#video" class="text-sm text-slate-600 hover:opacity-80 transition nav-link-theme">วิดีโอ</a>
            </div>
        </div>
        <div id="nav-mobile" class="hidden md:hidden border-t border-slate-200 px-6 py-4 space-y-3 bg-white/95">
            <a href="#about" class="block text-slate-600 nav-link-theme">เกี่ยวกับ</a>
            <a href="#faculty" class="block text-slate-600 nav-link-theme">คณาจารย์</a>
            <a href="#news" class="block text-slate-600 nav-link-theme">ข่าวสาร</a>
            <a href="#activities" class="block text-slate-600 nav-link-theme">กิจกรรม</a>
            <a href="#alumni" class="block text-slate-600 nav-link-theme">ศิษย์เก่า</a>
            <a href="#facilities" class="block text-slate-600 nav-link-theme">สิ่งอำนวยความสะดวก</a>
            <a href="#documents" class="block text-slate-600 nav-link-theme">เอกสาร</a>
            <a href="#video" class="block text-slate-600 nav-link-theme">วิดีโอ</a>
        </div>
    </div>
</nav>

<main id="app" class="hidden">

<!-- ==================== HERO (โครงแบบ Major Tom — Hero image เด่น, ข้อความชัด) ==================== -->
<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden section-bg-base">
    <canvas id="neural-canvas"></canvas>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="absolute inset-0 bg-cover bg-center hero-bg-image" id="hero-bg"></div>
    <div class="absolute inset-0 hero-overlay"></div>
    <div class="relative z-10 text-center max-w-4xl mx-auto px-6">
        <p id="hero-level" class="hero-sub text-sm tracking-[0.3em] uppercase mb-4 opacity-0" style="transition:opacity 1s 0.3s"></p>
        <h1 id="hero-title" class="text-4xl md:text-6xl lg:text-7xl xl:text-8xl font-extrabold hero-title leading-tight mb-4"></h1>
        <p id="hero-degree" class="text-xl md:text-2xl lg:text-3xl hero-sub font-light mb-10 opacity-0" style="transition:opacity 1s 1.2s"></p>
        <a href="#about" class="glow-btn inline-block px-8 py-4 text-white font-bold rounded-full text-lg opacity-0" style="transition:opacity 1s 1.6s">
            สำรวจหลักสูตร
        </a>
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 hero-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 14l-7 7m0 0l-7-7"/></svg>
    </div>
</section>

<!-- ==================== ABOUT & AUN-QA ==================== -->
<section id="about" class="relative py-24 md:py-32 section-bg-base">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-16 items-start">
            <div class="reveal-left">
                <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-8">เกี่ยวกับหลักสูตร<br><span class="section-accent">& AUN-QA</span></h2>
                <div id="about-philosophy" class="text-slate-600 leading-relaxed mb-6"></div>
                <div id="about-vision" class="text-slate-600 leading-relaxed"></div>
            </div>
            <div class="reveal-right">
                <h3 class="text-xl font-semibold section-accent mb-6 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                    ผลลัพธ์การเรียนรู้ที่คาดหวัง (ELOs)
                </h3>
                <div id="elos-grid" class="space-y-3 stagger-children"></div>
            </div>
        </div>
        <div id="about-curriculum" class="mt-16 reveal">
            <h3 class="text-xl font-semibold section-accent mb-6">โครงสร้างหลักสูตร</h3>
            <div id="curriculum-content" class="glass rounded-2xl p-8 text-slate-600 leading-relaxed"></div>
        </div>
    </div>
</section>

<!-- ==================== FACULTY (Meet the team style — รูปเล็กลง ~30%) ==================== -->
<section id="faculty" class="relative py-24 md:py-32 section-bg-tint-1">
    <div class="relative max-w-5xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">คณาจารย์ประจำหลักสูตร</h2>
        <div id="faculty-grid" class="faculty-grid-compact grid grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-5 stagger-children"></div>
        <p id="faculty-empty" class="text-slate-500 text-center hidden">ยังไม่มีข้อมูลคณาจารย์</p>
    </div>
</section>

<!-- ==================== ศิษย์เก่าถึงรุ่นน้อง (Testimonial Carousel) ==================== -->
<section id="alumni" class="relative py-24 md:py-32 section-bg-tint-2" style="display:none;">
    <div class="relative max-w-5xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">ศิษย์เก่า<span class="section-accent">ถึงรุ่นน้อง</span></h2>
        <div id="alumni-carousel-card" class="alumni-testimonial-card p-8 md:p-12 flex flex-col md:flex-row gap-8 md:gap-12 items-center md:items-start text-center md:text-left reveal">
            <!-- รูปวงกลม + ข้อความ + ตำแหน่ง/ที่ทำงาน (เติมด้วย JS) — ไม่มีกรอบ กลืนกับพื้นหลังแบบ Clean -->
        </div>
        <div id="alumni-carousel-nav" class="flex items-center justify-center gap-4 mt-8 reveal" style="display:none;">
            <button type="button" id="alumni-prev" class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition bg-white/80 hover:bg-[var(--theme)] hover:border-[var(--theme)] hover:text-white text-slate-600 border-slate-300" aria-label="ก่อนหน้า">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <div class="flex items-center gap-1 min-w-[120px] justify-center">
                <span id="alumni-counter" class="text-sm text-slate-600 font-medium">1 / 1</span>
                <div id="alumni-progress" class="flex-1 h-1 rounded-full bg-slate-200 max-w-[80px] overflow-hidden">
                    <div id="alumni-progress-bar" class="h-full rounded-full transition-all duration-300" style="width:100%; background: var(--theme);"></div>
                </div>
            </div>
            <button type="button" id="alumni-next" class="w-12 h-12 rounded-full flex items-center justify-center border-2 transition bg-white/80 hover:bg-[var(--theme)] hover:border-[var(--theme)] hover:text-white text-slate-600 border-slate-300" aria-label="ถัดไป">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
        <div id="alumni-more-wrap" class="text-center mt-10 reveal" style="display:none;">
            <button type="button" id="alumni-show-all-btn" class="glow-btn px-8 py-4 text-white font-bold rounded-full text-lg transition">
                ดูศิษย์เก่าทั้งหมด
            </button>
        </div>
    </div>
</section>

<!-- Modal ศิษย์เก่าทั้งหมด -->
<div id="alumni-modal" class="fixed inset-0 z-[100] hidden items-center justify-center p-4 bg-black/60 backdrop-blur-sm" aria-modal="true" role="dialog" aria-labelledby="alumni-modal-title">
    <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] flex flex-col overflow-hidden">
        <div class="flex items-center justify-between p-6 border-b border-slate-200 flex-shrink-0">
            <h3 id="alumni-modal-title" class="text-xl font-bold section-accent">ศิษย์เก่าถึงรุ่นน้อง — ทั้งหมด</h3>
            <button type="button" id="alumni-modal-close" class="w-10 h-10 rounded-full border border-slate-300 text-slate-600 hover:bg-slate-100 flex items-center justify-center transition" aria-label="ปิด">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div id="alumni-modal-body" class="p-6 overflow-y-auto flex-1 space-y-6"></div>
    </div>
</div>

<!-- ==================== NEWS ==================== -->
<section id="news" class="relative py-24 md:py-32 overflow-hidden section-bg-tint-3">
    <div class="max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-12">
            <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 reveal">ข่าวสาร<br><span class="section-accent">& ประชาสัมพันธ์</span></h2>
            <div class="flex gap-3 reveal">
                <button id="news-prev" class="w-10 h-10 rounded-full border border-slate-300 text-slate-600 hover:border-[var(--theme)] hover:text-[var(--theme)] transition flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button id="news-next" class="w-10 h-10 rounded-full border border-slate-300 text-slate-600 hover:border-[var(--theme)] hover:text-[var(--theme)] transition flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
        <div class="overflow-hidden rounded-2xl">
            <div id="news-track" class="news-track"></div>
        </div>
        <p id="news-empty" class="text-slate-500 text-center hidden mt-8">ยังไม่มีข่าว</p>
    </div>
</section>

<!-- ==================== ACTIVITIES ==================== -->
<section id="activities" class="relative py-24 md:py-32 section-bg-tint-4">
    <div class="relative max-w-7xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">กิจกรรม<span class="section-accent">ของหลักสูตร</span></h2>
        <div id="activities-grid" class="columns-1 md:columns-2 lg:columns-3 gap-6 space-y-6"></div>
        <p id="activities-empty" class="text-slate-500 text-center hidden">ยังไม่มีกิจกรรม</p>
    </div>
</section>

<!-- ==================== FACILITIES ==================== -->
<section id="facilities" class="relative py-24 md:py-32 section-bg-tint-1">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">สิ่งอำนวยความสะดวก<br><span class="section-accent">& การสนับสนุนการเรียนรู้</span></h2>
        <div id="facilities-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 stagger-children"></div>
        <p id="facilities-empty" class="text-slate-500 text-center hidden">ยังไม่มีรายการ</p>
    </div>
</section>

<!-- ==================== DOCUMENTS ==================== -->
<section id="documents" class="relative py-24 md:py-32 section-bg-tint-2">
    <div class="relative max-w-7xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">เอกสาร<span class="section-accent">ดาวน์โหลด</span></h2>
        <div id="documents-list" class="max-w-3xl mx-auto space-y-4 stagger-children"></div>
        <p id="documents-empty" class="text-slate-500 text-center hidden">ยังไม่มีเอกสาร</p>
    </div>
</section>

<!-- ==================== VIDEO (คลิปวิดีโอ สนับสนุน AUN-QA) ==================== -->
<section id="video" class="relative py-24 md:py-32 section-bg-tint-3" style="display:none;">
    <div class="max-w-4xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-12 text-center reveal">วิดีโอแนะนำหลักสูตร<br><span class="section-accent">คลิปและสื่อสนับสนุนการประกันคุณภาพ</span></h2>
        <div id="video-wrap" class="relative rounded-2xl overflow-hidden shadow-xl aspect-video bg-slate-200"></div>
    </div>
</section>

<!-- ==================== FOOTER ==================== -->
<footer id="footer" class="relative py-16 border-t border-slate-200/50 section-bg-tint-4">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 mb-12">
            <div class="reveal-left">
                <h3 class="text-xl font-bold section-accent mb-4">ติดต่อเรา</h3>
                <div id="footer-contact" class="text-slate-600 leading-relaxed"></div>
            </div>
            <div class="reveal-right">
                <h3 class="text-xl font-bold section-accent mb-4">แผนที่</h3>
                <div class="glass rounded-xl h-48 flex items-center justify-center text-slate-500">
                    <svg class="w-8 h-8 mr-2 opacity-50" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                    Map Placeholder
                </div>
            </div>
        </div>
        <div class="text-center text-slate-600 text-sm">
            <p>© <?= date('Y') ?> — Program SPA · Luxury Edition</p>
        </div>
    </div>
</footer>

</main>

<script>
(function() {
    var dataUrl = <?= json_encode($dataUrl) ?>;

    function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

    // Neural network canvas animation
    function initNeuralCanvas() {
        var c = document.getElementById('neural-canvas');
        if (!c) return;
        var ctx = c.getContext('2d');
        var w, h, particles = [], mouse = { x: -1000, y: -1000 };
        function resize() { w = c.width = c.offsetWidth; h = c.height = c.offsetHeight; }
        resize();
        window.addEventListener('resize', resize);
        c.addEventListener('mousemove', function(e) { var r = c.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; });
        c.addEventListener('mouseleave', function() { mouse.x = -1000; mouse.y = -1000; });
        var count = Math.min(80, Math.floor(w * h / 12000));
        for (var i = 0; i < count; i++) {
            particles.push({ x: Math.random() * w, y: Math.random() * h, vx: (Math.random() - 0.5) * 0.4, vy: (Math.random() - 0.5) * 0.4, r: Math.random() * 2 + 1 });
        }
        function draw() {
            ctx.clearRect(0, 0, w, h);
            for (var i = 0; i < particles.length; i++) {
                var p = particles[i];
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > w) p.vx *= -1;
                if (p.y < 0 || p.y > h) p.vy *= -1;
                var dm = Math.hypot(p.x - mouse.x, p.y - mouse.y);
                var glow = dm < 200 ? 1 - dm / 200 : 0;
                ctx.beginPath(); ctx.arc(p.x, p.y, p.r + glow * 2, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(218,165,32,' + (0.3 + glow * 0.5) + ')'; ctx.fill();
                for (var j = i + 1; j < particles.length; j++) {
                    var q = particles[j], dist = Math.hypot(p.x - q.x, p.y - q.y);
                    if (dist < 150) {
                        ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y);
                        ctx.strokeStyle = 'rgba(218,165,32,' + (0.08 * (1 - dist / 150)) + ')'; ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(draw);
        }
        draw();
    }

    // Animated hero title
    function animateTitle(text) {
        var el = document.getElementById('hero-title');
        el.innerHTML = '';
        for (var i = 0; i < text.length; i++) {
            var ch = text[i];
            var span = document.createElement('span');
            span.className = 'hero-title-char';
            span.style.animationDelay = (0.5 + i * 0.04) + 's';
            span.textContent = ch === ' ' ? '\u00a0' : ch;
            el.appendChild(span);
        }
    }

    // News carousel
    var newsIdx = 0, newsTotal = 0;
    function slideNews(dir) {
        var perView = window.innerWidth >= 1024 ? 3 : window.innerWidth >= 768 ? 2 : 1;
        newsIdx = Math.max(0, Math.min(newsIdx + dir, newsTotal - perView));
        var pct = newsIdx * (100 / perView);
        document.getElementById('news-track').style.transform = 'translateX(-' + pct + '%)';
    }

    function hexToRgb(hex) {
        hex = (hex || '').replace(/^#/, '');
        if (hex.length !== 6) return '218, 165, 32';
        var r = parseInt(hex.substr(0, 2), 16), g = parseInt(hex.substr(2, 2), 16), b = parseInt(hex.substr(4, 2), 16);
        return r + ', ' + g + ', ' + b;
    }

    function applyTheme(themeColor) {
        var hex = (themeColor || '#daa520').trim();
        if (!/^#[0-9A-Fa-f]{6}$/.test(hex)) hex = '#daa520';
        document.documentElement.style.setProperty('--theme', hex);
        document.documentElement.style.setProperty('--theme-rgb', hexToRgb(hex));
    }

    function renderData(d) {
        document.title = (d.name_th || d.name_en || 'หลักสูตร') + ' | Program SPA';

        applyTheme(d.theme_color);

        // สีข้อความและพื้นหลังจากแท็บการตั้งค่าเว็บไซต์
        if (d.text_color) document.documentElement.style.setProperty('--website-text', d.text_color); else document.documentElement.style.removeProperty('--website-text');
        if (d.background_color) document.documentElement.style.setProperty('--website-bg', d.background_color); else document.documentElement.style.removeProperty('--website-bg');

        // Hero
        if (d.hero_image) document.getElementById('hero-bg').style.backgroundImage = "url('" + esc(d.hero_image) + "')";
        animateTitle(d.name_th || d.name_en || '');
        document.getElementById('hero-level').textContent = d.level || '';
        document.getElementById('hero-degree').textContent = d.degree_th || d.degree_en || '';
        document.getElementById('hero-level').style.opacity = '1';
        document.getElementById('hero-degree').style.opacity = '1';
        document.querySelector('.glow-btn').style.opacity = '1';
        document.getElementById('nav-brand').textContent = d.name_th || 'หลักสูตร';

        // About
        if (d.philosophy) document.getElementById('about-philosophy').innerHTML = '<h3 class="text-lg font-semibold section-accent mb-3">ปรัชญา</h3><p>' + esc(d.philosophy).replace(/\n/g, '<br>') + '</p>';
        if (d.vision) document.getElementById('about-vision').innerHTML = '<h3 class="text-lg font-semibold section-accent mb-3">วัตถุประสงค์</h3><p>' + esc(d.vision).replace(/\n/g, '<br>') + '</p>';

        // ELOs
        var eg = document.getElementById('elos-grid'); eg.innerHTML = '';
        if (Array.isArray(d.elos) && d.elos.length) {
            d.elos.forEach(function(el, i) {
                var t = (typeof el === 'string') ? el : (el.title || el.text || el.name || el.description || '');
                eg.innerHTML += '<div class="flex items-start gap-3 p-4 rounded-xl glass"><span class="flex-shrink-0 w-8 h-8 rounded-full elo-num flex items-center justify-center text-sm font-bold">' + (i + 1) + '</span><span class="text-slate-600">' + esc(t) + '</span></div>';
            });
        }

        // Curriculum
        var cc = document.getElementById('curriculum-content');
        cc.innerHTML = d.curriculum_structure ? esc(d.curriculum_structure).replace(/\n/g, '<br>') : '<span class="text-slate-500">ไม่มีข้อมูล</span>';

        // Faculty
        var fg = document.getElementById('faculty-grid'); fg.innerHTML = '';
        if (Array.isArray(d.staff) && d.staff.length) {
            d.staff.forEach(function(s) {
                var img = s.image ? '<img src="' + esc(s.image) + '" alt="" class="w-full h-full object-cover">' : '<div class="w-full h-full bg-slate-200 flex items-center justify-center"><svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>';
                fg.innerHTML += '<div class="faculty-card luxury-card rounded-2xl overflow-hidden aspect-square max-w-[180px] w-full mx-auto relative group cursor-pointer">' + img + '<div class="faculty-overlay flex flex-col justify-end p-3"><div class="faculty-info"><p class="font-semibold text-slate-800 text-xs leading-tight">' + esc(s.name) + '</p><p class="text-[10px] mt-0.5 faculty-role">' + esc(s.role || s.position) + '</p></div></div></div>';
            });
        } else {
            document.getElementById('faculty-empty').classList.remove('hidden');
        }

        // News carousel
        var nt = document.getElementById('news-track'); nt.innerHTML = '';
        if (Array.isArray(d.news) && d.news.length) {
            newsTotal = d.news.length;
            d.news.forEach(function(n) {
                var img = n.image_url || n.thumbnail || '';
                var imgHtml = img ? '<img src="' + esc(img) + '" alt="" class="w-full h-48 object-cover">' : '<div class="w-full h-48 bg-slate-200"></div>';
                nt.innerHTML += '<div class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-3"><div class="luxury-card rounded-2xl overflow-hidden h-full">' + imgHtml + '<div class="p-5"><span class="text-xs news-date">' + esc(n.date || '') + '</span><h3 class="font-semibold text-slate-800 mt-2 line-clamp-2">' + esc(n.title || n.title_th || '') + '</h3><p class="text-slate-600 text-sm mt-2 line-clamp-2">' + esc((n.excerpt || '').substring(0, 120)) + '</p></div></div></div>';
            });
        } else {
            document.getElementById('news-empty').classList.remove('hidden');
        }

        // Activities masonry
        var ag = document.getElementById('activities-grid'); ag.innerHTML = '';
        if (Array.isArray(d.activities) && d.activities.length) {
            d.activities.forEach(function(a) {
                var imgs = a.images || [];
                var firstImg = (imgs[0] && imgs[0].url) ? imgs[0].url : '';
                var imgHtml = firstImg ? '<img src="' + esc(firstImg) + '" alt="" class="w-full rounded-xl mb-3 cursor-pointer hover:opacity-90 transition" onclick="document.getElementById(\'lightbox-img\').src=this.src;document.getElementById(\'lightbox\').classList.add(\'active\')">' : '';
                var thumbs = '';
                if (imgs.length > 1) {
                    thumbs = '<div class="flex gap-2 mt-2 flex-wrap">';
                    for (var i = 1; i < Math.min(imgs.length, 5); i++) {
                        thumbs += '<img src="' + esc(imgs[i].url) + '" alt="" class="w-12 h-12 rounded-lg object-cover cursor-pointer hover:ring-2 activity-thumb transition" onclick="document.getElementById(\'lightbox-img\').src=this.src;document.getElementById(\'lightbox\').classList.add(\'active\')">';
                    }
                    thumbs += '</div>';
                }
                ag.innerHTML += '<div class="break-inside-avoid luxury-card rounded-2xl overflow-hidden p-5 reveal-scale">' + imgHtml + '<h3 class="font-semibold text-slate-800">' + esc(a.title || '') + '</h3><p class="text-slate-600 text-sm mt-2">' + esc((a.description || '').substring(0, 150)) + '</p>' + thumbs + '</div>';
            });
        } else {
            document.getElementById('activities-empty').classList.remove('hidden');
        }

        // ศิษย์เก่าถึงรุ่นน้อง (Testimonial carousel: แสดงทีละคน — รูปวงกลม + คำพูดเด่น + ตำแหน่ง/ที่ทำงาน)
        var alumniSection = document.getElementById('alumni');
        var alumniCarouselCard = document.getElementById('alumni-carousel-card');
        var alumniCarouselNav = document.getElementById('alumni-carousel-nav');
        var alumniMoreWrap = document.getElementById('alumni-more-wrap');
        var alumniModalBody = document.getElementById('alumni-modal-body');
        window.__alumniList = Array.isArray(d.alumni) ? d.alumni : [];
        window.__alumniIdx = 0;
        function alumniCardHtml(a, truncate) {
            var photo = a.photo_url ? '<img src="' + esc(a.photo_url) + '" alt="" class="w-20 h-20 rounded-full object-cover flex-shrink-0">' : '<div class="w-20 h-20 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0"><svg class="w-10 h-10 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>';
            var meta = [];
            if (a.position) meta.push(esc(a.position));
            if (a.workplace) meta.push(esc(a.workplace));
            if (a.graduation_year) meta.push('จบปี ' + esc(a.graduation_year));
            var metaStr = meta.length ? '<p class="text-slate-500 text-sm mt-1">' + meta.join(' · ') + '</p>' : '';
            var msg = a.message || '';
            if (truncate && msg.length > 200) msg = msg.substring(0, 200) + '…';
            var msgHtml = msg ? '<p class="text-slate-700 leading-relaxed whitespace-pre-wrap">' + esc(msg).replace(/\n/g, '<br>') + '</p>' : '';
            return '<div class="luxury-card rounded-2xl p-6 flex gap-4 items-start">' + photo + '<div class="min-w-0 flex-1">' + msgHtml + metaStr + '</div></div>';
        }
        function renderAlumniSlide() {
            var list = window.__alumniList || [];
            var idx = window.__alumniIdx || 0;
            if (!alumniCarouselCard) return;
            if (list.length === 0) {
                alumniCarouselCard.innerHTML = '';
                return;
            }
            var a = list[idx];
            var photoHtml = a.photo_url
                ? '<img src="' + esc(a.photo_url) + '" alt="" class="w-28 h-28 md:w-36 md:h-36 rounded-full object-cover flex-shrink-0 border-4 border-white shadow-lg">'
                : '<div class="w-28 h-28 md:w-36 md:h-36 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0 border-4 border-white shadow-lg"><svg class="w-14 h-14 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>';
            var msg = (a.message || '').trim();
            var msgBlock = msg ? '<p class="text-slate-700 md:text-lg leading-relaxed whitespace-pre-wrap">' + esc(msg).replace(/\n/g, '<br>') + '</p>' : '<p class="text-slate-500 italic">ไม่มีข้อความ</p>';
            var metaParts = [];
            if (a.position) metaParts.push(esc(a.position));
            if (a.workplace) metaParts.push(esc(a.workplace));
            if (a.graduation_year) metaParts.push('จบปี ' + esc(a.graduation_year));
            var metaLine = metaParts.length ? '<p class="text-slate-500 text-sm mt-4 font-medium">' + metaParts.join(' · ') + '</p>' : '';
            alumniCarouselCard.innerHTML = '<div class="flex-shrink-0">' + photoHtml + '</div><div class="min-w-0 flex-1">' + msgBlock + metaLine + '</div>';
            var counterEl = document.getElementById('alumni-counter');
            var progressBar = document.getElementById('alumni-progress-bar');
            if (counterEl) counterEl.textContent = (idx + 1) + ' / ' + list.length;
            if (progressBar && list.length > 0) progressBar.style.width = ((idx + 1) / list.length * 100) + '%';
        }
        if (window.__alumniList.length) {
            alumniSection.style.display = 'block';
            renderAlumniSlide();
            if (alumniCarouselNav) alumniCarouselNav.style.display = window.__alumniList.length > 1 ? 'flex' : 'none';
            alumniModalBody.innerHTML = '';
            window.__alumniList.forEach(function(a) {
                alumniModalBody.innerHTML += alumniCardHtml(a, false);
            });
            alumniMoreWrap.style.display = 'block';
        } else {
            alumniSection.style.display = 'none';
            if (alumniCarouselNav) alumniCarouselNav.style.display = 'none';
        }

        // Facilities
        var fl = document.getElementById('facilities-grid'); fl.innerHTML = '';
        if (Array.isArray(d.facilities) && d.facilities.length) {
            d.facilities.forEach(function(f) {
                var typeIcons = { lab: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5', server: 'M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z', coworking: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21' };
                var icon = typeIcons[f.facility_type] || typeIcons.lab;
                var imgHtml = f.image ? '<img src="' + esc(f.image) + '" alt="" class="w-full h-48 object-cover">' : '<div class="w-full h-48 bg-slate-200 flex items-center justify-center facility-placeholder"><svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="' + icon + '"/></svg></div>';
                fl.innerHTML += '<div class="luxury-card rounded-2xl overflow-hidden">' + imgHtml + '<div class="p-6"><h3 class="font-semibold text-slate-800 text-lg">' + esc(f.title || '') + '</h3><p class="text-slate-600 text-sm mt-2 leading-relaxed">' + esc((f.description || '').substring(0, 200)) + '</p></div></div>';
            });
        } else {
            document.getElementById('facilities-empty').classList.remove('hidden');
        }

        // Documents
        var dl = document.getElementById('documents-list'); dl.innerHTML = '';
        if (Array.isArray(d.documents) && d.documents.length) {
            d.documents.forEach(function(doc) {
                dl.innerHTML += '<a href="' + esc(doc.url || '#') + '" target="_blank" rel="noopener" class="doc-row flex items-center gap-4 p-5 rounded-xl glass border border-slate-200 hover:border-[var(--theme)]/50 transition group">' +
                    '<div class="doc-icon flex-shrink-0 w-12 h-12 rounded-lg doc-icon-bg flex items-center justify-center"><svg class="w-6 h-6 doc-icon-fg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>' +
                    '<div class="flex-1 min-w-0"><p class="text-slate-800 font-medium truncate">' + esc(doc.title || '') + '</p><p class="text-slate-500 text-xs mt-1">' + (doc.type || 'PDF') + (doc.size ? ' · ' + doc.size : '') + '</p></div>' +
                    '<svg class="w-5 h-5 text-slate-500 doc-arrow transition flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></a>';
            });
        } else {
            document.getElementById('documents-empty').classList.remove('hidden');
        }

        // วิดีโอแนะนำ (คลิปสนับสนุน AUN-QA)
        var videoSection = document.getElementById('video');
        var videoWrap = document.getElementById('video-wrap');
        if (d.intro_video_url && videoSection && videoWrap) {
            var url = d.intro_video_url;
            if (url.indexOf('youtube.com') !== -1 || url.indexOf('youtu.be') !== -1) {
                var match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([^&\n?#]+)/);
                if (match) {
                    videoWrap.innerHTML = '<iframe src="https://www.youtube.com/embed/' + esc(match[1]) + '" class="w-full h-full absolute inset-0" allowfullscreen></iframe>';
                    videoSection.style.display = 'block';
                }
            } else if (url) {
                videoWrap.innerHTML = '<video controls class="w-full h-full absolute inset-0"><source src="' + esc(url) + '" type="video/mp4"></video>';
                videoSection.style.display = 'block';
            }
        }

        // Footer
        if (d.contact_info) document.getElementById('footer-contact').innerHTML = esc(d.contact_info).replace(/\n/g, '<br>');
    }

    function initReveal() {
        var selectors = '.reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children';
        var els = document.querySelectorAll(selectors);
        var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('visible'); } });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        els.forEach(function(el) { io.observe(el); });
    }

    function initNav() {
        var nav = document.getElementById('navbar');
        window.addEventListener('scroll', function() {
            nav.style.transform = window.scrollY > 300 ? 'translateY(0)' : 'translateY(-100%)';
        });
        document.getElementById('nav-toggle').addEventListener('click', function() {
            document.getElementById('nav-mobile').classList.toggle('hidden');
        });
        document.querySelectorAll('#nav-mobile a').forEach(function(a) {
            a.addEventListener('click', function() { document.getElementById('nav-mobile').classList.add('hidden'); });
        });
    }

    function initScrollTop() {
        var btn = document.getElementById('scroll-top');
        window.addEventListener('scroll', function() { btn.classList.toggle('show', window.scrollY > 600); });
    }

    document.getElementById('news-prev').addEventListener('click', function() { slideNews(-1); });
    document.getElementById('news-next').addEventListener('click', function() { slideNews(1); });

    var alumniModal = document.getElementById('alumni-modal');
    if (alumniModal) {
        document.getElementById('alumni-show-all-btn') && document.getElementById('alumni-show-all-btn').addEventListener('click', function() {
            alumniModal.classList.remove('hidden');
            alumniModal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        });
        document.getElementById('alumni-modal-close') && document.getElementById('alumni-modal-close').addEventListener('click', function() {
            alumniModal.classList.add('hidden');
            alumniModal.classList.remove('flex');
            document.body.style.overflow = '';
        });
        alumniModal.addEventListener('click', function(e) {
            if (e.target === alumniModal) {
                alumniModal.classList.add('hidden');
                alumniModal.classList.remove('flex');
                document.body.style.overflow = '';
            }
        });
    }
    function updateAlumniSlide() {
        var esc = function(s){ if(!s) return ''; var d=document.createElement('div'); d.textContent=s; return d.innerHTML; };
        var card = document.getElementById('alumni-carousel-card');
        if (!card) return;
        var list = window.__alumniList || [];
        var idx = window.__alumniIdx || 0;
        if (list.length === 0) return;
        var a = list[idx];
        var photoHtml = a.photo_url
            ? '<img src="' + esc(a.photo_url) + '" alt="" class="w-28 h-28 md:w-36 md:h-36 rounded-full object-cover flex-shrink-0 border-4 border-white shadow-lg">'
            : '<div class="w-28 h-28 md:w-36 md:h-36 rounded-full bg-slate-200 flex items-center justify-center flex-shrink-0 border-4 border-white shadow-lg"><svg class="w-14 h-14 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>';
        var msg = (a.message || '').trim();
        var msgBlock = msg ? '<p class="text-slate-700 md:text-lg leading-relaxed whitespace-pre-wrap">' + esc(msg).replace(/\n/g, '<br>') + '</p>' : '<p class="text-slate-500 italic">ไม่มีข้อความ</p>';
        var metaParts = []; if (a.position) metaParts.push(esc(a.position)); if (a.workplace) metaParts.push(esc(a.workplace)); if (a.graduation_year) metaParts.push('จบปี ' + esc(a.graduation_year));
        var metaLine = metaParts.length ? '<p class="text-slate-500 text-sm mt-4 font-medium">' + metaParts.join(' · ') + '</p>' : '';
        card.innerHTML = '<div class="flex-shrink-0">' + photoHtml + '</div><div class="min-w-0 flex-1">' + msgBlock + metaLine + '</div>';
        var counterEl = document.getElementById('alumni-counter'); if (counterEl) counterEl.textContent = (idx + 1) + ' / ' + list.length;
        var progressBar = document.getElementById('alumni-progress-bar'); if (progressBar && list.length > 0) progressBar.style.width = ((idx + 1) / list.length * 100) + '%';
    }
    document.getElementById('alumni-prev') && document.getElementById('alumni-prev').addEventListener('click', function() {
        var list = window.__alumniList || [];
        if (list.length === 0) return;
        window.__alumniIdx = (window.__alumniIdx - 1 + list.length) % list.length;
        updateAlumniSlide();
    });
    document.getElementById('alumni-next') && document.getElementById('alumni-next').addEventListener('click', function() {
        var list = window.__alumniList || [];
        if (list.length === 0) return;
        window.__alumniIdx = (window.__alumniIdx + 1) % list.length;
        updateAlumniSlide();
    });

    $.ajax({ url: dataUrl, method: 'GET', dataType: 'json' })
        .done(function(res) {
            if (res.success && res.data) {
                document.getElementById('loading').style.opacity = '0';
                setTimeout(function() {
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('app').classList.remove('hidden');
                    renderData(res.data);
                    initNeuralCanvas();
                    initReveal();
                    initNav();
                    initScrollTop();
                }, 500);
            } else {
                document.getElementById('loading').innerHTML = '<p class="text-red-400 text-center">ไม่พบข้อมูลหลักสูตร</p>';
            }
        })
        .fail(function() {
            document.getElementById('loading').innerHTML = '<p class="text-red-400 text-center">โหลดข้อมูลไม่สำเร็จ</p>';
        });
})();
</script>
</body>
</html>

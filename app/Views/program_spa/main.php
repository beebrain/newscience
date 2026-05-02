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
        a.faculty-card { display: block; text-decoration: none; color: inherit; -webkit-tap-highlight-color: transparent; }
        a.faculty-card:focus-visible { outline: 2px solid var(--theme); outline-offset: 3px; }
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

        /* Skeleton loading screen */
        .skeleton-shell { background: var(--website-bg, #f7f9fb); color: #1e293b; transition: opacity 0.35s ease; }
        .skeleton-block {
            position: relative; overflow: hidden; border-radius: 0.875rem;
            background: linear-gradient(90deg, #e2e8f0 0%, #f1f5f9 48%, #e2e8f0 100%);
            background-size: 240% 100%; animation: skeletonShimmer 1.4s ease-in-out infinite;
        }
        .skeleton-pill { border-radius: 9999px; }
        .skeleton-card { background: rgba(255,255,255,0.82); border: 1px solid rgba(148,163,184,0.24); box-shadow: 0 1px 3px rgba(15,23,42,0.06); }
        @keyframes skeletonShimmer { 0% { background-position: 120% 0; } 100% { background-position: -120% 0; } }
        @media (prefers-reduced-motion: reduce) {
            .skeleton-block { animation: none; }
            .orb, .hero-title-char, .reveal, .reveal-left, .reveal-right, .reveal-scale, .stagger-children > * { transition: none !important; animation: none !important; }
            .spa-topic-icon-ring, .program-info-intro-icon, .program-info-intro-line { animation: none !important; }
        }
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
        .nav-light { background: rgba(255,255,255,0.96); backdrop-filter: blur(12px); }
        .nav-brand-theme { color: var(--theme); }
        .nav-link-theme:hover { color: var(--theme); }
        .hero-title { color: #fff; text-shadow: 0 1px 12px rgba(0,0,0,0.35); }
        .hero-sub { color: #334155; }
        .hero-arrow { color: var(--theme); opacity: 0.9; }
        .elo-num { background: rgba(var(--theme-rgb), 0.15); color: var(--theme); }
        .faculty-role { color: var(--theme); }
        .news-date { color: var(--theme); }
        .activity-thumb { --tw-ring-color: var(--theme); }
        /* อาชีพ — การ์ด (careers_json) */
        .spa-career-item {
            display: flex; gap: 1rem; align-items: flex-start; padding: 1.15rem 1.25rem; border-radius: 1rem;
            background: color-mix(in srgb, var(--theme) 6%, #ffffff); border: 1px solid rgba(var(--theme-rgb), 0.2);
            box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .spa-career-item:hover {
            border-color: rgba(var(--theme-rgb), 0.45); transform: translateY(-3px);
            box-shadow: 0 10px 22px rgba(0,0,0,0.08), 0 0 16px rgba(var(--theme-rgb), 0.08);
        }
        .spa-career-prose a, .spa-tuition-prose a, .spa-study-plan-prose a, .spa-admission-prose a, .spa-main-topic-prose a { color: var(--theme); text-decoration: underline; }
        .spa-study-plan-prose table, .spa-admission-prose table, .spa-main-topic-prose table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        .spa-study-plan-prose th, .spa-study-plan-prose td, .spa-admission-prose th, .spa-admission-prose td, .spa-main-topic-prose th, .spa-main-topic-prose td { border: 1px solid #e2e8f0; padding: 0.6rem 0.75rem; vertical-align: top; }
        .spa-study-plan-prose th, .spa-admission-prose th, .spa-main-topic-prose th { background: color-mix(in srgb, var(--theme) 6%, #fff); color: #334155; font-weight: 600; }
        /* ข้อมูลหลักสูตร — แต่ละหัวข้อเป็น section เต็มความกว้าง */
        .spa-topic-outer {
            scroll-margin-top: 5.5rem;
            border-top: 1px solid rgba(226, 232, 240, 0.85);
        }
        #main-topics-sections > .spa-topic-outer:first-child { border-top: none; }
        #main-topics-sections > .spa-topic-outer:nth-child(odd) {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 55%, #f8fafc 100%);
        }
        #main-topics-sections > .spa-topic-outer:nth-child(even) {
            background: linear-gradient(135deg, rgba(255,255,255,0.98), color-mix(in srgb, var(--theme) 4%, #fff));
        }
        .spa-topic-panel {
            background: rgba(255,255,255,0.82);
            border: 1px solid rgba(226,232,240,0.9);
            box-shadow: 0 24px 70px rgba(15,23,42,0.06);
            border-radius: 1.75rem;
            padding: 1.5rem 1.35rem;
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.5s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.35s;
        }
        @media (min-width: 768px) {
            .spa-topic-panel { padding: 2rem 2.25rem; }
        }
        .spa-topic-layout--center .spa-topic-panel {
            box-shadow: 0 28px 80px rgba(15,23,42,0.08);
        }
        /* เข้าแบบมีทิศทาง + ระยะเวลานุ่มขึ้น */
        .spa-topic-outer.reveal,
        .spa-topic-outer.reveal-left,
        .spa-topic-outer.reveal-right,
        .spa-topic-outer.reveal-scale {
            transition-duration: 0.95s;
            transition-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
        }
        .spa-topic-outer.visible:hover .spa-topic-panel {
            transform: translateY(-3px);
            box-shadow: 0 28px 90px rgba(15,23,42,0.1);
            border-color: rgba(var(--theme-rgb), 0.22);
        }
        .spa-topic-icon-wrap { perspective: 800px; }
        .spa-topic-icon-ring {
            width: 3.75rem; height: 3.75rem;
            border-radius: 1.15rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, color-mix(in srgb, var(--theme) 14%, #fff), color-mix(in srgb, var(--theme) 6%, #fff));
            border: 1px solid rgba(var(--theme-rgb), 0.22);
            color: var(--theme);
            box-shadow: 0 10px 28px rgba(var(--theme-rgb), 0.14), inset 0 1px 0 rgba(255,255,255,0.75);
            animation: spaTopicIconFloat 4.2s ease-in-out infinite;
        }
        @media (min-width: 640px) {
            .spa-topic-icon-ring { width: 4.25rem; height: 4.25rem; border-radius: 1.25rem; }
        }
        .spa-topic-svg { width: 1.65rem; height: 1.65rem; }
        @media (min-width: 640px) {
            .spa-topic-svg { width: 1.85rem; height: 1.85rem; }
        }
        @keyframes spaTopicIconFloat {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            45% { transform: translateY(-5px) rotate(-2deg); }
            55% { transform: translateY(-5px) rotate(2deg); }
        }
        /* Intro — แถบไอคอนตกแต่ง */
        .program-info-intro-decor { margin-top: 2.25rem; }
        .program-info-intro-line {
            height: 2px; flex: 1; min-width: 2rem; max-width: 6rem;
            border-radius: 9999px;
            background: linear-gradient(90deg, transparent, rgba(var(--theme-rgb), 0.35), transparent);
            animation: programIntroLinePulse 3s ease-in-out infinite;
        }
        @keyframes programIntroLinePulse {
            0%, 100% { opacity: 0.45; transform: scaleX(0.92); }
            50% { opacity: 1; transform: scaleX(1); }
        }
        .program-info-intro-icon {
            width: 2.75rem; height: 2.75rem;
            border-radius: 0.85rem;
            display: flex; align-items: center; justify-content: center;
            background: color-mix(in srgb, var(--theme) 10%, #fff);
            border: 1px solid rgba(var(--theme-rgb), 0.14);
            color: var(--theme);
            animation: programIntroIconBob 2.8s ease-in-out infinite;
        }
        .program-info-intro-icon:nth-child(2) { animation-delay: 0.2s; }
        .program-info-intro-icon:nth-child(4) { animation-delay: 0.4s; }
        .program-info-intro-icon:nth-child(6) { animation-delay: 0.6s; }
        @keyframes programIntroIconBob {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .program-info-intro-icon svg { width: 1.1rem; height: 1.1rem; }
        .spa-topic-section__body { max-width: 76rem; }
        .spa-topic-section__body table { overflow: hidden; border-radius: 1rem; }
        .spa-topic-section__body th { color: #64748b; font-weight: 600; }
        .program-info-intro {
            background:
                radial-gradient(circle at 18% 0%, rgba(var(--theme-rgb), 0.1), transparent 34%),
                radial-gradient(circle at 82% 12%, rgba(15,23,42,0.06), transparent 36%),
                linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border-bottom: 1px solid rgba(226,232,240,0.85);
            padding-top: clamp(2rem, 5vw, 3.5rem);
            padding-bottom: clamp(2rem, 5vw, 3.5rem);
        }
        .program-info-kicker { color: var(--theme); letter-spacing: 0.18em; text-transform: uppercase; font-size: 0.72rem; font-weight: 700; }
        .program-info-title { color: #020617; letter-spacing: -0.055em; line-height: 0.96; }
        .program-info-subtitle { color: #64748b; line-height: 1.85; }
        .spa-topic-title {
            font-size: clamp(1.875rem, 4.2vw, 3.15rem);
            font-weight: 700;
            letter-spacing: -0.045em;
            line-height: 1.12;
            color: #0f172a;
            margin: 0;
        }
        .spa-topic-title::after {
            content: '';
            display: block;
            width: 3.25rem;
            height: 0.3rem;
            margin-top: 1rem;
            border-radius: 9999px;
            background: linear-gradient(90deg, var(--theme), color-mix(in srgb, var(--theme) 35%, #94a3b8));
        }
        .spa-topic-title--center::after { margin-left: auto; margin-right: auto; }
        .spa-topic-title--right::after { margin-left: auto; }
        .topic-empty-state {
            border: 1px dashed #cbd5e1;
            background: rgba(248,250,252,0.72);
            color: #94a3b8;
        }
        #curriculum-structure-block .ptb-block { margin-bottom: 1.25rem; }
        #curriculum-structure-block .ptb-block:last-child { margin-bottom: 0; }
        #curriculum-structure-block .ptb-title { font-size: 1.125rem; font-weight: 600; color: var(--theme); margin: 0 0 0.5rem; }
        #curriculum-structure-block .ptb-body { line-height: 1.75; color: #334155; }
        #curriculum-structure-block .ptb-body a { color: var(--theme); }
        /* ตาราง ปรัชญา / วัตถุประสงค์ / คุณลักษณะบัณฑิต (AUN-QA) */
        .spa-overview-aun-table { border: 1px solid rgba(148, 163, 184, 0.45); }
        .spa-overview-aun-table th {
            width: 34%; max-width: 14rem; vertical-align: top; text-align: left; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; background: color-mix(in srgb, var(--theme) 6%, #fff);
            border-bottom: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0; padding: 1rem 1.1rem;
        }
        .spa-overview-aun-table td { vertical-align: top; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.15rem; color: #334155; }
        .spa-overview-aun-table tr:last-child th, .spa-overview-aun-table tr:last-child td { border-bottom: none; }
        .spa-overview-aun-table .spa-ol { margin: 0; padding: 0 0 0 1.2rem; list-style: decimal; }
        .spa-overview-aun-table .spa-ol li { margin: 0.4rem 0; padding-left: 0.2rem; }
        .spa-overview-aun-prose p { margin: 0; line-height: 1.75; }
        .facility-placeholder { color: rgba(var(--theme-rgb), 0.25); }
        .doc-icon-bg { background: rgba(var(--theme-rgb), 0.1); }
        .doc-icon-fg { color: var(--theme); }
        .doc-row:hover .doc-arrow { color: var(--theme); }
        /* รายวิชาแยกตามปี — details/summary */
        #spa-curriculum-by-year details > summary { list-style: none; }
        #spa-curriculum-by-year details > summary::-webkit-details-marker { display: none; }
        .sample-card { background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 14px 32px rgba(15, 23, 42, 0.04); }
        /* Program Structure — การ์ดใหม่ + ไอคอน SVG */
        #structure.structure-band {
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 45%, #fafafa 100%);
        }
        .structure-section-head-icon {
            width: 3rem; height: 3rem; margin: 0 auto 1rem;
            border-radius: 1rem;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(145deg, color-mix(in srgb, var(--theme) 16%, #fff), color-mix(in srgb, var(--theme) 6%, #f8fafc));
            border: 1px solid rgba(var(--theme-rgb), 0.18);
            color: var(--theme);
            box-shadow: 0 12px 28px rgba(var(--theme-rgb), 0.1), inset 0 1px 0 rgba(255,255,255,0.85);
        }
        .structure-section-head-icon svg { width: 1.35rem; height: 1.35rem; }
        .structure-section-title {
            color: #0f172a;
            font-weight: 600;
            letter-spacing: -0.03em;
            font-size: clamp(1.5rem, 3vw, 2rem);
        }
        .structure-section-lead { color: #64748b; line-height: 1.75; max-width: 36rem; margin-left: auto; margin-right: auto; }
        .spa-structure-card {
            position: relative;
            border-radius: 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.95);
            background: linear-gradient(165deg, rgba(255,255,255,0.98) 0%, #ffffff 40%, color-mix(in srgb, var(--theme) 3%, #fff) 100%);
            box-shadow:
                0 1px 0 rgba(255,255,255,0.9) inset,
                0 22px 48px -14px rgba(15, 23, 42, 0.1);
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.45s, border-color 0.35s;
            overflow: hidden;
        }
        .spa-structure-card::before {
            content: '';
            position: absolute; inset: 0; border-radius: inherit; pointer-events: none;
            opacity: 0;
            transition: opacity 0.45s;
            background: radial-gradient(120% 80% at 100% 0%, rgba(var(--theme-rgb), 0.09), transparent 55%);
        }
        .spa-structure-card:hover {
            transform: translateY(-6px);
            border-color: rgba(var(--theme-rgb), 0.22);
            box-shadow:
                0 1px 0 rgba(255,255,255,0.95) inset,
                0 28px 56px -12px rgba(15, 23, 42, 0.14),
                0 0 0 1px rgba(var(--theme-rgb), 0.06);
        }
        .spa-structure-card:hover::before { opacity: 1; }
        .spa-structure-card__inner {
            position: relative;
            z-index: 1;
            padding: 1.5rem 1.35rem 1.4rem;
            box-sizing: border-box;
        }
        @media (min-width: 768px) {
            .spa-structure-card__inner { padding: 1.65rem 1.5rem 1.5rem; }
        }
        .spa-structure-card__header {
            display: flex;
            align-items: flex-start;
            gap: 0.9rem;
            margin-bottom: 1.1rem;
            min-width: 0;
        }
        .spa-structure-card__icon-wrap {
            flex-shrink: 0;
            width: 3rem;
            height: 3rem;
            border-radius: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            background: linear-gradient(145deg, color-mix(in srgb, var(--theme) 20%, #fff), color-mix(in srgb, var(--theme) 8%, #f8fafc));
            border: 1px solid rgba(var(--theme-rgb), 0.2);
            color: var(--theme);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.9), 0 4px 14px rgba(var(--theme-rgb), 0.07);
        }
        .spa-structure-card__icon-wrap .structure-card-svg { width: 1.5rem; height: 1.5rem; }
        .spa-structure-card__title {
            font-size: 1.125rem;
            font-weight: 600;
            letter-spacing: -0.02em;
            color: #0f172a;
            margin: 0;
            padding-top: 0.2rem;
            line-height: 1.35;
            flex: 1;
            min-width: 0;
        }
        .spa-structure-card__list { margin: 0; padding: 0; list-style: none; }
        .spa-structure-card__list-icon {
            flex-shrink: 0;
            width: 1.35rem; height: 1.35rem;
            border-radius: 9999px;
            display: flex; align-items: center; justify-content: center;
            margin-top: 0.1rem;
            background: color-mix(in srgb, var(--theme) 12%, #fff);
            color: var(--theme);
        }
        .spa-structure-card__list-icon svg { width: 0.65rem; height: 0.65rem; }
        @media (prefers-reduced-motion: reduce) {
            .spa-structure-card, .spa-structure-card::before { transition: none !important; }
            .spa-structure-card:hover { transform: none; }
        }
        .sample-section-title { color: #0f172a; font-weight: 500; letter-spacing: -0.01em; }
        .sample-small { font-size: 0.8125rem; line-height: 1.7; color: #475569; }
    </style>
</head>
<body class="min-h-screen antialiased">

<!-- Skeleton loading layout -->
<div id="loading" class="skeleton-shell fixed inset-0 z-[60] overflow-y-auto">
    <div id="skeleton-state" class="min-h-screen">
        <div class="fixed top-0 left-0 right-0 z-10 bg-white/95 backdrop-blur border-b border-slate-200">
            <div class="max-w-7xl mx-auto h-20 px-6 flex items-center justify-between">
                <div class="skeleton-block h-6 w-48"></div>
                <div class="hidden md:flex items-center gap-6">
                    <div class="skeleton-block skeleton-pill h-4 w-16"></div>
                    <div class="skeleton-block skeleton-pill h-4 w-20"></div>
                    <div class="skeleton-block skeleton-pill h-4 w-16"></div>
                    <div class="skeleton-block skeleton-pill h-4 w-20"></div>
                </div>
                <div class="skeleton-block skeleton-pill h-10 w-28"></div>
            </div>
        </div>

        <section class="relative min-h-[760px] pt-20 flex items-center justify-center overflow-hidden bg-[#001a48]">
            <div class="absolute inset-0 opacity-20">
                <div class="skeleton-block h-full w-full rounded-none"></div>
            </div>
            <div class="relative z-10 max-w-5xl mx-auto px-6 text-center w-full">
                <div class="skeleton-block skeleton-pill h-8 w-56 mx-auto mb-8"></div>
                <div class="skeleton-block h-16 md:h-20 w-11/12 max-w-3xl mx-auto mb-5"></div>
                <div class="skeleton-block h-8 w-2/3 max-w-xl mx-auto mb-8"></div>
                <div class="skeleton-block h-5 w-3/4 max-w-2xl mx-auto mb-10"></div>
                <div class="flex flex-wrap justify-center gap-4 mb-12">
                    <div class="skeleton-block h-12 w-36"></div>
                    <div class="skeleton-block h-12 w-40"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-w-3xl mx-auto">
                    <div class="skeleton-card rounded-2xl p-5"><div class="skeleton-block h-8 w-16 mx-auto mb-3"></div><div class="skeleton-block h-3 w-20 mx-auto"></div></div>
                    <div class="skeleton-card rounded-2xl p-5"><div class="skeleton-block h-8 w-16 mx-auto mb-3"></div><div class="skeleton-block h-3 w-20 mx-auto"></div></div>
                    <div class="skeleton-card rounded-2xl p-5"><div class="skeleton-block h-8 w-16 mx-auto mb-3"></div><div class="skeleton-block h-3 w-20 mx-auto"></div></div>
                    <div class="skeleton-card rounded-2xl p-5"><div class="skeleton-block h-8 w-16 mx-auto mb-3"></div><div class="skeleton-block h-3 w-20 mx-auto"></div></div>
                </div>
            </div>
        </section>

        <section class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-6 grid lg:grid-cols-2 gap-12">
                <div class="space-y-5">
                    <div class="skeleton-block h-10 w-64"></div>
                    <div class="skeleton-card rounded-2xl p-6 space-y-4">
                        <div class="skeleton-block h-5 w-full"></div>
                        <div class="skeleton-block h-5 w-11/12"></div>
                        <div class="skeleton-block h-5 w-4/5"></div>
                        <div class="skeleton-block h-24 w-full"></div>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-5">
                    <div class="skeleton-card rounded-2xl p-6"><div class="skeleton-block h-10 w-10 mb-5"></div><div class="skeleton-block h-5 w-3/4 mb-3"></div><div class="skeleton-block h-20 w-full"></div></div>
                    <div class="skeleton-card rounded-2xl p-6"><div class="skeleton-block h-10 w-10 mb-5"></div><div class="skeleton-block h-5 w-3/4 mb-3"></div><div class="skeleton-block h-20 w-full"></div></div>
                    <div class="skeleton-card rounded-2xl p-6"><div class="skeleton-block h-10 w-10 mb-5"></div><div class="skeleton-block h-5 w-3/4 mb-3"></div><div class="skeleton-block h-20 w-full"></div></div>
                    <div class="skeleton-card rounded-2xl p-6"><div class="skeleton-block h-10 w-10 mb-5"></div><div class="skeleton-block h-5 w-3/4 mb-3"></div><div class="skeleton-block h-20 w-full"></div></div>
                </div>
            </div>
        </section>
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
<nav id="navbar" class="fixed top-0 left-0 right-0 z-40 transition-all duration-500">
    <div class="nav-light border-b border-slate-200/80">
        <div class="max-w-7xl mx-auto px-6 flex items-center justify-between h-16">
            <a href="#hero" id="nav-brand" class="font-black text-blue-950 tracking-tight text-sm uppercase nav-brand-theme">หลักสูตร</a>
            <button id="nav-toggle" class="md:hidden text-slate-600 p-2" aria-label="เมนู">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div id="nav-links" class="hidden md:flex items-center gap-8 text-[11px]">
                <a href="#about" class="text-slate-600 hover:opacity-80 transition nav-link-theme">About</a>
                <a href="#structure" id="nav-main-topics" class="text-slate-600 hover:opacity-80 transition nav-link-theme">Structure</a>
                <a href="#faculty" id="nav-faculty" class="text-slate-600 hover:opacity-80 transition nav-link-theme">Faculty</a>
                <a href="#news" id="nav-news" class="text-slate-600 hover:opacity-80 transition nav-link-theme">News</a>
                <a href="#documents" id="nav-documents" class="text-slate-600 hover:opacity-80 transition nav-link-theme">Resources</a>
                <a href="#curriculum-courses" id="nav-curriculum-courses" class="text-slate-600 hover:opacity-80 transition nav-link-theme hidden">Courses</a>
                <a href="#careers" id="nav-careers" class="text-slate-600 hover:opacity-80 transition nav-link-theme hidden">Careers</a>
                <a href="#tuition" id="nav-tuition" class="text-slate-600 hover:opacity-80 transition nav-link-theme hidden">Tuition</a>
                <a href="#admission" id="nav-admission" class="text-slate-600 hover:opacity-80 transition nav-link-theme hidden">Admission</a>
                <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="px-5 py-2 rounded-full bg-[#001a48] text-white text-xs font-semibold shadow-sm hover:opacity-90 transition">Apply Now</a>
            </div>
        </div>
        <div id="nav-mobile" class="hidden md:hidden border-t border-slate-200 px-6 py-4 space-y-3 bg-white/95">
            <a href="#about" class="block text-slate-600 nav-link-theme">เกี่ยวกับ</a>
            <a href="#structure" id="nav-main-topics-mobile" class="block text-slate-600 nav-link-theme">โครงสร้าง</a>
            <a href="#curriculum-courses" id="nav-curriculum-courses-mobile" class="block text-slate-600 nav-link-theme hidden">รายวิชา</a>
            <a href="#careers" id="nav-careers-mobile" class="block text-slate-600 nav-link-theme hidden">อาชีพ</a>
            <a href="#faculty" id="nav-faculty-mobile" class="block text-slate-600 nav-link-theme">คณาจารย์</a>
            <a href="#news" id="nav-news-mobile" class="block text-slate-600 nav-link-theme">ข่าวสาร</a>
            <a href="#activities" id="nav-activities-mobile" class="block text-slate-600 nav-link-theme">กิจกรรม</a>
            <a href="#alumni" id="nav-alumni-mobile" class="block text-slate-600 nav-link-theme">ศิษย์เก่า</a>
            <a href="#facilities" id="nav-facilities-mobile" class="block text-slate-600 nav-link-theme">สิ่งอำนวยความสะดวก</a>
            <a href="#documents" id="nav-documents-mobile" class="block text-slate-600 nav-link-theme">เอกสาร</a>
            <a href="#tuition" id="nav-tuition-mobile" class="block text-slate-600 nav-link-theme hidden">ค่าเล่าเรียน</a>
            <a href="#admission" id="nav-admission-mobile" class="block text-slate-600 nav-link-theme hidden">การรับสมัคร</a>
            <a href="#video" class="block text-slate-600 nav-link-theme">วิดีโอ</a>
            <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="inline-flex px-5 py-2 rounded-full bg-[var(--theme)] text-white text-sm font-semibold">สมัครเรียน</a>
        </div>
    </div>
</nav>

<main id="app" class="hidden">

<!-- ==================== HERO ==================== -->
<section id="hero" class="relative min-h-[640px] md:min-h-[720px] pt-16 flex items-center justify-center overflow-hidden bg-[#001a48]">
    <canvas id="neural-canvas"></canvas>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
    <div class="absolute inset-0 bg-cover bg-center hero-bg-image" id="hero-bg"></div>
    <div class="absolute inset-0 bg-[#001a48]/82"></div>
    <div class="relative z-10 text-center max-w-4xl mx-auto px-6">
        <p id="hero-level" class="inline-block bg-blue-900/70 text-blue-100 border border-white/20 rounded-full px-4 py-1.5 text-[11px] mb-5 opacity-0" style="transition:opacity 1s 0.3s"></p>
        <p id="hero-name-en" class="text-xs md:text-sm text-blue-100/90 font-semibold mb-2 opacity-0" style="transition:opacity 1s 0.8s"></p>
        <h1 id="hero-title" class="text-3xl md:text-4xl lg:text-5xl font-semibold text-white leading-tight mb-4"></h1>
        <p id="hero-degree" class="text-sm md:text-base text-blue-100/95 font-light mb-4 opacity-0" style="transition:opacity 1s 1.2s"></p>
        <p id="hero-description" class="text-xs md:text-sm text-blue-50/85 leading-relaxed max-w-2xl mx-auto mb-8 opacity-0" style="transition:opacity 1s 1.35s"></p>
        <div class="flex flex-wrap gap-4 justify-center opacity-0" id="hero-actions" style="transition:opacity 1s 1.6s">
            <a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="inline-block px-7 py-3 rounded bg-amber-300 text-amber-950 text-xs font-semibold hover:bg-amber-200 transition shadow-sm">Apply Now</a>
            <a href="#structure" class="inline-block px-7 py-3 rounded border border-white/40 text-white text-xs font-semibold hover:bg-white/10 transition">View Curriculum</a>
            <a id="hero-website" href="#" target="_blank" rel="noopener" class="hidden px-7 py-3 rounded border border-white/30 text-white text-xs font-semibold hover:bg-white/10 transition">Program Website</a>
        </div>
    </div>
    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
        <svg class="w-6 h-6 hero-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19 14l-7 7m0 0l-7-7"/></svg>
    </div>
</section>

<!-- ==================== ABOUT & INTRO ==================== -->
<section id="about" class="relative py-24 md:py-32 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid md:grid-cols-12 gap-10 md:gap-16 items-center">
            <div class="md:col-span-5 reveal-left">
                <div class="aspect-square bg-white rounded-lg overflow-hidden relative shadow-sm border border-slate-200">
                    <div id="intro-image" class="absolute inset-0 bg-cover bg-center"></div>
                    <div id="intro-image-fallback" class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-blue-50 to-cyan-100 text-blue-900">
                        <svg class="w-24 h-24 opacity-30" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a5.25 5.25 0 110 10.5 5.25 5.25 0 010-10.5z"/><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15M12 4.5v15"/></svg>
                    </div>
                </div>
            </div>
            <div class="md:col-span-7 md:pl-8 reveal-right">
                <h2 id="intro-title" class="sample-section-title text-2xl md:text-3xl mb-5">Excellence in Education</h2>
                <p id="intro-lead" class="sample-small mb-6"></p>
                <div class="flex items-start gap-4 bg-white p-6 rounded-lg border-l-4 border-amber-300 shadow-sm">
                    <span class="inline-flex h-11 w-11 items-center justify-center rounded-full bg-blue-50 text-[#001a48]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M12 3l7.5 4.5v5.25c0 4.142-3.358 7.5-7.5 7.5s-7.5-3.358-7.5-7.5V7.5L12 3z"/></svg>
                    </span>
                    <div>
                        <h3 id="intro-cert-title" class="text-lg font-semibold text-[#001a48] mb-1">AUN-QA Certified</h3>
                        <p id="intro-cert-text" class="text-xs leading-relaxed text-slate-500">ข้อมูลผลลัพธ์การเรียนรู้และมาตรฐานของหลักสูตรถูกจัดแสดงเพื่อสนับสนุนการประกันคุณภาพและการตัดสินใจของผู้เรียน</p>
                    </div>
                </div>
            </div>
        </div>
        <div id="about-overview-table-wrap" class="spa-overview-aun-table rounded-2xl overflow-hidden bg-white shadow-sm hidden mt-14" role="region" aria-label="สรุปหลักสูตร">
            <table class="w-full border-collapse text-left">
                <tbody id="about-overview-tbody"></tbody>
            </table>
        </div>
    </div>
</section>

<!-- ==================== PROGRAM STRUCTURE ==================== -->
<section id="structure" class="structure-band relative py-24 md:py-28 overflow-hidden">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-14 md:mb-16">
            <div class="structure-section-head-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l9 4.5v6c0 5.25-3.75 9.75-9 10.5-5.25-.75-9-5.25-9-10.5v-6L12 3z"/><path d="M9 12l2 2 4-4"/></svg>
            </div>
            <h2 class="structure-section-title">Program Structure</h2>
            <p class="structure-section-lead mt-4 text-sm md:text-base">A comprehensive curriculum designed to build strong foundations and specialized expertise.</p>
        </div>
        <div id="structure-cards" class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8"></div>
        <div id="about-curriculum" class="mt-16 reveal hidden">
            <h3 class="text-xl font-semibold section-accent mb-6">โครงสร้างหลักสูตร</h3>
            <div id="curriculum-structure-block" class="bg-slate-50 rounded-2xl p-8 text-slate-600 leading-relaxed mb-10 border border-slate-200"></div>
        </div>
        <div id="about-study-plan" class="mt-10 reveal hidden">
            <h3 class="text-xl font-semibold section-accent mb-6">แผนการเรียน</h3>
            <div id="study-plan-block" class="bg-slate-50 rounded-2xl p-8 text-slate-600 leading-relaxed mb-10 spa-study-plan-prose overflow-x-auto border border-slate-200"></div>
        </div>
        <div id="curriculum-courses" class="mt-4 reveal hidden">
            <h3 class="text-xl font-semibold section-accent mb-2">รายวิชาโครงสร้างหลักสูตร</h3>
            <p class="text-sm text-slate-500 mb-6">รายวิชาตามปีการศึกษาและภาคเรียน (จากแผนการเรียนที่บันทึกในระบบผู้ดูแล)</p>
            <div id="spa-curriculum-by-year" class="max-w-4xl"></div>
        </div>
    </div>

    <!-- ข้อมูลหลักสูตร: intro + แต่ละหัวข้อเป็น section แยกเต็มความกว้าง -->
    <div id="main-topics" class="w-full mt-20 hidden">
        <div class="program-info-intro">
            <div class="max-w-7xl mx-auto px-6">
                <p class="program-info-kicker mb-4">Program Information</p>
                <h3 class="program-info-title text-4xl md:text-6xl font-semibold mb-5">ข้อมูลหลักสูตร<br class="hidden md:block">ที่อ่านง่ายขึ้น</h3>
                <p class="program-info-subtitle text-sm md:text-base max-w-2xl">แต่ละหัวข้อแยกเป็น section ของตัวเอง พร้อมไอคอนประกอบและแอนิเมชันเบา ๆ เพื่อให้อ่านไม่น่าเบื่อ</p>
                <div class="program-info-intro-decor flex w-full max-w-3xl flex-wrap items-center justify-center gap-3 sm:gap-4 md:justify-start" aria-hidden="true">
                    <span class="program-info-intro-line hidden md:block flex-1 min-w-[3rem] max-w-[7rem]"></span>
                    <span class="program-info-intro-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>
                    </span>
                    <span class="program-info-intro-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                    </span>
                    <span class="program-info-intro-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </span>
                    <span class="program-info-intro-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
                    </span>
                    <span class="program-info-intro-line hidden md:block flex-1 min-w-[3rem] max-w-[7rem]"></span>
                </div>
            </div>
        </div>
        <div id="main-topics-sections" class="w-full"></div>
    </div>
</section>

<!-- ==================== PLO & LEARNING STANDARDS ==================== -->
<section id="quality" class="relative py-20 bg-slate-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="grid lg:grid-cols-2 gap-12 items-start">
            <div>
                <h2 class="sample-section-title text-2xl md:text-3xl mb-4">PLO & Learning Standards</h2>
                <p class="sample-small">Programme Learning Outcomes & Learning Standards</p>
                <div id="learning-standards-intro-spa" class="text-slate-600 text-sm leading-relaxed mt-6 hidden"></div>
                <div id="plo-mapping-spa" class="mt-6 hidden overflow-x-auto text-xs"></div>
            </div>
            <div>
                <h4 class="text-sm font-semibold text-slate-700 mb-3 hidden" id="learning-standards-heading-spa">มาตรฐานการเรียนรู้</h4>
                <div id="learning-standards-grid-spa" class="space-y-2 mb-6 hidden"></div>
                <h4 class="text-sm font-semibold section-accent mb-3 hidden" id="plo-subheading-spa">PLO / ผลลัพธ์ระดับหลักสูตร</h4>
                <div id="elos-grid" class="space-y-3 stagger-children"></div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== CAREERS (อาชีพ — การ์ดจาก careers_json) ==================== -->
<section id="careers" class="relative py-24 md:py-32 section-bg-tint-1" style="display:none;">
    <div class="max-w-6xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-4 text-center reveal">อาชีพที่สามารถ<span class="section-accent">ประกอบได้</span></h2>
        <p class="text-center text-slate-500 text-sm mb-10 max-w-2xl mx-auto reveal">แนวทางอาชีพที่สอดคล้องกับคุณลักษณะบัณฑิตและผลลัพธ์การเรียนรู้ของหลักสูตร</p>
        <div id="careers-grid-wrap" class="hidden">
            <div id="careers-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        </div>
        <div id="careers-fallback" class="hidden mt-2 text-slate-600 leading-relaxed max-w-4xl mx-auto spa-career-prose"></div>
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

<!-- ==================== TUITION (ค่าเล่าเรียน/ค่าธรรมเนียม) ==================== -->
<section id="tuition" class="relative py-24 md:py-32 section-bg-tint-3" style="display:none;">
    <div class="max-w-4xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-4 text-center reveal">ค่าเล่าเรียน<span class="section-accent">/ ค่าธรรมเนียม</span></h2>
        <p class="text-center text-slate-500 text-sm mb-8 max-w-2xl mx-auto reveal">อัตราและรายละเอียดอ้างอิงตามที่หลักสูตรกำหนด (แก้ไขได้จากแอดมิน)</p>
        <div id="tuition-table-wrap" class="hidden overflow-x-auto reveal rounded-2xl border border-slate-200 bg-white/90 shadow-sm">
            <table class="w-full text-sm text-left text-slate-700 min-w-[320px]">
                <thead><tr class="bg-slate-100 text-slate-800"><th class="px-4 py-3 font-semibold border-b border-slate-200 w-[45%]">รายการ</th><th class="px-4 py-3 font-semibold border-b border-slate-200">จำนวน / รายละเอียด</th></tr></thead>
                <tbody id="tuition-table-body"></tbody>
            </table>
        </div>
        <div id="tuition-fallback" class="hidden mt-6 text-slate-600 leading-relaxed max-w-4xl mx-auto spa-tuition-prose"></div>
    </div>
</section>

<!-- ==================== ADMISSION (การรับสมัคร) ==================== -->
<section id="admission" class="relative py-24 md:py-32 section-bg-tint-4" style="display:none;">
    <div class="max-w-5xl mx-auto px-6">
        <h2 class="section-title text-3xl md:text-4xl font-bold text-slate-800 mb-4 text-center reveal">การ<span class="section-accent">รับสมัคร</span></h2>
        <p class="text-center text-slate-500 text-sm mb-10 max-w-2xl mx-auto reveal">จำนวนที่เปิดรับ · คุณสมบัติผู้เข้าเรียน · สิ่งสนับสนุนการเรียน</p>

        <div id="admission-plan-seats-wrap" class="mb-8 text-center reveal" style="display:none;">
            <div class="inline-flex flex-col items-center justify-center gap-1 px-8 py-5 rounded-2xl bg-white/90 border border-slate-200 shadow-sm">
                <span class="text-xs tracking-[0.25em] uppercase text-slate-500">จำนวนรับตามแผน</span>
                <span id="admission-plan-seats" class="text-3xl md:text-4xl font-bold section-accent"></span>
            </div>
        </div>

        <div id="admission-requirements-wrap" class="mb-8 reveal" style="display:none;">
            <h3 class="text-xl font-semibold section-accent mb-4 text-center">คุณสมบัติของผู้เข้าเรียน</h3>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white/90 shadow-sm">
                <table class="w-full text-sm text-left text-slate-700 min-w-[320px]">
                    <tbody id="admission-requirements-body"></tbody>
                </table>
            </div>
        </div>

        <div id="admission-supports-wrap" class="reveal" style="display:none;">
            <h3 class="text-xl font-semibold section-accent mb-4 text-center">สิ่งสนับสนุนการเรียน</h3>
            <ul id="admission-supports-list" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3 max-w-4xl mx-auto"></ul>
        </div>

        <div id="admission-fallback-wrap" class="mt-8 reveal" style="display:none;">
            <h3 class="text-xl font-semibold section-accent mb-4 text-center">รายละเอียดการรับสมัคร</h3>
            <div id="admission-fallback" class="glass rounded-2xl p-8 text-slate-600 leading-relaxed spa-admission-prose overflow-x-auto"></div>
        </div>
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
        <div class="grid md:grid-cols-4 gap-12 mb-12">
            <div class="md:col-span-2 reveal-left">
                <h3 id="footer-program-name" class="text-xl font-bold section-accent mb-4">หลักสูตร</h3>
                <p id="footer-description" class="text-slate-600 text-sm leading-relaxed max-w-lg mb-6"></p>
                <div id="footer-contact" class="text-slate-600 text-sm leading-relaxed"></div>
            </div>
            <div class="reveal">
                <h3 class="text-sm font-bold section-accent mb-4 uppercase tracking-widest">Quick Links</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="#about" class="text-slate-600 hover:text-[var(--theme)] transition">เกี่ยวกับหลักสูตร</a></li>
                    <li><a href="#structure" class="text-slate-600 hover:text-[var(--theme)] transition">โครงสร้างหลักสูตร</a></li>
                    <li><a href="#faculty" class="text-slate-600 hover:text-[var(--theme)] transition">คณาจารย์</a></li>
                    <li><a href="#documents" class="text-slate-600 hover:text-[var(--theme)] transition">เอกสารดาวน์โหลด</a></li>
                </ul>
            </div>
            <div class="reveal-right">
                <h3 class="text-sm font-bold section-accent mb-4 uppercase tracking-widest">Contact</h3>
                <ul class="space-y-3 text-sm text-slate-600">
                    <li><a id="footer-website" href="#" target="_blank" rel="noopener" class="hidden hover:text-[var(--theme)] transition">เว็บไซต์หลักสูตร</a></li>
                    <li><a href="https://academic.uru.ac.th/smarturu/" target="_blank" rel="noopener" class="hover:text-[var(--theme)] transition">สมัครเรียนออนไลน์</a></li>
                    <li><a href="#admission" class="hover:text-[var(--theme)] transition">ข้อมูลการรับสมัคร</a></li>
                </ul>
            </div>
        </div>
        <div class="text-center text-slate-600 text-sm">
            <p>© <?= date('Y') ?> <span id="footer-copyright-name">Program</span>. All rights reserved.</p>
        </div>
    </div>
</footer>

</main>

<script>
(function() {
    var dataUrl = <?= json_encode($dataUrl) ?>;

    function esc(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
    function hasRichContent(raw) {
        if (raw == null) return false;
        var s = String(raw);
        if (/<(img|table|iframe|video|ul|ol|li)\b/i.test(s)) return true;
        return s.replace(/&nbsp;/gi, ' ').replace(/<br\s*\/?>/gi, '\n').replace(/<[^>]*>/g, '').replace(/\s/g, '').length > 0;
    }
    function renderRichContent(raw) {
        if (raw == null) return '';
        var s = String(raw);
        return /<[^>]+>/.test(s) ? s : esc(s).replace(/\n/g, '<br>');
    }
    function emptyTopicHtml() {
        return '<p class="text-slate-400 italic">ยังไม่มีข้อมูล</p>';
    }
    function listHtml(items, ordered) {
        items = (Array.isArray(items) ? items : []).map(function (x) { return String(x || '').trim(); }).filter(Boolean);
        if (!items.length) return '';
        var tag = ordered ? 'ol' : 'ul';
        var cls = ordered ? 'list-decimal pl-5 space-y-1' : 'list-disc pl-5 space-y-1';
        return '<' + tag + ' class="' + cls + '">' + items.map(function (x) { return '<li>' + esc(x) + '</li>'; }).join('') + '</' + tag + '>';
    }
    function topicContentHtml(body) {
        return hasRichContent(body)
            ? body
            : '<div class="topic-empty-state rounded-2xl px-5 py-4 text-sm">ยังไม่มีข้อมูลในหัวข้อนี้</div>';
    }
    /** ไอคอนประจับหัวข้อ (stroke ตามสีธีมผ่าน currentColor) */
    function topicIconSvg(no) {
        var icons = {
            1: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21h6"/><path d="M10 21v-3a2 2 0 114 0v3"/><path d="M12 3a6 6 0 016 6c0 2.5-1.5 4.5-3 6v1a2 2 0 01-2 2h-2a2 2 0 01-2-2v-1c-1.5-1.5-3-3.5-3-6a6 6 0 016-6z"/></svg>',
            2: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>',
            3: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
            4: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
            5: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>',
            6: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>',
            7: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
            8: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
            9: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
            10: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-8.84 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
            11: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 21h8"/><path d="M12 17v4"/><path d="M7 4h10v5a5 5 0 01-10 0V4z"/><path d="M5 4H3a2 2 0 000 4h2"/><path d="M19 4h2a2 2 0 010 4h-2"/><path d="M9 9h6"/></svg>',
            12: '<svg class="spa-topic-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>'
        };
        var n = Number(no);
        return icons[n] || icons[1];
    }
    function topicIconWrap(no, align) {
        var ac = align === 'center' ? 'flex justify-center w-full mb-6' : (align === 'right' ? 'flex justify-start md:justify-end w-full mb-6' : 'flex justify-start w-full mb-6');
        return '<div class="spa-topic-icon-wrap ' + ac + '" aria-hidden="true"><div class="spa-topic-icon-ring">' + topicIconSvg(no) + '</div></div>';
    }
    /** 1,12 = กึ่งกลาง · คู่ = หัวข้อซ้ายเนื้อหาขวา · คี่(>1) = เนื้อหาซ้ายหัวข้อขวา */
    function topicLayoutKind(no) {
        var n = Number(no);
        if (n === 1 || n === 12) return 'center';
        return (n % 2 === 0) ? 'split' : 'split-reverse';
    }
    function topicRevealClass(kind) {
        if (kind === 'center') return 'spa-topic-outer reveal-scale';
        if (kind === 'split') return 'spa-topic-outer reveal-left';
        return 'spa-topic-outer reveal-right';
    }
    function topicHeadInner(no, title, align) {
        var wrap = 'flex flex-col items-start text-left';
        var titleMod = 'spa-topic-title';
        if (align === 'center') {
            wrap = 'flex flex-col items-center text-center';
            titleMod += ' spa-topic-title--center';
        }
        if (align === 'right') {
            wrap = 'flex flex-col items-start md:items-end text-left md:text-right';
            titleMod += ' spa-topic-title--right';
        }
        return '<div class="' + wrap + '">' + topicIconWrap(no, align) +
            '<h2 class="' + titleMod + '">' + esc(title) + '</h2>' +
            '</div>';
    }
    function topicSectionHtml(no, title, body) {
        var content = topicContentHtml(body);
        var kind = topicLayoutKind(no);
        var bodyCls = 'spa-topic-section__body spa-topic-panel min-w-0 text-sm md:text-[0.95rem] text-slate-600 leading-relaxed spa-main-topic-prose overflow-x-auto';
        var base = topicRevealClass(kind);
        if (kind === 'center') {
            return '<section id="main-topic-' + esc(no) + '" class="' + base + ' spa-topic-layout--center py-20 md:py-28">' +
                '<div class="max-w-7xl mx-auto px-6">' +
                '<div class="max-w-3xl mx-auto">' + topicHeadInner(no, title, 'center') + '</div>' +
                '<div class="' + bodyCls + ' max-w-3xl mx-auto mt-10">' + content + '</div>' +
                '</div></section>';
        }
        var headBlock = '<header class="spa-topic-head md:sticky md:top-28 shrink-0">' + topicHeadInner(no, title, 'left') + '</header>';
        var panelBlock = '<div class="' + bodyCls + '">' + content + '</div>';
        var gridCls = 'grid md:grid-cols-2 gap-10 lg:gap-16 xl:gap-20 items-start';
        if (kind === 'split') {
            return '<section id="main-topic-' + esc(no) + '" class="' + base + ' spa-topic-layout--split py-16 md:py-24">' +
                '<div class="max-w-7xl mx-auto px-6"><div class="' + gridCls + '">' + headBlock + panelBlock + '</div></div></section>';
        }
        return '<section id="main-topic-' + esc(no) + '" class="' + base + ' spa-topic-layout--split-reverse py-16 md:py-24">' +
            '<div class="max-w-7xl mx-auto px-6"><div class="' + gridCls + '">' +
            '<header class="spa-topic-head md:sticky md:top-28 shrink-0 md:order-2">' + topicHeadInner(no, title, 'right') + '</header>' +
            '<div class="' + bodyCls + ' md:order-1">' + content + '</div>' +
            '</div></div></section>';
    }

    var CAREER_ICONS = {
        cpu: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><line x1="9" y1="1" x2="9" y2="4"/><line x1="15" y1="1" x2="15" y2="4"/><line x1="9" y1="20" x2="9" y2="23"/><line x1="15" y1="20" x2="15" y2="23"/><line x1="20" y1="9" x2="23" y2="9"/><line x1="20" y1="14" x2="23" y2="14"/><line x1="1" y1="9" x2="4" y2="9"/><line x1="1" y1="14" x2="4" y2="14"/></svg>',
        chart: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
        search: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
        code: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>',
        users: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>',
        rocket: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"/><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"/></svg>',
        mortar: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 2 2 4 6 4s6-2 6-4v-5"/></svg>',
        target: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>',
        briefcase: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>',
        book: '<svg class="w-8 h-8 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg>'
    };

    var STRUCTURE_LIST_CHECK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
    var STRUCTURE_CARD_SVGS = {
        foundation: '<svg class="structure-card-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>',
        tracks: '<svg class="structure-card-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
        quality: '<svg class="structure-card-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>'
    };

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
        if (/[\u0E00-\u0E7F]/.test(text || '')) {
            el.textContent = text || '';
            return;
        }
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

    function toggleCurriculumCoursesNav(show) {
        var n = document.getElementById('nav-curriculum-courses');
        var nm = document.getElementById('nav-curriculum-courses-mobile');
        if (n) n.classList.toggle('hidden', !show);
        if (nm) nm.classList.toggle('hidden', !show);
    }

    function toggleMainTopicsNav(show) {
        var n = document.getElementById('nav-main-topics');
        var nm = document.getElementById('nav-main-topics-mobile');
        if (n) n.classList.toggle('hidden', !show);
        if (nm) nm.classList.toggle('hidden', !show);
    }

    function toggleCareersNav(show) {
        var n = document.getElementById('nav-careers');
        var nm = document.getElementById('nav-careers-mobile');
        if (n) n.classList.toggle('hidden', !show);
        if (nm) nm.classList.toggle('hidden', !show);
    }

    function toggleTuitionNav(show) {
        var n = document.getElementById('nav-tuition');
        var nm = document.getElementById('nav-tuition-mobile');
        if (n) n.classList.toggle('hidden', !show);
        if (nm) nm.classList.toggle('hidden', !show);
    }

    function toggleAdmissionNav(show) {
        var n = document.getElementById('nav-admission');
        var nm = document.getElementById('nav-admission-mobile');
        if (n) n.classList.toggle('hidden', !show);
        if (nm) nm.classList.toggle('hidden', !show);
    }

    function toggleNavPair(baseId, show) {
        var desktop = document.getElementById('nav-' + baseId);
        var mobile = document.getElementById('nav-' + baseId + '-mobile');
        if (desktop) desktop.classList.toggle('hidden', !show);
        if (mobile) mobile.classList.toggle('hidden', !show);
    }

    /** แสดงรายวิชาตามปี/ภาคจาก curriculum_json (โครงเดียวกับแผนการเรียนใน Admin) */
    function renderSpaCurriculumByYear(d) {
        var section = document.getElementById('curriculum-courses');
        var wrap = document.getElementById('spa-curriculum-by-year');
        if (!wrap || !section) return;
        var plan = d.curriculum;
        if (!Array.isArray(plan) || !plan.length) {
            section.classList.add('hidden');
            wrap.innerHTML = '';
            toggleCurriculumCoursesNav(false);
            return;
        }
        section.classList.remove('hidden');
        toggleCurriculumCoursesNav(true);
        var html = '';
        plan.forEach(function (year, i) {
            var sems = year.semesters || [];
            var semHtml = '';
            sems.forEach(function (sem, si) {
                var courses = sem.courses || [];
                var rows = '';
                courses.forEach(function (c) {
                    rows += '<tr><td class="border border-slate-200 px-2 py-1.5 text-sm">' + esc(c.code || '') + '</td>' +
                        '<td class="border border-slate-200 px-2 py-1.5 text-sm">' + esc(c.name || '') + '</td>' +
                        '<td class="border border-slate-200 px-2 py-1.5 text-sm text-center w-16">' + esc(String(c.credits != null && c.credits !== '' ? c.credits : '—')) + '</td></tr>';
                });
                if (!rows) {
                    rows = '<tr><td colspan="3" class="border border-slate-200 px-2 py-2 text-sm text-slate-500">ยังไม่มีรายวิชาในภาคนี้</td></tr>';
                }
                semHtml += '<div class="mb-4 last:mb-0"><h5 class="font-semibold text-slate-700 text-sm mb-2">' + esc(sem.name || ('ภาคเรียนที่ ' + (si + 1))) + '</h5>' +
                    '<div class="overflow-x-auto rounded-lg border border-slate-200">' +
                    '<table class="w-full border-collapse text-slate-700 min-w-[280px]"><thead><tr class="bg-slate-100 text-xs">' +
                    '<th class="border border-slate-200 px-2 py-1.5 text-left font-semibold">รหัสวิชา</th>' +
                    '<th class="border border-slate-200 px-2 py-1.5 text-left font-semibold">ชื่อวิชา</th>' +
                    '<th class="border border-slate-200 px-2 py-1.5 font-semibold">หน่วยกิต</th></tr></thead><tbody>' + rows + '</tbody></table></div></div>';
            });
            if (!semHtml) {
                semHtml = '<p class="text-sm text-slate-500">ยังไม่มีข้อมูลภาคเรียน</p>';
            }
            var yearLabel = year.year != null && year.year !== '' ? String(year.year) : ('ปีที่ ' + (i + 1));
            var title = year.title || ('ปีการศึกษาที่ ' + (i + 1));
            var credits = year.total_credits != null && year.total_credits !== '' ? ('รวม ' + year.total_credits + ' หน่วยกิต') : '';
            var openAttr = i === 0 ? ' open' : '';
            html += '<details class="group border border-slate-200 rounded-xl mb-3 overflow-hidden bg-white/90 shadow-sm"' + openAttr + '>' +
                '<summary class="cursor-pointer px-4 py-4 flex flex-wrap items-center justify-between gap-2">' +
                '<span class="flex items-center gap-3 min-w-0">' +
                '<span class="inline-flex items-center justify-center min-w-[2.75rem] h-9 px-2 rounded-lg text-sm font-bold text-white shrink-0" style="background:var(--theme)">' + esc(yearLabel) + '</span>' +
                '<span class="min-w-0"><span class="font-semibold text-slate-800 block">' + esc(title) + '</span>' +
                (credits ? '<span class="block text-xs text-slate-500 mt-0.5">' + esc(credits) + '</span>' : '') + '</span></span>' +
                '<svg class="w-5 h-5 text-slate-400 shrink-0 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg></summary>' +
                '<div class="px-4 pb-4 border-t border-slate-100 pt-4">' + semHtml + '</div></details>';
        });
        wrap.innerHTML = html;
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
        var degreeText = d.degree_th || d.degree_en || '';
        var descriptionText = d.description || d.description_en || '';
        document.getElementById('hero-level').textContent = d.level || '';
        document.getElementById('hero-name-en').textContent = d.name_en || '';
        document.getElementById('hero-degree').textContent = degreeText;
        document.getElementById('hero-description').textContent = descriptionText;
        var heroWebsite = document.getElementById('hero-website');
        if (heroWebsite && d.website) {
            heroWebsite.href = d.website;
            heroWebsite.classList.remove('hidden');
            heroWebsite.classList.add('inline-block');
        }
        document.getElementById('hero-level').style.opacity = '1';
        document.getElementById('hero-name-en').style.opacity = d.name_en ? '1' : '0';
        document.getElementById('hero-degree').style.opacity = '1';
        document.getElementById('hero-description').style.opacity = descriptionText ? '1' : '0';
        document.getElementById('hero-actions').style.opacity = '1';
        document.getElementById('nav-brand').textContent = d.name_en || d.name_th || 'หลักสูตร';

        // Intro image and lead copy
        var introImage = document.getElementById('intro-image');
        var introFallback = document.getElementById('intro-image-fallback');
        if (introImage && d.hero_image) {
            introImage.style.backgroundImage = "url('" + esc(d.hero_image) + "')";
            if (introFallback) introFallback.style.display = 'none';
        }
        document.getElementById('intro-title').textContent = d.name_en ? 'Excellence in ' + d.name_en : 'Excellence in Education';
        document.getElementById('intro-lead').textContent = descriptionText || 'หลักสูตรนี้ออกแบบเพื่อพัฒนาผู้เรียนด้วยองค์ความรู้เชิงลึก ทักษะปฏิบัติ และการประยุกต์ใช้จริงตามมาตรฐานคุณภาพการศึกษา';

        // ภาพรวม: ตาราง ปรัชญา / วัตถุประสงค์ (ข้อ) / คุณลักษณะบัณฑิต (ข้อ)
        var overviewWrap = document.getElementById('about-overview-table-wrap');
        var overviewTbody = document.getElementById('about-overview-tbody');
        if (overviewTbody) {
            overviewTbody.innerHTML = '';
            var objList = Array.isArray(d.objectives_list) ? d.objectives_list : [];
            if (!objList.length && d.vision) {
                var vs = String(d.vision);
                if (vs.trim().charAt(0) === '[') {
                    try { var pj = JSON.parse(vs); if (Array.isArray(pj)) objList = pj; } catch (e0) {}
                } else {
                    var parts = vs.split(/\r\n|\n|\r/).map(function (x) { return x.trim(); }).filter(Boolean);
                    objList = parts.length > 1 ? parts : (vs.trim() ? [vs.trim()] : []);
                }
            }
            var gpList = Array.isArray(d.graduate_profile_list) ? d.graduate_profile_list : [];
            if (!gpList.length && d.graduate_profile) {
                var gs = String(d.graduate_profile);
                if (gs.trim().charAt(0) === '[') {
                    try { var gj = JSON.parse(gs); if (Array.isArray(gj)) gpList = gj; } catch (e1) {}
                } else {
                    var gp2 = gs.split(/\r\n|\n|\r/).map(function (x) { return x.trim(); }).filter(Boolean);
                    gpList = gp2.length > 1 ? gp2 : (gs.trim() ? [gs.trim()] : []);
                }
            }
            var hasPhi = d.philosophy && String(d.philosophy).trim() !== '';
            var hasDesc = (d.description || d.description_en) && String(d.description || d.description_en).trim() !== '';
            var hasRow = hasDesc || hasPhi || objList.length > 0 || gpList.length > 0;
            if (hasRow) {
                if (hasDesc) {
                    overviewTbody.innerHTML += '<tr><th scope="row">คำอธิบาย<br class="md:hidden" />หลักสูตร</th><td><div class="spa-overview-aun-prose text-slate-700 text-sm sm:text-base leading-relaxed">' + esc(d.description || d.description_en).replace(/\n/g, '<br>') + '</div></td></tr>';
                }
                if (hasPhi) {
                    overviewTbody.innerHTML += '<tr><th scope="row">ปรัชญา</th><td><div class="spa-overview-aun-prose text-slate-700 text-sm sm:text-base leading-relaxed">' + esc(d.philosophy).replace(/\n/g, '<br>') + '</div></td></tr>';
                }
                if (objList.length) {
                    var ols = objList.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('');
                    overviewTbody.innerHTML += '<tr><th scope="row">วัตถุ<br class="md:hidden" />ประสงค์</th><td><ol class="spa-ol text-sm sm:text-base">' + ols + '</ol></td></tr>';
                }
                if (gpList.length) {
                    var gls = gpList.map(function (t) { return '<li>' + esc(t) + '</li>'; }).join('');
                    overviewTbody.innerHTML += '<tr><th scope="row">คุณลักษณะ<br class="md:hidden" />บัณฑิต</th><td><ol class="spa-ol text-sm sm:text-base">' + gls + '</ol></td></tr>';
                }
            }
            if (overviewWrap) overviewWrap.classList.toggle('hidden', !hasRow);
        }

        // Program Structure summary cards (top-level layout matching the sample)
        var structureCards = document.getElementById('structure-cards');
        if (structureCards) {
            var curriculumYears = Array.isArray(d.curriculum) ? d.curriculum.length : 0;
            var ploCount = Array.isArray(d.elos) ? d.elos.length : 0;
            var staffCount = Array.isArray(d.staff) ? d.staff.length : 0;
            var docCount = Array.isArray(d.documents) ? d.documents.length : 0;
            var structureData = [
                {
                    title: 'Core Foundation',
                    iconKey: 'foundation',
                    items: [
                        d.degree_th || d.degree_en || '',
                        d.level || '',
                        d.credits ? d.credits + ' หน่วยกิต' : ''
                    ]
                },
                {
                    title: 'Applied Tracks',
                    iconKey: 'tracks',
                    items: [
                        d.duration ? 'ระยะเวลาเรียน ' + d.duration + ' ปี' : '',
                        curriculumYears ? 'แผนรายวิชา ' + curriculumYears + ' ปีการศึกษา' : '',
                        d.website ? 'มีเว็บไซต์หลักสูตร' : ''
                    ]
                },
                {
                    title: 'Quality & Resources',
                    iconKey: 'quality',
                    items: [
                        ploCount ? 'PLO / ELO ' + ploCount + ' รายการ' : '',
                        staffCount ? 'คณาจารย์ ' + staffCount + ' คน' : '',
                        docCount ? 'เอกสารดาวน์โหลด ' + docCount + ' รายการ' : ''
                    ]
                }
            ];
            structureCards.innerHTML = structureData.map(function (card) {
                var items = card.items.filter(function (x) { return String(x || '').trim() !== ''; });
                if (!items.length) items = ['รอข้อมูลจากหลักสูตร'];
                var iconSvg = STRUCTURE_CARD_SVGS[card.iconKey] || STRUCTURE_CARD_SVGS.foundation;
                return '<div class="spa-structure-card">' +
                    '<div class="spa-structure-card__inner">' +
                    '<div class="spa-structure-card__header">' +
                    '<div class="spa-structure-card__icon-wrap" aria-hidden="true">' + iconSvg + '</div>' +
                    '<h3 class="spa-structure-card__title">' + esc(card.title) + '</h3>' +
                    '</div>' +
                    '<ul class="spa-structure-card__list space-y-3.5">' + items.map(function (item) {
                        return '<li class="flex items-start gap-3 text-sm text-slate-600 leading-relaxed">' +
                            '<span class="spa-structure-card__list-icon" aria-hidden="true">' + STRUCTURE_LIST_CHECK + '</span>' +
                            '<span>' + esc(item) + '</span></li>';
                    }).join('') + '</ul>' +
                    '</div></div>';
            }).join('');
        }

        // PLO + มาตรฐานการเรียนรู้
        var ls = d.learning_standards || {};
        var introEl = document.getElementById('learning-standards-intro-spa');
        var lsHead = document.getElementById('learning-standards-heading-spa');
        var lsGridSpa = document.getElementById('learning-standards-grid-spa');
        var mapSpa = document.getElementById('plo-mapping-spa');
        var ploSub = document.getElementById('plo-subheading-spa');
        if (introEl) {
            if (ls.intro && String(ls.intro).trim()) {
                introEl.innerHTML = '<p>' + esc(String(ls.intro)).replace(/\n/g, '<br>') + '</p>';
                introEl.classList.remove('hidden');
            } else { introEl.innerHTML = ''; introEl.classList.add('hidden'); }
        }
        if (lsGridSpa) {
            lsGridSpa.innerHTML = '';
            var stds = ls.standards || [];
            if (stds.length) {
                if (lsHead) lsHead.classList.remove('hidden');
                lsGridSpa.classList.remove('hidden');
                stds.forEach(function (st, i) {
                    var code = st.code || ('LS' + (i + 1));
                    var tit = st.title || st.category || '';
                    var det = st.detail || st.summary || '';
                    lsGridSpa.innerHTML += '<div class="p-3 rounded-lg border border-teal-200/60 bg-teal-50/40 text-slate-700 text-sm"><span class="font-bold text-teal-800">' + esc(code) + '</span> ' + esc(tit) + (det ? '<div class="mt-1 text-slate-600">' + esc(det).substring(0, 280) + (det.length > 280 ? '…' : '') + '</div>' : '') + '</div>';
                });
            } else {
                if (lsHead) lsHead.classList.add('hidden');
                lsGridSpa.classList.add('hidden');
            }
        }
        if (mapSpa) {
            var mp = ls.mapping || [];
            if (mp.length) {
                mapSpa.classList.remove('hidden');
                var rows = '';
                mp.forEach(function (m) {
                    rows += '<tr><td class="border border-slate-200 px-2 py-1">' + esc(m.standard_code || '—') + '</td><td class="border border-slate-200 px-2 py-1">' + esc(m.plo_refs || '—') + '</td></tr>';
                });
                mapSpa.innerHTML = '<table class="w-full border-collapse text-slate-700"><thead><tr class="bg-slate-100"><th class="border border-slate-200 px-2 py-1 text-left">มาตรฐาน</th><th class="border border-slate-200 px-2 py-1 text-left">PLO</th></tr></thead><tbody>' + rows + '</tbody></table>';
            } else { mapSpa.innerHTML = ''; mapSpa.classList.add('hidden'); }
        }
        var eg = document.getElementById('elos-grid'); eg.innerHTML = '';
        var hasElos = Array.isArray(d.elos) && d.elos.length;
        var hasLsBlock = (ls.intro && String(ls.intro).trim()) || (ls.standards && ls.standards.length) || (ls.mapping && ls.mapping.length);
        if (ploSub) ploSub.classList.toggle('hidden', !(hasElos && hasLsBlock));
        if (hasElos) {
            d.elos.forEach(function(el, i) {
                var t = (typeof el === 'string') ? el : (el.detail || el.title || el.text || el.name || el.description || '');
                if (!t && el.category) t = el.category;
                eg.innerHTML += '<div class="flex items-start gap-3 p-4 rounded-xl glass"><span class="flex-shrink-0 w-8 h-8 rounded-full elo-num flex items-center justify-center text-sm font-bold">' + (i + 1) + '</span><span class="text-slate-600">' + esc(t) + '</span></div>';
            });
        }

        // โครงสร้างหลักสูตร (ข้อความ) + รายวิชาแยกตามปี (จาก curriculum_json)
        var aboutCurriculum = document.getElementById('about-curriculum');
        var structBlock = document.getElementById('curriculum-structure-block');
        var structText = hasRichContent(d.curriculum_structure) ? String(d.curriculum_structure) : '';
        if (structBlock) {
            if (structText) {
                structBlock.innerHTML = renderRichContent(structText);
            } else {
                structBlock.innerHTML = '';
            }
        }
        if (aboutCurriculum) {
            aboutCurriculum.classList.toggle('hidden', !structText);
        }
        var aboutStudyPlan = document.getElementById('about-study-plan');
        var studyBlock = document.getElementById('study-plan-block');
        var studyText = hasRichContent(d.study_plan) ? String(d.study_plan) : '';
        if (studyBlock) {
            studyBlock.innerHTML = studyText ? renderRichContent(studyText) : '';
        }
        if (aboutStudyPlan) {
            aboutStudyPlan.classList.toggle('hidden', !studyText);
        }
        renderSpaCurriculumByYear(d);

        var mainTopicsSection = document.getElementById('main-topics');
        var mainTopicsSections = document.getElementById('main-topics-sections');
        if (mainTopicsSection && mainTopicsSections) {
            var generalRows = [
                ['ชื่อหลักสูตร', d.name_th || d.name_en || ''],
                ['ชื่อปริญญา/วุฒิการศึกษา', d.degree_th || d.degree_en || ''],
                ['ระดับ', d.level || ''],
                ['จำนวนหน่วยกิตรวม', d.credits ? (d.credits + ' หน่วยกิต') : ''],
                ['ระยะเวลาศึกษา', d.duration ? (d.duration + ' ปี') : ''],
                ['เว็บไซต์หลักสูตร', d.website || '']
            ].filter(function (r) { return String(r[1] || '').trim() !== ''; });
            var generalHtml = generalRows.length
                ? '<table class="w-full text-sm"><tbody>' + generalRows.map(function (r) {
                    return '<tr><th class="text-left w-40 align-top">' + esc(r[0]) + '</th><td>' + esc(r[1]) + '</td></tr>';
                }).join('') + '</tbody></table>'
                : '';

            var objectiveHtml = listHtml(objList, true);
            var elosHtml = Array.isArray(d.elos) && d.elos.length
                ? '<h5 class="font-semibold text-slate-700 mt-4 mb-2">PLO / ELO</h5>' + listHtml(d.elos.map(function (el) {
                    return (typeof el === 'string') ? el : (el.detail || el.title || el.text || el.name || el.description || el.category || '');
                }), true)
                : '';
            var targetHtml = (objectiveHtml ? '<h5 class="font-semibold text-slate-700 mb-2">วัตถุประสงค์</h5>' + objectiveHtml : '') + elosHtml;

            var curriculumHtml = '';
            if (hasRichContent(d.curriculum_structure)) {
                curriculumHtml += '<h5 class="font-semibold text-slate-700 mb-2">โครงสร้างหลักสูตร</h5>' + renderRichContent(d.curriculum_structure);
            }
            if (hasRichContent(d.study_plan)) {
                curriculumHtml += '<h5 class="font-semibold text-slate-700 mt-4 mb-2">แผนการเรียน</h5>' + renderRichContent(d.study_plan);
            }
            if (Array.isArray(d.curriculum) && d.curriculum.length) {
                curriculumHtml += '<p class="text-sm text-slate-500 mt-4">มีข้อมูลรายวิชาแยกตามปี/ภาคเรียน ' + d.curriculum.length + ' ปีการศึกษา</p>';
            }

            var staffHtml = Array.isArray(d.staff) && d.staff.length
                ? listHtml(d.staff.map(function (s) {
                    return [s.name, s.role || s.position].filter(Boolean).join(' - ');
                }), false)
                : '';

            var supportItems = [];
            var ad = d.admission_details || {};
            var sup = (ad.supports && typeof ad.supports === 'object') ? ad.supports : {};
            var supLabels = {
                scholarship: 'ทุนการศึกษา',
                first_term_loan: 'กองทุนยืมเงินค่าเทอมแรกเข้า',
                ksl_loan: 'กองทุนกู้ยืมเพื่อการศึกษา (กยศ.)',
                study_scholarship: 'ทุนการศึกษาระหว่างเรียน',
                entrepreneur_fund: 'ทุนสนับสนุนการเป็นผู้ประกอบการ',
                dormitory: 'หอพักนักศึกษาของมหาวิทยาลัย'
            };
            Object.keys(supLabels).forEach(function (k) { if (sup[k] === true) supportItems.push(supLabels[k]); });
            if (Array.isArray(d.facilities) && d.facilities.length) {
                d.facilities.forEach(function (f) { if (f.title) supportItems.push(f.title); });
            }
            var supportHtml = listHtml(supportItems, false);

            var successHtml = hasRichContent(d.success_outcomes) ? renderRichContent(d.success_outcomes) : '';
            if (!successHtml && Array.isArray(d.news) && d.news.length) {
                successHtml = '<p>มีข่าวสาร/กิจกรรมล่าสุดที่เกี่ยวข้องกับหลักสูตร ' + d.news.length + ' รายการ</p>';
            }

            var contactHtml = hasRichContent(d.contact_info) ? renderRichContent(d.contact_info) : '';

            var topics = [
                [1, 'ปรัชญาและแนวคิด', hasRichContent(d.philosophy) ? renderRichContent(d.philosophy) : ''],
                [2, 'ข้อมูลทั่วไป', generalHtml],
                [3, 'เป้าหมายหลักสูตร', targetHtml],
                [4, 'โครงสร้างหลักสูตร', curriculumHtml],
                [5, 'รายละเอียดวิชา', hasRichContent(d.course_details) ? renderRichContent(d.course_details) : ''],
                [6, 'รูปแบบการเรียนสอน', hasRichContent(d.teaching_methods) ? renderRichContent(d.teaching_methods) : ''],
                [7, 'การวัดและประเมินผล', hasRichContent(d.assessment_methods) ? renderRichContent(d.assessment_methods) : ''],
                [8, 'เกณฑ์การจบ', hasRichContent(d.graduation_requirements) ? renderRichContent(d.graduation_requirements) : ''],
                [9, 'บุคลากร', staffHtml],
                [10, 'การสนับสนุน', supportHtml],
                [11, 'ความสำเร็จ', successHtml],
                [12, 'การติดต่อ', contactHtml]
            ];
            mainTopicsSections.innerHTML = topics.map(function (t) {
                return topicSectionHtml(t[0], t[1], t[2]);
            }).join('');
            mainTopicsSection.classList.remove('hidden');
            toggleMainTopicsNav(true);
        }

        // อาชีพ (การ์ดจาก careers JSON + รายละเอียด HTML career_prospects ถ้ามี)
        var careersSection = document.getElementById('careers');
        var careersGrid = document.getElementById('careers-grid');
        var careersGridWrap = document.getElementById('careers-grid-wrap');
        var careersFallback = document.getElementById('careers-fallback');
        if (careersSection && careersGrid && careersGridWrap && careersFallback) {
            var hasCards = Array.isArray(d.careers) && d.careers.length > 0;
            var rawProspects = d.career_prospects != null ? String(d.career_prospects) : '';
            var hasProspects = hasRichContent(rawProspects);
            if (!hasCards && !hasProspects) {
                careersSection.style.display = 'none';
                toggleCareersNav(false);
                careersGrid.innerHTML = '';
                careersGridWrap.classList.add('hidden');
                careersFallback.classList.add('hidden');
                careersFallback.innerHTML = '';
            } else {
                careersSection.style.display = 'block';
                toggleCareersNav(true);
                careersGrid.innerHTML = '';
                if (hasCards) {
                    careersGridWrap.classList.remove('hidden');
                    d.careers.forEach(function (c, i) {
                        var key = (c.icon && CAREER_ICONS[c.icon]) ? c.icon : 'rocket';
                        var icon = CAREER_ICONS[key] || CAREER_ICONS.rocket;
                        var delay = 'transition-delay:' + (i * 60) + 'ms';
                        careersGrid.innerHTML += '<div class="spa-career-item reveal-scale" style="' + delay + '"><div class="text-[var(--theme)]" aria-hidden="true">' + icon + '</div><div class="min-w-0"><h3 class="font-semibold text-slate-800 text-base leading-snug mb-1">' + esc(c.title || '') + '</h3><p class="text-slate-600 text-sm leading-relaxed">' + esc(c.desc || '') + '</p></div></div>';
                    });
                } else {
                    careersGridWrap.classList.add('hidden');
                }
                if (hasProspects) {
                    careersFallback.classList.remove('hidden');
                    careersFallback.innerHTML = rawProspects;
                } else {
                    careersFallback.classList.add('hidden');
                    careersFallback.innerHTML = '';
                }
            }
        }

        // ค่าเล่าเรียน/ค่าธรรมเนียม (รายการ tuition_items + HTML tuition_fees)
        var tuitionSection = document.getElementById('tuition');
        var tuitionTbody = document.getElementById('tuition-table-body');
        var tuitionTableWrap = document.getElementById('tuition-table-wrap');
        var tuitionHtmlEl = document.getElementById('tuition-fallback');
        if (tuitionSection && tuitionTbody && tuitionTableWrap && tuitionHtmlEl) {
            var hasTuitionRows = Array.isArray(d.tuition_items) && d.tuition_items.length > 0;
            var rawTuitionHtml = d.tuition_fees != null ? String(d.tuition_fees) : '';
            var hasTuitionHtml = hasRichContent(rawTuitionHtml);
            if (!hasTuitionRows && !hasTuitionHtml) {
                tuitionSection.style.display = 'none';
                toggleTuitionNav(false);
                tuitionTbody.innerHTML = '';
                tuitionTableWrap.classList.add('hidden');
                tuitionHtmlEl.classList.add('hidden');
                tuitionHtmlEl.innerHTML = '';
            } else {
                tuitionSection.style.display = 'block';
                toggleTuitionNav(true);
                tuitionTbody.innerHTML = '';
                if (hasTuitionRows) {
                    tuitionTableWrap.classList.remove('hidden');
                    d.tuition_items.forEach(function (row) {
                        var note = row.note ? '<p class="text-slate-500 text-xs mt-1">' + esc(row.note) + '</p>' : '';
                        tuitionTbody.innerHTML += '<tr class="border-b border-slate-100"><td class="px-4 py-3 align-top font-medium text-slate-800">' + esc(row.label || '') + '</td><td class="px-4 py-3 align-top"><span class="text-slate-800">' + esc(row.amount || '') + '</span>' + note + '</td></tr>';
                    });
                } else {
                    tuitionTableWrap.classList.add('hidden');
                }
                if (hasTuitionHtml) {
                    tuitionHtmlEl.classList.remove('hidden');
                    tuitionHtmlEl.innerHTML = rawTuitionHtml;
                } else {
                    tuitionHtmlEl.classList.add('hidden');
                    tuitionHtmlEl.innerHTML = '';
                }
            }
        }

        // Faculty
        var facultySection = document.getElementById('faculty');
        var fg = document.getElementById('faculty-grid'); fg.innerHTML = '';
        if (Array.isArray(d.staff) && d.staff.length) {
            if (facultySection) facultySection.style.display = 'block';
            toggleNavPair('faculty', true);
            d.staff.forEach(function(s) {
                var img = s.image ? '<img src="' + esc(s.image) + '" alt="" class="w-full h-full object-cover">' : '<div class="w-full h-full bg-slate-200 flex items-center justify-center"><svg class="w-12 h-12 text-slate-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg></div>';
                var cvUrl = (s.cv_url || '').trim();
                var overlay = '<div class="faculty-overlay flex flex-col justify-end p-3"><div class="faculty-info"><p class="font-semibold text-slate-800 text-xs leading-tight">' + esc(s.name) + '</p><p class="text-[10px] mt-0.5 faculty-role">' + esc(s.role || s.position) + '</p></div></div>';
                var cardCls = 'faculty-card luxury-card rounded-2xl overflow-hidden aspect-square max-w-[180px] w-full mx-auto relative group ' + (cvUrl ? 'cursor-pointer' : 'cursor-default');
                if (cvUrl) {
                    var label = 'CV ' + (s.name || '').trim();
                    fg.innerHTML += '<a class="' + cardCls + '" href="' + esc(cvUrl) + '" target="_blank" rel="noopener noreferrer" title="ดู CV" aria-label="' + esc(label) + '">' + img + overlay + '</a>';
                } else {
                    fg.innerHTML += '<div class="' + cardCls + '">' + img + overlay + '</div>';
                }
            });
        } else {
            if (facultySection) facultySection.style.display = 'none';
            toggleNavPair('faculty', false);
        }

        // News carousel
        var newsSection = document.getElementById('news');
        var nt = document.getElementById('news-track'); nt.innerHTML = '';
        if (Array.isArray(d.news) && d.news.length) {
            if (newsSection) newsSection.style.display = 'block';
            toggleNavPair('news', true);
            newsTotal = d.news.length;
            d.news.forEach(function(n) {
                var img = n.image_url || n.thumbnail || '';
                var imgHtml = img ? '<img src="' + esc(img) + '" alt="" class="w-full h-48 object-cover">' : '<div class="w-full h-48 bg-slate-200"></div>';
                nt.innerHTML += '<div class="flex-shrink-0 w-full md:w-1/2 lg:w-1/3 px-3"><div class="luxury-card rounded-2xl overflow-hidden h-full">' + imgHtml + '<div class="p-5"><span class="text-xs news-date">' + esc(n.date || '') + '</span><h3 class="font-semibold text-slate-800 mt-2 line-clamp-2">' + esc(n.title || n.title_th || '') + '</h3><p class="text-slate-600 text-sm mt-2 line-clamp-2">' + esc((n.excerpt || '').substring(0, 120)) + '</p></div></div></div>';
            });
        } else {
            if (newsSection) newsSection.style.display = 'none';
            toggleNavPair('news', false);
        }

        // Activities masonry
        var activitiesSection = document.getElementById('activities');
        var ag = document.getElementById('activities-grid'); ag.innerHTML = '';
        if (Array.isArray(d.activities) && d.activities.length) {
            if (activitiesSection) activitiesSection.style.display = 'block';
            toggleNavPair('activities', true);
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
            if (activitiesSection) activitiesSection.style.display = 'none';
            toggleNavPair('activities', false);
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
            toggleNavPair('alumni', true);
            renderAlumniSlide();
            if (alumniCarouselNav) alumniCarouselNav.style.display = window.__alumniList.length > 1 ? 'flex' : 'none';
            alumniModalBody.innerHTML = '';
            window.__alumniList.forEach(function(a) {
                alumniModalBody.innerHTML += alumniCardHtml(a, false);
            });
            alumniMoreWrap.style.display = 'block';
        } else {
            alumniSection.style.display = 'none';
            toggleNavPair('alumni', false);
            if (alumniCarouselNav) alumniCarouselNav.style.display = 'none';
        }

        // Facilities
        var facilitiesSection = document.getElementById('facilities');
        var fl = document.getElementById('facilities-grid'); fl.innerHTML = '';
        if (Array.isArray(d.facilities) && d.facilities.length) {
            if (facilitiesSection) facilitiesSection.style.display = 'block';
            toggleNavPair('facilities', true);
            d.facilities.forEach(function(f) {
                var typeIcons = { lab: 'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5', server: 'M21.75 17.25v-.228a4.5 4.5 0 00-.12-1.03l-2.268-9.64a3.375 3.375 0 00-3.285-2.602H7.923a3.375 3.375 0 00-3.285 2.602l-2.268 9.64a4.5 4.5 0 00-.12 1.03v.228m19.5 0a3 3 0 01-3 3H5.25a3 3 0 01-3-3m19.5 0a3 3 0 00-3-3H5.25a3 3 0 00-3 3m16.5 0h.008v.008h-.008v-.008zm-3 0h.008v.008h-.008v-.008z', coworking: 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3H21m-3.75 3H21' };
                var icon = typeIcons[f.facility_type] || typeIcons.lab;
                var imgHtml = f.image ? '<img src="' + esc(f.image) + '" alt="" class="w-full h-48 object-cover">' : '<div class="w-full h-48 bg-slate-200 flex items-center justify-center facility-placeholder"><svg class="w-16 h-16" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="' + icon + '"/></svg></div>';
                fl.innerHTML += '<div class="luxury-card rounded-2xl overflow-hidden">' + imgHtml + '<div class="p-6"><h3 class="font-semibold text-slate-800 text-lg">' + esc(f.title || '') + '</h3><p class="text-slate-600 text-sm mt-2 leading-relaxed">' + esc((f.description || '').substring(0, 200)) + '</p></div></div>';
            });
        } else {
            if (facilitiesSection) facilitiesSection.style.display = 'none';
            toggleNavPair('facilities', false);
        }

        // Documents
        var documentsSection = document.getElementById('documents');
        var dl = document.getElementById('documents-list'); dl.innerHTML = '';
        if (Array.isArray(d.documents) && d.documents.length) {
            if (documentsSection) documentsSection.style.display = 'block';
            toggleNavPair('documents', true);
            d.documents.forEach(function(doc) {
                dl.innerHTML += '<a href="' + esc(doc.url || '#') + '" target="_blank" rel="noopener" class="doc-row flex items-center gap-4 p-5 rounded-xl glass border border-slate-200 hover:border-[var(--theme)]/50 transition group">' +
                    '<div class="doc-icon flex-shrink-0 w-12 h-12 rounded-lg doc-icon-bg flex items-center justify-center"><svg class="w-6 h-6 doc-icon-fg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg></div>' +
                    '<div class="flex-1 min-w-0"><p class="text-slate-800 font-medium truncate">' + esc(doc.title || '') + '</p><p class="text-slate-500 text-xs mt-1">' + (doc.type || 'PDF') + (doc.size ? ' · ' + doc.size : '') + '</p></div>' +
                    '<svg class="w-5 h-5 text-slate-500 doc-arrow transition flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg></a>';
            });
        } else {
            if (documentsSection) documentsSection.style.display = 'none';
            toggleNavPair('documents', false);
        }

        // การรับสมัคร (admission) — แสดงถ้ามี plan_seats, requirements, หรือ supports (มี default ทั้ง 6)
        (function renderAdmission() {
            var sec       = document.getElementById('admission');
            var planWrap  = document.getElementById('admission-plan-seats-wrap');
            var planEl    = document.getElementById('admission-plan-seats');
            var reqWrap   = document.getElementById('admission-requirements-wrap');
            var reqBody   = document.getElementById('admission-requirements-body');
            var supWrap   = document.getElementById('admission-supports-wrap');
            var supList   = document.getElementById('admission-supports-list');
            var fallbackWrap = document.getElementById('admission-fallback-wrap');
            var fallbackEl = document.getElementById('admission-fallback');
            if (!sec || !planWrap || !reqWrap || !supWrap) return;

            var ad = d.admission_details || null;
            var rawAdmissionInfo = d.admission_info != null ? String(d.admission_info) : '';
            var hasAdmissionInfo = hasRichContent(rawAdmissionInfo);
            if (!ad || typeof ad !== 'object') {
                if (!hasAdmissionInfo) {
                    sec.style.display = 'none';
                    toggleAdmissionNav(false);
                    return;
                }
                sec.style.display = 'block';
                toggleAdmissionNav(true);
                planWrap.style.display = 'none';
                reqWrap.style.display = 'none';
                supWrap.style.display = 'none';
                if (fallbackWrap && fallbackEl) {
                    fallbackEl.innerHTML = renderRichContent(rawAdmissionInfo);
                    fallbackWrap.style.display = 'block';
                }
                return;
            }

            var planSeats = (ad.plan_seats || '').trim();
            var req       = (ad.requirements && typeof ad.requirements === 'object') ? ad.requirements : {};
            var sup       = (ad.supports && typeof ad.supports === 'object') ? ad.supports : {};

            var reqLabels = [
                ['study_plan', 'แผนการเรียน'],
                ['mor_kor_2_url', 'มคอ 2. ฉบับย่อ'],
                ['english_grade', 'ผลการเรียนเฉลี่ยวิชาภาษาอังกฤษ'],
                ['selection_criteria', 'เกณฑ์การคัดเลือก'],
                ['tuition_per_term', 'ค่าเทอม'],
                ['duration', 'ระยะเวลาเรียน'],
                ['credits_note', 'จำนวนหน่วยกิต'],
                ['program_type', 'ประเภทการศึกษา']
            ];
            var supLabels = [
                ['scholarship', 'ทุนการศึกษา'],
                ['first_term_loan', 'กองทุนยืมเงินค่าเทอมแรกเข้า'],
                ['ksl_loan', 'กองทุนกู้ยืมเพื่อการศึกษา (กยศ.)'],
                ['study_scholarship', 'ทุนการศึกษาระหว่างเรียน'],
                ['entrepreneur_fund', 'ทุนสนับสนุนการเป็นผู้ประกอบการ'],
                ['dormitory', 'หอพักนักศึกษาของมหาวิทยาลัย']
            ];

            var hasPlan = planSeats !== '';
            var hasReq  = reqLabels.some(function (p) { return (req[p[0]] || '').trim() !== ''; });
            var enabledSup = supLabels.filter(function (p) { return sup[p[0]] === true; });
            var hasSup  = enabledSup.length > 0;

            if (!hasPlan && !hasReq && !hasSup && !hasAdmissionInfo) {
                sec.style.display = 'none';
                toggleAdmissionNav(false);
                return;
            }
            sec.style.display = 'block';
            toggleAdmissionNav(true);

            if (hasPlan) { planEl.textContent = planSeats; planWrap.style.display = 'block'; }
            else         { planWrap.style.display = 'none'; }

            if (hasReq) {
                var rows = '';
                reqLabels.forEach(function (p) {
                    var v = (req[p[0]] || '').trim();
                    if (v === '') return;
                    var cell = v;
                    if (p[0] === 'mor_kor_2_url') {
                        cell = '<a href="' + esc(v) + '" target="_blank" rel="noopener" class="section-accent underline">เปิดเอกสาร</a>';
                    } else {
                        cell = esc(v);
                    }
                    rows += '<tr class="border-b border-slate-100 last:border-0"><th scope="row" class="px-4 py-3 align-top font-medium text-slate-800 w-[45%] bg-slate-50/60">' + esc(p[1]) + '</th><td class="px-4 py-3 align-top">' + cell + '</td></tr>';
                });
                reqBody.innerHTML = rows;
                reqWrap.style.display = 'block';
            } else { reqWrap.style.display = 'none'; }

            if (hasSup) {
                supList.innerHTML = enabledSup.map(function (p) {
                    return '<li class="flex items-center gap-2 px-4 py-3 rounded-xl bg-white/90 border border-slate-200 shadow-sm text-slate-700 text-sm"><svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg><span>' + esc(p[1]) + '</span></li>';
                }).join('');
                supWrap.style.display = 'block';
            } else { supWrap.style.display = 'none'; }
            if (fallbackWrap && fallbackEl) {
                if (hasAdmissionInfo) {
                    fallbackEl.innerHTML = renderRichContent(rawAdmissionInfo);
                    fallbackWrap.style.display = 'block';
                } else {
                    fallbackEl.innerHTML = '';
                    fallbackWrap.style.display = 'none';
                }
            }
        })();

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
        var footerName = d.name_th || d.name_en || 'หลักสูตร';
        document.getElementById('footer-program-name').textContent = footerName;
        document.getElementById('footer-copyright-name').textContent = footerName;
        document.getElementById('footer-description').textContent = descriptionText || '';
        if (d.contact_info) document.getElementById('footer-contact').innerHTML = esc(d.contact_info).replace(/\n/g, '<br>');
        var footerWebsite = document.getElementById('footer-website');
        if (footerWebsite && d.website) {
            footerWebsite.href = d.website;
            footerWebsite.classList.remove('hidden');
        }
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
            nav.classList.toggle('shadow-md', window.scrollY > 16);
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

    function showLoadError(message) {
        var loading = document.getElementById('loading');
        loading.style.opacity = '1';
        loading.style.display = 'block';
        loading.innerHTML = '<div class="min-h-screen flex items-center justify-center px-6 bg-slate-50">' +
            '<div class="max-w-md w-full rounded-3xl bg-white border border-slate-200 shadow-sm p-8 text-center">' +
            '<div class="mx-auto mb-5 w-14 h-14 rounded-full bg-red-50 text-red-600 flex items-center justify-center">' +
            '<svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>' +
            '</div>' +
            '<h1 class="text-xl font-bold text-slate-900 mb-2">โหลดข้อมูลหลักสูตรไม่สำเร็จ</h1>' +
            '<p class="text-slate-600 text-sm leading-relaxed mb-6">' + esc(message || 'กรุณาลองใหม่อีกครั้ง') + '</p>' +
            '<button id="program-retry" type="button" class="px-6 py-3 rounded-full bg-[var(--theme)] text-white font-semibold hover:opacity-90 transition">ลองโหลดอีกครั้ง</button>' +
            '</div></div>';
        document.getElementById('program-retry').addEventListener('click', function () {
            window.location.reload();
        });
    }

    function finishLoading(data) {
        renderData(data);
        document.getElementById('app').classList.remove('hidden');
        initNeuralCanvas();
        initReveal();
        initNav();
        initScrollTop();

        var loading = document.getElementById('loading');
        loading.style.opacity = '0';
        setTimeout(function() {
            loading.style.display = 'none';
        }, 360);
    }

    $.ajax({ url: dataUrl, method: 'GET', dataType: 'json' })
        .done(function(res) {
            if (res.success && res.data) {
                finishLoading(res.data);
            } else {
                showLoadError((res && res.message) || 'ไม่พบข้อมูลหลักสูตร');
            }
        })
        .fail(function() {
            showLoadError('ระบบไม่สามารถเชื่อมต่อกับข้อมูลหลักสูตรได้ในขณะนี้');
        });
})();
</script>
</body>
</html>

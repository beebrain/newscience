<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'ระบบรับสมัครออนไลน์') ?> — งานสัปดาห์วิทยาศาสตร์ 2569</title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>?v=<?= (defined('FCPATH') && is_file(FCPATH . 'assets/css/fonts.css')) ? filemtime(FCPATH . 'assets/css/fonts.css') : '1' ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= base_url('assets/css/scienceweek-kids.css') ?>?v=<?= time() ?>">
    <style>
        :root {
            --sw-navy:   #0f1b4c;
            --sw-indigo: #1a237e;
            --sw-blue:   #1565c0;
            --sw-teal:   #00897b;
            --sw-teal-l: #00bfa5;
            --sw-bg:     #f0f4f8;
            --sw-card:   #ffffff;
            --sw-text:   #1a202c;
            --sw-muted:  #64748b;
            --sw-border: #e2e8f0;
            --sw-red:    #e53e3e;
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Sarabun', 'Noto Sans Thai', Tahoma, sans-serif;
            background: var(--sw-bg);
            color: var(--sw-text);
            font-size: 15px;
            line-height: 1.65;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Header ──────────────────────────────── */
        .sw-header {
            background: linear-gradient(135deg, var(--sw-navy) 0%, var(--sw-indigo) 50%, var(--sw-blue) 100%);
            color: #fff;
            padding: 0;
            position: relative;
            overflow: hidden;
        }
        .sw-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 50%, rgba(0,191,165,.18) 0%, transparent 55%),
                radial-gradient(circle at 85% 20%, rgba(66,165,245,.15) 0%, transparent 45%);
            pointer-events: none;
        }
        .sw-header-inner {
            position: relative;
            padding: 1.5rem 0 1.4rem;
        }
        .sw-header-badge {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            background: rgba(0,191,165,.25);
            border: 1px solid rgba(0,191,165,.4);
            border-radius: 20px;
            padding: .2rem .75rem;
            font-size: .75rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #a7f3d0;
            margin-bottom: .6rem;
        }
        .sw-header-badge::before { content: '●'; font-size: .5rem; color: var(--sw-teal-l); }
        .sw-header-brand {
            display: flex;
            align-items: flex-start;
            gap: .85rem;
        }
        .sw-header-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
            flex-shrink: 0;
            background: rgba(255,255,255,.95);
            border-radius: 10px;
            padding: 4px;
        }
        .sw-header h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 .25rem;
            letter-spacing: -.01em;
            line-height: 1.3;
        }
        .sw-header .subtitle {
            font-size: .82rem;
            opacity: .75;
            font-weight: 300;
        }
        .sw-header .nav-links {
            display: flex;
            gap: 1rem;
            margin-top: .75rem;
            flex-wrap: wrap;
        }
        .sw-header .nav-links a {
            color: rgba(255,255,255,.75);
            font-size: .82rem;
            text-decoration: none;
            transition: color .15s;
        }
        .sw-header .nav-links a:hover { color: #fff; }
        .sw-header .nav-links a.active { color: #fff; font-weight: 600; }

        /* ── Cards & Layout ──────────────────────── */
        .sw-card {
            background: var(--sw-card);
            border: 1px solid var(--sw-border);
            border-radius: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,.06), 0 4px 20px rgba(0,0,0,.05);
        }
        .sw-card-header {
            padding: 1.1rem 1.5rem;
            border-bottom: 1px solid var(--sw-border);
            border-radius: 14px 14px 0 0;
            background: linear-gradient(90deg, var(--sw-indigo) 0%, var(--sw-blue) 100%);
            color: #fff;
        }
        .sw-card-header h4 { margin: 0; font-size: 1.05rem; font-weight: 700; }
        .sw-card-header small { opacity: .75; font-size: .8rem; }
        .sw-card-body { padding: 1.5rem; }

        /* ── Competition cards (index) ────────────── */
        .comp-card {
            border: 1.5px solid var(--sw-border);
            border-radius: 14px;
            transition: transform .18s, box-shadow .18s, border-color .18s;
            cursor: pointer;
            background: #fff;
        }
        .comp-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(26,35,126,.15);
            border-color: var(--sw-teal-l);
        }

        /* ── Form elements ────────────────────────── */
        .form-label {
            font-weight: 600;
            font-size: .85rem;
            color: var(--sw-text);
            margin-bottom: .35rem;
        }
        .form-control, .form-select {
            border: 1.5px solid var(--sw-border);
            border-radius: 8px;
            font-size: .9rem;
            padding: .55rem .9rem;
            transition: border-color .15s, box-shadow .15s;
            background: #f8fafc;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--sw-teal-l);
            box-shadow: 0 0 0 3px rgba(0,191,165,.15);
            background: #fff;
        }
        .form-control.form-control-sm { padding: .45rem .8rem; font-size: .875rem; }

        /* section divider */
        .sw-section {
            display: flex;
            align-items: center;
            gap: .75rem;
            margin: 1.5rem 0 1rem;
        }
        .sw-section-num {
            width: 28px; height: 28px;
            background: var(--sw-indigo);
            color: #fff;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .8rem; font-weight: 700;
            flex-shrink: 0;
        }
        .sw-section h5 {
            margin: 0;
            font-size: .95rem;
            font-weight: 700;
            color: var(--sw-indigo);
        }
        .sw-section-line {
            flex: 1;
            height: 1px;
            background: var(--sw-border);
        }

        /* participant blocks */
        .participant-block {
            background: #f8fafc;
            border-radius: 10px;
            padding: 1rem 1.1rem;
            margin-bottom: .65rem;
            border: 1.5px solid var(--sw-border);
            position: relative;
        }
        .participant-block::before {
            content: '';
            position: absolute;
            left: 0; top: 8px; bottom: 8px;
            width: 3px;
            background: var(--sw-blue);
            border-radius: 0 2px 2px 0;
        }
        .reserve-block::before { background: var(--sw-muted); }
        .participant-num {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px;
            background: var(--sw-blue);
            color: #fff; border-radius: 50%;
            font-size: .72rem; font-weight: 700;
            margin-right: .4rem;
        }
        .reserve-block .participant-num { background: var(--sw-muted); }

        /* required mark */
        .required-mark { color: var(--sw-red); }

        /* level radio */
        .level-radio-group { display: flex; flex-wrap: wrap; gap: .6rem; }
        .level-radio-label {
            display: flex; align-items: center; gap: .5rem;
            padding: .55rem 1.1rem;
            border: 1.5px solid var(--sw-border);
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: .9rem;
            transition: border-color .15s, background .15s;
        }
        .level-radio-label:hover { border-color: var(--sw-blue); background: #eef2ff; }
        input[type=radio]:checked + .level-radio-label {
            border-color: var(--sw-teal-l);
            background: #e8faf7;
            color: var(--sw-teal);
        }
        input[type=radio] { display: none; }

        /* submit btn */
        .btn-sw-submit {
            background: linear-gradient(135deg, var(--sw-teal) 0%, var(--sw-teal-l) 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: .75rem 2.5rem;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: .02em;
            box-shadow: 0 4px 14px rgba(0,150,136,.35);
            transition: transform .15s, box-shadow .15s;
        }
        .btn-sw-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(0,150,136,.45);
            color: #fff;
        }

        /* badges */
        .badge-level { font-size: .78rem; font-weight: 600; }
        .badge-cap {
            background: #e0f2f1;
            color: var(--sw-teal);
            border-radius: 20px;
            padding: .2rem .65rem;
            font-size: .75rem;
            font-weight: 600;
        }
        .badge-cap.full { background: #fef2f2; color: var(--sw-red); }

        /* doc button */
        .btn-doc {
            display: inline-flex; align-items: center; gap: .35rem;
            padding: .35rem .85rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: .82rem;
            font-weight: 500;
            color: var(--sw-muted);
            background: #f8fafc;
            text-decoration: none;
            transition: border-color .15s, color .15s, background .15s;
        }
        .btn-doc:hover {
            border-color: var(--sw-blue);
            color: var(--sw-blue);
            background: #eef2ff;
        }

        /* footer */
        footer {
            background: var(--sw-navy);
            color: rgba(255,255,255,.55);
            padding: 1.25rem 0;
            font-size: .8rem;
            margin-top: 3rem;
        }
        footer a { color: rgba(255,255,255,.7); text-decoration: none; }

        /* alerts */
        .alert { border-radius: 10px; border: 0; }
        .alert-info { background: #e8f4fd; color: #1565c0; }
        .alert-danger { background: #fef2f2; color: #9b2335; }
        .alert-warning { background: #fffbeb; color: #92400e; border-left: 3px solid #f59e0b; }

        /* invalid feedback */
        .invalid-feedback { font-size: .8rem; }
    </style>
</head>
<body class="kids-theme">
<header class="sw-header">
    <div class="container sw-header-inner">
        <div class="sw-header-badge">Science Week 2026 · มรภ.อุตรดิตถ์</div>
        <a href="<?= base_url('scienceweek') ?>" class="sw-header-brand text-white text-decoration-none">
            <img src="<?= esc(favicon_url()) ?>" alt="โลโก้คณะวิทยาศาสตร์และเทคโนโลยี" class="sw-header-logo" width="48" height="48">
            <div>
                <h1>งานสัปดาห์วิทยาศาสตร์แห่งชาติ ส่วนภูมิภาค ประจำปี 2569</h1>
            </div>
        </a>
        <div class="subtitle">คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ วิทยาเขตลำรางทุ่งกะโล่ · 18–20 สิงหาคม 2569</div>
        <nav class="nav-links">
            <a href="<?= base_url('scienceweek') ?>">หน้าหลัก</a>
            <a href="<?= base_url('scienceweek/verify') ?>">ตรวจสอบรายชื่อ</a>
        </nav>
    </div>
</header>
<main class="container py-4">
    <?php if (session()->has('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= esc(session('error')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= esc(session('success')) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?= $this->renderSection('content') ?>
</main>
<footer class="text-center">
    <div class="container">
        ระบบรับสมัครออนไลน์ · งานสัปดาห์วิทยาศาสตร์ 2569 · คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์ วิทยาเขตลำรางทุ่งกะโล่
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$pid   = (int) ($program_id ?? 0);
$nameth = $program['name_th'] ?? '';
$nameen = $program['name_en'] ?? '';
$degth  = $program['degree_th'] ?? '';
$mainUrl = base_url('p/' . $pid . '/main');
$tc = $theme_color ?? '#1e40af';
$bg = $background_color !== null && $background_color !== '' ? $background_color : '#f8fafc';
$tx = $text_color !== null && $text_color !== '' ? $text_color : '#1e293b';
$hero = $hero_image_url ?? '';
$pageTitle = 'ข้อมูลหลักสูตร — ' . $nameth;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle) ?></title>
    <?php helper('site'); ?>
    <link rel="icon" type="image/png" href="<?= esc(favicon_url()) ?>" sizes="32x32">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --theme: <?= esc($tc) ?>; --text: <?= esc($tx) ?>; --bg: <?= esc($bg) ?>; }
        body { font-family: 'Noto Sans Thai', system-ui, sans-serif; color: var(--text); background: var(--bg); }
        .onepage-hero { background: linear-gradient(135deg, var(--theme) 0%, color-mix(in srgb, var(--theme) 80%, #000) 100%); }
        .onepage-prose { line-height: 1.7; }
        .onepage-prose h1, .onepage-prose h2, .onepage-prose h3 { color: #0f172a; font-weight: 600; margin-top: 0.5em; margin-bottom: 0.35em; }
        .onepage-prose p { margin-bottom: 0.75em; }
        .onepage-prose table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin: 1rem 0; }
        .onepage-prose th, .onepage-prose td { border: 1px solid #e2e8f0; padding: 0.5rem 0.6rem; text-align: left; }
        .onepage-prose ul, .onepage-prose ol { margin: 0.5em 0 0.5em 1.25em; }
        .onepage-nav a:hover { color: var(--theme); }
    </style>
</head>
<body>
    <header class="onepage-hero text-white min-h-[38vh] flex flex-col justify-end relative">
        <?php if ($hero !== ''): ?>
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('<?= esc($hero, 'attr') ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900/85 to-slate-900/40"></div>
        <?php endif; ?>
        <div class="relative max-w-4xl mx-auto px-4 md:px-6 pb-10 pt-16 w-full">
            <p class="text-white/80 text-sm mb-1">หน้าเดียว ข้อมูลสำหรับผู้สนใจหลักสูตร</p>
            <h1 class="text-2xl md:text-3xl font-bold leading-tight mb-1"><?= esc($nameth) ?></h1>
            <?php if ($nameen !== ''): ?><p class="text-white/90 text-sm md:text-base font-medium"><?= esc($nameen) ?></p><?php endif; ?>
            <?php if ($degth !== ''): ?><p class="text-white/85 text-sm mt-2"><?= esc($degth) ?></p><?php endif; ?>
            <a href="<?= esc($mainUrl, 'attr') ?>" class="inline-block mt-5 text-sm text-white/95 underline decoration-white/50 hover:decoration-white">← กลับหน้าเว็บหลักสูตร (แบบเต็ม)</a>
        </div>
    </header>

    <?php if (empty($sections)): ?>
    <div class="max-w-3xl mx-auto px-4 py-16 text-center text-slate-500">
        <p class="text-lg mb-2">ยังไม่มีเนื้อหาในหน้านี้</p>
        <p class="text-sm">หลักสูตรสามารถกรอกข้อมูลรายส่วนได้ที่ระบบจัดการ — กรุณาเข้าเมนู <strong>แก้ไขเนื้อหา &gt; หน้า Onepage ข้อมูล</strong> (สำหรับผู้ดูแล)</p>
    </div>
    <?php else: ?>
    <div class="max-w-5xl mx-auto px-4 md:px-6 py-8 flex flex-col md:flex-row gap-8">
        <nav class="onepage-nav md:w-52 shrink-0 md:sticky md:top-4 md:self-start border border-slate-200 rounded-lg p-3 bg-white shadow-sm" aria-label="สารบัญ">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">สารบัญ</p>
            <ul class="space-y-1.5 text-sm">
                <?php foreach ($sections as $s) : ?>
                    <li>
                        <a href="#sec-<?= esc($s['id'], 'attr') ?>" class="text-slate-700 block py-0.5 border-l-2 border-transparent pl-2 hover:border-current">
                            <?= esc($s['title']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <main class="flex-1 min-w-0 space-y-10 pb-16">
            <?php foreach ($sections as $s) : ?>
                <section id="sec-<?= esc($s['id'], 'attr') ?>" class="scroll-mt-6 border-b border-slate-200/80 pb-10 last:border-0">
                    <h2 class="text-xl md:text-2xl font-bold text-slate-900 mb-1"><?= esc($s['title']) ?></h2>
                    <?php if (($s['aun_hint'] ?? '') !== '' && $s['aun_hint'] !== '—') : ?>
                        <p class="text-xs text-slate-500 mb-3">อ้างอิง (ย่อ) AUN-QA: <?= esc($s['aun_hint']) ?></p>
                    <?php endif; ?>
                    <div class="onepage-prose text-slate-700 max-w-3xl">
                        <?= $s['body'] ?? '' ?>
                    </div>
                </section>
            <?php endforeach; ?>
        </main>
    </div>
    <?php endif; ?>

    <footer class="border-t border-slate-200 bg-slate-50 py-6 text-center text-sm text-slate-500">
        <a href="<?= esc($mainUrl, 'attr') ?>" class="text-slate-700 hover:underline">กลับหน้าเว็บหลักสูตร</a>
        <span class="mx-2">·</span>
        <span><?= esc($nameth) ?></span>
    </footer>
</body>
</html>

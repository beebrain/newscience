<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'เอกสาร') ?> — E-Document</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <?php helper('site'); ?>
    <link rel="stylesheet" href="<?= base_url('assets/vendor/@fortawesome/fontawesome-free/css/all.min.css') ?>">
    <style>
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; height: 100%; font-family: 'Sarabun', sans-serif; }

        body {
            background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
            color: #191c1d;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* === HEADER === */
        .viewer-header {
            background: linear-gradient(135deg, rgba(115,92,0,0.96) 0%, rgba(212,175,55,0.90) 100%);
            box-shadow: 0 4px 20px rgba(115,92,0,0.18);
            flex-shrink: 0;
            position: relative;
            overflow: hidden;
        }
        .viewer-header::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at top right, rgba(255,255,255,0.22), transparent 50%);
            pointer-events: none;
        }

        /* === META HEADER (details strip) === */
        .meta-strip {
            background: rgba(255,255,255,0.92);
            border-bottom: 1px solid rgba(226,232,240,0.95);
            flex-shrink: 0;
        }

        /* === PDF AREA === */
        .pdf-shell {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }
        .pdf-frame {
            flex: 1;
            width: 100%;
            border: none;
            min-height: 0;
        }

        /* === CHIPS === */
        .info-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #f1f5f9; border: 1px solid #e2e8f0;
            border-radius: 100px; padding: 3px 12px;
            font-size: 0.75rem; color: #475569; font-weight: 500;
            white-space: nowrap;
        }
        .info-chip i { color: #94a3b8; font-size: 0.65rem; }
        .person-chip {
            display: inline-flex; align-items: center; gap: 5px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 100px; padding: 3px 10px;
            font-size: 0.75rem; color: #1d4ed8; font-weight: 500;
        }
        .person-chip i { font-size: 0.65rem; }

        /* === NO-FILE CARD === */
        .doc-card {
            background: rgba(255,255,255,0.95);
            border: 1px solid rgba(226,232,240,0.95);
            box-shadow: 0 8px 32px rgba(15,23,42,0.08);
            border-radius: 1.25rem;
        }

        /* === DETAILS TOGGLE (mobile) === */
        #meta-toggle-btn { display: none; }
        @media (max-width: 639px) {
            #meta-toggle-btn { display: flex; }
            #meta-content { transition: max-height 0.25s ease; overflow: hidden; }
            #meta-content.collapsed { max-height: 0 !important; }
        }
    </style>
</head>

<?php
    /** @var array  $doc */
    /** @var array  $participants */
    $doc          = is_array($doc          ?? null) ? $doc          : [];
    $participants = is_array($participants ?? null) ? $participants : [];
    $file_error   = isset($file_error)  && is_string($file_error)  ? $file_error  : null;
    $pdf_url      = isset($pdf_url)     && is_string($pdf_url)     ? $pdf_url     : null;
    $title        = (string)($title        ?? $doc['title']  ?? 'เอกสาร');
    $ownerDisplay = (string)($owner_display ?? $doc['owner'] ?? '');
?>

<body>

<!-- ====================================================
     HEADER BAR (gold gradient)
     ==================================================== -->
<div class="viewer-header">
    <div class="relative z-10 px-4 py-3 flex items-center gap-3">

        <!-- Back -->
        <a href="<?= base_url('index.php/edoc') ?>"
           class="flex items-center gap-2 text-white/80 hover:text-white text-sm font-semibold transition flex-shrink-0">
            <i class="fas fa-arrow-left"></i>
            <span class="hidden sm:inline">กล่องเอกสาร</span>
        </a>

        <div class="w-px h-5 bg-white/30 flex-shrink-0"></div>

        <!-- Title -->
        <div class="flex-1 min-w-0">
            <div class="text-white font-semibold text-sm leading-tight line-clamp-1 opacity-90"><?= esc($title) ?></div>
        </div>

        <!-- Action buttons -->
        <div class="flex items-center gap-2 flex-shrink-0">
            <!-- Mobile: toggle details -->
            <button id="meta-toggle-btn"
                    onclick="toggleMeta()"
                    class="items-center gap-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-lg px-3 py-1.5 text-xs font-semibold transition">
                <i class="fas fa-info-circle"></i>
                <span>รายละเอียด</span>
            </button>

            <?php if (!empty($pdf_url)): ?>
            <a href="<?= esc($pdf_url) ?>" download
               class="flex items-center gap-1.5 bg-white/20 hover:bg-white/30 border border-white/30 text-white rounded-lg px-3 py-1.5 text-xs sm:text-sm font-semibold transition">
                <i class="fas fa-download"></i>
                <span class="hidden sm:inline">ดาวน์โหลด</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ====================================================
     METADATA STRIP (details below header)
     ==================================================== -->
<div class="meta-strip">
    <div id="meta-content" style="max-height: 600px;">

        <!-- ชื่อเรื่อง + ประเภท (แถวบน) -->
        <div class="px-4 pt-3 pb-2 border-b border-slate-100">
            <div class="text-slate-800 font-bold text-sm sm:text-base leading-snug mb-1.5"><?= esc($title) ?></div>
            <div class="flex flex-wrap gap-2 items-center">
                <?php if (!empty($doc['doctype'])): ?>
                <span class="info-chip" style="background:#ede9fe;border-color:#c4b5fd;color:#5b21b6;">
                    <i class="fas fa-tag" style="color:#7c3aed;"></i><?= esc((string)$doc['doctype']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['officeiddoc'])): ?>
                <span class="info-chip">
                    <i class="fas fa-hashtag"></i><?= esc((string)$doc['officeiddoc']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['datedoc'])): ?>
                <span class="info-chip">
                    <i class="fas fa-calendar-alt"></i><?= esc((string)$doc['datedoc']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['doc_year'])): ?>
                <span class="info-chip">
                    <i class="fas fa-calendar"></i>ปี <?= esc((string)$doc['doc_year']) ?>
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['pages'])): ?>
                <span class="info-chip">
                    <i class="fas fa-file-alt"></i><?= esc((string)$doc['pages']) ?> หน้า
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['copynum'])): ?>
                <span class="info-chip">
                    <i class="fas fa-copy"></i><?= esc((string)$doc['copynum']) ?> สำเนา
                </span>
                <?php endif; ?>
                <?php if (!empty($doc['regisdate'])): ?>
                <span class="info-chip">
                    <i class="fas fa-clock"></i>ลงทะเบียน <?= esc((string)$doc['regisdate']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- เจ้าของ + ผู้เกี่ยวข้อง (แถวล่าง) -->
        <?php if ($ownerDisplay !== '' || !empty($participants)): ?>
        <div class="px-4 py-2 flex flex-wrap gap-2 items-center">
            <?php if ($ownerDisplay !== ''): ?>
            <span class="info-chip" style="background:#fef9ec;border-color:#fde68a;color:#92400e;">
                <i class="fas fa-user" style="color:#d97706;"></i>
                <span class="font-semibold" style="color:#78350f;">เจ้าของเอกสาร:</span>&nbsp;<?= esc($ownerDisplay) ?>
            </span>
            <?php endif; ?>
            <?php if (!empty($participants)): ?>
                <span class="info-chip" style="background:#f0f9ff;border-color:#bae6fd;color:#075985;">
                    <i class="fas fa-users" style="color:#0284c7;"></i>
                    <span class="font-semibold" style="color:#0c4a6e;">ผู้เกี่ยวข้อง:</span>
                </span>
                <?php foreach ($participants as $p): ?>
                <span class="person-chip">
                    <i class="fas fa-user-circle"></i><?= esc((string)$p) ?>
                </span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php if (!empty($file_error)): ?>
<!-- ====================================================
     ไม่พบไฟล์
     ==================================================== -->
<div class="flex-1 flex items-start justify-center px-4 py-10 overflow-y-auto">
    <div class="w-full max-w-xl">

        <!-- Error card -->
        <div class="doc-card p-8 text-center mb-5">
            <div class="w-16 h-16 rounded-2xl bg-amber-50 border-2 border-amber-200 flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-file-pdf text-3xl text-amber-400"></i>
            </div>
            <h2 class="text-lg font-bold text-gray-800 mb-1">ไม่พบไฟล์เอกสาร</h2>
            <p class="text-gray-500 text-sm"><?= esc($file_error) ?></p>
        </div>

        <!-- Document detail card -->
        <?php if (!empty($doc)): ?>
        <div class="doc-card overflow-hidden">
            <div class="px-6 py-3 border-b border-gray-100">
                <h3 class="font-bold text-gray-500 text-xs tracking-widest uppercase">รายละเอียดเอกสาร</h3>
            </div>
            <div class="divide-y divide-gray-50">
                <?php
                $fields = [
                    ['fas fa-file-alt',     'ชื่อเรื่อง',       (string)($doc['title']       ?? '')],
                    ['fas fa-hashtag',      'เลขที่เอกสาร',     (string)($doc['officeiddoc'] ?? '')],
                    ['fas fa-tag',          'ประเภทเอกสาร',     (string)($doc['doctype']     ?? '')],
                    ['fas fa-calendar-alt', 'วันที่เอกสาร',     (string)($doc['datedoc']     ?? '')],
                    ['fas fa-calendar',     'ปีเอกสาร',         (string)($doc['doc_year']    ?? '')],
                    ['fas fa-clock',        'วันที่ลงทะเบียน',  (string)($doc['regisdate']   ?? '')],
                    ['fas fa-user',         'ผู้ส่ง',            $ownerDisplay],
                    ['fas fa-file',         'จำนวนหน้า',         !empty($doc['pages'])   ? ((string)$doc['pages'])   . ' หน้า'  : ''],
                    ['fas fa-copy',         'จำนวนสำเนา',        !empty($doc['copynum']) ? ((string)$doc['copynum']) . ' ชุด'   : ''],
                ];
                foreach ($fields as [$icon, $label, $value]):
                    if (empty($value)) continue;
                ?>
                <div class="flex items-start gap-4 px-6 py-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="<?= $icon ?> text-indigo-500 text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide"><?= $label ?></div>
                        <div class="text-sm text-gray-700 font-medium mt-0.5 break-words"><?= esc($value) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if (!empty($participants)): ?>
                <div class="flex items-start gap-4 px-6 py-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="fas fa-users text-indigo-500 text-xs"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1.5">ผู้เกี่ยวข้อง</div>
                        <div class="flex flex-wrap gap-1.5">
                            <?php foreach ($participants as $p): ?>
                            <span class="person-chip"><?= esc((string)$p) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="text-center mt-6">
            <a href="<?= base_url('index.php/edoc') ?>"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl px-6 py-3 transition shadow-md">
                <i class="fas fa-inbox"></i> กลับกล่องเอกสาร
            </a>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ====================================================
     มีไฟล์ — PDF เต็มพื้นที่
     ==================================================== -->
<div class="pdf-shell">
    <iframe src="<?= esc((string)$pdf_url) ?>" class="pdf-frame" allowfullscreen></iframe>
</div>
<?php endif; ?>

<script>
function toggleMeta() {
    const mc = document.getElementById('meta-content');
    mc.classList.toggle('collapsed');
}
</script>

</body>
</html>

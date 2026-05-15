<?php
/**
 * แสดงแม่แบบใบรับรองแบบ compact + collapsible
 * - default: thumbnail 80×112 (portrait) หรือ 120×80 (landscape)
 * - คลิก "ดูเต็ม" เพื่อ expand เป็นภาพใหญ่
 *
 * @var array<string,mixed> $event
 * @var string              $cert_base
 */
$cb   = rtrim((string) ($cert_base ?? ''), '/');
$kind = strtolower((string) ($event['background_kind'] ?? ''));
$rel  = trim((string) ($event['background_file'] ?? ''));
$has  = $rel !== '';
$fn   = $has ? basename(str_replace('\\', '/', $rel)) : '';
$eid  = (int) ($event['id'] ?? 0);
$previewUrl = ($has && $kind === 'image' && $eid > 0) ? ($cb . '/' . $eid . '/background-preview') : '';
$editUrl    = $eid > 0 ? ($cb . '/' . $eid . '/edit') : $cb . '/edit';

// Determine orientation from layout_json
$orient = 'portrait';
$lj = json_decode((string) ($event['layout_json'] ?? ''), true);
if (is_array($lj) && (($lj['orientation'] ?? '') === 'landscape')) {
    $orient = 'landscape';
}
$thumbW = $orient === 'landscape' ? 120 : 80;
$thumbH = $orient === 'landscape' ? 80  : 112;
?>
<section class="cert-tpl-block" style="display:flex; align-items:flex-start; gap:0.85rem; padding:0.65rem 0.85rem; background:#f8fafc; border:1px solid #e2e8f0; border-radius:0.5rem;">
    <?php if ($has && $kind === 'image' && $previewUrl !== ''): ?>
        <img src="<?= esc($previewUrl) ?>"
             alt="แม่แบบ"
             loading="lazy"
             style="width:<?= $thumbW ?>px; height:<?= $thumbH ?>px; object-fit:cover; border-radius:4px; border:1px solid #cbd5e1; background:#e2e8f0; flex-shrink:0; cursor:pointer;"
             onclick="document.getElementById('certTplFull').showModal()">
    <?php else: ?>
        <div style="width:<?= $thumbW ?>px; height:<?= $thumbH ?>px; background:#fef3c7; border:1px dashed #fde68a; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#92400e; font-size:11px; text-align:center; padding:0.25rem; flex-shrink:0;">
            ยังไม่มีไฟล์
        </div>
    <?php endif; ?>

    <div style="flex:1; min-width:0;">
        <div style="font-size:13px; font-weight:700; color:#0f172a; margin-bottom:0.15rem;">แม่แบบใบรับรอง</div>
        <?php if ($has): ?>
            <div style="font-size:12px; color:#475569; word-break:break-all; line-height:1.4; margin-bottom:0.35rem;">
                <span style="display:inline-block; padding:0.1rem 0.4rem; border-radius:9999px; background:#dbeafe; color:#1e40af; font-weight:600; margin-right:0.25rem;">
                    <?= $orient === 'landscape' ? 'A4 แนวนอน' : 'A4 แนวตั้ง' ?>
                </span>
                <code style="font-size:11px; color:#64748b;"><?= esc($fn) ?></code>
            </div>
        <?php else: ?>
            <div style="font-size:12px; color:#b45309; margin-bottom:0.35rem;">ยังไม่มีแม่แบบ — อัปโหลดก่อนออกใบ</div>
        <?php endif; ?>
        <div style="display:flex; gap:0.4rem; flex-wrap:wrap;">
            <a href="<?= esc($editUrl) ?>"
               style="font-size:12px; padding:0.25rem 0.6rem; border-radius:4px; background:#fff; color:#1d4ed8; border:1px solid #bfdbfe; text-decoration:none; font-weight:600;">
                แก้ไขแม่แบบ
            </a>
            <?php if ($has && $kind === 'image' && $previewUrl !== ''): ?>
                <button type="button"
                        onclick="document.getElementById('certTplFull').showModal()"
                        style="font-size:12px; padding:0.25rem 0.6rem; border-radius:4px; background:#fff; color:#475569; border:1px solid #e2e8f0; cursor:pointer; font-weight:600;">
                    ดูเต็ม
                </button>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if ($has && $kind === 'image' && $previewUrl !== ''): ?>
<dialog id="certTplFull" style="padding:0; border:none; border-radius:12px; max-width:90vw; max-height:90vh; background:transparent;">
    <div style="background:#fff; border-radius:12px; padding:1rem; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:0.5rem;">
            <strong style="font-size:14px;">แม่แบบใบรับรอง</strong>
            <button type="button" onclick="this.closest('dialog').close()"
                    style="background:#e5e7eb; border:none; border-radius:4px; padding:0.3rem 0.7rem; cursor:pointer; font-size:13px;">ปิด</button>
        </div>
        <img src="<?= esc($previewUrl) ?>"
             alt="แม่แบบใบรับรอง"
             style="display:block; max-width:80vw; max-height:78vh; object-fit:contain; border-radius:4px;">
    </div>
</dialog>
<?php endif; ?>

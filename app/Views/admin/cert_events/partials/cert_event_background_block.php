<?php
/**
 * แสดงสถานะไฟล์แม่แบบ + พรีวิวรูป (ถ้าเป็น image) บนหน้า show กิจกรรม
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
?>
<div>
    <strong>ไฟล์ใบรับรอง:</strong><br>
    <?php if (! $has): ?>
        <em style="color:#b45309;">ยังไม่อัปโหลด — ต้องมีก่อนออกใบ</em>
    <?php elseif ($kind === 'image' && $previewUrl !== ''): ?>
        <div style="margin-top:0.35rem;">
            <span style="display:inline-block; padding:0.15rem 0.45rem; border-radius:4px; font-size:12px; background:#dbeafe; color:#1e40af;">รูปภาพ</span>
            <code style="font-size: 11px; margin-left: 0.25rem;"><?= esc($fn) ?></code>
        </div>
        <div style="margin-top:0.5rem; border:1px solid #e5e7eb; border-radius:8px; padding:0.75rem; background:#fff; max-width: min(100%, 560px);">
            <img src="<?= esc($previewUrl) ?>"
                 alt="พรีวิวแม่แบบใบรับรอง"
                 loading="lazy"
                 style="max-width:100%; height:auto; display:block; border-radius:4px;">
        </div>
        <p style="margin:0.5rem 0 0; font-size:12px; color:#64748b;">
            <a href="<?= esc($editUrl) ?>">แก้ไข — หมุน / ครอบภาพ / ตำแหน่งชื่อ</a>
        </p>
    <?php elseif ($kind === 'pdf'): ?>
        <div style="margin-top:0.35rem;">
            <span style="display:inline-block; padding:0.15rem 0.45rem; border-radius:4px; font-size:12px; background:#fef3c7; color:#92400e;">PDF</span>
            <code style="font-size: 11px; margin-left: 0.25rem;"><?= esc($fn) ?></code>
        </div>
        <p style="margin:0.35rem 0 0; font-size:12px; color:#64748b;">ไม่แสดงพรีวิวในหน้านี้ — ใช้ไฟล์ตามที่อัปโหลดเมื่อออกใบ</p>
    <?php else: ?>
        <div style="margin-top:0.35rem;">
            <span style="font-size:12px;"><?= esc($kind !== '' ? $kind : 'ไฟล์') ?></span>
            <code style="font-size: 11px;"><?= esc($fn) ?></code>
        </div>
    <?php endif; ?>
</div>

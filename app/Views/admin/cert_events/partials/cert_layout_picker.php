<?php
/**
 * ตัวช่วยคลิกวางตำแหน่งชื่อผู้รับบนแม่แบบใบรับรอง (รูปเท่านั้น — PDF ใช้ layout_json เอง)
 *
 * @var string $layoutTextareaSelector เช่น #cert_event_layout_json
 * @var string $fileInputSelector        เช่น #cert_event_background_file
 * @var string                          $cert_base base path เช่น admin/cert-events
 * @var array<string,mixed>|null        $event      แถวกิจกรรมเมื่อแก้ไข (มี id + background รูป)
 */

use Config\Certificate as CertificateConfig;

$defaults     = json_decode(config(CertificateConfig::class)->eventCertificateDefaultLayoutJson, true) ?: [];
$defaultsJson = json_encode($defaults, JSON_UNESCAPED_UNICODE) ?: '{}';
$previewUrl = '';
if (is_array($event ?? null)
    && ! empty($event['id'])
    && strtolower((string) ($event['background_kind'] ?? '')) === 'image'
    && ! empty($event['background_file'])) {
    $previewUrl = rtrim((string) ($cert_base ?? ''), '/') . '/' . (int) $event['id'] . '/background-preview';
}
?>
<div class="cert-lp-wrap"
     data-cert-layout-picker
     data-layout-textarea="<?= esc($layoutTextareaSelector, 'attr') ?>"
     data-file-input="<?= esc($fileInputSelector, 'attr') ?>"
     data-defaults-json="<?= esc($defaultsJson, 'attr') ?>"
     data-preview-url="<?= esc($previewUrl, 'attr') ?>"
     data-sample-text="<?= esc('ชื่อ นามสกุล ผู้เข้ารับการอบรม', 'attr') ?>"
     style="margin: 1rem 0; padding: 1rem; border: 1px solid #93c5fd; border-radius: 0.5rem; background: #eff6ff;">

    <strong style="display:block; margin-bottom: 0.35rem; color: #1e40af;">กำหนดตำแหน่งชื่อผู้ได้รับใบประกาศ</strong>
    <p style="margin: 0 0 0.75rem; font-size: 13px; color: #1e3a8a; line-height: 1.5;">
        หลังอัปโหลด<strong>รูป</strong> JPG/PNG แล้ว คลิกบนภาพตรงจุดที่ต้องการให้แสดงชื่อผู้รับ
        — ลากจุดสีแดงเพื่อปรับตำแหน่ง — ระบบจะเขียนค่า <code>layout_json</code> ให้อัตโนมัติ
        (ไฟล์ PDF ยังไม่รองรับตัวอย่างภาพในหน้านี้)
    </p>

    <p class="cert-lp-note-pdf" style="display:none; font-size: 13px; color: #92400e; margin: 0 0 0.5rem;"></p>

    <div class="cert-lp-stage" style="display:none; position: relative; width: 100%; max-width: 520px; margin: 0 auto; aspect-ratio: 210 / 297; border: 1px solid #94a3b8; border-radius: 4px; overflow: hidden; background: #e2e8f0;">
        <img class="cert-lp-img" alt="แม่แบบใบรับรอง" src="" style="display:none; position:absolute; inset:0; width:100%; height:100%; object-fit: fill; cursor: crosshair;">
        <div class="cert-lp-marker" style="display:none; position:absolute; left:0; top:0; width:14px; height:14px; margin:-7px 0 0 -7px; background:#dc2626; border:2px solid #fff; border-radius:50%; box-shadow:0 1px 4px rgba(0,0,0,.35); z-index:3; cursor:move; touch-action:none;"></div>
        <div class="cert-lp-ghost" style="display:none; position:absolute; left:0; top:0; margin: 10px 0 0 4px; font-size: 13px; font-weight: 600; color: #0f172a; text-shadow: 0 0 4px #fff, 0 0 6px #fff; z-index:2; pointer-events:none; white-space:nowrap;"></div>
    </div>

    <div style="margin-top: 0.75rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem;">
        <label style="font-size: 13px; margin:0;">ขนาดตัวอักษรชื่อ (pt)</label>
        <input type="number" class="cert-lp-font-size form-control" min="8" max="48" value="22" style="width: 5rem; padding: 0.35rem 0.5rem;">
    </div>
</div>

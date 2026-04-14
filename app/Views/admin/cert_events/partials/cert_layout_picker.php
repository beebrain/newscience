<?php
/**
 * ลากกรอบบนภาพแม่แบบเพื่อตั้งตำแหน่งชื่อผู้รับ — ค่า layout_json เก็บใน hidden (ไม่ต้องแก้มือ)
 *
 * @var string               $layoutHiddenId      id ของ input hidden name=layout_json
 * @var string               $fileInputId         id ของ input file พื้นหลัง
 * @var string               $cert_base           base URL path
 * @var array<string,mixed>|null $event
 * @var string               $initial_layout_json ค่าเริ่ม (JSON string)
 */

use Config\Certificate as CertificateConfig;

$defaults       = json_decode(config(CertificateConfig::class)->eventCertificateDefaultLayoutJson, true) ?: [];
$defaultsJson   = json_encode($defaults, JSON_UNESCAPED_UNICODE) ?: '{}';
$previewUrl     = '';
$initialLayout  = (string) ($initial_layout_json ?? '');
if (is_array($event ?? null)
    && ! empty($event['id'])
    && strtolower((string) ($event['background_kind'] ?? '')) === 'image'
    && ! empty($event['background_file'])) {
    $previewUrl = rtrim((string) ($cert_base ?? ''), '/') . '/' . (int) $event['id'] . '/background-preview';
}
?>
<input type="hidden" name="layout_json" id="<?= esc($layoutHiddenId, 'attr') ?>" value="<?= esc($initialLayout, 'attr') ?>">

<div class="cert-lp-wrap"
     data-cert-layout-picker
     data-layout-input-id="<?= esc($layoutHiddenId, 'attr') ?>"
     data-file-input-id="<?= esc($fileInputId, 'attr') ?>"
     data-defaults-json="<?= esc($defaultsJson, 'attr') ?>"
     data-preview-url="<?= esc($previewUrl, 'attr') ?>"
     data-sample-text="<?= esc('ชื่อ นามสกุล ผู้เข้ารับการอบรม', 'attr') ?>"
     style="margin: 1rem 0; padding: 1rem; border: 1px solid #93c5fd; border-radius: 0.5rem; background: #eff6ff;">

    <strong style="display:block; margin-bottom: 0.35rem; color: #1e40af;">ระบุตำแหน่งชื่อผู้ได้รับใบประกาศ</strong>
    <p style="margin: 0 0 0.75rem; font-size: 13px; color: #1e3a8a; line-height: 1.5;">
        อัปโหลด<strong>รูป JPG/PNG</strong> ก่อน จากนั้นกดปุ่มด้านล่าง แล้ว<strong>ลากกรอบสี่เหลี่ยม</strong>บนภาพรอบพื้นที่ที่ต้องการให้แสดงชื่อ
        — ไม่ต้องกรอกพิกัดเอง
    </p>

    <button type="button" class="btn btn-primary cert-lp-open" style="margin-bottom: 0.75rem;" aria-expanded="false">
        แสดงภาพและลากกรอบระบุตำแหน่งชื่อ
    </button>

    <p class="cert-lp-note-pdf" style="display:none; font-size: 13px; color: #92400e; margin: 0 0 0.5rem;"></p>

    <div class="cert-lp-stage-wrap" style="display: none;">
        <div class="cert-lp-stage" style="position: relative; width: 100%; max-width: 520px; margin: 0 auto; aspect-ratio: 210 / 297; border: 1px solid #94a3b8; border-radius: 4px; overflow: hidden; background: #e2e8f0;">
            <img class="cert-lp-img" alt="แม่แบบใบรับรอง" src="" style="display:none; position:absolute; inset:0; width:100%; height:100%; object-fit: fill; cursor: crosshair; touch-action: none;">
            <div class="cert-lp-rubber" style="display:none; position:absolute; z-index:4; border:2px dashed #2563eb; background:rgba(37,99,235,0.12); pointer-events:none; box-sizing:border-box;"></div>
            <div class="cert-lp-rect-final" style="display:none; position:absolute; z-index:3; border:2px solid #16a34a; background:rgba(22,163,74,0.08); pointer-events:none; box-sizing:border-box;"></div>
            <div class="cert-lp-ghost" style="display:none; position:absolute; z-index:5; font-size: 13px; font-weight: 600; color: #0f172a; text-shadow: 0 0 4px #fff, 0 0 6px #fff; pointer-events:none;"></div>
        </div>
        <p style="margin: 0.5rem 0 0; font-size: 12px; color: #475569;">กดค้างแล้วลากบนภาพเพื่อวาดกรอบ — ปล่อยเมาส์เมื่อครอบคลุมพื้นที่ชื่อ</p>
    </div>
</div>

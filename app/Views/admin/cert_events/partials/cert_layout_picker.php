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

    <strong style="display:block; margin-bottom: 0.35rem; color: #1e40af;">ระบุตำแหน่งชื่อผู้ได้รับเกียรติบัตร</strong>
    <p style="margin: 0 0 0.75rem; font-size: 13px; color: #1e3a8a; line-height: 1.5;">
        อัปโหลด<strong>รูป JPG/PNG</strong> ก่อน — สามารถ<strong>หมุน</strong>และ<strong>ครอบตามสัดส่วน A4 แนวตั้ง</strong> (210:297) ได้ก่อนลากกรอบชื่อ
        — พื้นที่ลากด้านล่างแสดงเป็นมุมมองแนวนอน แต่พิกัดและ PDF ยังเป็น A4 แนวตั้ง
    </p>

    <div class="cert-lp-review-tools" style="display:none; margin-bottom: 0.75rem; padding: 0.75rem; background: #fff; border: 1px solid #bfdbfe; border-radius: 0.5rem;">
        <strong style="display:block; margin-bottom: 0.5rem; font-size: 13px; color: #1e3a8a;">ปรับภาพแม่แบบ (Review)</strong>
        <div style="display:flex; flex-wrap:wrap; gap:0.5rem; align-items:center;">
            <button type="button" class="btn btn-secondary btn-sm cert-lp-crop-open">หมุน / ครอบ A4</button>
            <span class="cert-lp-crop-hint text-muted" style="font-size:12px;">กรอบครอบล็อกอัตราส่วน A4 แนวตั้ง</span>
        </div>
        <div class="cert-lp-crop-panel" style="display:none; margin-top:0.75rem;">
            <div style="max-height:min(70vh,560px); overflow:auto; background:#f1f5f9; border-radius:4px; padding:0.5rem;">
                <img class="cert-lp-crop-target" alt="" src="" style="display:block; max-width:100%;">
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:0.5rem; margin-top:0.5rem; align-items:center;">
                <button type="button" class="btn btn-secondary btn-sm cert-lp-crop-rotate-left">หมุนซ้าย 90°</button>
                <button type="button" class="btn btn-secondary btn-sm cert-lp-crop-rotate-right">หมุนขวา 90°</button>
                <button type="button" class="btn btn-primary btn-sm cert-lp-crop-apply">ใช้ภาพนี้</button>
                <button type="button" class="btn btn-secondary btn-sm cert-lp-crop-cancel">ยกเลิก</button>
            </div>
        </div>
    </div>

    <button type="button" class="btn btn-primary cert-lp-open" style="margin-bottom: 0.75rem;" aria-expanded="false">
        แสดงภาพและลากกรอบระบุตำแหน่งชื่อ
    </button>

    <p class="cert-lp-note-pdf" style="display:none; font-size: 13px; color: #92400e; margin: 0 0 0.5rem;"></p>

    <div class="cert-lp-stage-wrap" style="display: none;">
        <div class="cert-lp-stage" style="position: relative; width: 100%; max-width: 960px; margin: 0 auto; aspect-ratio: 297 / 210; border: 1px solid #94a3b8; border-radius: 4px; overflow: hidden; background: #e2e8f0;">
            <div class="cert-lp-sheet" style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%) rotate(-90deg); transform-origin: center center; box-sizing: border-box; touch-action: none; cursor: crosshair;">
                <img class="cert-lp-img" alt="แม่แบบใบรับรอง" src="" style="display:none; position:absolute; left:0; top:0; width:100%; height:100%; object-fit: fill; cursor: crosshair; touch-action: none;">
                <div class="cert-lp-rubber" style="display:none; position:absolute; z-index:4; border:2px dashed #2563eb; background:rgba(37,99,235,0.12); pointer-events:none; box-sizing:border-box;"></div>
                <div class="cert-lp-rect-final" style="display:none; position:absolute; z-index:3; border:2px solid #16a34a; background:rgba(22,163,74,0.08); pointer-events:none; box-sizing:border-box;"></div>
                <div class="cert-lp-ghost" style="display:none; position:absolute; z-index:5; font-size: 13px; font-weight: 600; color: #0f172a; text-shadow: 0 0 4px #fff, 0 0 6px #fff; pointer-events:none; writing-mode: horizontal-tb; text-orientation: mixed; unicode-bidi: plaintext;"></div>
            </div>
        </div>
        <p style="margin: 0.5rem 0 0; font-size: 12px; color: #475569;">กดค้างแล้วลากบนภาพเพื่อวาดกรอบ — ปล่อยเมาส์เมื่อครอบคลุมพื้นที่ชื่อ</p>
    </div>
</div>

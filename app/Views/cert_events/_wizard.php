<?php
/**
 * Cert Event Wizard partial — 5-step flow shared by admin & user create/edit views.
 *
 * @var array<string,mixed>|null $event       (null = create mode)
 * @var string                   $cert_base   e.g. "/newScience/admin/cert-events"
 * @var string                   $action_url  POST target (store or update)
 * @var string                   $cancel_url  URL ของปุ่มยกเลิก
 */

$event       = isset($event) && is_array($event) ? $event : null;
$cert_base   = rtrim((string) ($cert_base ?? ''), '/');
$cancel_url  = (string) ($cancel_url ?? $cert_base);
$action_url  = (string) ($action_url ?? ($cert_base . '/store'));

$initialLayout = (string) old('layout_json', $event['layout_json'] ?? '');
$parsedLayout  = json_decode($initialLayout, true);
$initOrient    = (is_array($parsedLayout) && (($parsedLayout['orientation'] ?? '') === 'landscape'))
    ? 'landscape' : 'portrait';

$bgFile = trim((string) ($event['background_file'] ?? ''));
$bgKind = strtolower((string) ($event['background_kind'] ?? ''));
$hasImg = ($bgKind === 'image' && $bgFile !== '' && !empty($event['id']));
$bgUrl  = $hasImg ? ($cert_base . '/' . (int) $event['id'] . '/background-preview') : '';

$titleVal = (string) old('title', $event['title'] ?? '');
$descVal  = (string) old('description', $event['description'] ?? '');
$dateVal  = (string) old('event_date', $event['event_date'] ?? '');
?>

<style>
.cew-wrap { max-width: 920px; margin: 0 auto; }
.cew-steps { display: flex; gap: 0.4rem; list-style: none; padding: 0; margin: 0 0 1.25rem; flex-wrap: wrap; }
.cew-steps li {
    flex: 1; min-width: 110px; padding: 0.55rem 0.6rem; text-align: center;
    background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; border-radius: 6px;
    font-size: 13px; font-weight: 600;
}
.cew-steps li.is-current { background: #2563eb; color: #fff; border-color: #2563eb; }
.cew-steps li.is-done    { background: #16a34a; color: #fff; border-color: #16a34a; }

.cew-step {
    background: #fff; border: 1px solid #e5e7eb; border-radius: 12px;
    padding: 1.5rem; margin-bottom: 1rem;
}
.cew-step h3 { margin: 0 0 0.75rem; font-size: 1.05rem; color: #1e3a8a; font-weight: 700; }
.cew-step p.cew-help { margin: 0 0 1rem; font-size: 13px; color: #475569; line-height: 1.55; }

.cew-field { margin-bottom: 1rem; }
.cew-field > label { display: block; font-size: 14px; font-weight: 600; color: #374151; margin-bottom: 0.35rem; }
.cew-field input[type=text],
.cew-field input[type=date],
.cew-field input[type=file],
.cew-field textarea {
    width: 100%; padding: 0.55rem 0.7rem; font-size: 14px;
    border: 1px solid #d1d5db; border-radius: 6px; background: #fff; box-sizing: border-box;
}
.cew-field input:focus,
.cew-field textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.15); }
.cew-field textarea { resize: vertical; min-height: 80px; }
.cew-error { color: #b91c1c; font-size: 13px; margin-top: 0.5rem; min-height: 1.2em; }
.cew-info  { background: #fef3c7; color: #78350f; padding: 0.5rem 0.75rem; border-radius: 6px; font-size: 13px; margin-top: 0.5rem; }

.cew-orient-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 0.75rem; }
.cew-orient-tile {
    position: relative; display: block; padding: 1rem;
    border: 2px solid #e5e7eb; border-radius: 10px; background: #fff;
    cursor: pointer; text-align: center;
}
.cew-orient-tile input { position: absolute; opacity: 0; pointer-events: none; }
.cew-orient-tile:hover { border-color: #93c5fd; }
.cew-orient-tile.is-active { border-color: #2563eb; background: #eff6ff; }
.cew-orient-icon { width: 56px; height: 80px; background: #cbd5e1; border-radius: 4px; margin: 0 auto 0.5rem; }
.cew-orient-tile[data-value="landscape"] .cew-orient-icon { width: 80px; height: 56px; }
.cew-orient-tile strong { display: block; color: #1e3a8a; font-size: 14px; }
.cew-orient-tile .cew-orient-sub { font-size: 12px; color: #64748b; margin-top: 0.15rem; }

.cew-crop-wrap {
    background: #f1f5f9; padding: 0.5rem; border-radius: 6px;
    max-height: min(70vh, 560px); overflow: auto;
}
.cew-crop-target { display: block; max-width: 100%; }
.cew-crop-tools  { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.75rem; align-items: center; }

.cew-stage-wrap { text-align: center; }
.cew-stage {
    position: relative; margin: 0 auto;
    border: 1px solid #94a3b8; border-radius: 4px; overflow: hidden;
    background: #e2e8f0;
}
.cew-sheet { position: absolute; inset: 0; touch-action: none; cursor: crosshair; }
.cew-sheet .cew-stage-img {
    display: block; position: absolute; left: 0; top: 0;
    width: 100%; height: 100%; object-fit: fill; pointer-events: none;
}
.cew-rubber {
    position: absolute; border: 2px dashed #2563eb;
    background: rgba(37,99,235,0.12); pointer-events: none; box-sizing: border-box; z-index: 4;
}
.cew-rect {
    position: absolute; border: 2px solid #16a34a;
    background: rgba(22,163,74,0.08); pointer-events: none; box-sizing: border-box; z-index: 3;
}
.cew-ghost {
    position: absolute; pointer-events: none; z-index: 5;
    font-size: 13px; font-weight: 700; color: #0f172a;
    text-shadow: 0 0 4px #fff, 0 0 6px #fff;
    white-space: normal; word-break: keep-all;
}

.cew-summary dl { display: grid; grid-template-columns: max-content 1fr; gap: 0.4rem 1rem; margin: 0 0 1rem; font-size: 14px; }
.cew-summary dt { font-weight: 700; color: #64748b; }
.cew-summary dd { margin: 0; color: #1f2937; word-break: break-word; }
.cew-summary-section { background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0; }

.cew-nav { display: flex; justify-content: space-between; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap; }
.cew-nav-group { display: flex; gap: 0.5rem; }
.cew-btn {
    display: inline-block; padding: 0.55rem 1.2rem; border-radius: 6px;
    font-size: 14px; font-weight: 600; cursor: pointer; border: none; text-decoration: none;
}
.cew-btn:disabled { opacity: 0.6; cursor: not-allowed; }
.cew-btn-primary   { background: #2563eb; color: #fff; }
.cew-btn-primary:hover:not(:disabled)   { background: #1d4ed8; }
.cew-btn-secondary { background: #e5e7eb; color: #374151; }
.cew-btn-secondary:hover:not(:disabled) { background: #d1d5db; }
.cew-btn-success   { background: #16a34a; color: #fff; }
.cew-btn-success:hover:not(:disabled)   { background: #15803d; }
</style>

<div class="cew-wrap">
    <?php if (session()->getFlashdata('errors')): ?>
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
                    <li><?= esc((string) $e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form id="cewForm" method="post" action="<?= esc($action_url, 'attr') ?>" enctype="multipart/form-data"
          data-edit-mode="<?= $event ? '1' : '0' ?>"
          data-existing-bg-url="<?= esc($bgUrl, 'attr') ?>"
          data-existing-bg-kind="<?= esc($bgKind, 'attr') ?>"
          data-init-orientation="<?= esc($initOrient, 'attr') ?>"
          data-init-layout-json="<?= esc($initialLayout, 'attr') ?>"
          data-cancel-url="<?= esc($cancel_url, 'attr') ?>">
        <?= csrf_field() ?>

        <ol class="cew-steps">
            <li data-step-indicator="1">1. ข้อมูล</li>
            <li data-step-indicator="2">2. ไฟล์ &amp; แนว</li>
            <li data-step-indicator="3">3. ครอบ &amp; หมุน</li>
            <li data-step-indicator="4">4. ตำแหน่งชื่อ</li>
            <li data-step-indicator="5">5. ยืนยัน</li>
        </ol>

        <!-- Step 1: Info -->
        <section class="cew-step" data-step="1">
            <h3>ขั้นที่ 1 — ข้อมูลกิจกรรม</h3>
            <div class="cew-field">
                <label for="cewTitle">ชื่อกิจกรรม / หัวข้ออบรม <span style="color:#dc2626;">*</span></label>
                <input type="text" id="cewTitle" name="title" required value="<?= esc($titleVal, 'attr') ?>">
            </div>
            <div class="cew-field">
                <label for="cewDescription">รายละเอียด</label>
                <textarea id="cewDescription" name="description" rows="4"><?= esc($descVal) ?></textarea>
            </div>
            <div class="cew-field" style="max-width:240px;">
                <label for="cewEventDate">วันที่จัดกิจกรรม</label>
                <input type="date" id="cewEventDate" name="event_date" value="<?= esc($dateVal, 'attr') ?>">
            </div>
            <div class="cew-error" data-error-for="1"></div>
        </section>

        <!-- Step 2: Upload + Orientation -->
        <section class="cew-step" data-step="2" hidden>
            <h3>ขั้นที่ 2 — อัปโหลดแม่แบบและเลือกแนว</h3>
            <p class="cew-help">รองรับเฉพาะไฟล์ <strong>JPG</strong> หรือ <strong>PNG</strong> (ไม่รับ PDF) — ระบบจะเดาแนวกระดาษอัตโนมัติเมื่ออัปโหลด</p>

            <div class="cew-field">
                <label for="cewFile">เลือกไฟล์รูป<?= $hasImg ? ' (ปล่อยว่างเพื่อใช้ไฟล์เดิม)' : ' <span style="color:#dc2626;">*</span>' ?></label>
                <input type="file" id="cewFile" name="background_file" accept="image/jpeg,image/png,.jpg,.jpeg,.png">
                <?php if ($hasImg): ?>
                    <div class="cew-info">มีไฟล์เดิม: <?= esc(basename($bgFile)) ?> — เลือกไฟล์ใหม่เพื่อแทนที่</div>
                <?php elseif ($bgKind === 'pdf'): ?>
                    <div class="cew-info">ไฟล์เดิมเป็น PDF ซึ่งระบบใหม่ไม่รองรับแล้ว — กรุณาอัปโหลด JPG หรือ PNG ใหม่</div>
                <?php endif; ?>
            </div>

            <div class="cew-field">
                <label>เลือกแนวกระดาษ A4</label>
                <div class="cew-orient-grid">
                    <label class="cew-orient-tile" data-value="portrait">
                        <input type="radio" name="cew_orientation" value="portrait" <?= $initOrient === 'portrait' ? 'checked' : '' ?>>
                        <div class="cew-orient-icon"></div>
                        <strong>A4 แนวตั้ง</strong>
                        <div class="cew-orient-sub">210 × 297 mm</div>
                    </label>
                    <label class="cew-orient-tile" data-value="landscape">
                        <input type="radio" name="cew_orientation" value="landscape" <?= $initOrient === 'landscape' ? 'checked' : '' ?>>
                        <div class="cew-orient-icon"></div>
                        <strong>A4 แนวนอน</strong>
                        <div class="cew-orient-sub">297 × 210 mm</div>
                    </label>
                </div>
            </div>
            <div class="cew-error" data-error-for="2"></div>
        </section>

        <!-- Step 3: Crop & Rotate -->
        <section class="cew-step" data-step="3" hidden>
            <h3>ขั้นที่ 3 — ครอบและหมุนภาพ</h3>
            <p class="cew-help">กรอบครอบ <strong>ล็อกอัตราส่วน A4</strong> ตามแนวที่เลือกในขั้นก่อนหน้า — ลาก/ย่อ-ขยายกรอบและหมุนภาพได้</p>
            <div class="cew-crop-wrap">
                <img class="cew-crop-target" alt="" src="">
            </div>
            <div class="cew-crop-tools">
                <button type="button" class="cew-btn cew-btn-secondary" data-action="rotate-left">หมุนซ้าย 90°</button>
                <button type="button" class="cew-btn cew-btn-secondary" data-action="rotate-right">หมุนขวา 90°</button>
                <button type="button" class="cew-btn cew-btn-secondary" data-action="reset-crop">รีเซ็ตกรอบ</button>
            </div>
            <div class="cew-error" data-error-for="3"></div>
        </section>

        <!-- Step 4: Position name -->
        <section class="cew-step" data-step="4" hidden>
            <h3>ขั้นที่ 4 — ระบุตำแหน่งชื่อผู้รับใบประกาศ</h3>
            <p class="cew-help">ลากเมาส์บนภาพเพื่อวาดกรอบครอบตำแหน่งที่ต้องการให้ชื่อปรากฏ — ลากใหม่ได้เรื่อย ๆ จนพอใจ</p>
            <div class="cew-stage-wrap">
                <div class="cew-stage" data-role="position-stage">
                    <div class="cew-sheet" data-role="position-sheet">
                        <img class="cew-stage-img" alt="แม่แบบใบรับรอง">
                        <div class="cew-rubber" hidden></div>
                        <div class="cew-rect" hidden></div>
                        <div class="cew-ghost" hidden></div>
                    </div>
                </div>
            </div>
            <div class="cew-error" data-error-for="4"></div>
        </section>

        <!-- Step 5: Confirm -->
        <section class="cew-step" data-step="5" hidden>
            <h3>ขั้นที่ 5 — ยืนยันข้อมูล</h3>
            <div class="cew-summary cew-summary-section">
                <dl>
                    <dt>ชื่อกิจกรรม:</dt>  <dd data-summary="title">—</dd>
                    <dt>รายละเอียด:</dt>   <dd data-summary="description">—</dd>
                    <dt>วันที่:</dt>        <dd data-summary="event_date">—</dd>
                    <dt>แนวกระดาษ:</dt>    <dd data-summary="orientation">—</dd>
                    <dt>ตำแหน่งชื่อ:</dt>  <dd data-summary="namebox">—</dd>
                </dl>
                <p style="font-size:13px;color:#475569;margin:0 0 0.5rem;font-weight:600;">Preview ตำแหน่งชื่อบนแม่แบบ:</p>
                <div class="cew-stage-wrap">
                    <div class="cew-stage" data-role="preview-stage">
                        <div class="cew-sheet">
                            <img class="cew-stage-img" alt="">
                            <div class="cew-rect" hidden></div>
                            <div class="cew-ghost" hidden></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="cew-error" data-error-for="5"></div>
        </section>

        <input type="hidden" name="layout_json" id="cewLayoutJson" value="<?= esc($initialLayout, 'attr') ?>">

        <div class="cew-nav">
            <div class="cew-nav-group">
                <a href="<?= esc($cancel_url, 'attr') ?>" class="cew-btn cew-btn-secondary">ยกเลิก</a>
                <button type="button" class="cew-btn cew-btn-secondary" id="cewBack" hidden>ย้อนกลับ</button>
            </div>
            <div class="cew-nav-group">
                <button type="button" class="cew-btn cew-btn-primary" id="cewNext">ถัดไป</button>
                <button type="submit" class="cew-btn cew-btn-success" id="cewSubmit" hidden>บันทึก</button>
            </div>
        </div>
    </form>
</div>

<script src="<?= base_url('js/cert-event-wizard.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.CertEventWizard) {
        window.CertEventWizard.init('#cewForm');
    }
});
</script>

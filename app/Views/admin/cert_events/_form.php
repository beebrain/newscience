<?php
/**
 * Cert Event Form Partial - Used in modals
 *
 * @var array|null $event Event data for editing (null for create)
 * @var array $signers Available signers
 */
$isEdit = isset($event) && $event;
$cb = $cert_base ?? rtrim(base_url('admin/cert-events'), '/');
?>
<form id="certEventForm"
      method="post"
      enctype="multipart/form-data"
      action="<?= $isEdit ? esc($cb) . '/' . (int) $event['id'] . '/update' : esc($cb) . '/store' ?>"
      data-ajax="true"
      data-modal="certEventModal"
      data-reload="true"
      data-toast="<?= $isEdit ? 'อัปเดตกิจกรรมสำเร็จ' : 'สร้างกิจกรรมสำเร็จ' ?>">

    <?= csrf_field() ?>

    <div class="form-group" style="margin-bottom: 1rem;">
        <label for="title">ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
        <input type="text"
               id="title"
               name="title"
               class="form-control"
               value="<?= esc($event['title'] ?? old('title', '')) ?>"
               required
               placeholder="เช่น อบรมการใช้งานโปรแกรม Python สำหรับนักศึกษาใหม่">
    </div>

    <div class="form-group" style="margin-bottom: 1rem;">
        <label for="description">รายละเอียด</label>
        <textarea id="description"
                  name="description"
                  class="form-control"
                  rows="3"
                  placeholder="รายละเอียดเพิ่มเติมเกี่ยวกับกิจกรรม"><?= esc($event['description'] ?? old('description', '')) ?></textarea>
    </div>

    <div class="cert-event-bg-callout" style="margin-bottom: 1rem; padding: 1rem; border: 1px solid #f59e0b; border-radius: 0.5rem; background: #fffbeb;">
        <strong style="display:block; margin-bottom: 0.35rem; color: #92400e;">แนบแม่แบบใบรับรอง (สำคัญ)</strong>
        <p style="margin: 0 0 0.75rem; font-size: 13px; color: #78350f; line-height: 1.45;">
            อัปโหลด<strong>รูป JPG / PNG</strong> หรือ <strong>PDF</strong> ของใบรับรองที่ออกแบบไว้แล้ว (พื้นหลังว่างสำหรับชื่อผู้รับ)
            — ระบบจะซ้อนชื่อ วัตถุประสงค์ QR และลายเซ็นบนไฟล์นี้ ไม่ได้ดึงเทมเพลตจากเมนูเทมเพลตของระบบ
        </p>
        <label for="background_file" style="font-weight: 600; font-size: 14px;">เลือกไฟล์ใบรับรอง</label>
        <input type="file"
               id="background_file"
               name="background_file"
               class="form-control"
               accept=".pdf,.jpg,.jpeg,.png"
               style="margin-top: 0.25rem;">
        <p id="certEventBgHint" class="form-text" style="margin: 0.5rem 0 0; font-size: 12px; color: #92400e;">
            <?php if ($isEdit && ! empty($event['background_file'])): ?>
                ไฟล์ปัจจุบัน: <?= esc($event['background_kind'] ?? '') ?> — <?= esc($event['background_file']) ?> (เลือกไฟล์ใหม่เพื่อแทนที่)
            <?php else: ?>
                ยังไม่มีไฟล์ — แนะนำแนบตอนสร้างหรือแก้ไขก่อนกดออกใบ
            <?php endif; ?>
        </p>
    </div>

    <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
        <div class="form-group">
            <label for="event_date">วันที่จัดกิจกรรม</label>
            <input type="date"
                   id="event_date"
                   name="event_date"
                   class="form-control"
                   value="<?= esc($event['event_date'] ?? old('event_date', '')) ?>">
        </div>

        <div class="form-group">
            <label for="status">สถานะ</label>
            <select id="status" name="status" class="form-control" required>
                <option value="draft" <?= ($event['status'] ?? old('status', 'draft')) === 'draft' ? 'selected' : '' ?>>ร่าง (Draft)</option>
                <option value="open" <?= ($event['status'] ?? old('status')) === 'open' ? 'selected' : '' ?>>เปิด (Open)</option>
                <option value="issued" <?= ($event['status'] ?? old('status')) === 'issued' ? 'selected' : '' ?>>ออก Cert แล้ว (Issued)</option>
                <option value="closed" <?= ($event['status'] ?? old('status')) === 'closed' ? 'selected' : '' ?>>ปิด (Closed)</option>
            </select>
            <small class="form-text text-muted">
                ร่าง = กำลังเตรียม, เปิด = พร้อมเพิ่มผู้รับ, ออก Cert แล้ว = ออกใบรับรองแล้ว, ปิด = จบกิจกรรม
            </small>
        </div>
    </div>

    <div class="form-group" style="margin-bottom: 1rem;">
        <label for="signer_id">ผู้ลงนามในใบรับรอง</label>
        <select id="signer_id" name="signer_id" class="form-control">
            <option value="">ไม่ระบุ (ไม่แสดงลายเซ็น)</option>
            <?php foreach ($signers as $signer): ?>
                <option value="<?= $signer['uid'] ?>"
                        <?= ($event['signer_id'] ?? old('signer_id')) == $signer['uid'] ? 'selected' : '' ?>>
                    <?= esc($signer['tf_name'] ?? $signer['name']) ?>
                    (<?= $signer['position'] ?? 'ไม่ระบุตำแหน่ง' ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group" style="margin-bottom: 0;">
        <label for="layout_json">ปรับตำแหน่งข้อความ (ไม่บังคับ)</label>
        <textarea id="layout_json"
                  name="layout_json"
                  class="form-control"
                  rows="3"
                  placeholder='{"field_mapping":{"student_name":{"x":90,"y":145,"font_size":22}},...}'><?= esc($event['layout_json'] ?? old('layout_json', '')) ?></textarea>
        <small class="form-text text-muted">เว้นว่างได้ — ใช้เมื่อต้องการเลื่อนตำแหน่งชื่อ/QR เทียบกับแม่แบบ</small>
    </div>

</form>

<style>
.form-group label {
    display: block;
    margin-bottom: 0.25rem;
    font-weight: 500;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    font-size: 14px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
}

.text-danger {
    color: #dc3545;
}

.text-muted {
    color: #6b7280;
    font-size: 12px;
}
</style>

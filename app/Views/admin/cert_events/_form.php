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
    
    <p class="form-text text-muted" style="margin-bottom: 1rem; font-size: 13px;">
        ใบรับรองแต่ละกิจกรรมใช้<strong>ไฟล์รูปหรือ PDF ของใบรับรอง</strong>ที่อัปโหลดในหน้าแก้ไขกิจกรรม (ไม่ใช้เทมเพลตจากระบบ)
        — หลังสร้างกิจกรรมแล้วให้เปิดเมนู «ดู» หรือ «แก้ไข» เพื่ออัปโหลดไฟล์ก่อนออกใบ
    </p>

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

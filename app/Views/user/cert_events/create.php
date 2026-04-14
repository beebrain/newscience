<?php $cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/'); ?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">สร้างกิจกรรมใบรับรอง</h2>
    </div>

    <div class="card-body">
        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" action="<?= esc($cb) ?>/store" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= esc(old('title')) ?>" required>
                <small class="form-text text-muted">เช่น "อบรมหลักสูตร Python 2024", "การประชุมวิชาการ"</small>
            </div>

            <div class="form-group">
                <label>รายละเอียด</label>
                <textarea name="description" class="form-control" rows="4"><?= esc(old('description')) ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem; padding: 1rem; border: 1px solid #f59e0b; border-radius: 0.5rem; background: #fffbeb;">
                <strong style="display:block; margin-bottom: 0.35rem; color: #92400e;">แนบแม่แบบใบรับรอง (รูปหรือ PDF)</strong>
                <p style="margin: 0 0 0.75rem; font-size: 13px; color: #78350f; line-height: 1.5;">
                    อัปโหลดไฟล์<strong> JPG / PNG / PDF</strong> ที่เป็นแบบใบรับรองจริง — ระบบจะซ้อนชื่อและ QR บนไฟล์นี้ ไม่ใช้เทมเพลตจากระบบ
                </p>
                <label style="font-weight: 600;">เลือกไฟล์ <small style="font-weight:400;color:#78350f;">(ต้องมีก่อนออกใบ)</small></label>
                <input type="file" id="cert_event_background_file" name="background_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="margin-top:0.25rem;">
            </div>

            <div class="grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label>วันที่จัดกิจกรรม</label>
                    <input type="date" name="event_date" class="form-control" value="<?= esc(old('event_date')) ?>">
                </div>

                <div class="form-group">
                    <label>สถานะ</label>
                    <select name="status" class="form-control">
                        <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>ร่าง (ยังไม่เปิด)</option>
                        <option value="open" <?= old('status', 'open') === 'open' ? 'selected' : '' ?>>เปิด (พร้อมเพิ่มรายชื่อ)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>ผู้ลงนาม</label>
                <select name="signer_id" class="form-control">
                    <option value="">-- ไม่ระบุ (ไม่มีลายเซ็น) --</option>
                    <?php foreach ($signers as $signer): ?>
                        <option value="<?= $signer['uid'] ?>" <?= old('signer_id') == $signer['uid'] ? 'selected' : '' ?>>
                            <?= esc(($signer['tf_name'] ?? $signer['gf_name']) . ' ' . ($signer['tl_name'] ?? $signer['gl_name'])) ?> (<?= $signer['role'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?= view('admin/cert_events/partials/cert_layout_picker', [
                'layoutHiddenId'      => 'cert_event_layout_json',
                'fileInputId'         => 'cert_event_background_file',
                'cert_base'           => $cb,
                'event'               => null,
                'initial_layout_json' => (string) old('layout_json', ''),
            ]) ?>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= esc($cb) ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">สร้างกิจกรรม</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('js/cert-layout-picker.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.CertLayoutPicker) {
        window.CertLayoutPicker.initAll();
    }
});
</script>
<?= $this->endSection() ?>

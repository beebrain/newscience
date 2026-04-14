<?php $cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/'); ?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">แก้ไขกิจกรรม</h2>
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

        <form method="post" action="<?= esc($cb) ?>/<?= (int) $event['id'] ?>/update" enctype="multipart/form-data" class="form-grid">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" value="<?= esc(old('title', $event['title'])) ?>" required>
            </div>

            <div class="form-group">
                <label>รายละเอียด</label>
                <textarea name="description" class="form-control" rows="4"><?= esc(old('description', $event['description'])) ?></textarea>
            </div>

            <div style="margin-bottom: 1.25rem; padding: 1rem; border: 1px solid #f59e0b; border-radius: 0.5rem; background: #fffbeb;">
                <strong style="display:block; margin-bottom: 0.35rem; color: #92400e;">แนบแม่แบบใบรับรอง (รูปหรือ PDF)</strong>
                <p style="margin: 0 0 0.75rem; font-size: 13px; color: #78350f; line-height: 1.5;">
                    อัปโหลดไฟล์<strong> JPG / PNG / PDF</strong> ของใบรับรองที่ออกแบบแล้ว — ระบบจะซ้อนชื่อและ QR บนไฟล์นี้
                </p>
                <label style="font-weight: 600;">เปลี่ยนไฟล์แม่แบบ (ถ้าต้องการ)</label>
                <input type="file" id="cert_event_background_file" name="background_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" style="margin-top:0.25rem;">
                <?php if (! empty($event['background_file'])): ?>
                    <small class="form-text text-muted" style="display:block;margin-top:0.5rem;">ปัจจุบัน: <?= esc($event['background_kind'] ?? '') ?> — <?= esc($event['background_file']) ?></small>
                <?php else: ?>
                    <small class="form-text text-muted" style="display:block;margin-top:0.5rem;color:#b45309;">ยังไม่มีไฟล์ — ต้องอัปโหลดก่อนออกใบ</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>วันที่จัดกิจกรรม</label>
                <input type="date" name="event_date" class="form-control" value="<?= esc(old('event_date', $event['event_date'])) ?>">
            </div>

            <?= view('admin/cert_events/partials/cert_layout_picker', [
                'layoutHiddenId'      => 'cert_event_layout_json',
                'fileInputId'         => 'cert_event_background_file',
                'cert_base'           => $cb,
                'event'               => $event,
                'initial_layout_json' => (string) old('layout_json', $event['layout_json'] ?? ''),
            ]) ?>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= esc($cb) ?>/<?= (int) $event['id'] ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
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

<?php
/** @var array<string,mixed> $event */
/** @var string $cert_base */
$cb = $cert_base ?? rtrim(base_url('dashboard/cert-events'), '/');
?>
<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('styles') ?>
<style>
.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.15s;
    box-sizing: border-box;
}
.form-control:focus { outline: none; border-color: #eab308; box-shadow: 0 0 0 3px rgba(234,179,8,0.15); }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.35rem; font-weight: 600; font-size: 14px; color: #374151; }
.form-text { font-size: 12px; color: #6b7280; margin-top: 0.25rem; }
.form-actions { display: flex; gap: 1rem; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid #e5e7eb; }
.btn { display: inline-block; padding: 0.45rem 1rem; border-radius: 0.5rem; font-size: 14px; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: opacity 0.15s; }
.btn:hover { opacity: 0.88; }
.btn-primary { background: #eab308; color: #111827; }
.btn-secondary { background: #e5e7eb; color: #374151; }
.btn-danger { background: #dc2626; color: #fff; }
.btn-sm { padding: 0.25rem 0.6rem; font-size: 12px; }
.text-danger { color: #dc2626; }
.card { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
.card-body { padding: 1.5rem; }
.alert { padding: 0.875rem 1rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 14px; }
.alert-danger { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-6">
    <div class="card">
        <div class="card-header">
            <h2 style="margin: 0; font-size: 1.25rem; color: #111827;">แก้ไขกิจกรรม</h2>
        </div>

        <div class="card-body">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        <?php foreach ((array) session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc((string) $error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc($cb) ?>/<?= (int) $event['id'] ?>/update" enctype="multipart/form-data">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="title">ชื่อกิจกรรม/หัวข้ออบรม <span class="text-danger">*</span></label>
                    <input type="text"
                           id="title"
                           name="title"
                           class="form-control"
                           value="<?= esc((string) old('title', $event['title'] ?? '')) ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="description">รายละเอียด</label>
                    <textarea id="description"
                              name="description"
                              class="form-control"
                              rows="4"><?= esc((string) old('description', $event['description'] ?? '')) ?></textarea>
                </div>

                <!-- Background File Upload -->
                <div style="margin-bottom: 1.25rem; padding: 1rem; border: 1px solid #f59e0b; border-radius: 0.5rem; background: #fffbeb;">
                    <strong style="display:block; margin-bottom: 0.35rem; color: #92400e;">แนบแม่แบบใบรับรอง (รูปหรือ PDF)</strong>
                    <p style="margin: 0 0 0.75rem; font-size: 13px; color: #78350f; line-height: 1.5;">
                        อัปโหลดไฟล์<strong> JPG / PNG / PDF</strong> ของใบรับรองที่ออกแบบแล้ว — ระบบจะซ้อนชื่อและ QR บนไฟล์นี้
                    </p>
                    <label style="font-weight: 600; font-size: 14px;">เปลี่ยนไฟล์แม่แบบ (ถ้าต้องการ)</label>
                    <input type="file"
                           id="cert_event_background_file"
                           name="background_file"
                           class="form-control"
                           accept=".pdf,.jpg,.jpeg,.png"
                           style="margin-top: 0.35rem;">
                    <?php if (! empty($event['background_file'])): ?>
                        <p class="form-text">ปัจจุบัน: <?= esc($event['background_kind'] ?? '') ?> — <?= esc($event['background_file']) ?></p>
                    <?php else: ?>
                        <p class="form-text" style="color:#b45309;">ยังไม่มีไฟล์ — ต้องอัปโหลดก่อนออกใบ</p>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="event_date">วันที่จัดกิจกรรม</label>
                    <input type="date"
                           id="event_date"
                           name="event_date"
                           class="form-control"
                           value="<?= esc((string) old('event_date', $event['event_date'] ?? '')) ?>"
                           style="max-width: 240px;">
                </div>

                <?= view('admin/cert_events/partials/cert_layout_picker', [
                    'layoutHiddenId'      => 'cert_event_layout_json',
                    'fileInputId'         => 'cert_event_background_file',
                    'cert_base'           => $cb,
                    'event'               => $event,
                    'initial_layout_json' => (string) old('layout_json', $event['layout_json'] ?? ''),
                ]) ?>

                <div class="form-actions">
                    <a href="<?= esc($cb) ?>/<?= (int) $event['id'] ?>" class="btn btn-secondary">ยกเลิก</a>
                    <button type="submit" class="btn btn-primary">บันทึกการแก้ไข</button>
                </div>
            </form>
        </div>
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

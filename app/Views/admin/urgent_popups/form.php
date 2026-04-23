<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($popup) && $popup !== null;
$formAction = $isEdit ? base_url('admin/urgent-popups/update/' . $popup['id']) : base_url('admin/urgent-popups/store');
$pageLabel = $isEdit ? 'แก้ไขประกาศด่วน' : 'เพิ่มประกาศด่วน';
?>

<div class="card">
    <div class="card-header">
        <h2><?= $pageLabel ?></h2>
        <a href="<?= base_url('admin/urgent-popups') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <div class="card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div class="form-group">
                <label for="title" class="form-label">หัวข้อประกาศ <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required
                       value="<?= esc($popup['title'] ?? old('title')) ?>"
                       placeholder="เช่น ประกาศปิดเรียนชั่วคราว">
            </div>

            <div class="form-group">
                <label for="content" class="form-label">เนื้อหา (รองรับ HTML)</label>
                <textarea id="content" name="content" class="form-control" rows="5"
                          placeholder="รายละเอียดประกาศ..."><?= esc($popup['content'] ?? old('content')) ?></textarea>
                <small class="form-text">สามารถใช้แท็ก HTML พื้นฐาน เช่น &lt;strong&gt;, &lt;a href="..."&gt;</small>
            </div>

            <div class="form-group">
                <label for="image" class="form-label">รูปภาพ (ไม่บังคับ) — เลือกอัตราส่วน crop หรือใช้ภาพต้นฉบับก็ได้</label>
                <div class="popup-image-box <?= ($isEdit && !empty($popup['image'])) ? 'has-image' : '' ?>" id="popupImageBox" role="button" tabindex="0" aria-label="เลือกรูปประกาศด่วน">
                    <div id="popupImagePlaceholder">
                        <?php if ($isEdit && !empty($popup['image'])): ?>
                            <?php $imgUrl = image_manager_serve_url('popup', $popup['image']); ?>
                            <div class="popup-image-preview">
                                <img src="<?= esc($imgUrl) ?>" alt="" style="max-width: 100%; max-height: 160px; object-fit: contain;">
                            </div>
                            <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: var(--color-gray-500);">คลิกเพื่อเปลี่ยนภาพ</p>
                        <?php else: ?>
                            <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin-bottom: 0.5rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p style="margin: 0; color: var(--color-gray-600);">คลิกเพื่อเลือกรูป หรือลากวาง</p>
                            <small style="color: var(--color-gray-500);">JPG, PNG, WebP — เลือกแล้วจะมีตัวเลือกอัตราส่วน หรือจะไม่ crop ก็ได้</small>
                        <?php endif; ?>
                    </div>
                </div>
                <input type="file" id="image" name="image" accept="image/*" class="input-file-hidden" aria-hidden="true">
                <input type="hidden" name="image_base64" id="image_base64" value="">
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="link_url" class="form-label">ลิงก์ (URL)</label>
                    <input type="url" id="link_url" name="link_url" class="form-control"
                           value="<?= esc($popup['link_url'] ?? old('link_url')) ?>"
                           placeholder="https://...">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="link_text" class="form-label">ข้อความปุ่ม</label>
                    <input type="text" id="link_text" name="link_text" class="form-control"
                           value="<?= esc($popup['link_text'] ?? old('link_text') ?: 'ดูรายละเอียด') ?>"
                           placeholder="ดูรายละเอียด">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="start_date" class="form-label">วันที่เริ่มแสดง</label>
                    <input type="datetime-local" id="start_date" name="start_date" class="form-control"
                           value="<?= $isEdit && !empty($popup['start_date']) ? date('Y-m-d\TH:i', strtotime($popup['start_date'])) : (old('start_date') ?? '') ?>">
                    <small class="form-text">ว่างไว้ = แสดงทันที</small>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="end_date" class="form-label">วันที่หยุดแสดง</label>
                    <input type="datetime-local" id="end_date" name="end_date" class="form-control"
                           value="<?= $isEdit && !empty($popup['end_date']) ? date('Y-m-d\TH:i', strtotime($popup['end_date'])) : (old('end_date') ?? '') ?>">
                    <small class="form-text">ว่างไว้ = แสดงตลอด</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="sort_order" class="form-label">ลำดับ (0 = แรก)</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
                           value="<?= (int)($popup['sort_order'] ?? old('sort_order') ?? 0) ?>">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">&nbsp;</label>
                    <div style="display: flex; align-items: center; padding-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_active" value="1" <?= ($popup['is_active'] ?? 1) ? 'checked' : '' ?>>
                            เปิดแสดงบนหน้าแรก
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึก' ?></button>
                <a href="<?= base_url('admin/urgent-popups') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: flex; gap: 1rem; }
.form-row .form-group { margin-bottom: 1rem; }
.required { color: #EF4444; }
.popup-image-box { border: 2px dashed var(--color-gray-300, #d1d5db); border-radius: 8px; padding: 1.5rem; text-align: center; cursor: pointer; min-height: 140px; display: flex; align-items: center; justify-content: center; }
.popup-image-box.has-image { border-style: solid; }
.popup-image-box:hover { border-color: var(--primary, #eab308); background: #fefce8; }
.input-file-hidden { position: absolute; width: 0; height: 0; opacity: 0; overflow: hidden; }
@media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/js/smart-image-crop.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    SmartImageCrop.mount({
        triggerEl:   document.getElementById('popupImageBox'),
        fileInput:   document.getElementById('image'),
        base64Input: document.getElementById('image_base64'),
        previewEl:   document.getElementById('popupImagePlaceholder'),
        entity: 'popup',
        aspectPresets: [
            { value: 'free', label: 'อิสระ (แนะนำสำหรับโปสเตอร์)', ratio: NaN },
            { value: '4:3',  label: '4:3',  ratio: 4/3 },
            { value: '3:4',  label: '3:4 (แนวตั้ง)',  ratio: 3/4 },
            { value: '1:1',  label: '1:1',  ratio: 1 },
            { value: '16:9', label: '16:9', ratio: 16/9 },
        ],
        defaultAspect: 'free',
        allowNoCrop: true,
        maxWidth: 1600,
        maxHeight: 1600,
        quality: 0.92,
    });
});
</script>

<?= $this->endSection() ?>

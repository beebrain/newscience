<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($poster) && $poster !== null;
$formAction = $isEdit ? base_url('admin/executive-posters/update/' . $poster['id']) : base_url('admin/executive-posters/store');
$pageLabel = $isEdit ? 'แก้ไขโปสเตอร์ผู้บริหาร' : 'เพิ่มโปสเตอร์ผู้บริหาร';
?>

<div class="card">
    <div class="card-header">
        <h2><?= $pageLabel ?></h2>
        <a href="<?= base_url('admin/executive-posters') ?>" class="btn btn-secondary">← กลับ</a>
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
                <label for="title" class="form-label">ชื่อ / ตำแหน่ง <span class="required">*</span></label>
                <input type="text" id="title" name="title" class="form-control" required
                       value="<?= esc($poster['title'] ?? old('title')) ?>"
                       placeholder="เช่น ผศ.ดร.ปริญญา ไกรวุฒินันท์ — คณบดี">
                <small class="form-text">ใช้เป็น alt text และแสดงใต้โปสเตอร์</small>
            </div>

            <div class="form-group">
                <label for="caption" class="form-label">คำบรรยายรอง (ไม่บังคับ)</label>
                <input type="text" id="caption" name="caption" class="form-control"
                       value="<?= esc($poster['caption'] ?? old('caption')) ?>"
                       placeholder="เช่น Dean, Faculty of Science and Technology">
            </div>

            <div class="form-group">
                <label for="image" class="form-label">โปสเตอร์ <span class="required">*</span> — แนะนำสัดส่วนแนวตั้ง 3:4 หรือ 2:3</label>
                <div class="poster-image-box <?= ($isEdit && !empty($poster['image'])) ? 'has-image' : '' ?>" id="posterImageBox" role="button" tabindex="0" aria-label="เลือกโปสเตอร์ผู้บริหาร">
                    <div id="posterImagePlaceholder">
                        <?php if ($isEdit && !empty($poster['image'])): ?>
                            <?php $imgUrl = image_manager_serve_url('executive_poster', $poster['image']); ?>
                            <div class="poster-image-preview">
                                <img src="<?= esc($imgUrl) ?>" alt="" style="max-width: 100%; max-height: 240px; object-fit: contain;">
                            </div>
                            <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: var(--color-gray-500);">คลิกเพื่อเปลี่ยนภาพ</p>
                        <?php else: ?>
                            <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin-bottom: 0.5rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p style="margin: 0; color: var(--color-gray-600);">คลิกเพื่อเลือกโปสเตอร์ หรือลากวาง</p>
                            <small style="color: var(--color-gray-500);">JPG, PNG, WebP — แนะนำขนาด 1080×1440 หรือสัดส่วนแนวตั้ง</small>
                        <?php endif; ?>
                    </div>
                </div>
                <input type="file" id="image" name="image" accept="image/*" class="input-file-hidden" aria-hidden="true">
                <input type="hidden" name="image_base64" id="image_base64" value="">
            </div>

            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="link_url" class="form-label">ลิงก์ไปหน้าประวัติ (ไม่บังคับ)</label>
                    <input type="url" id="link_url" name="link_url" class="form-control"
                           value="<?= esc($poster['link_url'] ?? old('link_url')) ?>"
                           placeholder="https://... หรือ /personnel-cv/...">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="sort_order" class="form-label">ลำดับ (น้อย → มาก)</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" min="0"
                           value="<?= (int)($poster['sort_order'] ?? old('sort_order') ?? 0) ?>">
                </div>
            </div>

            <div class="form-group">
                <div style="display: flex; align-items: center; padding-top: 0.5rem;">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" value="1" <?= ($poster['is_active'] ?? 1) ? 'checked' : '' ?>>
                        เปิดแสดงใน slider หน้า About
                    </label>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึก' ?></button>
                <a href="<?= base_url('admin/executive-posters') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-row { display: flex; gap: 1rem; }
.form-row .form-group { margin-bottom: 1rem; }
.required { color: #EF4444; }
.poster-image-box { border: 2px dashed var(--color-gray-300, #d1d5db); border-radius: 8px; padding: 1.5rem; text-align: center; cursor: pointer; min-height: 180px; display: flex; align-items: center; justify-content: center; }
.poster-image-box.has-image { border-style: solid; }
.poster-image-box:hover { border-color: var(--primary, #eab308); background: #fefce8; }
.input-file-hidden { position: absolute; width: 0; height: 0; opacity: 0; overflow: hidden; }
@media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/js/smart-image-crop.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    SmartImageCrop.mount({
        triggerEl:   document.getElementById('posterImageBox'),
        fileInput:   document.getElementById('image'),
        base64Input: document.getElementById('image_base64'),
        previewEl:   document.getElementById('posterImagePlaceholder'),
        entity: 'executive_poster',
        aspectPresets: [
            { value: '3:4',  label: '3:4 (แนวตั้ง — แนะนำ)', ratio: 3/4 },
            { value: '2:3',  label: '2:3 (แนวตั้ง)',  ratio: 2/3 },
            { value: 'free', label: 'อิสระ', ratio: NaN },
            { value: '4:3',  label: '4:3',  ratio: 4/3 },
            { value: '1:1',  label: '1:1',  ratio: 1 },
        ],
        defaultAspect: '3:4',
        allowNoCrop: true,
        maxWidth: 1600,
        maxHeight: 2000,
        quality: 0.92,
    });
});
</script>

<?= $this->endSection() ?>

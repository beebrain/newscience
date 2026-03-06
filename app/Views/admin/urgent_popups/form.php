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
                <label for="image" class="form-label">รูปภาพ (ไม่บังคับ) — เลือกแล้วจะตัด crop ให้</label>
                <div class="popup-image-box <?= ($isEdit && !empty($popup['image'])) ? 'has-image' : '' ?>" id="popupImageBox" role="button" tabindex="0" aria-label="เลือกรูปประกาศด่วน">
                    <div id="popupImagePlaceholder">
                        <?php if ($isEdit && !empty($popup['image'])): ?>
                            <?php $imgUrl = base_url('serve/uploads/urgent_popups/' . basename($popup['image'])); ?>
                            <div class="popup-image-preview">
                                <img src="<?= $imgUrl ?>" alt="" style="max-width: 100%; max-height: 160px; object-fit: contain;">
                            </div>
                            <p style="margin: 0.5rem 0 0; font-size: 0.875rem; color: var(--color-gray-500);">คลิกเพื่อเปลี่ยนภาพ (จะเปิดให้ crop อีกครั้ง)</p>
                        <?php else: ?>
                            <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; margin-bottom: 0.5rem;"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            <p style="margin: 0; color: var(--color-gray-600);">คลิกเพื่อเลือกรูป หรือลากวาง</p>
                            <small style="color: var(--color-gray-500);">JPG, PNG, WebP — เลือกแล้วจะเปิดหน้าตัด crop (อัตราส่วน 4:3)</small>
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

<!-- โมดัล crop รูปประกาศด่วน -->
<div id="popup-crop-modal" class="popup-crop-modal" role="dialog" aria-modal="true" aria-labelledby="popup-crop-modal-title" style="display: none;">
    <div class="popup-crop-modal__backdrop"></div>
    <div class="popup-crop-modal__box">
        <div class="popup-crop-modal__header">
            <h3 id="popup-crop-modal-title" class="popup-crop-modal__title">ตัดรูปประกาศด่วน</h3>
            <button type="button" class="popup-crop-modal__close" id="popupCropClose" aria-label="ปิด">×</button>
        </div>
        <div class="popup-crop-modal__body">
            <div class="popup-crop-container">
                <img id="popup-crop-image" src="" alt="">
            </div>
        </div>
        <div class="popup-crop-modal__footer">
            <button type="button" class="btn btn-secondary" id="popupCropCancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="popupCropConfirm">ตัดและใช้ภาพ</button>
        </div>
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
.popup-crop-modal { position: fixed; inset: 0; z-index: 1050; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.popup-crop-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
.popup-crop-modal__box { position: relative; background: #fff; border-radius: 12px; max-width: 90vw; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.popup-crop-modal__header { display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-gray-200); flex-shrink: 0; }
.popup-crop-modal__title { margin: 0; font-size: 1.125rem; font-weight: 600; }
.popup-crop-modal__close { background: none; border: none; font-size: 1.5rem; line-height: 1; cursor: pointer; color: var(--color-gray-600); padding: 0 0.25rem; }
.popup-crop-modal__body { padding: 0; overflow: hidden; flex: 1; min-height: 0; }
.popup-crop-container { width: 100%; height: 60vh; max-height: 500px; background: #000; overflow: hidden; }
.popup-crop-container img { max-width: 100%; max-height: 100%; display: block; }
.popup-crop-modal__footer { padding: 1rem 1.25rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; flex-shrink: 0; }
@media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } }
</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var popupImageBox = document.getElementById('popupImageBox');
    var popupImagePlaceholder = document.getElementById('popupImagePlaceholder');
    var imageInput = document.getElementById('image');
    var imageBase64Input = document.getElementById('image_base64');
    var cropModal = document.getElementById('popup-crop-modal');
    var cropImageEl = document.getElementById('popup-crop-image');
    var cropCloseBtn = document.getElementById('popupCropClose');
    var cropCancelBtn = document.getElementById('popupCropCancel');
    var cropConfirmBtn = document.getElementById('popupCropConfirm');
    var cropperInstance = null;
    var cropObjectUrl = null;
    var hasOriginal = <?= ($isEdit && !empty($popup['image'])) ? 'true' : 'false' ?>;
    var originalPlaceholderHtml = popupImagePlaceholder ? popupImagePlaceholder.innerHTML : '';

    function openCropModal(file) {
        if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) return;
        if (cropObjectUrl) URL.revokeObjectURL(cropObjectUrl);
        cropObjectUrl = URL.createObjectURL(file);
        cropImageEl.src = cropObjectUrl;
        if (cropModal) cropModal.style.display = 'flex';
        if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
        setTimeout(function() {
            if (typeof Cropper !== 'undefined' && cropImageEl) {
                cropperInstance = new Cropper(cropImageEl, {
                    aspectRatio: 4 / 3,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.8,
                    restore: false,
                    guides: true,
                    center: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true
                });
            }
        }, 100);
    }

    function closeCropModal() {
        if (cropModal) cropModal.style.display = 'none';
        if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
        if (cropObjectUrl) { URL.revokeObjectURL(cropObjectUrl); cropObjectUrl = null; }
        if (imageInput) imageInput.value = '';
    }

    function applyCrop() {
        if (!cropperInstance || !imageBase64Input) return;
        cropperInstance.getCroppedCanvas({ maxWidth: 1200, maxHeight: 900, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
            var reader = new FileReader();
            reader.onload = function() {
                imageBase64Input.value = reader.result;
                if (imageInput) imageInput.value = '';
                if (popupImageBox) popupImageBox.classList.add('has-image');
                var resetBtn = hasOriginal ? '<button type="button" class="btn-popup-reset" style="margin-top:0.5rem;font-size:0.8125rem;color:var(--color-gray-500);background:none;border:none;cursor:pointer;text-decoration:underline;">ใช้ภาพเดิม</button>' : '';
                if (popupImagePlaceholder) {
                    popupImagePlaceholder.innerHTML = '<div class="popup-image-preview"><img src="' + reader.result + '" alt="" style="max-width:100%;max-height:160px;object-fit:contain;"></div><p style="margin:0.5rem 0 0;font-size:0.875rem;color:var(--color-gray-500);">คลิกเพื่อเปลี่ยนภาพ</p>' + resetBtn;
                    var btn = popupImagePlaceholder.querySelector('.btn-popup-reset');
                    if (btn) btn.addEventListener('click', function(ev) {
                        ev.stopPropagation();
                        imageInput.value = '';
                        imageBase64Input.value = '';
                        popupImagePlaceholder.innerHTML = originalPlaceholderHtml;
                        if (popupImageBox) popupImageBox.classList.remove('has-image');
                    });
                }
                closeCropModal();
            };
            reader.readAsDataURL(blob);
        }, 'image/jpeg', 0.9);
    }

    if (popupImageBox && imageInput) {
        popupImageBox.addEventListener('click', function(e) {
            if (e.target.closest('.btn-popup-reset')) return;
            imageInput.click();
        });
        popupImageBox.addEventListener('keydown', function(e) {
            if ((e.key === 'Enter' || e.key === ' ') && !e.target.closest('.btn-popup-reset')) { e.preventDefault(); imageInput.click(); }
        });
        popupImageBox.addEventListener('dragover', function(e) { e.preventDefault(); this.style.borderColor = 'var(--primary)'; });
        popupImageBox.addEventListener('dragleave', function(e) { e.preventDefault(); this.style.borderColor = ''; });
        popupImageBox.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) openCropModal(file);
        });
    }
    if (imageInput) imageInput.addEventListener('change', function() {
        var file = this.files[0];
        if (file && file.type.startsWith('image/')) openCropModal(file);
    });
    if (cropCloseBtn) cropCloseBtn.addEventListener('click', closeCropModal);
    if (cropCancelBtn) cropCancelBtn.addEventListener('click', closeCropModal);
    if (cropConfirmBtn) cropConfirmBtn.addEventListener('click', applyCrop);
    if (cropModal && cropModal.querySelector('.popup-crop-modal__backdrop')) {
        cropModal.querySelector('.popup-crop-modal__backdrop').addEventListener('click', closeCropModal);
    }
});
</script>

<?= $this->endSection() ?>

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
                <label for="image" class="form-label">รูปภาพ (ไม่บังคับ)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <?php if ($isEdit && !empty($popup['image'])): ?>
                    <div class="mt-2">
                        <img src="<?= base_url('serve/uploads/urgent_popups/' . basename($popup['image'])) ?>"
                             alt="" style="max-width: 200px; max-height: 120px; border-radius: 8px;">
                        <small class="d-block text-muted">รูปปัจจุบัน — อัปโหลดใหม่เพื่อเปลี่ยน</small>
                    </div>
                <?php endif; ?>
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
@media (max-width: 768px) { .form-row { flex-direction: column; gap: 0; } }
</style>

<?= $this->endSection() ?>

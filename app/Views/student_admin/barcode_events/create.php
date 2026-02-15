<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2>สร้าง Event แจกบาร์โค้ด</h2>
        <a href="<?= base_url('student-admin/barcode-events') ?>" class="btn btn-secondary">ยกเลิก</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('student-admin/barcode-events/store') ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="title" class="form-label">ชื่อ Event *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= old('title') ?>" required maxlength="500">
            </div>
            <div class="form-group">
                <label for="description" class="form-label">รายละเอียด</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= old('description') ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date" class="form-label">วันที่ *</label>
                <input type="date" id="event_date" name="event_date" class="form-control" value="<?= old('event_date') ?>" required>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">สถานะ *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="active" <?= old('status') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="closed" <?= old('status') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">สร้าง Event</button>
        </form>
        <p class="form-text" style="margin-top: 1rem; color: var(--color-gray-600); font-size: 0.9375rem;">หลังสร้าง Event แล้ว ให้ไปที่หน้ารายละเอียด Event เพื่อเพิ่มบาร์โค้ดก่อน จากนั้นจึงเพิ่มผู้มีสิทธิ์รับบาร์โค้ดได้</p>
    </div>
</div>
<?= $this->endSection() ?>

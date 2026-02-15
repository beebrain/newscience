<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2>แก้ไข Event</h2>
        <a href="<?= base_url('student-admin/barcode-events') ?>" class="btn btn-secondary">กลับรายการ</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('student-admin/barcode-events/update/' . $event['id']) ?>" method="post">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="title" class="form-label">ชื่อ Event *</label>
                <input type="text" id="title" name="title" class="form-control" value="<?= esc($event['title'] ?? '') ?>" required maxlength="500">
            </div>
            <div class="form-group">
                <label for="description" class="form-label">รายละเอียด</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= esc($event['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="event_date" class="form-label">วันที่ *</label>
                <input type="date" id="event_date" name="event_date" class="form-control" value="<?= esc($event['event_date'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="status" class="form-label">สถานะ *</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="draft" <?= ($event['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="active" <?= ($event['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="closed" <?= ($event['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">บันทึก</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

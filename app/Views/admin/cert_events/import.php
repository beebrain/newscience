<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">นำเข้ารายชื่อผู้รับ</h2>
    </div>

    <div class="card-body">
        <div style="background: #e3f2fd; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
            <h4 style="margin-top: 0;">กิจกรรม: <?= esc($event['title']) ?></h4>
            <p style="margin-bottom: 0;">รองรับไฟล์ CSV หรือ TXT ที่มีคอลัมน์ดังนี้:</p>
            <ul style="margin: 0.5rem 0;">
                <li><strong>name</strong> หรือ <strong>ชื่อ</strong> (จำเป็น)</li>
                <li><strong>email</strong> หรือ <strong>อีเมล</strong> (ถ้ามี)</li>
                <li><strong>student_id</strong> หรือ <strong>รหัสนักศึกษา</strong> (ถ้ามี)</li>
                <li><strong>program</strong> หรือ <strong>หลักสูตร</strong> (ถ้ามี)</li>
                <li><strong>note</strong> หรือ <strong>หมายเหตุ</strong> (ถ้ามี)</li>
            </ul>
        </div>

        <div style="background: #f5f5f5; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
            <strong>ตัวอย่างไฟล์ CSV:</strong>
            <pre style="background: white; padding: 0.5rem; border: 1px solid #ddd; margin: 0.5rem 0;">name,email,student_id,program
สมชาย ใจดี,somchai@email.com,65100001,วิทยาการคอมพิวเตอร์
สมหญิง รักเรียน,somying@email.com,65100002,เทคโนโลยีสารสนเทศ</pre>
        </div>

        <?php if (session()->getFlashdata('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach (session()->getFlashdata('errors') as $error): ?>
                        <li><?= esc($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/cert-events/' . $event['id'] . '/import') ?>">
            <?= csrf_field() ?>

            <div class="form-group">
                <label>เลือกไฟล์ CSV/TXT <span class="text-danger">*</span></label>
                <input type="file" name="csv_file" class="form-control" accept=".csv,.txt,text/csv" required>
                <small class="form-text text-muted">รองรับไฟล์ .csv หรือ .txt (UTF-8)</small>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                <a href="<?= base_url('admin/cert-events/' . $event['id']) ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">นำเข้ารายชื่อ</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

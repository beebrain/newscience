<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;">เพิ่มเทมเพลตใบรับรอง</h2>
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

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" action="<?= base_url('admin/cert-templates/store') ?>" class="form-grid">
            <?= csrf_field() ?>
            <div class="form-group">
                <label>ชื่อเทมเพลต (ไทย) <span class="text-danger">*</span></label>
                <input type="text" name="name_th" class="form-control" value="<?= esc(old('name_th')) ?>" required>
            </div>

            <div class="form-group">
                <label>ชื่อเทมเพลต (อังกฤษ)</label>
                <input type="text" name="name_en" class="form-control" value="<?= esc(old('name_en')) ?>">
            </div>

            <div class="form-group">
                <label>ระดับการออกใบรับรอง</label>
                <select name="level" class="form-control" required>
                    <option value="program" <?= old('level') === 'program' ? 'selected' : '' ?>>ระดับหลักสูตร (ประธานหลักสูตรลงนาม)</option>
                    <option value="faculty" <?= old('level') === 'faculty' ? 'selected' : '' ?>>ระดับคณะ (คณบดีลงนาม)</option>
                </select>
            </div>

            <div class="form-group">
                <label>สถานะ</label>
                <select name="status" class="form-control" required>
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>ปิดใช้งาน</option>
                </select>
            </div>

            <div class="form-group">
                <label>ไฟล์เทมเพลต (PDF) <span class="text-danger">*</span></label>
                <input type="file" name="template_file" class="form-control" accept="application/pdf" required>
                <small class="form-text text-muted">PDF background 1 หน้า ระบุ margin 0</small>
            </div>

            <div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit,minmax(200px,1fr)); gap: 1rem;">
                <?php
                    $defaults = [
                        'signature_x' => '300',
                        'signature_y' => '600',
                        'qr_x' => '450',
                        'qr_y' => '700',
                        'qr_size' => '60',
                    ];
                ?>
                <?php foreach ($defaults as $field => $default): ?>
                    <div class="form-group">
                        <label><?= strtoupper(str_replace('_', ' ', $field)) ?></label>
                        <input type="number" step="0.01" name="<?= $field ?>" class="form-control" value="<?= esc(old($field, $default)) ?>" required>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label>Field Mapping (JSON)</label>
                <textarea name="field_mapping" class="form-control" rows="8" placeholder='{"student_name":{"x":100,"y":200,"font_size":18}}'><?= esc(old('field_mapping')) ?></textarea>
                <small class="form-text text-muted">กำหนดตำแหน่งตัวแปรแต่ละตัวใน PDF (พิกัดเริ่มจากซ้ายบน)</small>
            </div>

            <div class="form-actions" style="display: flex; gap: 1rem;">
                <a href="<?= base_url('admin/cert-templates') ?>" class="btn btn-secondary">ยกเลิก</a>
                <button type="submit" class="btn btn-primary">บันทึกเทมเพลต</button>
            </div>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

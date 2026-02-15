<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>เพิ่มหลักสูตร</h2>
        <a href="<?= base_url('admin/programs') ?>" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
    <div class="card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc(is_array($e) ? implode(', ', $e) : $e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form action="<?= base_url('admin/programs/store') ?>" method="post">
            <?= csrf_field() ?>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name_th">ชื่อหลักสูตร (ไทย) <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="name_th" id="name_th" class="form-control" value="<?= esc(old('name_th')) ?>" required placeholder="เช่น คณิตศาสตร์ประยุกต์">
                </div>
                <div class="form-group">
                    <label class="form-label" for="name_en">ชื่อหลักสูตร (อังกฤษ)</label>
                    <input type="text" name="name_en" id="name_en" class="form-control" value="<?= esc(old('name_en')) ?>" placeholder="Applied Mathematics">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="degree_th">ชื่อปริญญา (ไทย)</label>
                    <input type="text" name="degree_th" id="degree_th" class="form-control" value="<?= esc(old('degree_th', 'วิทยาศาสตรบัณฑิต')) ?>" placeholder="วิทยาศาสตรบัณฑิต">
                </div>
                <div class="form-group">
                    <label class="form-label" for="degree_en">ชื่อปริญญา (อังกฤษ)</label>
                    <input type="text" name="degree_en" id="degree_en" class="form-control" value="<?= esc(old('degree_en', 'Bachelor of Science')) ?>" placeholder="Bachelor of Science">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="level">ระดับ <span style="color: var(--color-danger);">*</span></label>
                    <select name="level" id="level" class="form-control" required>
                        <option value="bachelor" <?= old('level') === 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                        <option value="master" <?= old('level') === 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                        <option value="doctorate" <?= old('level') === 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" for="organization_unit_id">หน่วยงาน (หลักสูตร)</label>
                    <select name="organization_unit_id" id="organization_unit_id" class="form-control">
                        <option value="">-- เลือกตามระดับ (ป.ตรี/บัณฑิต) --</option>
                        <?php foreach ($departments ?? [] as $d): ?>
                            <option value="<?= (int)$d['id'] ?>" <?= (string)old('organization_unit_id') === (string)$d['id'] ? 'selected' : '' ?>><?= esc($d['name_th']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="duration">ระยะเวลาเรียน</label>
                    <input type="text" name="duration" id="duration" class="form-control" value="<?= esc(old('duration', '4 ปี')) ?>" placeholder="4 ปี">
                </div>
                <div class="form-group">
                    <label class="form-label" for="credits">หน่วยกิต</label>
                    <input type="number" name="credits" id="credits" class="form-control" value="<?= esc(old('credits')) ?>" placeholder="ไม่ระบุ" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label" for="sort_order">ลำดับการแสดง</label>
                    <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= esc(old('sort_order')) ?>" placeholder="เว้นว่างได้ (ใช้ลำดับถัดไป)" min="0">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="status">สถานะ <span style="color: var(--color-danger);">*</span></label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" <?= old('status', 'active') === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                    <option value="inactive" <?= old('status') === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="website">เว็บไซต์หลักสูตร</label>
                <input type="url" name="website" id="website" class="form-control" value="<?= esc(old('website')) ?>" placeholder="https://...">
            </div>

            <div class="form-group">
                <label class="form-label" for="description">รายละเอียด (ไทย)</label>
                <textarea name="description" id="description" class="form-control" rows="3" placeholder="คำอธิบายหลักสูตร"><?= esc(old('description')) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label" for="description_en">รายละเอียด (อังกฤษ)</label>
                <textarea name="description_en" id="description_en" class="form-control" rows="3" placeholder="Program description"><?= esc(old('description_en')) ?></textarea>
            </div>

            <div style="display: flex; gap: 0.5rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="<?= base_url('admin/programs') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
            <p style="color: var(--color-gray-500); font-size: 0.9rem; margin-top: 1rem;">หลังบันทึกหลักสูตรแล้ว สามารถไปที่ <strong>แก้ไขหลักสูตร</strong> เพื่อเลือกประธานหลักสูตรจากรายชื่ออาจารย์ที่สังกัดหลักสูตรนี้ได้ (เพิ่มอาจารย์ในหลักสูตรได้จากหน้า <a href="<?= base_url('admin/organization') ?>">โครงสร้างองค์กร</a>)</p>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

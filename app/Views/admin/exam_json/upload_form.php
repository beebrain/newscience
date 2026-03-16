<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>นำเข้าตารางคุมสอบ</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">อัปโหลดไฟล์ Excel และบันทึกเป็น JSON</p>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem; max-width: 600px;">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-error" style="margin-bottom: 1rem; padding: 0.75rem 1rem; background: var(--color-danger); color: white; border-radius: 6px;">
                <?php if (is_array(session()->getFlashdata('error'))): ?>
                    <?php foreach (session()->getFlashdata('error') as $err): ?>
                        <p style="margin: 0;"><?= is_string($err) ? $err : json_encode($err) ?></p>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="margin: 0;"><?= session()->getFlashdata('error') ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/exam/upload') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">ภาคการศึกษา <span style="color: var(--color-danger);">*</span></label>
                <input type="text" name="semester" class="form-control" placeholder="เช่น 2/2568" required
                       value="<?= old('semester') ?>">
                <span style="font-size: 0.75rem; color: var(--color-gray-500);">รูปแบบ: เลขภาค/ปีการศึกษา (เช่น 1/2568, 2/2568, 3/2568)</span>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">ประเภทการสอบ <span style="color: var(--color-danger);">*</span></label>
                <select name="exam_type" class="form-control" required>
                    <option value="">-- เลือก --</option>
                    <option value="midterm" <?= old('exam_type') === 'midterm' ? 'selected' : '' ?>>กลางภาค (Midterm)</option>
                    <option value="final" <?= old('exam_type') === 'final' ? 'selected' : '' ?>>ปลายภาค (Final)</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label class="form-label">ไฟล์ Excel <span style="color: var(--color-danger);">*</span></label>
                <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
                <span style="font-size: 0.75rem; color: var(--color-gray-500);">รองรับไฟล์ .xlsx, .xls เท่านั้น</span>
            </div>

            <div class="info-box" style="background: var(--color-gray-50); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.875rem;">โครงสร้างไฟล์ Excel ที่รองรับ:</h4>
                <table style="font-size: 0.75rem; width: 100%;">
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ A:</strong> section</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ B:</strong> course_code</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ C:</strong> course_name</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ D:</strong> student_group</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ E:</strong> student_program</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ F:</strong> instructor</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ G:</strong> exam_date</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ H:</strong> exam_time</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ I:</strong> room</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ J:</strong> examiner1</td></tr>
                    <tr><td style="padding: 2px 0;"><strong>คอลัมน์ K:</strong> examiner2</td></tr>
                </table>
            </div>

            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="btn btn-primary">นำเข้าข้อมูล</button>
                <a href="<?= base_url('admin/exam') ?>" class="btn" style="background: var(--color-gray-200);">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary);
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}

.btn-primary {
    background: var(--color-primary);
    color: white;
}
</style>

<?= $this->endSection() ?>

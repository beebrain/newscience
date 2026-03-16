<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="upload-form-container" style="max-width: 800px;">
    <div style="margin-bottom: 1.5rem;">
        <a href="<?= base_url('admin/exam') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; color: var(--color-gray-600); text-decoration: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            กลับไปหน้าจัดการ
        </a>
    </div>

    <h2 style="margin-bottom: 0.5rem;">นำเข้าข้อมูลตารางสอบ</h2>
    <p style="color: var(--color-gray-600); margin-bottom: 1.5rem;">อัปโหลดไฟล์ Excel ตารางสอบและระบุภาคเรียน</p>

    <div class="card">
        <div class="card-body">
            <form id="uploadForm" enctype="multipart/form-data">
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">ภาคเรียน <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" name="semester" id="semesterInput" class="form-control" placeholder="เช่น 1/2568" pattern="[1-3]/\d{4}" required style="width: 200px;">
                    <small style="color: var(--color-gray-600);">รูปแบบ: 1/2568, 2/2568, 3/2568</small>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">ประเภทการสอบ <span style="color: var(--color-danger);">*</span></label>
                    <select name="exam_type" class="form-control" required style="width: 200px;">
                        <option value="">เลือกประเภท</option>
                        <option value="midterm">สอบกลางภาค (Midterm)</option>
                        <option value="final">สอบปลายภาค (Final)</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: 500; margin-bottom: 0.5rem;">ไฟล์ Excel <span style="color: var(--color-danger);">*</span></label>
                    <input type="file" name="excelFile" id="excelFile" accept=".xlsx,.xls" required class="form-control">
                    <small style="color: var(--color-gray-600);">รองรับไฟล์ .xlsx ขนาดไม่เกิน 5MB</small>
                </div>

                <div style="background: var(--color-gray-50); padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <h4 style="font-size: 0.875rem; margin: 0 0 0.5rem 0;">โครงสร้างไฟล์ Excel ที่รองรับ:</h4>
                    <ul style="margin: 0; padding-left: 1.25rem; font-size: 0.875rem; color: var(--color-gray-600);">
                        <li>คอลัมน์ A: Section</li>
                        <li>คอลัมน์ B: รหัสวิชา</li>
                        <li>คอลัมน์ C: ชื่อวิชา</li>
                        <li>คอลัมน์ D: กลุ่มเรียน</li>
                        <li>คอลัมน์ E: โครงการ</li>
                        <li>คอลัมน์ F: อาจารย์ผู้สอน</li>
                        <li>คอลัมน์ G: วันที่สอบ</li>
                        <li>คอลัมน์ H: เวลาสอบ</li>
                        <li>คอลัมน์ I: ห้องสอบ</li>
                        <li>คอลัมน์ J: ประธานกรรมการ (nickname)</li>
                        <li>คอลัมน์ K: กรรมการ (nickname)</li>
                    </ul>
                </div>

                <div id="uploadError" style="display: none; color: var(--color-danger); margin-bottom: 1rem;"></div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" id="uploadBtn" class="btn btn-primary" style="min-width: 120px;">
                        <span id="uploadBtnText">นำเข้า</span>
                        <span id="uploadBtnSpinner" style="display: none;">
                            <svg class="spinner" viewBox="0 0 24 24" style="width: 16px; height: 16px; display: inline-block;">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="30 10"/>
                            </svg>
                            กำลังนำเข้า...
                        </span>
                    </button>
                    <a href="<?= base_url('admin/exam') ?>" class="btn" style="background: var(--color-gray-200);">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.spinner {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
const baseUrl = '<?= base_url() ?>';

const form = document.getElementById('uploadForm');
const btn = document.getElementById('uploadBtn');
const btnText = document.getElementById('uploadBtnText');
const btnSpinner = document.getElementById('uploadBtnSpinner');
const errorDiv = document.getElementById('uploadError');

form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(form);
    
    btn.disabled = true;
    btnText.style.display = 'none';
    btnSpinner.style.display = 'inline';
    errorDiv.style.display = 'none';
    
    fetch(`${baseUrl}admin/exam/upload`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
        
        if (data.success) {
            swalAlert(data.message, 'success').then(() => {
                window.location.href = data.redirect_url;
            });
        } else {
            errorDiv.textContent = data.message || 'เกิดข้อผิดพลาด';
            errorDiv.style.display = 'block';
            if (data.errors) {
                const errors = Object.values(data.errors).join(', ');
                errorDiv.textContent += ': ' + errors;
            }
        }
    })
    .catch(err => {
        btn.disabled = false;
        btnText.style.display = 'inline';
        btnSpinner.style.display = 'none';
        errorDiv.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
        errorDiv.style.display = 'block';
        console.error(err);
    });
});
</script>

<?= $this->endSection() ?>

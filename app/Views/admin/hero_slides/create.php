<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>เพิ่ม Hero Slide</h2>
        <a href="<?= base_url('admin/hero-slides') ?>" class="btn btn-secondary">← กลับ</a>
    </div>
    
    <div class="card-body">
        <form action="<?= base_url('admin/hero-slides/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="image" class="form-label">รูปภาพ <span class="required">*</span></label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*" required>
                <small class="form-text">ขนาดแนะนำ: 1920x800 px, รองรับ JPG, PNG, WEBP (สูงสุด 5MB)</small>
                <div id="imagePreview" style="margin-top: 10px;"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="title" class="form-label">หัวข้อ</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="เช่น ขอแสดงความยินดี">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="subtitle" class="form-label">หัวข้อรอง</label>
                    <input type="text" id="subtitle" name="subtitle" class="form-control"
                           placeholder="เช่น Congratulations!">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">คำอธิบาย</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                          placeholder="คำอธิบายสั้นๆ"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="link" class="form-label">ลิงก์ (URL)</label>
                    <input type="url" id="link" name="link" class="form-control"
                           placeholder="https://example.com/news/123">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="link_text" class="form-label">ข้อความปุ่ม</label>
                    <input type="text" id="link_text" name="link_text" class="form-control"
                           placeholder="ดูรายละเอียด" value="ดูรายละเอียด">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="start_date" class="form-label">วันที่เริ่มแสดง</label>
                    <input type="datetime-local" id="start_date" name="start_date" class="form-control">
                    <small class="form-text">ว่างไว้ = แสดงทันที</small>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label for="end_date" class="form-label">วันที่หยุดแสดง</label>
                    <input type="datetime-local" id="end_date" name="end_date" class="form-control">
                    <small class="form-text">ว่างไว้ = แสดงตลอด</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group" style="flex: 1;">
                    <label for="sort_order" class="form-label">ลำดับ</label>
                    <input type="number" id="sort_order" name="sort_order" class="form-control" value="0" min="0">
                </div>
                <div class="form-group" style="flex: 1;">
                    <label class="form-label">&nbsp;</label>
                    <div style="display: flex; gap: 1rem; align-items: center; padding-top: 0.5rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="is_active" value="1" checked>
                            เปิดใช้งาน
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem;">
                            <input type="checkbox" name="show_buttons" value="1">
                            แสดงปุ่ม "เกี่ยวกับเรา/สมัครเรียน"
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="<?= base_url('admin/hero-slides') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('image').addEventListener('change', function(e) {
    const preview = document.getElementById('imagePreview');
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="max-width: 400px; max-height: 200px; border-radius: 8px;">`;
        };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
.form-row {
    display: flex;
    gap: 1rem;
}
.form-row .form-group {
    margin-bottom: 1rem;
}
.required {
    color: #EF4444;
}
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
        gap: 0;
    }
}
</style>

<?= $this->endSection() ?>

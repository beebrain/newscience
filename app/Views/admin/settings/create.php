<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <h2>เพิ่มการตั้งค่าใหม่</h2>
        <p class="form-hint" style="margin: 0.25rem 0 0 0;">สร้างการตั้งค่าที่กำหนดเองสำหรับเว็บไซต์</p>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <form action="<?= base_url('admin/settings/store-new') ?>" method="post">
            <?= csrf_field() ?>
            
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="setting_key" class="form-label">
                    Setting Key <span class="required" style="color: var(--color-error);">*</span>
                </label>
                <input type="text" 
                       id="setting_key" 
                       name="setting_key" 
                       class="form-control"
                       value="<?= old('setting_key') ?>"
                       placeholder="เช่น site_name, contact_email"
                       required
                       maxlength="100">
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">
                    ใช้ตัวอักษรภาษาอังกฤษ ตัวเลข และ underscore เท่านั้น (unique)
                </p>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="setting_type" class="form-label">
                    ประเภทข้อมูล <span class="required" style="color: var(--color-error);">*</span>
                </label>
                <select id="setting_type" name="setting_type" class="form-control" required>
                    <option value="text" <?= old('setting_type') === 'text' ? 'selected' : '' ?>>
                        Text (ข้อความสั้น)
                    </option>
                    <option value="textarea" <?= old('setting_type') === 'textarea' ? 'selected' : '' ?>>
                        Textarea (ข้อความยาว)
                    </option>
                    <option value="boolean" <?= old('setting_type') === 'boolean' ? 'selected' : '' ?>>
                        Boolean (เปิด/ปิด)
                    </option>
                    <option value="image" <?= old('setting_type') === 'image' ? 'selected' : '' ?>>
                        Image (รูปภาพ)
                    </option>
                    <option value="json" <?= old('setting_type') === 'json' ? 'selected' : '' ?>>
                        JSON (ข้อมูลโครงสร้าง)
                    </option>
                </select>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">
                    เลือกประเภทข้อมูลที่เหมาะสมกับการใช้งาน
                </p>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="category" class="form-label">
                    หมวดหมู่ <span class="required" style="color: var(--color-error);">*</span>
                </label>
                <select id="category" name="category" class="form-control" required>
                    <option value="general" <?= old('category') === 'general' ? 'selected' : '' ?>>ทั่วไป</option>
                    <option value="site" <?= old('category') === 'site' ? 'selected' : '' ?>>เว็บไซต์</option>
                    <option value="contact" <?= old('category') === 'contact' ? 'selected' : '' ?>>ติดต่อ</option>
                    <option value="social" <?= old('category') === 'social' ? 'selected' : '' ?>>โซเชียลมีเดีย</option>
                    <option value="seo" <?= old('category') === 'seo' ? 'selected' : '' ?>>SEO</option>
                    <option value="appearance" <?= old('category') === 'appearance' ? 'selected' : '' ?>>การแสดงผล</option>
                    <option value="custom" <?= old('category') === 'custom' ? 'selected' : '' ?>>กำหนดเอง</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="setting_value" class="form-label">
                    ค่าเริ่มต้น
                </label>
                <input type="text" 
                       id="setting_value" 
                       name="setting_value" 
                       class="form-control"
                       value="<?= old('setting_value') ?>"
                       placeholder="ค่าเริ่มต้นของการตั้งค่า">
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label for="description" class="form-label">
                    คำอธิบาย
                </label>
                <input type="text" 
                       id="description" 
                       name="description" 
                       class="form-control"
                       value="<?= old('description') ?>"
                       placeholder="คำอธิบายการใช้งาน (optional)"
                       maxlength="255">
            </div>

            <div class="form-actions" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-gray-200);">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 0.5rem;">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    บันทึกการตั้งค่า
                </button>
                <a href="<?= base_url('admin/settings') ?>" class="btn btn-secondary" style="margin-left: 0.5rem;">
                    ยกเลิก
                </a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

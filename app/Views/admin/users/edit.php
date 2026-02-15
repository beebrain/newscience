<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2><?= $page_title ?></h2>
        <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            กลับรายการผู้ใช้
        </a>
    </div>

    <div class="card-body" style="padding: 1.5rem 2rem;">
        <form action="<?= base_url('admin/users/update-' . $user_type . '/' . ($user_type === 'faculty' ? $user['uid'] : $user['id'])) ?>" method="post" id="userEditForm">
            <?= csrf_field() ?>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    ข้อมูลหลัก
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="email" class="form-label">อีเมล *</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?= old('email', $user['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="login_uid" class="form-label">Login UID</label>
                        <input type="text" id="login_uid" name="login_uid" class="form-control"
                            value="<?= old('login_uid', $user['login_uid'] ?? '') ?>" readonly>
                        <small class="form-hint">ไม่สามารถแก้ไขได้ (ใช้สำหรับการเข้าสู่ระบบ)</small>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="role" class="form-label">บทบาท *</label>
                        <select id="role" name="role" class="form-control" required>
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role ?>" <?= old('role', $user['role']) === $role ? 'selected' : '' ?>>
                                    <?= $role ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status" class="form-label">สถานะ *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="active" <?= old('status', $user['status']) === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                            <option value="inactive" <?= old('status', $user['status']) === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    ข้อมูลส่วนตัว
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title" class="form-label">คำนำหน้า</label>
                        <select id="title" name="title" class="form-control">
                            <option value="">-- เลือก --</option>
                            <option value="ดร." <?= old('title', $user['title'] ?? '') === 'ดร.' ? 'selected' : '' ?>>ดร.</option>
                            <option value="ศ.ดร." <?= old('title', $user['title'] ?? '') === 'ศ.ดร.' ? 'selected' : '' ?>>ศ.ดร.</option>
                            <option value="รศ.ดร." <?= old('title', $user['title'] ?? '') === 'รศ.ดร.' ? 'selected' : '' ?>>รศ.ดร.</option>
                            <option value="ผศ.ดร." <?= old('title', $user['title'] ?? '') === 'ผศ.ดร.' ? 'selected' : '' ?>>ผศ.ดร.</option>
                            <option value="ศ." <?= old('title', $user['title'] ?? '') === 'ศ.' ? 'selected' : '' ?>>ศ.</option>
                            <option value="รศ." <?= old('title', $user['title'] ?? '') === 'รศ.' ? 'selected' : '' ?>>รศ.</option>
                            <option value="ผศ." <?= old('title', $user['title'] ?? '') === 'ผศ.' ? 'selected' : '' ?>>ผศ.</option>
                            <option value="อ." <?= old('title', $user['title'] ?? '') === 'อ.' ? 'selected' : '' ?>>อ.</option>
                            <option value="นาย" <?= old('title', $user['title'] ?? '') === 'นาย' ? 'selected' : '' ?>>นาย</option>
                            <option value="นาง" <?= old('title', $user['title'] ?? '') === 'นาง' ? 'selected' : '' ?>>นาง</option>
                            <option value="นางสาว" <?= old('title', $user['title'] ?? '') === 'นางสาว' ? 'selected' : '' ?>>นางสาว</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="th_name" class="form-label">ชื่อ (ไทย)</label>
                        <input type="text" id="th_name" name="th_name" class="form-control"
                            value="<?= old('th_name', $user['th_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="thai_lastname" class="form-label">นามสกุล (ไทย)</label>
                        <input type="text" id="thai_lastname" name="thai_lastname" class="form-control"
                            value="<?= old('thai_lastname', $user['thai_lastname'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="gf_name" class="form-label">ชื่อ (อังกฤษ)</label>
                        <input type="text" id="gf_name" name="gf_name" class="form-control"
                            value="<?= old('gf_name', $user['gf_name'] ?? '') ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gl_name" class="form-label">นามสกุล (อังกฤษ)</label>
                        <input type="text" id="gl_name" name="gl_name" class="form-control"
                            value="<?= old('gl_name', $user['gl_name'] ?? '') ?>">
                    </div>
                </div>
            </section>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                        <line x1="12" y1="21" x2="12" y2="13" />
                    </svg>
                    บันทึกข้อมูล
                </button>
                
                <a href="<?= base_url('admin/users') ?>" class="btn btn-secondary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                    ยกเลิก
                </a>
            </div>
        </form>
    </div>
</div>

<style>
.form-section {
    margin-bottom: 2rem;
}

.form-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 1rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--color-gray-900);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-label {
    font-weight: 500;
    margin-bottom: 0.5rem;
    color: var(--color-gray-700);
}

.form-control {
    padding: 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-control[readonly] {
    background-color: var(--color-gray-100);
    color: var(--color-gray-600);
}

.form-hint {
    font-size: 0.75rem;
    color: var(--color-gray-500);
    margin-top: 0.25rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    padding-top: 2rem;
    border-top: 1px solid var(--color-gray-200);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
}
</style>

<?= $this->endSection() ?>

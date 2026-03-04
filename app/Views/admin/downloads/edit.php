<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('admin/downloads/documents/' . $category['id']) ?>" class="btn btn-secondary btn-sm">กลับไปรายการเอกสาร</a>
                <a href="<?= base_url('admin/downloads') ?>" class="btn btn-outline btn-sm">หมวดหมู่ทั้งหมด</a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" style="margin: 1rem 1.5rem; padding: 0.75rem 1rem; background: #ffe3e3; border-radius: 8px; color: #c92a2a;">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card-body" style="padding: 1.5rem;">
        <form action="<?= base_url('admin/downloads/update/' . $document['id']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="title" class="form-label">ชื่อเอกสาร *</label>
                <input type="text" id="title" name="title" class="form-control" required value="<?= esc($document['title']) ?>">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label class="form-label">ประเภท</label>
                <select id="file_type" name="file_type" class="form-control">
                    <option value="link" <?= ($document['file_type'] ?? '') === 'link' ? 'selected' : '' ?>>ลิงก์</option>
                    <option value="pdf" <?= ($document['file_type'] ?? '') === 'pdf' ? 'selected' : '' ?>>PDF</option>
                    <option value="doc" <?= ($document['file_type'] ?? '') === 'doc' ? 'selected' : '' ?>>Word</option>
                    <option value="docx" <?= ($document['file_type'] ?? '') === 'docx' ? 'selected' : '' ?>>Word (docx)</option>
                    <option value="xlsx" <?= ($document['file_type'] ?? '') === 'xlsx' ? 'selected' : '' ?>>Excel</option>
                    <option value="pptx" <?= ($document['file_type'] ?? '') === 'pptx' ? 'selected' : '' ?>>PowerPoint</option>
                    <option value="zip" <?= ($document['file_type'] ?? '') === 'zip' ? 'selected' : '' ?>>ZIP</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="external_url" class="form-label">ลิงก์ภายนอก (ถ้าเป็นลิงก์)</label>
                <input type="url" id="external_url" name="external_url" class="form-control" value="<?= esc($document['external_url'] ?? '') ?>" placeholder="https://...">
            </div>
            <div class="form-group" style="margin-bottom: 1rem;">
                <label for="file" class="form-label">อัปโหลดไฟล์ใหม่ (ถ้าไม่กรอกจะใช้ของเดิม)</label>
                <input type="file" id="file" name="file" class="form-control">
                <?php if (!empty($document['file_path'])): ?>
                    <small class="form-hint">ไฟล์ปัจจุบัน: <?= esc(basename($document['file_path'])) ?></small>
                <?php endif; ?>
            </div>
            <div class="form-actions-download" style="display: flex; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid #e5e7eb; margin-top: 1rem;">
                <button type="submit" class="btn btn-primary btn-save-download" name="submit" value="1">บันทึกการแก้ไข</button>
                <a href="<?= base_url('admin/downloads/documents/' . $category['id']) ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-control { width: 100%; max-width: 500px; padding: 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 6px; }
.form-label { display: block; margin-bottom: 0.25rem; font-weight: 500; font-size: 0.875rem; }
.form-hint { font-size: 0.75rem; color: var(--color-gray-500); margin-top: 0.25rem; display: block; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; border: none; font-size: 0.875rem; }
.btn-primary { background: var(--color-primary-600); color: white; }
.btn-secondary { background: var(--color-gray-200); color: var(--color-gray-700); }
.btn-outline { background: transparent; border: 1px solid var(--color-gray-300); color: var(--color-gray-700); }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
.btn-save-download { min-width: 120px; background: #2563eb !important; color: #fff !important; cursor: pointer; }
.btn-save-download:hover { background: #1d4ed8 !important; }
</style>

<?= $this->endSection() ?>

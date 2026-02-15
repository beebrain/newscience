<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                        กลับ
                </a>
                <a href="<?= base_url('program-admin') ?>" class="btn btn-outline btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11H3z" />
                        <polyline points="3 9 12 9 12 22" />
                    </svg>
                    แดชบอร์ด
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Upload Form -->
        <div class="upload-section" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">อัปโหลดไฟล์ใหม่</h3>
            <form action="<?= base_url('program-admin/upload-download/' . $program['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="title" class="form-label">ชื่อไฟล์ *</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="เช่น: คู่มือนักศึกษา">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="file_type" class="form-label">ประเภทไฟล์ *</label>
                        <select id="file_type" name="file_type" class="form-control" required>
                            <option value="">-- เลือก --</option>
                            <option value="pdf">PDF</option>
                            <option value="doc">Word</option>
                            <option value="docx">Word</option>
                            <option value="xlsx">Excel</option>
                            <option value="pptx">PowerPoint</option>
                            <option value="zip">ZIP</option>
                            <option value="jpg">รูปภาพ</option>
                            <option value="png">รูปภาพ</option>
                            <option value="mp4">วิดีโอ</option>
                            <option value="other">อื่นๆ</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="file" class="form-label">ไฟล์ *</label>
                    <input type="file" id="file" name="file" class="form-control" required>
                    <small class="form-hint">รองรับไฟล์ประเภท: PDF, DOC, DOCX, XLSX, PPTX, ZIP, JPG, PNG, MP4 ขนาดสูงสุด 10MB</small>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    อัปโหลดไฟล์
                </button>
            </form>
        </div>

        <!-- Downloads List -->
        <div class="downloads-section">
            <h3 style="margin-bottom: 1rem;">ไฟล์ที่อัปโหลดแล้ว</h3>
            
            <?php if (!empty($downloads)): ?>
                <div class="downloads-list">
                    <?php foreach ($downloads as $download): ?>
                        <div class="download-item" style="display: flex; align-items: center; padding: 1.5rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 1rem; transition: box-shadow 0.2s;">
                            <div class="file-icon" style="margin-right: 1.5rem;">
                                <div style="width: 48px; height: 48px; background: var(--color-gray-100); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                        <polyline points="14 2 14 8 20 8" />
                                        <line x1="16" y1="13" x2="8" y2="13" />
                                        <line x1="16" y1="17" x2="8" y2="17" />
                                    </svg>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= esc($download['title']) ?></div>
                                <div style="font-size: 0.875rem; color: var(--color-gray-600); margin-bottom: 0.25rem;">
                                    <span style="text-transform: uppercase; font-weight: 500;"><?= esc($download['file_type']) ?></span> • 
                                    <?= $programDownloadModel->getFormattedSize($download['file_size']) ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-500);">
                                    อัปโหลดเมื่อ: <?= date('d/m/Y H:i', strtotime($download['created_at'])) ?>
                                </div>
                            </div>
                            <div class="actions" style="display: flex; gap: 0.5rem;">
                                <a href="<?= base_url('serve/' . $download['file_path']) ?>" class="btn btn-outline btn-sm" target="_blank">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    ดาวน์โหลด
                                </a>
                                <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?= base_url('program-admin/delete-download/' . $download['id']) ?>')">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="3 6 5 6 21 6" />
                                        <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
                                    </svg>
                                    ลบ
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 3rem; color: var(--color-gray-500);">
                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    <h3 style="margin-bottom: 0.5rem;">ยังไม่มีไฟล์ดาวน์โหลด</h3>
                    <p>เริ่มต้นโดยอัปโหลดไฟล์เอกสารสำหรับนักศึกษา</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function confirmDelete(url) {
    if (confirm('คุณแน่ใจว่าต้องการลบไฟล์นี้? การลบไฟล์จะไม่สามารถกู้คืนได้')) {
        window.location.href = url;
    }
}
</script>

<style>
.form-row {
    display: grid;
    gap: 1rem;
    margin-bottom: 1rem;
}

@media (min-width: 768px) {
    .form-row {
        grid-template-columns: repeat(2, 1fr);
    }
}

.form-group {
    margin-bottom: 1rem;
}

.form-label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: var(--color-gray-700);
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid var(--color-gray-300);
    border-radius: 6px;
    font-size: 0.875rem;
    transition: border-color 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: var(--color-primary-500);
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-hint {
    font-size: 0.75rem;
    color: var(--color-gray-500);
    margin-top: 0.25rem;
}

.download-item:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.empty-state {
    text-align: center;
    padding: 3rem;
    color: var(--color-gray-500);
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s;
    text-decoration: none;
}

.btn-primary {
    background: var(--color-primary-600);
    color: white;
}

.btn-primary:hover {
    background: var(--color-primary-700);
}

.btn-secondary {
    background: var(--color-gray-200);
    color: var(--color-gray-700);
}

.btn-secondary:hover {
    background: var(--color-gray-300);
}

.btn-outline {
    background: transparent;
    border: 1px solid var(--color-gray-300);
    color: var(--color-gray-700);
}

.btn-outline:hover {
    background: var(--color-gray-50);
}

.btn-danger {
    background: var(--color-red-600);
    color: white;
}

.btn-danger:hover {
    background: var(--color-red-700);
}

.btn-sm {
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
}
</style>

<?= $this->endSection() ?>

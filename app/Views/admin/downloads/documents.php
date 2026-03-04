<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('admin/downloads') ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    กลับไปหมวดหมู่
                </a>
            </div>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success" style="margin: 1rem 1.5rem; padding: 0.75rem 1rem; background: #d3f9d8; border-radius: 8px; color: #2b8a3e;">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" style="margin: 1rem 1.5rem; padding: 0.75rem 1rem; background: #ffe3e3; border-radius: 8px; color: #c92a2a;">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Upload / Add link form -->
        <div class="upload-section" style="margin-bottom: 2rem;">
            <h3 style="margin-bottom: 1rem;">เพิ่มเอกสาร (อัปโหลดไฟล์ หรือลิงก์ภายนอก)</h3>
            <form action="<?= base_url('admin/downloads/upload/' . $category['id']) ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-row" style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                    <div class="form-group" style="flex: 1;">
                        <label for="title" class="form-label">ชื่อเอกสาร *</label>
                        <input type="text" id="title" name="title" class="form-control" required placeholder="เช่น คู่มือการใช้งาน">
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="file_type" class="form-label">ประเภท (สำหรับไฟล์)</label>
                        <select id="file_type" name="file_type" class="form-control">
                            <option value="">-- เลือกตามไฟล์ --</option>
                            <option value="pdf">PDF</option>
                            <option value="doc">Word</option>
                            <option value="docx">Word</option>
                            <option value="xlsx">Excel</option>
                            <option value="pptx">PowerPoint</option>
                            <option value="zip">ZIP</option>
                            <option value="jpg">รูปภาพ</option>
                            <option value="png">รูปภาพ</option>
                            <option value="mp4">วิดีโอ</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label class="form-label">วิธีเพิ่ม</label>
                    <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                        <label><input type="radio" name="source_type" value="file" checked> อัปโหลดไฟล์</label>
                        <label><input type="radio" name="source_type" value="link"> ลิงก์ภายนอก</label>
                    </div>
                </div>
                <div id="file-field" class="form-group" style="margin-bottom: 1rem;">
                    <label for="file" class="form-label">ไฟล์ *</label>
                    <input type="file" id="file" name="file" class="form-control">
                    <small class="form-hint">รองรับ: PDF, DOC, DOCX, XLSX, PPTX, ZIP, JPG, PNG, MP4 ขนาดสูงสุด 10MB</small>
                </div>
                <div id="link-field" class="form-group" style="margin-bottom: 1rem; display: none;">
                    <label for="external_url" class="form-label">URL ลิงก์ภายนอก *</label>
                    <input type="url" id="external_url" name="external_url" class="form-control" placeholder="https://...">
                </div>
                <button type="submit" class="btn btn-primary btn-save-download" name="submit" value="1">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                        <polyline points="17 8 12 3 7 8" />
                        <line x1="12" y1="3" x2="12" y2="15" />
                    </svg>
                    เพิ่มเอกสาร
                </button>
            </form>
        </div>

        <!-- Documents list -->
        <div class="downloads-section">
            <h3 style="margin-bottom: 1rem;">รายการเอกสารในหมวดนี้</h3>
            <?php if (!empty($documents)): ?>
                <div class="downloads-list">
                    <?php foreach ($documents as $doc): ?>
                        <?php $url = \App\Models\DownloadDocumentModel::getDocumentUrl($doc); ?>
                        <div class="download-item" style="display: flex; align-items: center; padding: 1rem 1.25rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 1rem;">
                            <div style="margin-right: 1rem; width: 48px; height: 48px; background: var(--color-gray-100); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                </svg>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?= esc($doc['title']) ?></div>
                                <div style="font-size: 0.875rem; color: var(--color-gray-600);">
                                    <span style="text-transform: uppercase;"><?= esc($doc['file_type']) ?></span>
                                    <?php if (!empty($doc['file_size'])): ?> • <?= $documentModel->getFormattedSize((int)$doc['file_size']) ?><?php endif; ?>
                                </div>
                                <div style="font-size: 0.75rem; color: var(--color-gray-500);">
                                    อัปเดต: <?= date('d/m/Y H:i', strtotime($doc['updated_at'])) ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 0.5rem;">
                                <?php if ($url): ?>
                                    <a href="<?= esc($url) ?>" class="btn btn-outline btn-sm" target="_blank" rel="noopener">เปิด/ดาวน์โหลด</a>
                                <?php endif; ?>
                                <a href="<?= base_url('admin/downloads/edit/' . $doc['id']) ?>" class="btn btn-outline btn-sm">แก้ไข</a>
                                <a href="<?= base_url('admin/downloads/delete/' . $doc['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('ลบเอกสารนี้หรือไม่?');">ลบ</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state" style="text-align: center; padding: 3rem; color: var(--color-gray-500);">
                    <p>ยังไม่มีเอกสารในหมวดนี้</p>
                    <p>ใช้ฟอร์มด้านบนเพื่ออัปโหลดไฟล์หรือเพิ่มลิงก์ภายนอก</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
(function() {
    var fileField = document.getElementById('file-field');
    var linkField = document.getElementById('link-field');
    var fileInput = document.getElementById('file');
    var urlInput = document.getElementById('external_url');
    document.querySelectorAll('input[name="source_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (this.value === 'link') {
                fileField.style.display = 'none';
                linkField.style.display = 'block';
                fileInput.removeAttribute('required');
                urlInput.setAttribute('required', 'required');
            } else {
                fileField.style.display = 'block';
                linkField.style.display = 'none';
                fileInput.setAttribute('required', 'required');
                urlInput.removeAttribute('required');
            }
        });
    });
})();
</script>

<style>
.form-control { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid var(--color-gray-300); border-radius: 6px; }
.form-label { display: block; margin-bottom: 0.25rem; font-weight: 500; font-size: 0.875rem; }
.form-hint { font-size: 0.75rem; color: var(--color-gray-500); margin-top: 0.25rem; }
.btn { padding: 0.5rem 1rem; border-radius: 6px; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.375rem; border: none; font-size: 0.875rem; }
.btn-primary { background: var(--color-primary-600); color: white; }
.btn-secondary { background: var(--color-gray-200); color: var(--color-gray-700); }
.btn-outline { background: transparent; border: 1px solid var(--color-gray-300); color: var(--color-gray-700); }
.btn-danger { background: #e03131; color: white; }
.btn-sm { padding: 0.375rem 0.75rem; font-size: 0.8125rem; }
.download-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
.btn-save-download { min-width: 100px; background: #2563eb !important; color: #fff !important; cursor: pointer; }
.btn-save-download:hover { background: #1d4ed8 !important; }
</style>

<?= $this->endSection() ?>

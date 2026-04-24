<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>เพิ่มข่าวใหม่</h2>
        <a href="<?= base_url('admin/news') ?>" class="btn btn-secondary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            กลับรายการข่าว
        </a>
    </div>

    <div class="card-body" style="padding: 1.5rem 2rem;">
        <form action="<?= base_url('admin/news/store') ?>" method="post" enctype="multipart/form-data" id="newsForm">
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
                <div class="form-group">
                    <label for="title" class="form-label">หัวข้อข่าว *</label>
                    <input type="text" id="title" name="title" class="form-control"
                        value="<?= old('title') ?>" placeholder="เช่น ประกาศรับสมัครนักศึกษา ปี 2567…" required>
                </div>
                <div class="form-row form-row--status-tags">
                    <div class="form-group form-group--status">
                        <label for="status" class="form-label">สถานะ *</label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="draft" <?= old('status') === 'draft' ? 'selected' : '' ?>>ร่าง</option>
                            <option value="published" <?= old('status') === 'published' ? 'selected' : '' ?>>เผยแพร่</option>
                        </select>
                    </div>
                    <div class="form-group form-group--tags">
                        <label class="form-label">ประเภทข่าว (Tags)</label>
                        <?php
                        $tags_category = array_filter($tags ?? [], fn($t) => strpos($t['slug'] ?? '', 'program_') !== 0);
                        $tags_program  = array_filter($tags ?? [], fn($t) => strpos($t['slug'] ?? '', 'program_') === 0);
                        ?>
                        <?php if (!empty($tags)): ?>
                            <?php if (!empty($tags_category)): ?>
                                <p class="form-label form-label--sub">ประเภท</p>
                                <div class="form-check-group">
                                    <?php foreach ($tags_category as $tag): ?>
                                        <label class="form-check-inline">
                                            <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>" <?= in_array($tag['id'], old('tag_ids') ?? []) ? 'checked' : '' ?>>
                                            <span><?= esc($tag['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($tags_program)): ?>
                                <p class="form-label form-label--sub">หลักสูตร</p>
                                <div class="form-check-group form-check-group--programs">
                                    <?php foreach ($tags_program as $tag): ?>
                                        <label class="form-check-inline">
                                            <input type="checkbox" name="tag_ids[]" value="<?= (int) $tag['id'] ?>" <?= in_array($tag['id'], old('tag_ids') ?? []) ? 'checked' : '' ?>>
                                            <span><?= esc($tag['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="form-hint form-hint--warning">ยังไม่มีประเภทข่าวในระบบ — กรุณารัน <code>database/add_news_tags.sql</code> และ <code>scripts/sync_news_tags_from_programs.php</code></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="published_at" class="form-label">วันและเวลาประกาศ</label>
                    <input type="datetime-local" id="published_at" name="published_at" class="form-control" value="<?= esc(old('published_at') ?? '') ?>" style="max-width: 22rem;">
                    <p class="form-hint">ใช้เมื่อสถานะเป็น <strong>เผยแพร่</strong> — ว่างไว้ = ใช้เวลาปัจจุบันตอนบันทึก</p>
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    ภาพปก
                </h3>
                <div class="form-group">
                    <label for="featured_image" class="form-label">รูปภาพหลัก (แสดงในรายการและหน้ารายละเอียด)</label>
                    <div class="featured-image-box" id="featuredImageBox" role="button" tabindex="0" aria-label="เลือกภาพปก">
                        <div id="featuredImagePlaceholder">
                            <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-bottom: 0.5rem;">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                <circle cx="8.5" cy="8.5" r="1.5" />
                                <polyline points="21 15 16 10 5 21" />
                            </svg>
                            <p style="margin: 0; color: var(--color-gray-600);">คลิกเพื่อเลือกภาพ หรือลากวาง</p>
                            <small style="color: var(--color-gray-500);">PNG, JPG, WebP ไม่เกิน 5MB</small>
                        </div>
                    </div>
                    <input type="file" id="featured_image" name="featured_image" accept="image/*" class="input-file-hidden" aria-describedby="featuredImagePlaceholder">
                    <input type="hidden" name="featured_image_base64" id="featured_image_base64" value="">
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                        <line x1="16" y1="2" x2="16" y2="6" />
                        <line x1="8" y1="2" x2="8" y2="6" />
                        <line x1="3" y1="10" x2="21" y2="10" />
                    </svg>
                    การแสดงผล
                </h3>
                <div class="form-group">
                    <label class="form-label">แสดงใน section กิจกรรมที่จะมาถึง</label>
                    <p class="form-hint">เลือกประเภทข่าว: ข่าวทั่วไป หรือข่าวเกี่ยวกับ Event ที่จะเกิดขึ้น</p>
                    <?php $defDisplayAsEvent = (int)(old('display_as_event') ?? 0); ?>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="display_as_event" value="0" <?= $defDisplayAsEvent === 0 ? 'checked' : '' ?>>
                            <span>ข่าวประชาสัมพันธ์ / กิจกรรมทั่วไป</span>
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="display_as_event" value="1" <?= $defDisplayAsEvent === 1 ? 'checked' : '' ?>>
                            <span>ข่าว Event ที่จะเกิดขึ้น</span>
                        </label>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="width:1.25em;height:1.25em;">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                    </svg>
                    ลิงก์ Facebook
                </h3>
                <div class="form-group">
                    <label for="facebook_url" class="form-label">URL โพสต์หรือหน้าข่าวบน Facebook</label>
                    <p class="form-hint">ไม่บังคับ — ถ้ากรอก ลิงก์จะแสดงในหน้ารายละเอียดข่าว และเมื่อกดจะแจ้งว่าพาท่านไปยัง Facebook</p>
                    <input type="url" id="facebook_url" name="facebook_url" class="form-control"
                        value="<?= old('facebook_url') ?>" placeholder="https://www.facebook.com/...">
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    เนื้อหา
                </h3>
                <div class="form-group">
                    <label for="excerpt" class="form-label">สรุปย่อ</label>
                    <textarea id="excerpt" name="excerpt" class="form-control" rows="3"
                        placeholder="สรุปสั้นๆ ของข่าว (แสดงในรายการ)"><?= old('excerpt') ?></textarea>
                </div>
                <div class="form-group form-group--content">
                    <label for="news-content-editor" class="form-label">เนื้อหาข่าว *</label>
                    <p class="form-hint">ใช้แถบเครื่องมือด้านบนสำหรับ <strong>ตัวหนา</strong> <em>ตัวเอียง</em> ขีดเส้นใต้ และรายการ</p>
                    <div id="news-content-editor" class="news-rich-editor" style="min-height: 320px; background: #fff; border: 1px solid var(--color-gray-200); border-radius: 8px;"></div>
                    <textarea id="content" name="content" class="form-control" style="display: none;"><?= old('content') ?></textarea>
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                        <circle cx="8.5" cy="8.5" r="1.5" />
                        <polyline points="21 15 16 10 5 21" />
                    </svg>
                    รูปภาพ
                </h3>
                <div class="form-group">
                    <label class="form-label">เพิ่มรูปภาพ</label>
                    <p class="form-hint">JPG, PNG, GIF, WebP</p>
                    <div class="file-upload" id="dropZoneImages">
                        <input type="file" name="attachments_images[]" id="additionalImages" multiple accept="image/*">
                        <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                            <circle cx="8.5" cy="8.5" r="1.5" />
                            <polyline points="21 15 16 10 5 21" />
                        </svg>
                        <p>คลิกหรือลากวางเพื่ออัปโหลดรูปภาพ</p>
                        <small>JPG, PNG, WebP</small>
                    </div>
                    <div class="attachment-preview-list image-preview-grid" id="imagePreview"></div>
                </div>
            </section>

            <section class="form-section">
                <h3 class="form-section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    ไฟล์แนบ
                </h3>
                <div class="form-group">
                    <label class="form-label">เพิ่มไฟล์แนบ</label>
                    <p class="form-hint">PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX</p>
                    <div class="file-upload" id="dropZoneDocs">
                        <input type="file" name="attachments_docs[]" id="additionalDocs" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                        <svg class="file-upload-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                            <polyline points="14 2 14 8 20 8" />
                            <line x1="16" y1="13" x2="8" y2="13" />
                            <line x1="16" y1="17" x2="8" y2="17" />
                        </svg>
                        <p>คลิกหรือลากวางเพื่ออัปโหลดเอกสาร</p>
                        <small>PDF, DOC, DOCX, XLS, PPT</small>
                    </div>
                    <div class="attachment-preview-list attachment-preview-list--docs" id="docPreview"></div>
                </div>
            </section>

            <div class="form-actions-bar">
                <button type="submit" class="btn btn-primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    บันทึกข่าว
                </button>
                <a href="<?= base_url('admin/news') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<link href="<?= base_url('assets/vendor/quill/quill.snow.css') ?>" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous">
<script src="<?= base_url('assets/vendor/quill/quill.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>
<script src="<?= base_url('assets/js/smart-image-crop.js') ?>"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var contentTextarea = document.getElementById('content');
        var editorEl = document.getElementById('news-content-editor');
        if (editorEl && contentTextarea) {
            var quill = new Quill(editorEl, {
                theme: 'snow',
                placeholder: 'เขียนเนื้อหาข่าวที่นี่...',
                modules: {
                    toolbar: [
                        [{
                            'header': [1, 2, 3, false]
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        [{
                            'align': []
                        }],
                        ['link'],
                        ['clean']
                    ]
                }
            });
            quill.root.innerHTML = contentTextarea.value || '';
            document.getElementById('newsForm').addEventListener('submit', function(e) {
                var quillContent = quill.root.innerHTML.trim();
                if (!quillContent || quillContent === '<p><br></p>' || quillContent === '<p></p>') {
                    e.preventDefault();
                    swalAlert('กรุณากรอกเนื้อหาข่าว', 'warning');
                    editorEl.focus();
                    return false;
                }
                contentTextarea.value = quillContent;
            });
        }

        if (document.getElementById('featuredImageBox') && window.SmartImageCrop) {
            SmartImageCrop.mount({
                triggerEl:   document.getElementById('featuredImageBox'),
                fileInput:   document.getElementById('featured_image'),
                base64Input: document.getElementById('featured_image_base64'),
                previewEl:   document.getElementById('featuredImagePlaceholder'),
                entity: 'news',
                aspectPresets: [
                    { value: '16:9', label: '16:9 (แนะนำ)', ratio: 16/9 },
                    { value: '4:3',  label: '4:3',  ratio: 4/3 },
                    { value: '1:1',  label: '1:1',  ratio: 1 },
                    { value: '3:4',  label: '3:4 (แนวตั้ง)',  ratio: 3/4 },
                    { value: 'free', label: 'อิสระ',  ratio: NaN },
                ],
                defaultAspect: '16:9',
                allowNoCrop: true,
                maxWidth: 1920,
                maxHeight: 1080,
                quality: 0.92,
            });
        }

        var dropZoneImages = document.getElementById('dropZoneImages');
        var fileInputImages = document.getElementById('additionalImages');
        var imagePreview = document.getElementById('imagePreview');
        var selectedImageFiles = [];

        if (dropZoneImages) dropZoneImages.addEventListener('click', function() {
            fileInputImages.click();
        });
        if (dropZoneImages) {
            dropZoneImages.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZoneImages.style.borderColor = 'var(--primary)';
            });
            dropZoneImages.addEventListener('dragleave', function() {
                dropZoneImages.style.borderColor = '';
            });
            dropZoneImages.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZoneImages.style.borderColor = '';
                var files = e.dataTransfer.files;
                for (var i = 0; i < files.length; i++) {
                    if (files[i].type.startsWith('image/')) {
                        selectedImageFiles.push(files[i]);
                        addImagePreview(files[i], selectedImageFiles.length - 1);
                    }
                }
                updateImageInput();
            });
        }
        if (fileInputImages) fileInputImages.addEventListener('change', function(e) {
            var files = e.target.files;
            for (var i = 0; i < files.length; i++) {
                selectedImageFiles.push(files[i]);
                addImagePreview(files[i], selectedImageFiles.length - 1);
            }
            updateImageInput();
        });

        function addImagePreview(file, index) {
            var div = document.createElement('div');
            div.className = 'image-preview-item';
            var reader = new FileReader();
            reader.onload = function(ev) {
                div.innerHTML = '<img src="' + ev.target.result + '" alt="" width="120" height="120"><button type="button" class="remove-btn" data-index="' + index + '" aria-label="ลบรูป">×</button>';
                div.querySelector('.remove-btn').addEventListener('click', function() {
                    selectedImageFiles.splice(index, 1);
                    imagePreview.innerHTML = '';
                    for (var j = 0; j < selectedImageFiles.length; j++) addImagePreview(selectedImageFiles[j], j);
                    updateImageInput();
                });
                imagePreview.appendChild(div);
            };
            reader.readAsDataURL(file);
        }

        function updateImageInput() {
            var dt = new DataTransfer();
            for (var i = 0; i < selectedImageFiles.length; i++) dt.items.add(selectedImageFiles[i]);
            fileInputImages.files = dt.files;
        }

        var dropZoneDocs = document.getElementById('dropZoneDocs');
        var fileInputDocs = document.getElementById('additionalDocs');
        var docPreview = document.getElementById('docPreview');
        var selectedDocFiles = [];
        var allowedDocExt = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        function isDoc(file) {
            var ext = (file.name.split('.').pop() || '').toLowerCase();
            return allowedDocExt.indexOf(ext) !== -1;
        }
        if (dropZoneDocs) dropZoneDocs.addEventListener('click', function() {
            fileInputDocs.click();
        });
        if (dropZoneDocs) {
            dropZoneDocs.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropZoneDocs.style.borderColor = 'var(--primary)';
            });
            dropZoneDocs.addEventListener('dragleave', function() {
                dropZoneDocs.style.borderColor = '';
            });
            dropZoneDocs.addEventListener('drop', function(e) {
                e.preventDefault();
                dropZoneDocs.style.borderColor = '';
                var files = e.dataTransfer.files;
                for (var i = 0; i < files.length; i++) {
                    if (isDoc(files[i])) {
                        selectedDocFiles.push(files[i]);
                        addDocPreview(files[i], selectedDocFiles.length - 1);
                    }
                }
                updateDocInput();
            });
        }
        if (fileInputDocs) fileInputDocs.addEventListener('change', function(e) {
            var files = e.target.files;
            for (var i = 0; i < files.length; i++) {
                if (isDoc(files[i])) {
                    selectedDocFiles.push(files[i]);
                    addDocPreview(files[i], selectedDocFiles.length - 1);
                }
            }
            updateDocInput();
        });

        function addDocPreview(file, index) {
            var div = document.createElement('div');
            div.className = 'image-preview-item image-preview-item--doc';
            div.innerHTML = '<div class="attachment-doc-preview"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="40" height="40"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg><span class="attachment-doc-name">' + (file.name || 'เอกสาร') + '</span></div><button type="button" class="remove-btn" data-index="' + index + '" aria-label="ลบไฟล์">×</button>';
            div.querySelector('.remove-btn').addEventListener('click', function() {
                selectedDocFiles.splice(index, 1);
                docPreview.innerHTML = '';
                for (var j = 0; j < selectedDocFiles.length; j++) addDocPreview(selectedDocFiles[j], j);
                updateDocInput();
            });
            docPreview.appendChild(div);
        }

        function updateDocInput() {
            var dt = new DataTransfer();
            for (var i = 0; i < selectedDocFiles.length; i++) dt.items.add(selectedDocFiles[i]);
            fileInputDocs.files = dt.files;
        }
    });
</script>
<?= $this->endSection() ?>
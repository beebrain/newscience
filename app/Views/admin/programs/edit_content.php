<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('program-admin') ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="19" y1="12" x2="5" y2="12"></line>
                        <polyline points="12 19 5 12 12 5"></polyline>
                    </svg>
                    กลับ
                </a>
                <a href="<?= base_url('program-admin/preview/' . $program['id']) ?>" class="btn btn-outline btn-sm" target="_blank">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    ดูตัวอย่าง
                </a>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <!-- Tab Navigation -->
        <div class="tab-navigation" style="display: flex; border-bottom: 1px solid var(--color-gray-200); background: var(--color-gray-50);">
            <button type="button" class="tab-button active" data-tab="basic" onclick="switchTab('basic')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                    <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                </svg>
                ข้อมูลพื้นฐาน
            </button>
            <button type="button" class="tab-button" data-tab="content" onclick="switchTab('content')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                    <polyline points="10 9 9 9 8 9" />
                </svg>
                เนื้อหาหลักสูตร
            </button>
            <button type="button" class="tab-button" data-tab="downloads" onclick="switchTab('downloads')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                    <polyline points="7 10 12 15 17 10" />
                    <line x1="12" y1="15" x2="12" y2="3" />
                </svg>
                ดาวน์โหลด
            </button>
            <button type="button" class="tab-button" data-tab="content-builder" onclick="window.location.href='<?= base_url('program-admin/content-builder/' . $program['id']) ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                    <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                    <path d="M2 2l7.586 7.586"></path>
                    <circle cx="11" cy="11" r="2"></circle>
                </svg>
                Content Builder
            </button>
            <button type="button" class="tab-button" data-tab="personnel" onclick="switchTab('personnel')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 00-3-3.87" />
                    <path d="M16 3.13a4 4 0 010 7.75" />
                </svg>
                บุคลากร
            </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content-container">
            <!-- Basic Info Tab -->
            <div id="basic-tab" class="tab-content active">
                <form action="<?= base_url('program-admin/update/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
                    <?= csrf_field() ?>

                    <div class="form-section">
                        <h4 class="form-section-title">ข้อมูลพื้นฐาน</h4>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="name_th" class="form-label">ชื่อหลักสูตร (ไทย) *</label>
                                <input type="text" id="name_th" name="name_th" class="form-control" value="<?= esc($program['name_th']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="name_en" class="form-label">ชื่อหลักสูตร (อังกฤษ)</label>
                                <input type="text" id="name_en" name="name_en" class="form-control" value="<?= esc($program['name_en']) ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="level" class="form-label">ระดับ *</label>
                                <select id="level" name="level" class="form-control" required>
                                    <option value="bachelor" <?= $program['level'] === 'bachelor' ? 'selected' : '' ?>>ปริญญาตรี</option>
                                    <option value="master" <?= $program['level'] === 'master' ? 'selected' : '' ?>>ปริญญาโท</option>
                                    <option value="doctorate" <?= $program['level'] === 'doctorate' ? 'selected' : '' ?>>ปริญญาเอก</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="status" class="form-label">สถานะ *</label>
                                <select id="status" name="status" class="form-control" required>
                                    <option value="active" <?= $program['status'] === 'active' ? 'selected' : '' ?>>ใช้งาน</option>
                                    <option value="inactive" <?= $program['status'] === 'inactive' ? 'selected' : '' ?>>ไม่ใช้งาน</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกข้อมูล
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Content Tab -->
            <div id="content-tab" class="tab-content">
                <form action="<?= base_url('program-admin/update-page/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
                    <?= csrf_field() ?>

                    <div class="form-section">
                        <h4 class="form-section-title">เนื้อหาหลักสูตร</h4>

                        <div class="form-group">
                            <label for="philosophy" class="form-label">ปรัชญาหลักสูตร</label>
                            <textarea id="philosophy" name="philosophy" class="form-control" rows="4"><?= esc($program_page['philosophy'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="objectives" class="form-label">วัตถุประสงค์</label>
                            <textarea id="objectives" name="objectives" class="form-control" rows="4"><?= esc($program_page['objectives'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="graduate_profile" class="form-label">คุณลักษณะบัณฑิต</label>
                            <textarea id="graduate_profile" name="graduate_profile" class="form-control" rows="4"><?= esc($program_page['graduate_profile'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="curriculum_structure" class="form-label">โครงสร้างหลักสูตร</label>
                            <textarea id="curriculum_structure" name="curriculum_structure" class="form-control" rows="6"><?= esc($program_page['curriculum_structure'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="study_plan" class="form-label">แผนการเรียน</label>
                            <textarea id="study_plan" name="study_plan" class="form-control" rows="6"><?= esc($program_page['study_plan'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="career_prospects" class="form-label">อาชีพที่สามารถประกอบได้</label>
                            <textarea id="career_prospects" name="career_prospects" class="form-control" rows="4"><?= esc($program_page['career_prospects'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="tuition_fees" class="form-label">ค่าเล่าเรียน/ค่าธรรมเนียม</label>
                            <textarea id="tuition_fees" name="tuition_fees" class="form-control" rows="4"><?= esc($program_page['tuition_fees'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="admission_info" class="form-label">การรับสมัคร</label>
                            <textarea id="admission_info" name="admission_info" class="form-control" rows="4"><?= esc($program_page['admission_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="contact_info" class="form-label">ข้อมูลติดต่อ</label>
                            <textarea id="contact_info" name="contact_info" class="form-control" rows="4"><?= esc($program_page['contact_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="intro_video_url" class="form-label">วิดีโอแนะนำ</label>
                            <input type="url" id="intro_video_url" name="intro_video_url" class="form-control" value="<?= esc($program_page['intro_video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="theme_color" class="form-label">สีธีม</label>
                                <input type="color" id="theme_color" name="theme_color" class="form-control" value="<?= esc($program_page['theme_color'] ?? '#1e40af') ?>">
                            </div>
                            <div class="form-group">
                                <label for="is_published" class="form-label">สถานะการเผยแพร่</label>
                                <select id="is_published" name="is_published" class="form-control">
                                    <option value="0" <?= ($program_page['is_published'] ?? 0) == 0 ? 'selected' : '' ?>>ยังไม่เผยแพร่</option>
                                    <option value="1" <?= ($program_page['is_published'] ?? 0) == 1 ? 'selected' : '' ?>>เผยแพร่แล้ว</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="meta_description" class="form-label">คำอธิบายสำหรับ SEO</label>
                            <textarea id="meta_description" name="meta_description" class="form-control" rows="2" placeholder="คำอธิบายสำหรับแสดงในผลการค้นหา"><?= esc($program_page['meta_description'] ?? '') ?></textarea>
                        </div>

                        <div class="form-actions" style="margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกเนื้อหา
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Downloads Tab -->
            <div id="downloads-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>เอกสารดาวน์โหลด</h4>
                        <p style="color: var(--color-gray-600);">จัดการไฟล์เอกสารสำหรับนักศึกษาดาวน์โหลด</p>
                    </div>

                    <!-- Upload Form -->
                    <form action="<?= base_url('program-admin/upload-download/' . $program['id']) ?>" method="post" enctype="multipart/form-data" style="margin-bottom: 2rem;">
                        <?= csrf_field() ?>
                        <div class="form-row">
                            <div class="form-group" style="flex: 1;">
                                <label for="title" class="form-label">ชื่อไฟล์ *</label>
                                <input type="text" id="title" name="title" class="form-control" required>
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
                                    <option value="other">อื่นๆ</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="file" class="form-label">ไฟล์ *</label>
                            <input type="file" id="file" name="file" class="form-control" required>
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

                    <!-- Downloads List -->
                    <?php if (!empty($downloads)): ?>
                        <div class="downloads-list">
                            <?php foreach ($downloads as $download): ?>
                                <div class="download-item" style="display: flex; align-items: center; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px; margin-bottom: 0.5rem;">
                                    <div class="file-icon" style="margin-right: 1rem;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-500);">
                                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                            <polyline points="14 2 14 8 20 8" />
                                            <line x1="16" y1="13" x2="8" y2="13" />
                                            <line x1="16" y1="17" x2="8" y2="17" />
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <div style="font-weight: 500;"><?= esc($download['title']) ?></div>
                                        <div style="font-size: 0.875rem; color: var(--color-gray-600);">
                                            <?= $programDownloadModel->getFormattedSize($download['file_size']) ?> •
                                            <span style="text-transform: uppercase;"><?= esc($download['file_type']) ?></span>
                                        </div>
                                    </div>
                                    <div class="actions">
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
                        <div class="empty-state" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                <polyline points="7 10 12 15 17 10" />
                                <line x1="12" y1="15" x2="12" y2="3" />
                            </svg>
                            <p>ยังไม่มีไฟล์ดาวน์โหลด</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Personnel Tab -->
            <div id="personnel-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>บุคลากรหลักสูตร</h4>
                        <p style="color: var(--color-gray-600);">ประธานหลักสูตรและอาจารย์ประจำหลักสูตร</p>
                    </div>

                    <!-- Coordinator -->
                    <?php if ($coordinator): ?>
                        <div class="personnel-card" style="border: 1px solid var(--color-blue-200); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; background: var(--color-blue-50);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="person-avatar" style="width: 64px; height: 64px; background: var(--color-blue-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-blue-600);">
                                        <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                </div>
                                <div>
                                    <h5 style="margin: 0; color: var(--color-blue-700);">ประธานหลักสูตร</h5>
                                    <p style="margin: 0.25rem 0 0 0; font-weight: 600;"><?= esc($coordinator['name']) ?></p>
                                    <p style="margin: 0; color: var(--color-gray-600); font-size: 0.875rem;"><?= esc($coordinator['position'] ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Other Personnel -->
                    <?php if (!empty($personnel_list)): ?>
                        <h5 style="margin-bottom: 1rem;">อาจารย์ประจำหลักสูตร</h5>
                        <div class="personnel-grid" style="display: grid; gap: 1rem;">
                            <?php foreach ($personnel_list as $personnel): ?>
                                <div class="personnel-item" style="display: flex; align-items: center; gap: 1rem; padding: 1rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                    <div class="person-avatar" style="width: 48px; height: 48px; background: var(--color-gray-100); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                            <circle cx="12" cy="7" r="4" />
                                        </svg>
                                    </div>
                                    <div style="flex: 1;">
                                        <p style="margin: 0; font-weight: 500;"><?= esc($personnel['name']) ?></p>
                                        <p style="margin: 0; color: var(--color-gray-600); font-size: 0.875rem;"><?= esc($personnel['position'] ?? '') ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state" style="text-align: center; padding: 2rem; color: var(--color-gray-500);">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" />
                                <circle cx="9" cy="7" r="4" />
                                <path d="M23 21v-2a4 4 0 00-3-3.87" />
                                <path d="M16 3.13a4 4 0 010 7.75" />
                            </svg>
                            <p>ยังไม่มีข้อมูลบุคลากร</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Tab Navigation -->
<script>
    function switchTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });

        // Remove active class from all buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active');
        });

        // Show selected tab
        document.getElementById(tabName + '-tab').classList.add('active');

        // Add active class to clicked button
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
    }

    function confirmDelete(url) {
        if (confirm('คุณแน่ใจว่าต้องการลบไฟล์นี้?')) {
            window.location.href = url;
        }
    }

    // Initialize first tab
    document.addEventListener('DOMContentLoaded', function() {
        switchTab('basic');
    });
</script>

<style>
    .tab-navigation {
        display: flex;
        border-bottom: 1px solid var(--color-gray-200);
        background: var(--color-gray-50);
    }

    .tab-button {
        padding: 1rem 1.5rem;
        border: none;
        background: none;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 500;
        color: var(--color-gray-600);
    }

    .tab-button:hover {
        color: var(--color-gray-900);
        background: var(--color-gray-100);
    }

    .tab-button.active {
        color: var(--color-primary-600);
        border-bottom-color: var(--color-primary-600);
        background: white;
    }

    .tab-content-container {
        background: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block;
    }

    .form-section {
        margin-bottom: 2rem;
    }

    .form-section-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--color-gray-900);
    }

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

    .form-actions {
        display: flex;
        gap: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-gray-200);
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

    .personnel-grid {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 768px) {
        .personnel-grid {
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        }
    }
</style>

<?= $this->endSection() ?>
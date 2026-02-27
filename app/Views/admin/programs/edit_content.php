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
            <button type="button" class="tab-button" data-tab="news" onclick="switchTab('news'); loadProgramNews();">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
                ข่าวหลักสูตร
            </button>
            <button type="button" class="tab-button" data-tab="activities" onclick="window.location.href='<?= base_url('program-admin/activities/' . $program['id']) ?>'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 20V10"></path>
                    <path d="M12 20V4"></path>
                    <path d="M6 20v-6"></path>
                </svg>
                กิจกรรม
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
            <button type="button" class="tab-button" data-tab="website" onclick="switchTab('website')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-1.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h1.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v1.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-1.09a1.65 1.65 0 00-1.51 1z" />
                </svg>
                การตั้งค่าเว็บไซต์
            </button>
        </div>

        <!-- Tab Content -->
        <div class="tab-content-container">
            <!-- Basic Info Tab -->
            <div id="basic-tab" class="tab-content active">
                <form id="basic-info-form" action="<?= base_url('program-admin/update/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
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

                        <?php
                        $heroBasic = $program_page['hero_image'] ?? '';
                        $heroBasicUrl = '';
                        if ($heroBasic !== '') {
                            $heroBasicUrl = (strpos($heroBasic, 'http') === 0) ? $heroBasic : base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $heroBasic), '/'));
                        }
                        ?>
                        <div class="form-group hero-basic-wrap">
                            <label class="form-label">รูปหน้าปกหลักสูตร</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">ใช้เป็นภาพหน้าปกบนหน้าเว็บหลักสูตร แนะนำอัตราส่วน 16:9 (กว้าง 1920px ขึ้นไป) ลากวางหรือเลือกไฟล์แล้วครอปให้พอดี</p>
                            <div id="hero-basic-preview" class="hero-basic-preview" style="<?= $heroBasicUrl ? '' : 'display:none;' ?> margin-bottom: 0.75rem;">
                                <img id="hero-basic-img" src="<?= esc($heroBasicUrl) ?>" alt="หน้าปกปัจจุบัน" style="max-width: 100%; max-height: 220px; width: auto; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                <div style="margin-top: 0.5rem;">
                                    <button type="button" id="hero-basic-remove" class="btn btn-outline btn-sm">ลบรูปหน้าปก</button>
                                </div>
                            </div>
                            <div id="hero-basic-drop" class="hero-basic-drop <?= $heroBasicUrl ? 'hero-basic-drop--hidden' : '' ?>">
                                <input type="file" id="hero-basic-file" accept="image/jpeg,image/png,image/webp,image/gif" style="position:absolute;opacity:0;width:100%;height:100%;left:0;top:0;cursor:pointer;">
                                <span class="hero-basic-drop__text">ลากวางรูปที่นี่ หรือคลิกเพื่อเลือกไฟล์</span>
                                <span class="hero-basic-drop__hint">JPG, PNG, WEBP, GIF</span>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="basic-save-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกข้อมูล
                            </button>
                            <span id="basic-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Content Tab -->
            <div id="content-tab" class="tab-content">
                <form id="content-page-form" action="<?= base_url('program-admin/update-page/' . $program['id']) ?>" method="post" enctype="multipart/form-data" style="padding: 1.5rem;">
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

                        <?php
                        $elos_initial = [];
                        if (!empty($program_page['elos_json'])) {
                            $decoded = json_decode($program_page['elos_json'], true);
                            if (is_array($decoded)) { $elos_initial = $decoded; }
                        }
                        ?>
                        <div class="form-group elos-editor-wrap">
                            <label class="form-label">ELO (ผลลัพธ์การเรียนรู้ที่คาดหวัง)</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.75rem;">สำหรับหน้า AUN-QA program-detail — กรอกเฉพาะ หมวด และ รายละเอียด แล้วกดบันทึก ELO ได้ทันที</p>
                            <div id="elos-list" class="elos-list" data-initial="<?= htmlspecialchars(json_encode($elos_initial, JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="elos-actions" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="elos-add-btn">+ เพิ่ม ELO</button>
                                <button type="button" class="btn btn-primary btn-sm" id="elos-save-ajax-btn">บันทึก ELO</button>
                                <span id="elos-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                            <textarea id="elos_json" name="elos_json" class="form-control" style="display: none;" aria-hidden="true"><?= esc($program_page['elos_json'] ?? '') ?></textarea>
                        </div>

                        <?php
                        $curriculum_initial = [];
                        if (!empty($program_page['curriculum_json'])) {
                            $decoded = json_decode($program_page['curriculum_json'], true);
                            if (is_array($decoded)) { $curriculum_initial = $decoded; }
                        }
                        ?>
                        <div class="form-group curriculum-editor-wrap">
                            <label class="form-label">หลักสูตร/แผนการเรียน</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.75rem;">สำหรับหน้า AUN-QA program-detail — เพิ่มปี/ภาคเรียน/วิชา แล้วกดบันทึกแผนการเรียน</p>
                            <div id="curriculum-list" class="curriculum-list" data-initial="<?= htmlspecialchars(json_encode($curriculum_initial, JSON_UNESCAPED_UNICODE)) ?>"></div>
                            <div class="curriculum-actions" style="margin-top: 0.5rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button type="button" class="btn btn-outline btn-sm" id="curriculum-add-year-btn">+ เพิ่มปี</button>
                                <button type="button" class="btn btn-primary btn-sm" id="curriculum-save-ajax-btn">บันทึกแผนการเรียน</button>
                                <span id="curriculum-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                            <textarea id="curriculum_json" name="curriculum_json" class="form-control" style="display: none;" aria-hidden="true"><?= esc($program_page['curriculum_json'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="curriculum_structure" class="form-label">โครงสร้างหลักสูตร</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="curriculum_structure" name="curriculum_structure" class="form-control" rows="8"><?= esc($program_page['curriculum_structure'] ?? '') ?></textarea>
                            <div style="margin-top: 0.5rem;">
                                <button type="button" class="btn btn-primary btn-sm" id="curriculum-structure-save-ajax-btn">บันทึกโครงสร้างหลักสูตร</button>
                                <span id="curriculum-structure-ajax-msg" class="ajax-msg" aria-live="polite"></span>
                            </div>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="study_plan" class="form-label">แผนการเรียน</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="study_plan" name="study_plan" class="form-control" rows="6"><?= esc($program_page['study_plan'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="career_prospects" class="form-label">อาชีพที่สามารถประกอบได้</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="career_prospects" name="career_prospects" class="form-control" rows="4"><?= esc($program_page['career_prospects'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="tuition_fees" class="form-label">ค่าเล่าเรียน/ค่าธรรมเนียม</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="tuition_fees" name="tuition_fees" class="form-control" rows="4"><?= esc($program_page['tuition_fees'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="admission_info" class="form-label">การรับสมัคร</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="admission_info" name="admission_info" class="form-control" rows="4"><?= esc($program_page['admission_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group content-with-toolbar">
                            <label for="contact_info" class="form-label">ข้อมูลติดต่อ</label>
                            <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">แทรกข้อความสำเร็จรูปด้วยปุ่มด้านล่าง</p>
                            <div class="structure-toolbar" role="toolbar" aria-label="เครื่องมือแทรกข้อความ">
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<h3>หัวข้อ</h3>">หัวข้อ</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ul>\n<li>รายการ</li>\n</ul>">รายการจุด</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<ol>\n<li>รายการ</li>\n</ol>">รายการเลข</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<hr>">เส้นคั่น</button>
                                <button type="button" class="btn btn-outline btn-sm structure-tool" data-insert="<p>ย่อหน้า</p>">ย่อหน้า</button>
                            </div>
                            <textarea id="contact_info" name="contact_info" class="form-control" rows="4"><?= esc($program_page['contact_info'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="intro_video_url" class="form-label">วิดีโอแนะนำ</label>
                            <input type="url" id="intro_video_url" name="intro_video_url" class="form-control" value="<?= esc($program_page['intro_video_url'] ?? '') ?>" placeholder="https://youtube.com/watch?v=...">
                        </div>

                        <div class="form-section" style="margin-top: 1.5rem;">
                            <h4 class="form-section-title">หน้าตัวแทนหลักสูตร (SPA)</h4>
                            <div class="form-group">
                                <label class="form-label">รูปหน้าปกหลักสูตร (Hero)</label>
                                <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 0.5rem;">ใช้แสดงเป็นพื้นหลังส่วน Hero บนหน้าเว็บหลักสูตร (แนะนำขนาดกว้าง 1920px ขึ้นไป)</p>
                                <?php
                                $hero = $program_page['hero_image'] ?? '';
                                $heroUrl = '';
                                if ($hero !== '') {
                                    $heroUrl = (strpos($hero, 'http') === 0) ? $hero : base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $hero), '/'));
                                }
                                ?>
                                <?php if ($heroUrl !== ''): ?>
                                <div class="hero-preview-wrap" style="margin-bottom: 0.75rem;">
                                    <img id="hero-preview-img" src="<?= esc($heroUrl) ?>" alt="หน้าปกปัจจุบัน" style="max-width: 100%; max-height: 200px; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                </div>
                                <?php else: ?>
                                <div class="hero-preview-wrap" style="margin-bottom: 0.75rem; display: none;">
                                    <img id="hero-preview-img" src="" alt="" style="max-width: 100%; max-height: 200px; object-fit: contain; border: 1px solid var(--color-gray-200); border-radius: 8px;">
                                </div>
                                <?php endif; ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                    <input type="file" id="hero_image" name="hero_image" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif" style="max-width: 280px;">
                                    <label class="form-label" style="margin: 0; display: flex; align-items: center; gap: 0.35rem; cursor: pointer;">
                                        <input type="checkbox" name="hero_image_remove" value="1" id="hero_image_remove"> ลบรูปหน้าปก
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
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

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary" id="content-save-btn">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกเนื้อหา
                            </button>
                            <span id="content-ajax-msg" class="ajax-msg" aria-live="polite"></span>
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
                                        <a href="<?= (strpos($download['file_path'], 'uploads/') === 0 ? base_url('serve/' . $download['file_path']) : base_url('serve/uploads/' . $download['file_path'])) ?>" class="btn btn-outline btn-sm" target="_blank">
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

            <!-- News Tab -->
            <div id="news-tab" class="tab-content">
                <div style="padding: 1.5rem;">
                    <div class="section-header" style="margin-bottom: 1.5rem;">
                        <h4>ข่าวหลักสูตร</h4>
                        <p style="color: var(--color-gray-600);">ข่าวที่แท็กกับหลักสูตรนี้ (ค่าเริ่มต้นจะแท็กหลักสูตรปัจจุบัน)</p>
                    </div>
                    <div id="program-news-list" style="margin-bottom: 2rem;">
                        <p style="color: var(--color-gray-500);">กำลังโหลด...</p>
                    </div>
                    <hr style="margin: 2rem 0;">

                    <style>
                    .news-form-container { font-family: 'Sarabun', 'Prompt', sans-serif; color: #334155; background: #f8fafc; padding: 2rem; border-radius: 12px; }
                    .news-form-container .news-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
                    .news-form-container .news-card-title { font-size: 1.1rem; font-weight: 600; color: #0f172a; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.5rem; }
                    .news-form-container .news-card-title .card-title-icon { flex-shrink: 0; color: #475569; }
                    .news-form-container input[type="file"] { width: 100%; padding: 0.75rem 1rem; font-size: 0.9375rem; color: #334155; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box; }
                    .news-form-container input[type="file"]:focus { border-color: #3b82f6; outline: 0; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); }
                    .news-form-container .form-group { margin-bottom: 1.25rem; }
                    .news-form-container .form-group:last-child { margin-bottom: 0; }
                    .news-form-container .form-label { display: block; font-weight: 500; margin-bottom: 0.5rem; color: #475569; }
                    .news-form-container .text-danger { color: #ef4444; }
                    .news-form-container .form-control { width: 100%; padding: 0.75rem 1rem; font-size: 1rem; line-height: 1.5; color: #334155; background: #fff; border: 1px solid #cbd5e1; border-radius: 6px; transition: border-color 0.15s, box-shadow 0.15s; box-sizing: border-box; }
                    .news-form-container .form-control:focus { border-color: #3b82f6; outline: 0; box-shadow: 0 0 0 3px rgba(59,130,246,0.25); }
                    .news-form-container .form-row { display: flex; gap: 1.5rem; flex-wrap: wrap; }
                    .news-form-container .form-row .form-group { flex: 1; min-width: 250px; }
                    .news-form-container .file-upload-info { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
                    .news-form-container .checkbox-wrapper { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; padding: 0.5rem 0; }
                    .news-form-container .checkbox-wrapper input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
                    .news-form-container .btn-submit { background: #2563eb; color: #fff; font-weight: 600; padding: 0.75rem 2rem; border: none; border-radius: 6px; cursor: pointer; transition: background-color 0.2s; font-size: 1rem; display: inline-flex; align-items: center; gap: 0.5rem; }
                    .news-form-container .btn-submit:hover { background: #1d4ed8; }
                    .news-form-container .btn-cancel { background: #f1f5f9; color: #334155; padding: 0.75rem 1.5rem; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; font-size: 1rem; }
                    .news-form-container .btn-cancel:hover { background: #e2e8f0; }
                    </style>

                    <div class="news-form-container">
                        <div style="margin-bottom: 2rem;">
                            <h3 style="margin: 0 0 0.5rem 0; color: #0f172a;">สร้างข่าวหลักสูตรใหม่</h3>
                            <p style="color: #64748b; margin: 0;">เพิ่มเนื้อหาและประกาศสำหรับนักศึกษาในหลักสูตร</p>
                        </div>

                        <form action="<?= base_url('program-admin/news/' . $program['id'] . '/create') ?>" method="post" enctype="multipart/form-data" id="program-news-form">
                            <?= csrf_field() ?>
                            <div class="news-card">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                                    ข้อมูลทั่วไป
                                </div>
                                <div class="form-group">
                                    <label for="news_title" class="form-label">หัวข้อข่าว <span class="text-danger">*</span></label>
                                    <input type="text" id="news_title" name="title" class="form-control" required minlength="3" maxlength="500" placeholder="เช่น ประกาศรับสมัครนักศึกษา ปีการศึกษา 2567">
                                </div>
                                <div class="form-group">
                                    <label for="news_content" class="form-label">เนื้อหาข่าว <span class="text-danger">*</span></label>
                                    <textarea id="news_content" name="content" class="form-control" rows="8" required placeholder="ใส่รายละเอียดข่าวที่นี่..."></textarea>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label for="news_excerpt" class="form-label">สรุปเนื้อหาสั้นๆ (ไม่บังคับ)</label>
                                    <textarea id="news_excerpt" name="excerpt" class="form-control" rows="2" placeholder="ข้อความสรุปที่จะแสดงในหน้าแรก (ถ้าไม่ใส่ ระบบจะดึงจากเนื้อหาบางส่วน)"></textarea>
                                </div>
                            </div>
                            <div class="news-card">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-1.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h1.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v1.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-1.09a1.65 1.65 0 00-1.51 1z"/></svg>
                                    การแสดงผล
                                </div>
                                <div class="form-row">
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label for="news_status" class="form-label">สถานะการเผยแพร่ <span class="text-danger">*</span></label>
                                        <select id="news_status" name="status" class="form-control" required>
                                            <option value="draft">ร่าง (ยังไม่แสดงผล)</option>
                                            <option value="published" selected>เผยแพร่ทันที</option>
                                        </select>
                                    </div>
                                    <div class="form-group" style="margin-bottom: 0;">
                                        <label class="form-label">ประเภทกิจกรรม</label>
                                        <label class="checkbox-wrapper">
                                            <input type="checkbox" name="display_as_event" value="1">
                                            <span>แสดงข่าวนี้เป็น <strong>"กิจกรรมที่จะเกิดขึ้น"</strong> (Upcoming Event)</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="news-card" style="margin-bottom: 2rem;">
                                <div class="news-card-title">
                                    <svg class="card-title-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.44 11.05l-9.19 9.19a6 6 0 01-8.49-8.49l9.19-9.19a4 4 0 015.66 5.66l-9.2 9.19a2 2 0 01-2.83-2.83l8.49-8.48"/></svg>
                                    สื่อและไฟล์แนบ
                                </div>
                                <div class="form-group">
                                    <label for="news_featured_image" class="form-label">ภาพปกข่าว (Featured Image)</label>
                                    <input type="file" id="news_featured_image" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control">
                                    <div class="file-upload-info">รองรับไฟล์: JPG, PNG, WEBP (แนะนำขนาด 1200x630px)</div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">รูปภาพประกอบเพิ่มเติม (เลือกได้หลายไฟล์)</label>
                                    <input type="file" name="attachments_images[]" accept="image/jpeg,image/png,image/gif,image/webp" class="form-control" multiple>
                                    <div class="file-upload-info">สร้างเป็นแกลลอรี่ภาพด้านล่างข่าว</div>
                                </div>
                                <div class="form-group" style="margin-bottom: 0;">
                                    <label class="form-label">เอกสารแนบ (เลือกได้หลายไฟล์)</label>
                                    <input type="file" name="attachments_docs[]" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx" class="form-control" multiple>
                                    <div class="file-upload-info">รองรับไฟล์: PDF, Word, Excel, PowerPoint</div>
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <button type="button" class="btn-cancel" onclick="window.history.back()">ยกเลิก</button>
                                <button type="submit" class="btn-submit" style="margin-left: 1rem;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    บันทึกข่าว
                                </button>
                            </div>
                        </form>
                    </div>
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

            <!-- Website Settings Tab -->
            <div id="website-tab" class="tab-content">
                <form action="<?= base_url('program-admin/update-website/' . $program['id']) ?>" method="post" style="padding: 1.5rem;">
                    <?= csrf_field() ?>
                    <div class="form-section">
                        <h4 class="form-section-title">การตั้งค่าเว็บไซต์หลักสูตร</h4>
                        <p class="form-text text-muted" style="font-size: 0.875rem; margin-bottom: 1rem;">กำหนดสีธีม สีข้อความ และสีพื้นหลังของหน้าเว็บหลักสูตร (SPA)</p>

                        <div class="form-group" style="margin-bottom: 1.25rem;">
                            <label for="theme_color_hex" class="form-label">สีธีมหลักสูตร</label>
                            <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">ใช้เป็นสีหลัก (ปุ่ม, ไฮไลต์) บนหน้าเว็บหลักสูตร (รูปแบบ #RRGGBB)</p>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <input type="color" id="theme_color" class="form-control" value="<?= esc($program_page['theme_color'] ?? '#1e40af') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                <input type="text" id="theme_color_hex" name="theme_color" class="form-control" value="<?= esc($program_page['theme_color'] ?? '#1e40af') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#1e40af">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="text_color" class="form-label">สีข้อความ</label>
                                <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">สีของข้อความหลักบนหน้าเว็บ (รูปแบบ #RRGGBB)</p>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="color" id="text_color" class="form-control" value="<?= esc($program_page['text_color'] ?? '#1e293b') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                    <input type="text" id="text_color_hex" name="text_color" class="form-control" value="<?= esc($program_page['text_color'] ?? '') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#1e293b">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="var h=document.getElementById('text_color_hex'); var c=document.getElementById('text_color'); h.value=''; c.value='#1e293b';" title="ใช้ค่าตั้งต้น">ล้าง</button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="background_color" class="form-label">สีพื้นหลัง</label>
                                <p class="form-text text-muted" style="font-size: 0.8125rem; margin-bottom: 0.5rem;">สีพื้นหลังหลักของหน้าเว็บ (รูปแบบ #RRGGBB)</p>
                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <input type="color" id="background_color" class="form-control" value="<?= esc($program_page['background_color'] ?? '#f8fafc') ?>" style="width: 60px; height: 40px; padding: 2px; cursor: pointer;">
                                    <input type="text" id="background_color_hex" name="background_color" class="form-control" value="<?= esc($program_page['background_color'] ?? '') ?>" style="width: 100px; font-family: monospace;" maxlength="7" placeholder="#f8fafc">
                                    <button type="button" class="btn btn-outline btn-sm" onclick="var h=document.getElementById('background_color_hex'); var c=document.getElementById('background_color'); h.value=''; c.value='#f8fafc';" title="ใช้ค่าตั้งต้น">ล้าง</button>
                                </div>
                            </div>
                        </div>

                        <p class="form-text text-muted" style="font-size: 0.8125rem; margin-top: 0.5rem;">เว้นว่างเพื่อใช้ค่าตั้งต้นของระบบ</p>

                        <div class="form-actions" style="margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                    <line x1="12" y1="21" x2="12" y2="13" />
                                </svg>
                                บันทึกการตั้งค่าเว็บไซต์
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal ครอปรูปหน้าปก -->
<div id="hero-crop-modal" class="hero-crop-modal" role="dialog" aria-labelledby="hero-crop-modal-title" aria-modal="true" style="display:none;">
    <div class="hero-crop-modal__backdrop"></div>
    <div class="hero-crop-modal__box">
        <div class="hero-crop-modal__header">
            <h3 id="hero-crop-modal-title" class="hero-crop-modal__title">ครอปภาพหน้าปก</h3>
            <p class="hero-crop-modal__subtitle">ปรับกรอบให้พอดีกับภาพหน้าปก (อัตราส่วน 16:9)</p>
            <button type="button" class="hero-crop-modal__close" id="hero-crop-close" aria-label="ปิด">&times;</button>
        </div>
        <div class="hero-crop-modal__body">
            <div class="hero-crop-container">
                <img id="hero-crop-image" src="" alt="">
            </div>
        </div>
        <div class="hero-crop-modal__footer">
            <button type="button" class="btn btn-outline" id="hero-crop-cancel">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="hero-crop-confirm">
                <span class="hero-crop-confirm-text">ตกลง ใช้รูปนี้</span>
                <span class="hero-crop-confirm-loading" style="display:none;">กำลังอัปโหลด...</span>
            </button>
        </div>
    </div>
</div>

<style>
.hero-basic-drop {
    position: relative;
    border: 2px dashed var(--color-gray-300);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    background: var(--color-gray-50);
    transition: border-color 0.2s, background 0.2s;
}
.hero-basic-drop:hover, .hero-basic-drop.dragover { border-color: var(--color-primary); background: rgba(var(--color-primary-rgb, 59, 130, 246), 0.05); }
.hero-basic-drop__text { display: block; font-weight: 500; color: var(--color-gray-700); margin-bottom: 0.25rem; }
.hero-basic-drop__hint { font-size: 0.8125rem; color: var(--color-gray-500); }
.hero-basic-drop--hidden { display: none !important; }
.hero-crop-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 1rem; }
.hero-crop-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,0.6); }
.hero-crop-modal__box { position: relative; background: #fff; border-radius: 12px; max-width: 900px; width: 100%; max-height: 90vh; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
.hero-crop-modal__header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--color-gray-200); flex-shrink: 0; }
.hero-crop-modal__title { margin: 0 2rem 0 0; font-size: 1.125rem; }
.hero-crop-modal__subtitle { margin: 0.25rem 0 0; font-size: 0.875rem; color: var(--color-gray-600); }
.hero-crop-modal__close { position: absolute; top: 1rem; right: 1rem; width: 32px; height: 32px; border: none; background: none; font-size: 1.5rem; line-height: 1; color: var(--color-gray-500); cursor: pointer; }
.hero-crop-modal__close:hover { color: var(--color-gray-800); }
.hero-crop-modal__body { padding: 1rem; overflow: hidden; flex: 1; min-height: 0; }
.hero-crop-container { width: 100%; height: 60vh; max-height: 500px; background: #000; overflow: hidden; }
.hero-crop-container img { max-width: 100%; max-height: 100%; display: block; }
.hero-crop-modal__footer { padding: 1rem 1.25rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem; flex-shrink: 0; }
</style>

<!-- Cropper.js -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.css" crossorigin="anonymous" />
<script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.1/dist/cropper.min.js" crossorigin="anonymous"></script>

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
        swalConfirm({ title: 'คุณแน่ใจว่าต้องการลบไฟล์นี้?', confirmText: 'ลบ', cancelText: 'ยกเลิก' }).then(function(ok) {
            if (ok) window.location.href = url;
        });
    }

    function loadProgramNews() {
        var listEl = document.getElementById('program-news-list');
        if (!listEl) return;
        var programId = <?= (int)($program['id'] ?? 0) ?>;
        var baseUrl = '<?= base_url() ?>';
        fetch(baseUrl + 'program-admin/news/' + programId)
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success || !res.data) { listEl.innerHTML = '<p style="color: var(--color-gray-500);">ไม่มีข่าว</p>'; return; }
                var data = res.data;
                if (data.length === 0) {
                    listEl.innerHTML = '<p style="color: var(--color-gray-500);">ยังไม่มีข่าวที่แท็กกับหลักสูตรนี้</p>';
                    return;
                }
                var html = '<div style="display: flex; flex-direction: column; gap: 0.75rem;">';
                data.forEach(function (n) {
                    var thumb = n.thumb_url ? '<img src="' + n.thumb_url + '" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">' : '<span style="width: 60px; height: 40px; background: var(--color-gray-200); border-radius: 4px; display: inline-block;"></span>';
                    html += '<div style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem; border: 1px solid var(--color-gray-200); border-radius: 8px;">' + thumb + '<div style="flex: 1;"><a href="' + (n.url || (baseUrl + 'news/' + n.id)) + '" target="_blank" style="font-weight: 500;">' + (n.title || '') + '</a><div style="font-size: 0.875rem; color: var(--color-gray-600);">' + (n.created_at_formatted || '') + '</div></div><a href="' + baseUrl + 'admin/news/edit/' + n.id + '" class="btn btn-outline btn-sm" target="_blank">แก้ไข</a></div>';
                });
                html += '</div>';
                listEl.innerHTML = html;
            })
            .catch(function () { listEl.innerHTML = '<p style="color: var(--color-gray-500);">โหลดข่าวไม่สำเร็จ</p>'; });
    }

    var newsContentEditor = null;
    function initNewsCKEditor() {
        var ta = document.getElementById('news_content');
        if (!ta || newsContentEditor !== null) return;
        if (typeof ClassicEditor === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.ckeditor.com/ckeditor5/43.0.0/classic/ckeditor.js';
            s.onload = function() { startNewsCKEditor(); };
            document.head.appendChild(s);
        } else {
            startNewsCKEditor();
        }
    }
    function startNewsCKEditor() {
        var ta = document.getElementById('news_content');
        if (!ta || newsContentEditor !== null) return;
        ClassicEditor.create(ta, {
            language: 'th',
            placeholder: 'ใส่รายละเอียดข่าวที่นี่...',
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo']
        }).then(function(editor) {
            newsContentEditor = editor;
        }).catch(function(err) { console.warn('CKEditor init:', err); });
    }
    function ensureNewsEditorSync() {
        if (newsContentEditor && typeof newsContentEditor.getData === 'function') {
            var ta = document.getElementById('news_content');
            if (ta) ta.value = newsContentEditor.getData();
        }
    }

    // Theme color picker <-> hex sync & hero image preview
    (function() {
        var colorInput = document.getElementById('theme_color');
        var hexInput = document.getElementById('theme_color_hex');
        if (colorInput && hexInput) {
            colorInput.addEventListener('input', function() { hexInput.value = this.value; });
            hexInput.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) colorInput.value = v;
            });
        }
        var heroFile = document.getElementById('hero_image');
        var heroPreview = document.getElementById('hero-preview-img');
        var heroPreviewWrap = heroPreview && heroPreview.closest('.hero-preview-wrap');
        var heroRemove = document.getElementById('hero_image_remove');
        if (heroFile && heroPreviewWrap) {
            heroFile.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    var r = new FileReader();
                    r.onload = function() { heroPreview.src = r.result; heroPreviewWrap.style.display = 'block'; };
                    r.readAsDataURL(this.files[0]);
                }
            });
        }
        if (heroRemove && heroPreviewWrap) {
            heroRemove.addEventListener('change', function() {
                heroPreviewWrap.style.display = this.checked ? 'none' : 'block';
            });
        }
    })();

    // Hero image: drag-drop + crop modal (แท็บข้อมูลพื้นฐาน)
    (function() {
        var programId = <?= (int)($program['id'] ?? 0) ?>;
        var uploadHeroUrl = '<?= base_url('program-admin/upload-hero/' . (int)($program['id'] ?? 0)) ?>';
        var csrfInput = document.querySelector('input[name="<?= csrf_token() ?>"]');
        var dropZone = document.getElementById('hero-basic-drop');
        var preview = document.getElementById('hero-basic-preview');
        var previewImg = document.getElementById('hero-basic-img');
        var fileInput = document.getElementById('hero-basic-file');
        var removeBtn = document.getElementById('hero-basic-remove');
        var modal = document.getElementById('hero-crop-modal');
        var cropImage = document.getElementById('hero-crop-image');
        var cropClose = document.getElementById('hero-crop-close');
        var cropCancel = document.getElementById('hero-crop-cancel');
        var cropConfirm = document.getElementById('hero-crop-confirm');
        var cropConfirmText = cropConfirm && cropConfirm.querySelector('.hero-crop-confirm-text');
        var cropConfirmLoading = cropConfirm && cropConfirm.querySelector('.hero-crop-confirm-loading');
        var cropperInstance = null;
        var currentObjectUrl = null;

        function openCropModal(file) {
            if (!file || !file.type.match(/^image\/(jpeg|png|gif|webp)$/)) return;
            if (currentObjectUrl) URL.revokeObjectURL(currentObjectUrl);
            currentObjectUrl = URL.createObjectURL(file);
            cropImage.src = currentObjectUrl;
            modal.style.display = 'flex';
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
            setTimeout(function() {
                if (typeof Cropper !== 'undefined' && cropImage) {
                    cropperInstance = new Cropper(cropImage, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 0.8,
                        restore: false,
                        guides: true,
                        center: true,
                        highlight: false,
                        cropBoxMovable: true,
                        cropBoxResizable: true
                    });
                }
            }, 100);
        }

        function closeCropModal() {
            modal.style.display = 'none';
            if (cropperInstance) { cropperInstance.destroy(); cropperInstance = null; }
            if (currentObjectUrl) { URL.revokeObjectURL(currentObjectUrl); currentObjectUrl = null; }
            if (fileInput) fileInput.value = '';
        }

        function uploadCropped() {
            if (!cropperInstance || !uploadHeroUrl) return;
            if (cropConfirmText) cropConfirmText.style.display = 'none';
            if (cropConfirmLoading) cropConfirmLoading.style.display = 'inline';
            cropConfirm.disabled = true;
            cropperInstance.getCroppedCanvas({ maxWidth: 1920, maxHeight: 1080, imageSmoothingQuality: 'high' }).toBlob(function(blob) {
                var fd = new FormData();
                fd.append('hero_image', blob, 'hero.jpg');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
                fetch(uploadHeroUrl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success && res.hero_url) {
                            previewImg.src = res.hero_url;
                            preview.style.display = 'block';
                            if (dropZone) dropZone.classList.add('hero-basic-drop--hidden');
                            closeCropModal();
                        } else {
                            alert(res.message || 'อัปโหลดไม่สำเร็จ');
                        }
                    })
                    .catch(function() { alert('เกิดข้อผิดพลาดในการเชื่อมต่อ'); })
                    .finally(function() {
                        cropConfirm.disabled = false;
                        if (cropConfirmText) cropConfirmText.style.display = 'inline';
                        if (cropConfirmLoading) cropConfirmLoading.style.display = 'none';
                    });
            }, 'image/jpeg', 0.9);
        }

        if (fileInput) {
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) openCropModal(this.files[0]);
            });
        }
        if (dropZone) {
            dropZone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); this.classList.add('dragover'); });
            dropZone.addEventListener('dragleave', function(e) { e.preventDefault(); this.classList.remove('dragover'); });
            dropZone.addEventListener('drop', function(e) {
                e.preventDefault();
                e.stopPropagation();
                this.classList.remove('dragover');
                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]) openCropModal(e.dataTransfer.files[0]);
            });
        }
        if (cropClose) cropClose.addEventListener('click', closeCropModal);
        if (cropCancel) cropCancel.addEventListener('click', closeCropModal);
        if (cropConfirm) cropConfirm.addEventListener('click', uploadCropped);
        if (modal && modal.querySelector('.hero-crop-modal__backdrop')) {
            modal.querySelector('.hero-crop-modal__backdrop').addEventListener('click', closeCropModal);
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', function() {
                if (!confirm('ต้องการลบรูปหน้าปกใช่หรือไม่?')) return;
                var fd = new FormData();
                fd.append('hero_image_remove', '1');
                if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
                fetch(uploadHeroUrl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(res) {
                        if (res.success) {
                            previewImg.src = '';
                            preview.style.display = 'none';
                            if (dropZone) dropZone.classList.remove('hero-basic-drop--hidden');
                        }
                    });
            });
        }
    })();

    // Website color pickers sync
    (function() {
        var textColor = document.getElementById('text_color');
        var textColorHex = document.getElementById('text_color_hex');
        if (textColor && textColorHex) {
            textColor.addEventListener('input', function() { textColorHex.value = this.value; });
            textColorHex.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) textColor.value = v;
            });
        }
        var bgColor = document.getElementById('background_color');
        var bgColorHex = document.getElementById('background_color_hex');
        if (bgColor && bgColorHex) {
            bgColor.addEventListener('input', function() { bgColorHex.value = this.value; });
            bgColorHex.addEventListener('input', function() {
                var v = this.value.trim();
                if (/^#[0-9A-Fa-f]{6}$/.test(v)) bgColor.value = v;
            });
        }
    })();

    // Initialize first tab (or tab from query string)
    document.addEventListener('DOMContentLoaded', function() {
        var params = new URLSearchParams(window.location.search);
        var tab = params.get('tab');
        if (tab === 'news' && document.getElementById('news-tab')) {
            switchTab('news');
            loadProgramNews();
            setTimeout(initNewsCKEditor, 100);
        } else if (tab === 'website' && document.getElementById('website-tab')) {
            switchTab('website');
        } else {
            switchTab('basic');
        }
        document.getElementById('program-news-form') && document.getElementById('program-news-form').addEventListener('submit', function() {
            ensureNewsEditorSync();
        });
        });
    var origSwitchTab = window.switchTab;
    if (typeof origSwitchTab === 'function') {
        window.switchTab = function(tabName) {
            origSwitchTab(tabName);
            if (tabName === 'news') { loadProgramNews(); setTimeout(initNewsCKEditor, 100); }
        };
    }

    // --- Ajax save: Basic Info ---
    (function() {
        var form = document.getElementById('basic-info-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('basic-save-btn');
            var msg = document.getElementById('basic-ajax-msg');
            if (!btn || !msg) return;
            btn.disabled = true;
            msg.textContent = 'กำลังบันทึก...';
            msg.style.color = 'var(--color-gray-600)';
            var fd = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    msg.textContent = res.message || (res.success ? 'บันทึกเรียบร้อย' : 'เกิดข้อผิดพลาด');
                    msg.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)';
                })
                .catch(function() {
                    btn.disabled = false;
                    msg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                    msg.style.color = 'var(--color-error)';
                });
        });
    })();

    // --- Ajax save: Content Page ---
    (function() {
        var form = document.getElementById('content-page-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var btn = document.getElementById('content-save-btn');
            var msg = document.getElementById('content-ajax-msg');
            if (!btn || !msg) return;
            btn.disabled = true;
            msg.textContent = 'กำลังบันทึก...';
            msg.style.color = 'var(--color-gray-600)';
            if (typeof window.buildElosJson === 'function') window.buildElosJson();
            if (typeof window.buildCurriculumJson === 'function') window.buildCurriculumJson();
            var fd = new FormData(form);
            fetch(form.action, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    btn.disabled = false;
                    msg.textContent = res.message || (res.success ? 'บันทึกเรียบร้อย' : 'เกิดข้อผิดพลาด');
                    msg.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)';
                })
                .catch(function() {
                    btn.disabled = false;
                    msg.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ';
                    msg.style.color = 'var(--color-error)';
                });
        });
    })();
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
        color: var(--secondary);
        border-bottom-color: var(--secondary);
        background: white;
    }

    .tab-content-container {
        background: white;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: block !important;
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
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.2);
    }

    .form-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 2rem;
        padding-top: 1rem;
        border-top: 1px solid var(--color-gray-200);
    }

    .btn {
        padding: 0.75rem 1.5rem;
        border: 2px solid transparent;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.2s;
        text-decoration: none;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--color-primary) 0%, var(--color-primary-dark) 100%);
        color: var(--color-dark);
        border-color: var(--color-primary-dark);
    }

    .btn-primary:hover {
        filter: brightness(1.05);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.35);
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

    .elos-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .elo-row {
        display: flex;
        gap: 0.75rem;
        align-items: flex-start;
        padding: 1rem;
        border: 1px solid var(--color-gray-200);
        border-radius: 8px;
        background: var(--color-gray-50);
    }

    .elo-row__fields {
        flex: 1;
        min-width: 0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .elo-row__actions {
        flex-shrink: 0;
    }

    .form-row--2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    @media (max-width: 640px) {
        .elo-row { flex-direction: column; }
        .elo-row__fields { grid-template-columns: 1fr; }
        .form-row--2 { grid-template-columns: 1fr; }
    }

    .curriculum-list { display: flex; flex-direction: column; gap: 1.5rem; }
    .curriculum-year-card { border: 1px solid var(--color-gray-200); border-radius: 8px; padding: 1rem; background: var(--color-gray-50); }
    .curriculum-year-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem; }
    .curriculum-year-body { margin-top: 1rem; margin-bottom: 0.5rem; }
    .curriculum-semester { margin-bottom: 1rem; padding: 0.75rem; background: white; border-radius: 6px; border: 1px solid var(--color-gray-200); }
    .curriculum-semester__head { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem; flex-wrap: wrap; }
    .curriculum-semester-name { flex: 1; min-width: 10rem; }
    .curriculum-course-table { width: 100%; border-collapse: collapse; margin-bottom: 0.5rem; font-size: 0.875rem; }
    .curriculum-course-table th, .curriculum-course-table td { padding: 0.35rem 0.5rem; text-align: left; }
    .curriculum-course-table th { font-weight: 600; color: var(--color-gray-700); }
    .structure-toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.5rem; }
    .ajax-msg { font-size: 0.875rem; font-weight: 500; margin-left: 0.5rem; transition: color 0.2s; }
</style>

<script>
(function () {
    function formatJson(taId) {
        var ta = document.getElementById(taId);
        if (!ta) return;
        var raw = (ta.value || '').trim();
        if (!raw) return;
        try {
            var obj = JSON.parse(raw);
            ta.value = JSON.stringify(obj, null, 2);
        } catch (e) {
            swalAlert('JSON ไม่ถูกต้อง: ' + e.message, 'error');
        }
    }
    // --- ELO UI: โหลดจาก data-initial, บันทึกได้ผ่าน Ajax ---
    var elosList = document.getElementById('elos-list');
    var elosJsonField = document.getElementById('elos_json');
    var contentForm = document.querySelector('#content-tab form');
    if (!elosList || !elosJsonField || !contentForm) return;

    function escapeHtml(s) {
        if (s == null) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function addElosRow(data) {
        data = data || {};
        var category = escapeHtml(data.category || '');
        var detail = escapeHtml(data.detail || '');
        var row = document.createElement('div');
        row.className = 'elo-row';
        row.innerHTML =
            '<div class="elo-row__fields">' +
            '<div class="form-group"><label class="form-label">หมวด (category)</label><input type="text" class="form-control elo-field elo-category" value="' + category + '" placeholder="เช่น ความรู้ (Knowledge)"></div>' +
            '<div class="form-group"><label class="form-label">รายละเอียด (detail)</label><textarea class="form-control elo-field elo-detail" rows="3" placeholder="อธิบายผลลัพธ์การเรียนรู้ที่คาดหวัง">' + detail + '</textarea></div>' +
            '</div>' +
            '<div class="elo-row__actions"><button type="button" class="btn btn-danger btn-sm elo-remove-btn">ลบ</button></div>';
        elosList.appendChild(row);
        row.querySelector('.elo-remove-btn').addEventListener('click', function () { row.remove(); });
    }

    function buildElosJson() {
        var rows = elosList.querySelectorAll('.elo-row');
        var arr = [];
        rows.forEach(function (row) {
            var category = (row.querySelector('.elo-category') && row.querySelector('.elo-category').value) || '';
            var detail = (row.querySelector('.elo-detail') && row.querySelector('.elo-detail').value) || '';
            var summary = detail.length > 120 ? detail.substring(0, 120) + '…' : detail;
            arr.push({ category: category, title: category || ('ELO ' + (arr.length + 1)), summary: summary, detail: detail });
        });
        elosJsonField.value = JSON.stringify(arr);
        return elosJsonField.value;
    }

    window.buildElosJson = buildElosJson;
    contentForm.addEventListener('submit', function () { buildElosJson(); });

    var programId = <?= (int)($program['id'] ?? 0) ?>;
    var updatePageJsonUrl = '<?= base_url('program-admin/update-page-json/' . (int)($program['id'] ?? 0)) ?>';
    var csrfInput = contentForm.querySelector('input[name="csrf_test_name"]') || contentForm.querySelector('input[type="hidden"][name*="csrf"]');
    function showElosMsg(msg, isError) {
        var el = document.getElementById('elos-ajax-msg');
        if (el) { el.textContent = msg; el.style.color = isError ? 'var(--color-error)' : 'var(--secondary)'; }
    }
    document.getElementById('elos-save-ajax-btn') && document.getElementById('elos-save-ajax-btn').addEventListener('click', function () {
        var btn = this;
        var json = buildElosJson();
        btn.disabled = true;
        showElosMsg('กำลังบันทึก...');
        var fd = new FormData();
        fd.append('elos_json', json);
        if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
        fetch(updatePageJsonUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                btn.disabled = false;
                showElosMsg(res.success ? 'บันทึก ELO เรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success);
            })
            .catch(function () { btn.disabled = false; showElosMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
    });

    var initialData = [];
    try {
        var raw = elosList.getAttribute('data-initial') || '[]';
        initialData = JSON.parse(raw);
        if (!Array.isArray(initialData)) initialData = [];
    } catch (e) {
        initialData = [];
    }
    if (initialData.length === 0) {
        addElosRow({});
    } else {
        initialData.forEach(function (item) {
            addElosRow(item);
        });
    }

    document.getElementById('elos-add-btn') && document.getElementById('elos-add-btn').addEventListener('click', function () {
        addElosRow({});
    });

    // --- หลักสูตร/แผนการเรียน: repeater ปี > ภาคเรียน > วิชา + บันทึก Ajax ---
    var curriculumList = document.getElementById('curriculum-list');
    var curriculumJsonField = document.getElementById('curriculum_json');
    if (curriculumList && curriculumJsonField) {
        function esc(s) { if (s == null) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        function addCourseRow(semBody, c) {
            c = c || {};
            var tr = document.createElement('tr');
            tr.className = 'curriculum-course-row';
            tr.innerHTML = '<td><input type="text" class="form-control form-control-sm curriculum-course-code" value="' + esc(c.code || '') + '" placeholder="รหัส"></td>' +
                '<td><input type="text" class="form-control form-control-sm curriculum-course-name" value="' + esc(c.name || '') + '" placeholder="ชื่อวิชา"></td>' +
                '<td><input type="number" class="form-control form-control-sm curriculum-course-credits" value="' + esc(c.credits ?? '') + '" placeholder="0" min="0" style="width:4rem"></td>' +
                '<td><button type="button" class="btn btn-danger btn-sm curriculum-remove-course">ลบ</button></td>';
            semBody.appendChild(tr);
            tr.querySelector('.curriculum-remove-course').addEventListener('click', function () { tr.remove(); });
        }
        function addSemester(yearCard, sem) {
            sem = sem || {};
            var semDiv = document.createElement('div');
            semDiv.className = 'curriculum-semester';
            semDiv.innerHTML = '<div class="curriculum-semester__head"><label class="form-label">ภาคเรียน</label><input type="text" class="form-control form-control-sm curriculum-semester-name" value="' + esc(sem.name || '') + '" placeholder="ภาคเรียนที่ 1"><button type="button" class="btn btn-danger btn-sm curriculum-remove-semester">ลบภาคเรียน</button></div>' +
                '<table class="curriculum-course-table"><thead><tr><th>รหัส</th><th>ชื่อวิชา</th><th>หน่วยกิต</th><th></th></tr></thead><tbody class="curriculum-semester-body"></tbody></table>' +
                '<button type="button" class="btn btn-outline btn-sm curriculum-add-course">+ เพิ่มวิชา</button>';
            var tbody = semDiv.querySelector('.curriculum-semester-body');
            (sem.courses || []).forEach(function (c) { addCourseRow(tbody, c); });
            if (!(sem.courses && sem.courses.length)) addCourseRow(tbody, {});
            yearCard.querySelector('.curriculum-year-body').appendChild(semDiv);
            semDiv.querySelector('.curriculum-add-course').addEventListener('click', function () { addCourseRow(tbody, {}); });
            semDiv.querySelector('.curriculum-remove-semester').addEventListener('click', function () { semDiv.remove(); });
        }
        function addYearCard(data) {
            data = data || {};
            var year = data.year || (curriculumList.querySelectorAll('.curriculum-year-card').length + 1);
            var title = esc(data.title || '');
            var credits = esc(data.total_credits ?? '');
            var card = document.createElement('div');
            card.className = 'curriculum-year-card';
            card.innerHTML = '<div class="curriculum-year-head"><h5 class="curriculum-year-title">ปีที่ ' + year + '</h5><button type="button" class="btn btn-danger btn-sm curriculum-remove-year">ลบปี</button></div>' +
                '<div class="form-row form-row--2"><div class="form-group"><label class="form-label">ชื่อช่วงปี</label><input type="text" class="form-control curriculum-year-title-input" value="' + title + '" placeholder="ชั้นปีที่ 1"></div>' +
                '<div class="form-group"><label class="form-label">หน่วยกิตรวม</label><input type="number" class="form-control curriculum-year-credits" value="' + credits + '" placeholder="18" min="0" style="width:6rem"></div></div>' +
                '<div class="curriculum-year-body"></div>' +
                '<button type="button" class="btn btn-outline btn-sm curriculum-add-semester">+ เพิ่มภาคเรียน</button>';
            curriculumList.appendChild(card);
            (data.semesters || []).forEach(function (s) { addSemester(card, s); });
            if (!(data.semesters && data.semesters.length)) addSemester(card, {});
            card.querySelector('.curriculum-add-semester').addEventListener('click', function () { addSemester(card, {}); });
            card.querySelector('.curriculum-remove-year').addEventListener('click', function () { card.remove(); });
        }
        function buildCurriculumJson() {
            var years = [];
            curriculumList.querySelectorAll('.curriculum-year-card').forEach(function (card, i) {
                var y = i + 1;
                var title = (card.querySelector('.curriculum-year-title-input') && card.querySelector('.curriculum-year-title-input').value) || ('ชั้นปีที่ ' + y);
                var total = parseInt((card.querySelector('.curriculum-year-credits') && card.querySelector('.curriculum-year-credits').value) || 0, 10) || 0;
                var semesters = [];
                card.querySelectorAll('.curriculum-semester').forEach(function (semEl) {
                    var name = (semEl.querySelector('.curriculum-semester-name') && semEl.querySelector('.curriculum-semester-name').value) || '';
                    var courses = [];
                    semEl.querySelectorAll('.curriculum-course-row').forEach(function (row) {
                        var code = (row.querySelector('.curriculum-course-code') && row.querySelector('.curriculum-course-code').value) || '';
                        var nameC = (row.querySelector('.curriculum-course-name') && row.querySelector('.curriculum-course-name').value) || '';
                        var cred = parseInt((row.querySelector('.curriculum-course-credits') && row.querySelector('.curriculum-course-credits').value) || 0, 10) || 0;
                        courses.push({ code: code, name: nameC, credits: cred });
                    });
                    semesters.push({ name: name, courses: courses });
                });
                years.push({ year: y, title: title, total_credits: total, semesters: semesters });
            });
            curriculumJsonField.value = JSON.stringify(years);
            return curriculumJsonField.value;
        }
        var curriculumInitial = [];
        try {
            var rawC = curriculumList.getAttribute('data-initial') || '[]';
            curriculumInitial = JSON.parse(rawC);
            if (!Array.isArray(curriculumInitial)) curriculumInitial = [];
        } catch (e) { curriculumInitial = []; }
        if (curriculumInitial.length === 0) addYearCard({});
        else curriculumInitial.forEach(function (y) { addYearCard(y); });
        document.getElementById('curriculum-add-year-btn') && document.getElementById('curriculum-add-year-btn').addEventListener('click', function () { addYearCard({}); });
        function showCurriculumMsg(msg, err) {
            var el = document.getElementById('curriculum-ajax-msg');
            if (el) { el.textContent = msg; el.style.color = err ? 'var(--color-error)' : 'var(--secondary)'; }
        }
        document.getElementById('curriculum-save-ajax-btn') && document.getElementById('curriculum-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var json = buildCurriculumJson();
            btn.disabled = true;
            showCurriculumMsg('กำลังบันทึก...');
            var fd = new FormData();
            fd.append('curriculum_json', json);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) { btn.disabled = false; showCurriculumMsg(res.success ? 'บันทึกแผนการเรียนเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'), !res.success); })
                .catch(function () { btn.disabled = false; showCurriculumMsg('เกิดข้อผิดพลาดในการเชื่อมต่อ', true); });
        });
        window.buildCurriculumJson = buildCurriculumJson;
        contentForm.addEventListener('submit', function () { buildCurriculumJson(); });
    }

    // --- Toolbar แทรกข้อความ: ใช้กับโครงสร้างหลักสูตร และแผนการเรียน/อาชีพ/ค่าเล่าเรียน/การรับสมัคร/ข้อมูลติดต่อ ---
    function applyStructureTool(btn, targetTextarea) {
        if (!targetTextarea) return;
        var insert = btn.getAttribute('data-insert') || '';
        var start = targetTextarea.selectionStart, end = targetTextarea.selectionEnd, val = targetTextarea.value;
        targetTextarea.value = val.substring(0, start) + insert + val.substring(end);
        targetTextarea.selectionStart = targetTextarea.selectionEnd = start + insert.length;
        targetTextarea.focus();
    }
    document.querySelectorAll('.structure-tool').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var group = this.closest('.form-group') || this.closest('.content-with-toolbar');
            var ta = group ? group.querySelector('textarea') : null;
            if (ta) applyStructureTool(this, ta);
        });
    });
    var structureTa = document.getElementById('curriculum_structure');
    if (structureTa) {
        document.getElementById('curriculum-structure-save-ajax-btn') && document.getElementById('curriculum-structure-save-ajax-btn').addEventListener('click', function () {
            var btn = this;
            var msgEl = document.getElementById('curriculum-structure-ajax-msg');
            btn.disabled = true;
            if (msgEl) msgEl.textContent = 'กำลังบันทึก...';
            var fd = new FormData();
            fd.append('curriculum_structure', structureTa.value);
            if (csrfInput) fd.append(csrfInput.name, csrfInput.value);
            fetch(updatePageJsonUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    btn.disabled = false;
                    if (msgEl) { msgEl.textContent = res.success ? 'บันทึกโครงสร้างหลักสูตรเรียบร้อย' : (res.message || 'เกิดข้อผิดพลาด'); msgEl.style.color = res.success ? 'var(--secondary)' : 'var(--color-error)'; }
                })
                .catch(function () { btn.disabled = false; if (msgEl) msgEl.textContent = 'เกิดข้อผิดพลาดในการเชื่อมต่อ'; });
        });
    }
})();
</script>

<?= $this->endSection() ?>
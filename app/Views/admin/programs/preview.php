<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <div style="display: flex; gap: 0.5rem; margin-top: 0.5rem;">
                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-secondary btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                    </svg>
                    แก้ไข
                </a>
                <a href="<?= base_url('program-admin') ?>" class="btn btn-outline btn-sm">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11H3z" />
                        <polyline points="3 9 12 9 12 22" />
                    </svg>
                    แดชบอร์ด
                </a>
                <button type="button" class="btn btn-primary btn-sm" onclick="window.open('<?= base_url('program/' . ($page['slug'] ?? 'program-' . $program['id'])) ?>', '_blank')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                        <circle cx="12" cy="12" r="3" />
                    </svg>
                    ดูเว็บไซต์จริง
                </button>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        <div class="preview-container" style="border: 1px solid var(--color-gray-200); border-radius: 8px; overflow: hidden;">
            <!-- Preview Header -->
            <div class="preview-header" style="background: var(--color-gray-100); padding: 1rem; border-bottom: 1px solid var(--color-gray-200);">
                <div style="display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-gray-600);">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        <span style="font-weight: 500; color: var(--color-gray-700);">ตัวอย่างหน้าเว็บไซต์หลักสูตร</span>
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <span style="padding: 0.25rem 0.75rem; background: var(--color-blue-100); color: var(--color-blue-700); border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                            <?= $page['is_published'] ? 'เผยแพร่แล้ว' : 'ยังไม่เผยแพร่' ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Preview Content -->
            <div class="preview-content" style="padding: 2rem; background: white;">
                <!-- Hero Section -->
                <div class="hero-section" style="text-align: center; padding: 3rem 0; background: linear-gradient(135deg, <?= $page['theme_color'] ?? '#1e40af' ?> 0%, <?= $page['theme_color'] ?? '#1e40af' ?>dd 100%); color: white; margin: -2rem -2rem 2rem -2rem; border-radius: 0 0 12px 12px;">
                    <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 1rem;"><?= esc($program['name_th']) ?></h1>
                    <p style="font-size: 1.25rem; margin-bottom: 2rem; opacity: 0.9;"><?= esc($program['degree_th']) ?></p>
                    <div style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= esc($program['credits'] ?? '4') ?></div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">หน่วยกิต</div>
                        </div>
                        <div style="text-align: center;">
                            <div style="font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem;"><?= esc($program['duration'] ?? '4') ?></div>
                            <div style="font-size: 0.875rem; opacity: 0.8;">ปี</div>
                        </div>
                    </div>
                </div>

                <!-- Content Sections -->
                <div style="max-width: 800px; margin: 0 auto;">
                    <?php if (!empty($page['philosophy'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">ปรัชญาหลักสูตร</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['philosophy'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['objectives'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">วัตถุประสงค์</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['objectives'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['graduate_profile'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">คุณลักษณะบัณฑิต</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['graduate_profile'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['curriculum_structure'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">โครงสร้างหลักสูตร</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['curriculum_structure'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['study_plan'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">แผนการเรียน</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['study_plan'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['career_prospects'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">อาชีพที่สามารถประกอบได้</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['career_prospects'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['tuition_fees'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">ค่าเล่าเรียน/ค่าธรรมเนียม</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['tuition_fees'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['admission_info'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">การรับสมัคร</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['admission_info'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($page['contact_info'])): ?>
                        <section style="margin-bottom: 3rem;">
                            <h2 style="color: var(--color-gray-900); margin-bottom: 1rem; font-size: 1.875rem; font-weight: 600;">ข้อมูลติดต่อ</h2>
                            <div style="color: var(--color-gray-700); line-height: 1.6;"><?= $page['contact_info'] ?></div>
                        </section>
                    <?php endif; ?>

                    <?php if (empty($page['philosophy']) && empty($page['objectives']) && empty($page['graduate_profile'])): ?>
                        <div class="empty-content" style="text-align: center; padding: 3rem; color: var(--color-gray-500);">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom: 1rem;">
                                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                            </svg>
                            <h3 style="margin-bottom: 0.5rem;">ยังไม่มีเนื้อหา</h3>
                            <p>กรุณาเพิ่มเนื้อหาหลักสูตรเพื่อแสดงในหน้าเว็บไซต์</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .preview-container {
        border: 1px solid var(--color-gray-200);
        border-radius: 8px;
        overflow: hidden;
        background: white;
    }

    .preview-header {
        background: var(--color-gray-100);
        padding: 1rem;
        border-bottom: 1px solid var(--color-gray-200);
    }

    .preview-content {
        padding: 2rem;
        background: white;
    }

    .hero-section {
        text-align: center;
        padding: 3rem 0;
        background: linear-gradient(135deg, #1e40af 0%, #1e40afdd 100%);
        color: white;
        margin: -2rem -2rem 2rem -2rem;
        border-radius: 0 0 12px 12px;
    }

    .empty-content {
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

    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
    }

    section h2 {
        color: var(--color-gray-900);
        margin-bottom: 1rem;
        font-size: 1.875rem;
        font-weight: 600;
    }

    section div {
        color: var(--color-gray-700);
        line-height: 1.6;
    }
</style>

<?= $this->endSection() ?>
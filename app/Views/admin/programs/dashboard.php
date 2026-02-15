<?= $this->extend($layout) ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header">
            <h2><?= esc($page_title) ?></h2>
            <p class="form-hint" style="margin: 0.25rem 0 0 0;">จัดการเนื้อหาเว็บไซต์หลักสูตรที่คุณเป็นประธาน</p>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Quick Stats -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div class="stat-card" style="background: var(--color-blue-50); border: 1px solid var(--color-blue-200); border-radius: 8px; padding: 1rem;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 600; color: var(--color-blue-600);"><?= $total_programs ?></div>
                <div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">หลักสูตรที่ดูแล</div>
            </div>
            <div class="stat-card" style="background: var(--color-green-50); border: 1px solid var(--color-green-200); border-radius: 8px; padding: 1rem;">
                <div class="stat-number" style="font-size: 2rem; font-weight: 600; color: var(--color-green-600);"><?= $published_pages ?></div>
                <div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">เผยแพร่แล้ว</div>
            </div>
        </div>

        <!-- Programs List -->
        <?php if (empty($programs)): ?>
            <div class="empty-state" style="text-align: center; padding: 3rem;">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--color-gray-400); margin-bottom: 1rem;">
                    <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5z" />
                    <path d="M12 22V2" />
                </svg>
                <h3 style="color: var(--color-gray-600); margin-bottom: 0.5rem;">ยังไม่มีหลักสูตรที่คุณดูแล</h3>
                <p style="color: var(--color-gray-500);">กรุณาติดต่อผู้ดูแลระบบเพื่อกำหนดหลักสูตร</p>
            </div>
        <?php else: ?>
            <div class="programs-grid" style="display: grid; gap: 1.5rem;">
                <?php foreach ($programs as $program): ?>
                    <div class="program-card" style="border: 1px solid var(--color-gray-200); border-radius: 12px; overflow: hidden; transition: box-shadow 0.2s;">
                        <div class="card-header" style="background: var(--color-gray-50); padding: 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
                            <div style="display: flex; align-items: center; gap: 1rem;">
                                <div class="program-icon" style="width: 48px; height: 48px; background: var(--color-blue-100); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--color-blue-600);">
                                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
                                    </svg>
                                </div>
                                <div style="flex: 1;">
                                    <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600; color: var(--color-gray-900);"><?= esc($program['name_th']) ?></h3>
                                    <p style="margin: 0.25rem 0 0 0; color: var(--color-gray-600); font-size: 0.875rem;"><?= esc($program['degree_th']) ?> (<?= esc($program['level']) ?>)</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card-body" style="padding: 1.5rem;">
                            <!-- Status Badge -->
                            <div style="margin-bottom: 1rem;">
                                <?php 
                                $isPublished = isset($program['page']) && $program['page']['is_published'];
                                $statusColor = $isPublished ? 'var(--color-green-600)' : 'var(--color-gray-600)';
                                $statusBg = $isPublished ? 'var(--color-green-100)' : 'var(--color-gray-100)';
                                $statusText = $isPublished ? 'เผยแพร่แล้ว' : 'ยังไม่เผยแพร่';
                                ?>
                                <span style="display: inline-block; padding: 0.25rem 0.75rem; background: <?= $statusBg ?>; color: <?= $statusColor ?>; border-radius: 12px; font-size: 0.75rem; font-weight: 500;">
                                    <?= $statusText ?>
                                </span>
                            </div>
                            
                            <!-- Quick Actions -->
                            <div class="actions" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="<?= base_url('program-admin/edit/' . $program['id']) ?>" class="btn btn-primary btn-sm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7" />
                                        <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                    </svg>
                                    แก้ไขเนื้อหา
                                </a>
                                <a href="<?= base_url('program-admin/downloads/' . $program['id']) ?>" class="btn btn-outline btn-sm">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" />
                                        <polyline points="7 10 12 15 17 10" />
                                        <line x1="12" y1="15" x2="12" y2="3" />
                                    </svg>
                                    ดาวน์โหลด
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
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.program-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.stats-grid {
    margin-bottom: 2rem;
}

.stat-card {
    text-align: center;
}

.programs-grid {
    display: grid;
    gap: 1.5rem;
}

@media (min-width: 768px) {
    .programs-grid {
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    }
}
</style>

<?= $this->endSection() ?>

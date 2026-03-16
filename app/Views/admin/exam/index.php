<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="exam-admin-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <div>
        <h2 style="margin: 0 0 0.25rem 0;">จัดการตารางคุมสอบ</h2>
        <p style="color: var(--color-gray-600); margin: 0;">นำเข้าและจัดการตารางสอบสำหรับบุคลากร</p>
    </div>
    <a href="<?= base_url('admin/exam/upload') ?>" class="btn btn-primary" style="display: flex; align-items: center; gap: 0.5rem;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
            <polyline points="17 8 12 3 7 8"/>
            <line x1="12" y1="3" x2="12" y2="15"/>
        </svg>
        นำเข้า Excel
    </a>
</div>

<!-- Stats Row -->
<div class="stats-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
    <div class="stat-card" style="background: white; border-radius: 8px; padding: 1.25rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <div style="font-size: 0.875rem; color: var(--color-gray-600);">ภาคเรียนที่เผยแพร่</div>
        <div style="font-size: 1.5rem; font-weight: 600; color: var(--color-primary);"><?= count($batches) ?></div>
    </div>
</div>

<!-- Batches Table -->
<div class="card">
    <div class="card-header" style="border-bottom: 1px solid var(--color-gray-200);">
        <h3 style="margin: 0; font-size: 1rem;">รายการนำเข้า</h3>
    </div>
    <div class="card-body" style="padding: 0;">
        <div style="overflow-x: auto;">
            <table class="table" style="width: 100%;">
                <thead>
                    <tr style="background: var(--color-gray-50);">
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">ภาคเรียน</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">ประเภท</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">ไฟล์</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">สถานะ</th>
                        <th style="padding: 1rem; text-align: left; font-weight: 600;">นำเข้าเมื่อ</th>
                        <th style="padding: 1rem; text-align: center; font-weight: 600;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($batches)): ?>
                        <tr>
                            <td colspan="6" style="padding: 2rem; text-align: center; color: var(--color-gray-600);">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 48px; height: 48px; margin-bottom: 0.5rem; opacity: 0.5;">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                    <polyline points="17 8 12 3 7 8"/>
                                    <line x1="12" y1="3" x2="12" y2="15"/>
                                </svg>
                                <p>ยังไม่มีข้อมูลนำเข้า</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($batches as $batch): ?>
                            <tr style="border-bottom: 1px solid var(--color-gray-100);">
                                <td style="padding: 1rem;"><?= esc($batch['semester_label']) ?></td>
                                <td style="padding: 1rem;">
                                    <?php if ($batch['exam_type'] === 'midterm'): ?>
                                        <span class="badge" style="background: var(--color-warning); color: #333;">สอบกลางภาค</span>
                                    <?php else: ?>
                                        <span class="badge badge-primary">สอบปลายภาค</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                    <?= esc($batch['source_filename'] ?? '-') ?>
                                </td>
                                <td style="padding: 1rem;">
                                    <?php if ($batch['status'] === 'published'): ?>
                                        <span class="badge badge-success">เผยแพร่แล้ว</span>
                                    <?php elseif ($batch['status'] === 'draft'): ?>
                                        <span class="badge" style="background: var(--color-gray-200);">ร่าง</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: var(--color-gray-300);">เก็บถาวร</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 1rem; font-size: 0.875rem; color: var(--color-gray-600);">
                                    <?= date('d/m/Y H:i', strtotime($batch['created_at'])) ?>
                                </td>
                                <td style="padding: 1rem; text-align: center;">
                                    <?php if ($batch['status'] === 'draft'): ?>
                                        <a href="<?= base_url("admin/exam/preview/{$batch['id']}") ?>" class="btn btn-sm btn-primary">
                                            ตรวจสอบ
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= base_url("admin/exam/preview/{$batch['id']}") ?>" class="btn btn-sm btn-secondary">
                                            ดูรายละเอียด
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>แบบรายงานสรุป บริการวิชาการ</h2>
        <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary">← จัดการบริการวิชาการ</a>
    </div>

    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <div class="form-section" style="margin-bottom: 2rem;">
            <h3 class="form-section-title">ภาพรวม</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem;">
                <div style="padding: 1rem 1.5rem; background: var(--color-gray-100, #f3f4f6); border-radius: 8px; min-width: 160px;">
                    <div style="font-size: 0.875rem; color: var(--color-gray-600, #4b5563);">จำนวนรายการทั้งหมด</div>
                    <div style="font-size: 1.5rem; font-weight: 600;"><?= number_format($total) ?></div>
                </div>
                <div style="padding: 1rem 1.5rem; background: var(--color-gray-100, #f3f4f6); border-radius: 8px; min-width: 160px;">
                    <div style="font-size: 0.875rem; color: var(--color-gray-600, #4b5563);">บุคลากรที่ร่วมให้บริการ (ไม่ซ้ำ)</div>
                    <div style="font-size: 1.5rem; font-weight: 600;"><?= number_format($distinct_participants) ?></div>
                </div>
            </div>
        </div>

        <div class="form-section" style="margin-bottom: 2rem;">
            <h3 class="form-section-title">สรุปตามปีการศึกษา (พ.ศ.)</h3>
            <?php if (empty($by_year)): ?>
                <p style="color: var(--color-gray-600);">ยังไม่มีข้อมูล</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ปีการศึกษา</th>
                                <th style="width: 120px; text-align: right;">จำนวนรายการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($by_year as $row): ?>
                                <tr>
                                    <td><?= esc($row['year']) ?></td>
                                    <td style="text-align: right;"><?= number_format($row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-section" style="margin-bottom: 2rem;">
            <h3 class="form-section-title">สรุปตามลักษณะการบริการวิชาการ</h3>
            <?php if (empty($by_service_type)): ?>
                <p style="color: var(--color-gray-600);">ยังไม่มีข้อมูล</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ลักษณะการบริการ</th>
                                <th style="width: 120px; text-align: right;">จำนวนรายการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($by_service_type as $row): ?>
                                <tr>
                                    <td><?= esc($service_type_labels[$row['service_type']] ?? $row['service_type'] ?: '—') ?></td>
                                    <td style="text-align: right;"><?= number_format($row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.form-section-title { font-size: 1rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200, #e5e7eb); }
</style>

<?= $this->endSection() ?>

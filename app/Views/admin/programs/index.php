<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>จัดการหลักสูตร</h2>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= base_url('admin/programs/create') ?>" class="btn btn-primary">เพิ่มหลักสูตร</a>
        </div>
    </div>
    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success" style="margin-bottom: 1rem;"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;"><?= esc(session('error')) ?></div>
        <?php endif; ?>
        <p style="color: var(--color-gray-600); margin-bottom: 1rem;">
            จัดการรายชื่อหลักสูตร (สาขาวิชา) ที่ใช้ในโครงสร้างองค์กรและหน้าสาธารณะ
        </p>

        <?php if (empty($programs)): ?>
            <p style="color: var(--color-gray-500);">ยังไม่มีหลักสูตรในระบบ <a href="<?= base_url('admin/programs/create') ?>">เพิ่มหลักสูตร</a></p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 50px;">ลำดับ</th>
                        <th>ชื่อหลักสูตร (ไทย)</th>
                        <th>ชื่อหลักสูตร (อังกฤษ)</th>
                        <th>ระดับ</th>
                        <th>สังกัด/แผนก</th>
                        <th>ประธานหลักสูตร</th>
                        <th>สถานะ</th>
                        <th style="width: 160px;">กำหนดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($programs as $p): ?>
                        <tr>
                            <td><?= (int)($p['sort_order'] ?? 0) ?></td>
                            <td><?= esc($p['name_th'] ?? '') ?></td>
                            <td><?= esc($p['name_en'] ?? '-') ?></td>
                            <td>
                                <?php
                                $levelLabels = ['bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'doctorate' => 'ปริญญาเอก'];
                                echo esc($levelLabels[$p['level'] ?? 'bachelor'] ?? $p['level']);
                                ?>
                            </td>
                            <td><?= esc($p['department_name'] ?? '-') ?></td>
                            <td><?= esc(($coordinator_names ?? [])[(int)($p['coordinator_id_from_pp'] ?? $p['chair_personnel_id'] ?? $p['coordinator_id'] ?? 0)] ?? '-') ?></td>
                            <td>
                                <?php if (($p['status'] ?? '') === 'active'): ?>
                                    <span style="color: var(--color-success); font-weight: 500;">ใช้งาน</span>
                                <?php else: ?>
                                    <span style="color: var(--color-gray-500);">ไม่ใช้งาน</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('admin/programs/edit/' . $p['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                <a href="<?= base_url('admin/programs/delete/' . $p['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('ต้องการปิดการแสดงหลักสูตรนี้หรือไม่? (จะตั้งเป็นไม่ใช้งาน)');">ปิดใช้</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>

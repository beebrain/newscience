<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
$statusLabels = [
    'pending'    => 'รอตรวจสอบ',
    'verified'   => 'รออนุมัติ',
    'approved'   => 'รอสร้าง PDF',
    'generating' => 'กำลังสร้าง',
    'completed'  => 'เสร็จสิ้น',
    'rejected'   => 'ปฏิเสธ',
];
?>
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 style="margin:0;">จัดการคำขอใบรับรอง</h2>
            <p class="text-muted" style="margin:.25rem 0 0;">ตรวจสอบและจัดการคำขอจากนักศึกษา</p>
        </div>
        <a href="<?= base_url('admin/certificates/pending') ?>" class="btn btn-primary">ดูเฉพาะรอตรวจสอบ</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card-body" style="padding:0;">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                </svg>
                <h3>ไม่มีคำขอ</h3>
            </div>
        <?php else: ?>
            <table class="table" style="margin:0;">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>นักศึกษา</th>
                        <th>ประเภท</th>
                        <th>สถานะ</th>
                        <th>วันที่สร้าง</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= esc($r['request_number']) ?></td>
                            <td><?= esc(($r['student_name'] ?? '') . ' ' . ($r['student_lastname'] ?? '')) ?></td>
                            <td><?= esc($r['template_name'] ?? '-') ?></td>
                            <td><span class="badge badge-status"><?= esc($statusLabels[$r['status']] ?? $r['status']) ?></span></td>
                            <td><?= esc(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                            <td><a href="<?= base_url('admin/certificates/' . $r['id']) ?>" class="btn btn-secondary btn-sm">ดูรายละเอียด</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

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
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h2 style="margin:0;">รออนุมัติใบรับรอง</h2>
        <a href="<?= base_url('approve/certificates/history') ?>" class="btn btn-secondary">ประวัติการอนุมัติ</a>
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
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                <h3>ไม่มีคำขอรออนุมัติ</h3>
            </div>
        <?php else: ?>
            <table class="table" style="margin:0;">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>นักศึกษา</th>
                        <th>ประเภท</th>
                        <th>ระดับ</th>
                        <th>สร้างเมื่อ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= esc($r['request_number']) ?></td>
                            <td><?= esc(($r['student_name'] ?? '') . ' ' . ($r['student_lastname'] ?? '')) ?></td>
                            <td><?= esc($r['template_name'] ?? '-') ?></td>
                            <td><?= $r['level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?></td>
                            <td><?= esc(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                            <td><a href="<?= base_url('approve/certificates/' . $r['id']) ?>" class="btn btn-primary btn-sm">อนุมัติ</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

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
$actionLabels = [
    'submit'  => 'สร้างคำขอ',
    'verify'  => 'ตรวจสอบ',
    'approve' => 'อนุมัติ',
    'reject'  => 'ปฏิเสธ',
    'return'  => 'ส่งกลับ',
];
?>
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 style="margin:0;">คำขอ <?= esc($request['request_number']) ?></h2>
            <p class="text-muted" style="margin:.25rem 0 0;">สถานะ: <strong><?= esc($statusLabels[$request['status']] ?? $request['status']) ?></strong></p>
        </div>
        <a href="<?= base_url('admin/certificates') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card-body">
        <div class="detail-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1.5rem;">
            <div>
                <h4>ข้อมูลคำขอ</h4>
                <table class="table table-sm">
                    <tr><td>นักศึกษา</td><td><?= esc(($request['student_name'] ?? '') . ' ' . ($request['student_lastname'] ?? '')) ?></td></tr>
                    <tr><td>อีเมล</td><td><?= esc($request['student_email'] ?? '-') ?></td></tr>
                    <tr><td>ประเภท</td><td><?= esc($request['template_name'] ?? '-') ?> (<?= $request['template_level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?>)</td></tr>
                    <tr><td>วัตถุประสงค์</td><td><?= nl2br(esc($request['purpose'])) ?></td></tr>
                    <tr><td>จำนวน</td><td><?= (int) $request['copies'] ?> ฉบับ</td></tr>
                    <?php if ($request['note']): ?>
                        <tr><td>หมายเหตุ</td><td><?= nl2br(esc($request['note'])) ?></td></tr>
                    <?php endif; ?>
                </table>
            </div>

            <div>
                <h4>ไทม์ไลน์</h4>
                <?php if (empty($timeline)): ?>
                    <p class="text-muted">ไม่มีบันทึก</p>
                <?php else: ?>
                    <ul style="list-style:none; padding:0; margin:0;">
                        <?php foreach ($timeline as $t): ?>
                            <li style="padding:.5rem 0; border-bottom:1px dashed #e5e7eb;">
                                <small class="text-muted"><?= esc(date('d/m/Y H:i', strtotime($t['created_at']))) ?></small><br>
                                <strong><?= esc($actionLabels[$t['action']] ?? $t['action']) ?></strong>
                                <?php if ($t['comment']): ?>: <?= esc($t['comment']) ?><?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($request['status'] === 'pending'): ?>
            <div style="margin-top:1.5rem; padding:1rem; background:#f8fafc; border-radius:.5rem;">
                <h4>ดำเนินการ</h4>
                <div style="display:flex; gap:1rem; flex-wrap:wrap;">
                    <form method="post" action="<?= base_url('admin/certificates/verify/' . $request['id']) ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-success" onclick="return confirm('ยืนยันการตรวจสอบ?')">ตรวจสอบผ่าน</button>
                    </form>

                    <form method="post" action="<?= base_url('admin/certificates/reject/' . $request['id']) ?>" style="display:inline;">
                        <?= csrf_field() ?>
                        <input type="text" name="reason" class="form-control" placeholder="เหตุผลการปฏิเสธ" required style="display:inline-block; width:auto; margin-right:.5rem;">
                        <button type="submit" class="btn btn-danger" onclick="return confirm('ยืนยันการปฏิเสธ?')">ปฏิเสธ</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($request['status'] === 'rejected' && $request['rejected_reason']): ?>
            <div style="margin-top:1rem; padding:1rem; background:#fef2f2; border:1px solid #fca5a5; border-radius:.5rem;">
                <h4 style="margin-top:0; color:#b91c1c;">เหตุผลการปฏิเสธ</h4>
                <p><?= nl2br(esc($request['rejected_reason'])) ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

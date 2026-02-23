<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
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
            <h2 style="margin:0;">อนุมัติคำขอ <?= esc($request['request_number']) ?></h2>
            <p class="text-muted" style="margin:.25rem 0 0;">ระดับ: <?= $request['level'] === 'program' ? 'ระดับหลักสูตร' : 'ระดับคณะ' ?></p>
        </div>
        <div style="display:flex; gap:.5rem;">
            <a href="<?= base_url('approve/certificates') ?>" class="btn btn-secondary">← กลับ</a>
        </div>
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
                <h4>ข้อมูลนักศึกษา</h4>
                <p><strong>ชื่อ:</strong> <?= esc(($request['student_name'] ?? '') . ' ' . ($request['student_lastname'] ?? '')) ?></p>
                <p><strong>ประเภทใบรับรอง:</strong> <?= esc($request['template_name'] ?? '-') ?></p>
                <p><strong>วัตถุประสงค์:</strong> <?= nl2br(esc($request['purpose'])) ?></p>
                <p><strong>จำนวน:</strong> <?= (int) $request['copies'] ?> ฉบับ</p>
                <?php if ($request['note']): ?>
                    <p><strong>หมายเหตุ:</strong> <?= nl2br(esc($request['note'])) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <h4>ประวัติการดำเนินการ</h4>
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

        <?php if ($can_approve): ?>
            <div style="margin-top:1.5rem; padding:1rem; background:#f0fdf4; border:1px solid #86efac; border-radius:.5rem;">
                <h4 style="margin-top:0;">การอนุมัติ</h4>
                <p class="text-muted">กรุณายืนยันรหัสผ่านของท่านเพื่อลงนามอนุมัติ</p>

                <form method="post" action="<?= base_url('approve/certificates/approve/' . $request['id']) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="password" name="password" class="form-control" placeholder="รหัสผ่าน" required style="display:inline-block; width:auto; min-width:200px; margin-right:.5rem;">
                    <button type="submit" class="btn btn-success" onclick="return confirm('ยืนยันการอนุมัติ? การกระทำนี้จะลงนามในเอกสาร')">อนุมัติ</button>
                </form>

                <hr style="margin:1rem 0; border:0; border-top:1px solid #e5e7eb;">

                <form method="post" action="<?= base_url('approve/certificates/reject/' . $request['id']) ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <input type="text" name="reason" class="form-control" placeholder="เหตุผลการปฏิเสธ" required style="display:inline-block; width:auto; min-width:250px; margin-right:.5rem;">
                    <button type="submit" class="btn btn-danger" onclick="return confirm('ยืนยันการปฏิเสธ?')">ปฏิเสธ</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

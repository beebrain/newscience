<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php
helper('url');
$users = $users ?? [];
$rightsByUid = $rightsByUid ?? [];
$base = base_url();
$adminBase = rtrim($base, '/') . '/evaluate/admin';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<div class="eval-rights-page">
    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
        <div class="card-body">
            <p class="text-muted small mb-3">
                <strong>ส่งคำร้อง:</strong> ส่งคำร้องขอประเมินการสอนได้ &nbsp;|&nbsp;
                <strong>เป็นผู้ประเมิน:</strong> อยู่ในรายชื่อผู้ทรงคุณวุฒิ/ผู้ประเมินได้ &nbsp;|&nbsp;
                <strong>จัดการระบบ:</strong> เข้าหน้า Admin จัดการประเมินและหน้าระบุสิทธิ์ได้
            </p>
            <form method="post" action="<?= esc($adminBase) ?>/saveRights">
                <?= csrf_field() ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อ-นามสกุล</th>
                                <th>อีเมล</th>
                                <th class="text-center">ส่งคำร้อง</th>
                                <th class="text-center">เป็นผู้ประเมิน</th>
                                <th class="text-center">จัดการระบบ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $u):
                                $uid = (int) $u['uid'];
                                $r = $rightsByUid[$uid] ?? null;
                                $name = ($u['gf_name'] ?? $u['tf_name'] ?? '') . ' ' . ($u['gl_name'] ?? $u['tl_name'] ?? '');
                            ?>
                                <tr>
                                    <td><?= esc($name ?: '—') ?></td>
                                    <td><?= esc($u['email'] ?? '') ?></td>
                                    <td class="text-center">
                                        <input type="hidden" name="uid[]" value="<?= $uid ?>">
                                        <input type="checkbox" name="can_submit_<?= $uid ?>" value="1" <?= ($r && (int)($r['can_submit_teaching'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="can_referee_<?= $uid ?>" value="1" <?= ($r && (int)($r['can_be_referee'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    </td>
                                    <td class="text-center">
                                        <input type="checkbox" name="can_manage_<?= $uid ?>" value="1" <?= ($r && (int)($r['can_manage_evaluate'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>บันทึกสิทธิ์</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

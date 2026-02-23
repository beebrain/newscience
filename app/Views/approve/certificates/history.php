<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin:0;">ประวัติการอนุมัติ</h2>
    </div>

    <div class="card-body" style="padding:0;">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg>
                <h3>ไม่มีประวัติการอนุมัติ</h3>
            </div>
        <?php else: ?>
            <table class="table" style="margin:0;">
                <thead>
                    <tr>
                        <th>เลขที่</th>
                        <th>นักศึกษา</th>
                        <th>ประเภท</th>
                        <th>วันที่อนุมัติ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= esc($r['request_number']) ?></td>
                            <td><?= esc(($r['student_name'] ?? '') . ' ' . ($r['student_lastname'] ?? '')) ?></td>
                            <td><?= esc($r['template_name'] ?? '-') ?></td>
                            <td><?= $r['approved_at'] ? esc(date('d/m/Y H:i', strtotime($r['approved_at']))) : '-' ?></td>
                            <td><a href="<?= base_url('approve/certificates/' . $r['id']) ?>" class="btn btn-secondary btn-sm">ดู</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

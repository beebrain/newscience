<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<div class="portal-card">
    <div class="portal-card-header" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1rem;">
        <div>
            <h2 style="margin:0;">รายละเอียดใบรับรอง</h2>
            <p class="text-muted" style="margin:.25rem 0 0;">
                กิจกรรม: <strong><?= esc($recipient['event_title']) ?></strong>
            </p>
        </div>
        <a href="<?= base_url('student/certificates') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="detail-grid" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(280px,1fr)); gap:1rem; margin-top:1rem;">
        <div class="detail-block">
            <h4>ข้อมูลผู้รับ</h4>
            <table class="table table-sm">
                <tr>
                    <td>ชื่อ</td>
                    <td><?= esc($recipient['recipient_name']) ?></td>
                </tr>
                <tr>
                    <td>อีเมล</td>
                    <td><?= esc($recipient['recipient_email'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>รหัส</td>
                    <td><?= esc($recipient['recipient_id_no'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <div class="detail-block">
            <h4>ข้อมูลกิจกรรม</h4>
            <table class="table table-sm">
                <tr>
                    <td>ชื่อกิจกรรม</td>
                    <td><?= esc($recipient['event_title']) ?></td>
                </tr>
                <tr>
                    <td>วันที่จัด</td>
                    <td><?= $recipient['event_date'] ? date('d/m/Y', strtotime($recipient['event_date'])) : '-' ?></td>
                </tr>
                <tr>
                    <td>เทมเพลต</td>
                    <td><?= esc($recipient['template_name'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td>สถานะ</td>
                    <td>
                        <?php if ($recipient['status'] === 'issued'): ?>
                            <span class="badge badge-success">ออกแล้ว</span>
                        <?php elseif ($recipient['status'] === 'pending'): ?>
                            <span class="badge badge-warning">รอออก</span>
                        <?php else: ?>
                            <span class="badge badge-danger">ไม่สำเร็จ</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <?php if ($recipient['status'] === 'issued' && $recipient['certificate_no']): ?>
        <div class="detail-block" style="margin-top:1rem; padding:1rem; background:#f0fdf4; border:1px solid #86efac; border-radius:.5rem;">
            <h4 style="margin-top:0;">ใบรับรอง</h4>
            <p>เลขที่: <strong><?= esc($recipient['certificate_no']) ?></strong></p>
            <p>วันที่ออก: <?= $recipient['issued_date'] ? date('d/m/Y', strtotime($recipient['issued_date'])) : '-' ?></p>
            <?php if ($recipient['download_count']): ?>
                <p>ดาวน์โหลดแล้ว: <?= $recipient['download_count'] ?> ครั้ง</p>
            <?php endif; ?>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <a href="<?= base_url('student/certificates/' . $recipient['id'] . '/download') ?>" class="btn btn-success">ดาวน์โหลด PDF</a>
                <?php if ($recipient['verification_token']): ?>
                    <a href="<?= base_url('verify/' . $recipient['verification_token']) ?>" target="_blank" class="btn btn-secondary">ตรวจสอบความถูกต้อง</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($recipient['status'] === 'pending'): ?>
        <div class="detail-block" style="margin-top:1rem; padding:1rem; background:#fefce8; border:1px solid #fde047; border-radius:.5rem;">
            <h4 style="margin-top:0; color:#854d0e;">รอการออกใบรับรอง</h4>
            <p>ใบรับรองของคุณอยู่ในระบบและรอการออกโดยผู้ดูแลระบบ</p>
        </div>
    <?php endif; ?>

    <?php if ($recipient['status'] === 'failed'): ?>
        <div class="detail-block" style="margin-top:1rem; padding:1rem; background:#fef2f2; border:1px solid #fca5a5; border-radius:.5rem;">
            <h4 style="margin-top:0; color:#b91c1c;">การออกใบรับรองไม่สำเร็จ</h4>
            <p>กรุณาติดต่อผู้ดูแลระบบเพื่อตรวจสอบ</p>
            <?php if ($recipient['error_message']): ?>
                <p><small>รายละเอียด: <?= esc($recipient['error_message']) ?></small></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .badge-success {
        background: #28a745;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 12px;
    }

    .badge-warning {
        background: #ffc107;
        color: black;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 12px;
    }

    .badge-danger {
        background: #dc3545;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 12px;
    }
</style>
<?= $this->endSection() ?>
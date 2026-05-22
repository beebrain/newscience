<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<div class="portal-card">
    <div class="portal-card-header">
        <div>
            <h2>ประวัติการเข้าอบรม / ใบประกาศ</h2>
            <p class="text-muted">รายการกิจกรรมและใบรับรองที่คุณได้รับจากคณะ/หลักสูตร</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <strong>หมายเหตุ:</strong> ใบประกาศจะถูกออกโดยผู้จัดกิจกรรมเมื่อคุณเข้าร่วมอบรมหรือกิจกรรมที่จัดโดยคณะ/หลักสูตร และจะแสดงที่นี่เป็นการรวบรวมประวัติการเข้าอบรมของคุณ
    </div>

    <?php if (empty($certificates)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                <line x1="3" y1="9" x2="21" y2="9" />
            </svg>
            <h3>ยังไม่มีประวัติการเข้าอบรม</h3>
            <p>เมื่อคุณได้รับใบประกาศจากกิจกรรม รายการจะแสดงที่นี่</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>กิจกรรม/หัวข้อ</th>
                        <th>วันที่กิจกรรม</th>
                        <th>วันที่ออกใบ</th>
                        <th>สถานะ</th>
                        <th>เลขที่ Certificate</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td>
                                <strong><?= esc($cert['event_title']) ?></strong>
                            </td>
                            <td><?= $cert['event_date'] ? date('d/m/Y', strtotime($cert['event_date'])) : '-' ?></td>
                            <td>
                                <?php if (! empty($cert['issued_date'])): ?>
                                    <?= date('d/m/Y', strtotime((string) $cert['issued_date'])) ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cert['status'] === 'issued'): ?>
                                    <span class="badge badge-success">ออกแล้ว</span>
                                <?php elseif ($cert['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">รอออก</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">ไม่สำเร็จ</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($cert['certificate_no']): ?>
                                    <code style="font-size: 12px;"><?= esc($cert['certificate_no']) ?></code>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('student/certificates/' . $cert['id']) ?>" class="btn btn-secondary btn-sm">รายละเอียด</a>
                                <?php if ($cert['status'] === 'issued' && $cert['pdf_path']): ?>
                                    <a href="<?= base_url('student/certificates/' . $cert['id'] . '/download') ?>" class="btn btn-primary btn-sm">ดาวน์โหลด</a>
                                <?php endif; ?>
                                <?php if ($cert['status'] === 'issued' && ! empty($cert['verification_token'])): ?>
                                    <a href="<?= base_url('verify/' . $cert['verification_token']) ?>" class="btn btn-secondary btn-sm" target="_blank" rel="noopener">ตรวจสอบ</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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

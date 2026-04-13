<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <h2 style="margin:0;">รายงานใบรับรองที่ออกแล้ว</h2>
        <a href="<?= esc($cert_base) ?>" class="btn btn-secondary">กลับรายการกิจกรรม</a>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="alert alert-info">ยังไม่มีรายการที่ออกใบแล้ว</div>
        <?php else: ?>
            <p style="color:#666;font-size:14px;">แสดงสูงสุด <?= count($rows) ?> รายการล่าสุด</p>
            <div style="overflow-x:auto;">
                <table class="table" style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th style="padding:0.5rem;">กิจกรรม</th>
                            <th style="padding:0.5rem;">ผู้รับ</th>
                            <th style="padding:0.5rem;">อีเมล</th>
                            <th style="padding:0.5rem;">เลขที่</th>
                            <th style="padding:0.5rem;">วันที่ออก</th>
                            <th style="padding:0.5rem;">ผู้สร้างกิจกรรม</th>
                            <th style="padding:0.5rem;">ส่งอีเมล</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr style="border-bottom:1px solid #dee2e6;">
                                <td style="padding:0.5rem;"><?= esc($r['event_title'] ?? '') ?></td>
                                <td style="padding:0.5rem;"><?= esc($r['recipient_name'] ?? '') ?></td>
                                <td style="padding:0.5rem;"><?= esc($r['recipient_email'] ?? '') ?></td>
                                <td style="padding:0.5rem;"><code><?= esc($r['certificate_no'] ?? '') ?></code></td>
                                <td style="padding:0.5rem;"><?= esc($r['issued_date'] ?? '') ?></td>
                                <td style="padding:0.5rem;font-size:12px;">
                                    <?= esc(trim(($r['creator_tf_name'] ?? '') . ' ' . ($r['creator_tl_name'] ?? '')) ?: ($r['creator_email'] ?? '-')) ?>
                                </td>
                                <td style="padding:0.5rem;font-size:12px;">
                                    <?php if (! empty($r['email_sent_at'])): ?>
                                        <span class="badge badge-success">ส่งแล้ว</span><br><?= esc($r['email_sent_at']) ?>
                                    <?php elseif (! empty($r['email_error'])): ?>
                                        <span class="badge badge-danger">ล้มเหลว</span><br><small><?= esc($r['email_error']) ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
.badge{padding:0.2rem 0.45rem;border-radius:4px;font-size:11px;}
.badge-success{background:#28a745;color:#fff;}
.badge-danger{background:#dc3545;color:#fff;}
</style>
<?= $this->endSection() ?>

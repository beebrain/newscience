<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<?php
$stateLabels = [
    'locked'         => ['คุณไม่มีสิทธิ์ในกิจกรรมนี้', 'secondary'],
    'ready_claim'    => ['คุณมีสิทธิ์ — แตะเข้าไปเปิดคูปองรับรหัส', 'primary'],
    'wait_pool'      => ['มีสิทธิ์ — รอรหัสว่างจากผู้จัด', 'warning'],
    'confirm_receipt'=> ['มีสิทธิ์ — เปิดคูปองเพื่อยืนยันการรับ', 'primary'],
    'opened'         => ['รับสิทธิ์แล้ว', 'success'],
    'event_closed'   => ['กิจกรรมปิดแล้ว', 'secondary'],
];
?>
<div class="card barcodes-page">
    <div class="card-header">
        <h2 style="margin: 0;">บาร์โค้ดของฉัน</h2>
        <a href="<?= base_url('student') ?>" class="btn btn-secondary">← กลับ Portal</a>
    </div>
    <div class="card-body">
        <p style="color: var(--color-gray-600); margin-bottom: 0.5rem;">กิจกรรมทั้งหมดที่เกี่ยวกับบาร์โค้ด — ดูว่าคุณมีสิทธิ์กิจกรรมใดบ้าง แล้วเข้าไป<strong>เปิดคูปอง</strong>เพื่อยืนยันการรับสิทธิ์และดูรหัส</p>
        <p style="color: var(--color-gray-500); font-size: 0.875rem; margin-bottom: 1.5rem;">กิจกรรมที่ยังไม่เปิดรับจะแสดงสถานะให้ทราบเท่านั้น</p>

        <?php if (empty($portal_events)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <p style="margin: 0;">ยังไม่มีกิจกรรมบาร์โค้ดในขณะนี้</p>
            </div>
        <?php else: ?>
            <section class="barcode-activity-grid" aria-label="รายการกิจกรรมบาร์โค้ด">
                <?php foreach ($portal_events as $row):
                    $ev = $row['event'];
                    $st = $row['state']['state'] ?? 'locked';
                    $label = $stateLabels[$st][0] ?? $st;
                    $badge = $stateLabels[$st][1] ?? 'secondary';
                    $eid = (int) ($ev['id'] ?? 0);
                    $badgeClass = $badge === 'primary' ? 'badge-st--primary' : ($badge === 'success' ? 'badge-st--success' : ($badge === 'warning' ? 'badge-st--warning' : 'badge-st--muted'));
                    ?>
                    <a href="<?= base_url('student/barcodes/event/' . $eid) ?>" class="barcode-activity-card">
                        <div class="barcode-activity-card-inner">
                            <div class="barcode-activity-head">
                                <h3 class="barcode-activity-title"><?= esc($ev['title'] ?? 'กิจกรรม') ?></h3>
                                <span class="barcode-activity-badge <?= esc($badgeClass) ?>"><?= esc($label) ?></span>
                            </div>
                            <?php if (!empty($ev['event_date'])): ?>
                                <p class="barcode-activity-date"><?= esc($ev['event_date']) ?></p>
                            <?php endif; ?>
                            <?php
                            $desc = trim(strip_tags((string) ($ev['description'] ?? '')));
                            if ($desc !== '') {
                                if (function_exists('mb_strlen') && mb_strlen($desc, 'UTF-8') > 120) {
                                    $desc = mb_substr($desc, 0, 120, 'UTF-8') . '…';
                                } elseif (strlen($desc) > 120) {
                                    $desc = substr($desc, 0, 117) . '...';
                                }
                                ?>
                                <p class="barcode-activity-desc"><?= esc($desc) ?></p>
                            <?php } ?>
                            <span class="barcode-activity-cta">เปิดคูปอง / ดูรายละเอียด →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</div>
<style>
.barcodes-page .barcode-activity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}
.barcodes-page .barcode-activity-card {
    display: block;
    text-decoration: none;
    color: inherit;
    border-radius: 12px;
    border: 1px solid var(--color-gray-200, #e5e7eb);
    background: var(--color-white);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: box-shadow 0.2s, border-color 0.2s, transform 0.15s;
}
.barcodes-page .barcode-activity-card:hover {
    border-color: var(--primary, #ca8a04);
    box-shadow: 0 6px 20px rgba(0,0,0,0.08);
    transform: translateY(-2px);
}
.barcodes-page .barcode-activity-card-inner { padding: 1.125rem 1.25rem; }
.barcodes-page .barcode-activity-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}
.barcodes-page .barcode-activity-title {
    font-size: 1.0625rem;
    font-weight: 600;
    margin: 0;
    flex: 1;
    min-width: 0;
}
.barcodes-page .barcode-activity-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 999px;
    white-space: nowrap;
    max-width: 100%;
    text-align: center;
}
.badge-st--primary { background: rgba(202, 138, 4, 0.15); color: #92400e; }
.badge-st--success { background: rgba(34, 197, 94, 0.15); color: #166534; }
.badge-st--warning { background: rgba(251, 191, 36, 0.2); color: #92400e; }
.badge-st--muted { background: var(--color-gray-100); color: var(--color-gray-600); }
.barcodes-page .barcode-activity-date {
    font-size: 0.875rem;
    color: var(--color-gray-500);
    margin: 0 0 0.5rem 0;
}
.barcodes-page .barcode-activity-desc {
    font-size: 0.8125rem;
    color: var(--color-gray-600);
    margin: 0 0 0.75rem 0;
    line-height: 1.45;
}
.barcodes-page .barcode-activity-cta {
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--primary-dark, #a16207);
}
@media (max-width: 640px) {
    .barcodes-page .card-header, .barcodes-page .card-body { padding: 1rem; }
}
</style>
<?= $this->endSection() ?>

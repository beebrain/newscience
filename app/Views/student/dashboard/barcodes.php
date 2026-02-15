<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<div class="card barcodes-page">
    <div class="card-header">
        <h2 style="margin: 0;">บาร์โค้ดของฉัน</h2>
        <a href="<?= base_url('student') ?>" class="btn btn-secondary">← กลับ Portal</a>
    </div>
    <div class="card-body">
        <p style="color: var(--color-gray-600); margin-bottom: 1.5rem;">รายการบาร์โค้ดที่คุณได้รับจากแต่ละกิจกรรม</p>

        <?php if (!empty($eligible_events_without_barcode)): ?>
            <section class="barcode-section" aria-label="กิจกรรมที่รับบาร์โค้ดได้">
                <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; color: var(--color-gray-700);">รับบาร์โค้ดจากกิจกรรม</h3>
                <?php foreach ($eligible_events_without_barcode as $ev): ?>
                    <div class="card barcode-card">
                        <div class="card-header" style="padding: 1rem 1.25rem;">
                            <h4 style="font-size: 1.0625rem; font-weight: 600; margin: 0;"><?= esc($ev['event_title']) ?></h4>
                            <?php if (!empty($ev['event_date'])): ?>
                                <span style="color: var(--color-gray-500); font-size: 0.875rem;"><?= esc($ev['event_date']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="padding: 1rem 1.25rem;">
                            <form action="<?= base_url('student/barcodes/claim-from-event/' . (int) $ev['event_id']) ?>" method="post">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-primary barcode-claim-btn">รับบาร์โค้ด</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>

        <?php if (empty($by_event) && empty($eligible_events_without_barcode)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                <p style="margin: 0;">ยังไม่มีบาร์โค้ดที่ได้รับในขณะนี้</p>
            </div>
        <?php elseif (!empty($by_event)): ?>
            <section class="barcode-section" aria-label="บาร์โค้ดที่ได้รับแล้ว">
                <?php if (!empty($eligible_events_without_barcode)): ?>
                    <h3 style="font-size: 1rem; font-weight: 600; margin: 0 0 1rem 0; color: var(--color-gray-700);">บาร์โค้ดที่ได้รับแล้ว</h3>
                <?php endif; ?>
                <?php foreach ($by_event as $eid => $group): ?>
                    <div class="card barcode-card">
                        <div class="card-header" style="padding: 1rem 1.25rem;">
                            <h4 style="font-size: 1.0625rem; font-weight: 600; margin: 0;"><?= esc($group['event_title']) ?></h4>
                            <?php if (!empty($group['event_date'])): ?>
                                <span style="color: var(--color-gray-500); font-size: 0.875rem;"><?= esc($group['event_date']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body" style="padding: 1rem 1.25rem;">
                            <ul class="barcode-list" style="list-style: none; padding: 0; margin: 0;">
                                <?php foreach ($group['barcodes'] as $b): ?>
                                    <li style="padding: 0.75rem 0; border-bottom: 1px solid var(--color-gray-100);">
                                        <?php if (!empty($b['claimed_at'])): ?>
                                            <span style="font-family: ui-monospace, monospace; font-size: 1.0625rem;"><?= esc($b['code']) ?></span>
                                            <span style="color: var(--color-gray-500); font-size: 0.8125rem;"> — ยืนยันการรับแล้ว <?= esc($b['claimed_at']) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--color-gray-600); font-size: 0.9375rem;">คุณมีบาร์โค้ดสำหรับกิจกรรมนี้ — กดรับเพื่อดูรหัส</span>
                                            <form action="<?= base_url('student/barcodes/claim/' . $b['id']) ?>" method="post" style="display: inline-block; margin-top: 0.5rem;">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-primary btn-sm barcode-claim-btn">รับบาร์โค้ด</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </div>
</div>
<style>
.barcodes-page .barcode-card { margin-bottom: 1.25rem; }
.barcodes-page .barcode-section + .barcode-section { margin-top: 1.5rem; }
.barcode-claim-btn { min-height: 2.75rem; min-width: 8rem; font-size: 0.9375rem; padding: 0.5rem 1rem; }
@media (max-width: 640px) {
    .barcodes-page .card-header, .barcodes-page .card-body { padding: 1rem; }
    .barcode-claim-btn { min-height: 2.5rem; min-width: 7rem; }
}
</style>
<?= $this->endSection() ?>

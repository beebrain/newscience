<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<?php
$st = $state['state'] ?? 'locked';
$eid = (int) ($event['id'] ?? 0);
$firstUnclaimed = (int) ($state['first_unclaimed_id'] ?? 0);
?>
<div class="card barcode-event-page">
    <div class="card-header">
        <h2 style="margin: 0;">กิจกรรมบาร์โค้ด</h2>
        <a href="<?= base_url('student/barcodes') ?>" class="btn btn-secondary">← รายการกิจกรรม</a>
    </div>
    <div class="card-body">
        <p class="be-meta"><strong><?= esc($event['title'] ?? '') ?></strong>
            <?php if (!empty($event['event_date'])): ?>
                <span class="be-date"> · <?= esc($event['event_date']) ?></span>
            <?php endif; ?>
            <span class="be-status badge-mini"><?= esc($event['status'] ?? '') ?></span>
        </p>
        <?php if (!empty($event['description'])): ?>
            <div class="be-desc"><?= nl2br(esc((string) $event['description'])) ?></div>
        <?php endif; ?>

        <div class="coupon-area">
            <?php if ($st === 'locked'): ?>
                <div class="coupon-static coupon-static--muted">
                    <div class="coupon-static-icon" aria-hidden="true">🔒</div>
                    <h3 class="coupon-static-title">คูปองนี้ไม่ใช่ของคุณ</h3>
                    <p class="coupon-static-text">คุณไม่อยู่ในรายชื่อผู้มีสิทธิ์รับบาร์โค้ดจากกิจกรรมนี้ หากควรมีสิทธิ์ กรุณาติดต่อผู้จัดกิจกรรม</p>
                </div>

            <?php elseif ($st === 'opened'): ?>
                <div class="coupon-static coupon-static--success">
                    <div class="coupon-static-icon" aria-hidden="true">✓</div>
                    <h3 class="coupon-static-title">รับสิทธิ์แล้ว</h3>
                    <p class="coupon-static-text">การจับคู่บาร์โค้ดกับบัญชีของคุณสำเร็จ — รหัสของคุณมีดังนี้</p>
                    <ul class="be-code-list">
                        <?php foreach ($state['my_barcodes'] as $b): ?>
                            <li>
                                <code class="be-code"><?= esc($b['code'] ?? '') ?></code>
                                <?php if (!empty($b['claimed_at'])): ?>
                                    <span class="be-code-note">ยืนยันเมื่อ <?= esc($b['claimed_at']) ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

            <?php else: ?>
                <p class="coupon-intro">แตะที่คูปองด้านล่างเพื่อพลิกเปิด — จากนั้นดำเนินการยืนยันตามขั้นตอน</p>
                <div class="coupon-flip" id="couponFlipRoot">
                    <div class="coupon-flip-inner" id="couponFlipInner">
                        <button type="button" class="coupon-face coupon-front" id="couponFlipFront" aria-label="พลิกเปิดคูปอง">
                            <span class="coupon-ribbon">สิทธิ์พิเศษ</span>
                            <span class="coupon-dots"></span>
                            <span class="coupon-front-title"><?= esc($event['title'] ?? 'กิจกรรม') ?></span>
                            <span class="coupon-front-hint">แตะเพื่อเปิดคูปอง</span>
                        </button>
                        <div class="coupon-face coupon-back">
                            <?php if ($st === 'ready_claim'): ?>
                                <h3 class="coupon-back-title">ยืนยันการรับสิทธิ์</h3>
                                <p class="coupon-back-text">เมื่อกดปุ่มด้านล่าง ระบบจะจับคู่รหัสจากกองให้คุณหนึ่งรายการ และบันทึกว่าคุณยืนยันรับสิทธิ์แล้ว (เปิดคูปอง)</p>
                                <form method="post" action="<?= base_url('student/barcodes/claim-from-event/' . $eid) ?>" class="coupon-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary btn-coupon-submit">ยืนยัน — เปิดคูปองรับรหัส</button>
                                </form>

                            <?php elseif ($st === 'confirm_receipt' && $firstUnclaimed > 0): ?>
                                <h3 class="coupon-back-title">ยืนยันการรับสิทธิ์</h3>
                                <p class="coupon-back-text">คุณได้รับการจับคู่บาร์โค้ดแล้ว — กดยืนยันเพื่อดูรหัสและบันทึกว่ารับทราบ</p>
                                <form method="post" action="<?= base_url('student/barcodes/claim/' . $firstUnclaimed) ?>" class="coupon-form">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-primary btn-coupon-submit">ยืนยัน — เปิดคูปองดูรหัส</button>
                                </form>

                            <?php elseif ($st === 'wait_pool'): ?>
                                <h3 class="coupon-back-title">รอรหัสว่าง</h3>
                                <p class="coupon-back-text">คุณมีสิทธิ์ในกิจกรรมนี้แล้ว แต่ขณะนี้ยังไม่มีรหัสว่างในกอง — กรุณาลองกลับมาใหม่เมื่อผู้จัดเพิ่มรหัส หรือเมื่อมีผู้คืนรหัส</p>

                            <?php elseif ($st === 'event_closed'): ?>
                                <h3 class="coupon-back-title">กิจกรรมปิดแล้ว</h3>
                                <p class="coupon-back-text">ไม่สามารถรับรหัสเพิ่มจากกิจกรรมนี้ได้อีก หากคุณควรได้รับสิทธิ์แต่ไม่ทันรับรหัส กรุณาติดต่อผู้จัด</p>

                            <?php else: ?>
                                <p class="coupon-back-text">สถานะไม่ทราบ</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<style>
.barcode-event-page .be-meta { margin: 0 0 0.75rem 0; font-size: 1rem; }
.barcode-event-page .be-date { color: var(--color-gray-500); font-weight: 400; }
.barcode-event-page .be-status {
    display: inline-block;
    margin-left: 0.35rem;
    font-size: 0.7rem;
    text-transform: uppercase;
    padding: 0.15rem 0.45rem;
    border-radius: 4px;
    background: var(--color-gray-100);
    color: var(--color-gray-600);
}
.barcode-event-page .be-desc {
    color: var(--color-gray-700);
    font-size: 0.9375rem;
    line-height: 1.5;
    margin-bottom: 1.5rem;
}
.coupon-area { margin-top: 0.5rem; max-width: 420px; margin-left: auto; margin-right: auto; }
.coupon-intro {
    text-align: center;
    font-size: 0.875rem;
    color: var(--color-gray-600);
    margin-bottom: 1rem;
}
.coupon-static {
    border-radius: 16px;
    padding: 1.75rem 1.5rem;
    text-align: center;
    border: 2px dashed var(--color-gray-300);
    background: linear-gradient(165deg, #fffbeb 0%, #fff 45%, #fef3c7 100%);
}
.coupon-static--muted {
    background: var(--color-gray-50);
    border-color: var(--color-gray-200);
}
.coupon-static--success {
    border-style: solid;
    border-color: #22c55e;
    background: linear-gradient(165deg, #ecfdf5 0%, #fff 50%, #d1fae5 100%);
}
.coupon-static-icon { font-size: 2rem; line-height: 1; margin-bottom: 0.5rem; }
.coupon-static-title { margin: 0 0 0.5rem 0; font-size: 1.125rem; }
.coupon-static-text { margin: 0; font-size: 0.9rem; color: var(--color-gray-600); line-height: 1.5; }
.be-code-list { list-style: none; padding: 0; margin: 1rem 0 0 0; text-align: left; }
.be-code-list li { padding: 0.65rem 0; border-top: 1px solid rgba(0,0,0,0.06); }
.be-code { font-size: 1.125rem; font-family: ui-monospace, monospace; }
.be-code-note { display: block; font-size: 0.75rem; color: var(--color-gray-500); margin-top: 0.25rem; }

.coupon-flip { perspective: 1000px; }
.coupon-flip-inner {
    position: relative;
    min-height: 220px;
    transition: transform 0.65s ease;
    transform-style: preserve-3d;
}
.coupon-flip-inner.is-flipped { transform: rotateY(180deg); }
.coupon-face {
    position: absolute;
    inset: 0;
    backface-visibility: hidden;
    border-radius: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 1.25rem;
    box-sizing: border-box;
}
.coupon-front {
    background: linear-gradient(145deg, #fef08a 0%, #fde047 35%, #facc15 100%);
    border: 2px solid #ca8a04;
    box-shadow: 0 8px 24px rgba(202, 138, 4, 0.25);
    cursor: pointer;
    font: inherit;
    width: 100%;
    text-align: center;
}
.coupon-front:focus { outline: 3px solid var(--primary); outline-offset: 2px; }
.coupon-ribbon {
    position: absolute;
    top: 10px;
    right: -8px;
    background: #b45309;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    padding: 0.25rem 1.5rem 0.25rem 0.75rem;
    border-radius: 4px 0 0 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.coupon-dots {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    border: 3px dotted rgba(180, 83, 9, 0.5);
    margin-bottom: 0.75rem;
}
.coupon-front-title {
    font-weight: 800;
    font-size: 1.05rem;
    color: #713f12;
    padding: 0 0.5rem;
    line-height: 1.3;
}
.coupon-front-hint {
    margin-top: 0.75rem;
    font-size: 0.8rem;
    color: #92400e;
    font-weight: 600;
}
.coupon-back {
    transform: rotateY(180deg);
    background: #fff;
    border: 2px solid var(--color-gray-200);
    box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    overflow-y: auto;
    align-items: stretch;
    text-align: left;
}
.coupon-back-title { margin: 0 0 0.5rem 0; font-size: 1.05rem; text-align: center; }
.coupon-back-text { margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--color-gray-600); line-height: 1.5; }
.coupon-form { text-align: center; }
.btn-coupon-submit { min-height: 2.75rem; width: 100%; max-width: 100%; }
@media (max-width: 640px) {
    .barcode-event-page .card-header, .barcode-event-page .card-body { padding: 1rem; }
}
</style>
<script>
(function() {
    var inner = document.getElementById('couponFlipInner');
    var front = document.getElementById('couponFlipFront');
    if (!inner || !front) return;
    front.addEventListener('click', function() {
        inner.classList.add('is-flipped');
    });
})();
</script>
<?= $this->endSection() ?>

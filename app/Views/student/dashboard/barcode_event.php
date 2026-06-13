<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<?php
include __DIR__ . '/../partials/activity_ui.php';
$st              = $state['state'] ?? 'locked';
$eid             = (int) ($event['id'] ?? 0);
$firstUnclaimed  = (int) ($state['first_unclaimed_id'] ?? 0);
$meta            = student_activity_state_meta()[$st] ?? ['label' => $st, 'hint' => '', 'tone' => 'muted'];
$dateStr         = student_activity_format_date($event['event_date'] ?? null);
$statusLabel     = ['active' => 'เปิดรับ', 'closed' => 'ปิดแล้ว', 'draft' => 'ฉบับร่าง'][$event['status'] ?? ''] ?? ($event['status'] ?? '');
?>
<div class="stu-act-detail">
    <nav class="stu-act-detail__crumb" aria-label="เส้นทาง">
        <a href="<?= base_url('student/barcodes') ?>">กิจกรรมของฉัน</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page"><?= esc($event['title'] ?? 'กิจกรรม') ?></span>
    </nav>

    <header class="stu-act-detail__head">
        <div>
            <h1 class="stu-act-detail__title"><?= esc($event['title'] ?? 'กิจกรรม') ?></h1>
            <p class="stu-act-detail__meta">
                <?php if ($dateStr !== ''): ?>
                    <time datetime="<?= esc($event['event_date'] ?? '') ?>"><?= esc($dateStr) ?></time>
                    <span aria-hidden="true"> · </span>
                <?php endif; ?>
                <span class="stu-act-detail__status"><?= esc($statusLabel) ?></span>
            </p>
        </div>
        <span class="stu-act__badge stu-act__badge--<?= esc($meta['tone']) ?>"><?= esc($meta['label']) ?></span>
    </header>

    <?php if (! empty($event['description'])): ?>
        <div class="stu-act-detail__desc"><?= nl2br(esc((string) $event['description'])) ?></div>
    <?php endif; ?>

    <section class="stu-act-detail__panel" aria-labelledby="act-panel-heading">
        <h2 id="act-panel-heading" class="visually-hidden">สถานะการรับรหัส</h2>

        <?php if ($st === 'locked'): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--muted" role="status">
                <p class="stu-act-detail__notice-title">คุณยังไม่มีสิทธิ์ในกิจกรรมนี้</p>
                <p>ถ้าผู้จัดให้รหัสเข้าร่วม ให้กลับไปที่ <a href="<?= base_url('student/barcodes') ?>">รายการกิจกรรม</a> แล้วกรอกรหัสในช่อง “รหัสเข้าร่วม”</p>
            </div>

        <?php elseif ($st === 'opened'): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--success" role="status">
                <p class="stu-act-detail__notice-title">รหัสของคุณ</p>
                <p class="stu-act-detail__notice-sub">เก็บรหัสนี้ไว้ใช้ตามที่ผู้จัดกำหนด (เช่น แสดงที่จุดลงทะเบียน)</p>
            </div>
            <ul class="stu-act-detail__codes" role="list">
                <?php foreach ($state['my_barcodes'] as $b):
                    $code = (string) ($b['code'] ?? '');
                    ?>
                    <li class="stu-act-detail__code-row" style="border: 0; padding: 0.5rem 0;">
                        <div class="coupon-card" id="coupon-card-<?= (int) ($b['id'] ?? 0) ?>">
                            <div class="coupon-card__inner">
                                <!-- Front side: Unrevealed Coupon -->
                                <div class="coupon-card__front" onclick="revealCoupon(<?= (int) ($b['id'] ?? 0) ?>)" role="button" tabindex="0" aria-label="กดเพื่อแสดงรหัสเข้าร่วมกิจกรรม">
                                    <div class="coupon-card__ticket-pattern"></div>
                                    <div class="coupon-card__content">
                                        <span class="coupon-card__icon" aria-hidden="true">🎟️</span>
                                        <div class="coupon-card__text">
                                            <span class="coupon-card__label">คูปองรหัสเข้าร่วม</span>
                                            <span class="coupon-card__hint">แตะเพื่อเปิดดูรหัส</span>
                                        </div>
                                    </div>
                                    <div class="coupon-card__reveal-badge">เปิดดูรหัส</div>
                                </div>
                                <!-- Back side: Revealed Code -->
                                <div class="coupon-card__back">
                                    <div class="coupon-card__code-container">
                                        <code class="stu-act-detail__code" id="barcode-code-<?= (int) ($b['id'] ?? 0) ?>"><?= esc($code) ?></code>
                                        <div class="coupon-card__actions">
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm stu-act-detail__copy"
                                                data-copy-target="barcode-code-<?= (int) ($b['id'] ?? 0) ?>"
                                                aria-label="คัดลอกรหัส <?= esc($code) ?>"
                                            >คัดลอกรหัส</button>
                                        </div>
                                    </div>
                                    <?php if (! empty($b['claimed_at'])): ?>
                                        <div class="coupon-card__footer">
                                            <span>รับเมื่อ <?= esc($b['claimed_at']) ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>

        <?php elseif ($st === 'ready_claim'): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--action">
                <p class="stu-act-detail__notice-title">พร้อมรับรหัส</p>
                <p>ระบบจะจับคู่รหัสจากกองให้คุณหนึ่งรายการทันทีเมื่อกดปุ่มด้านล่าง</p>
            </div>
            <form method="post" action="<?= base_url('student/barcodes/claim-from-event/' . $eid) ?>" class="stu-act-detail__form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary stu-act-detail__submit">รับรหัสของฉัน</button>
            </form>

        <?php elseif ($st === 'confirm_receipt' && $firstUnclaimed > 0): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--action">
                <p class="stu-act-detail__notice-title">รอยืนยันการรับรหัส</p>
                <p>กดยืนยันเพื่อดูรหัสที่ระบบจับคู่ไว้ให้คุณ</p>
            </div>
            <form method="post" action="<?= base_url('student/barcodes/claim/' . $firstUnclaimed) ?>" class="stu-act-detail__form">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-primary stu-act-detail__submit">ยืนยันและดูรหัส</button>
            </form>

        <?php elseif ($st === 'wait_pool'): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--warn" role="status">
                <p class="stu-act-detail__notice-title">รอรหัสว่าง</p>
                <p>คุณมีสิทธิ์แล้ว แต่ขณะนี้รหัสในกองหมด — ลองกลับมาใหม่ภายหลัง หรือแจ้งผู้จัดกิจกรรม</p>
            </div>

        <?php elseif ($st === 'event_closed'): ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--muted" role="status">
                <p class="stu-act-detail__notice-title">กิจกรรมปิดรับแล้ว</p>
                <p>ไม่สามารถรับรหัสเพิ่มได้ หากควรได้รับสิทธิ์ กรุณาติดต่อผู้จัด</p>
            </div>

        <?php else: ?>
            <div class="stu-act-detail__notice stu-act-detail__notice--muted" role="status">
                <p>ไม่สามารถแสดงสถานะได้ — กลับไปที่ <a href="<?= base_url('student/barcodes') ?>">รายการกิจกรรม</a></p>
            </div>
        <?php endif; ?>
    </section>
</div>

<style>
.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border: 0;
}
.stu-act__badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.2rem 0.55rem;
    border-radius: 999px;
    white-space: nowrap;
}
.stu-act__badge--action { background: #ccfbf1; color: #115e59; }
.stu-act__badge--success { background: #dcfce7; color: #166534; }
.stu-act__badge--warn { background: #fef3c7; color: #92400e; }
.stu-act__badge--muted { background: var(--color-gray-100); color: var(--color-gray-600); }
.stu-act-detail__crumb {
    font-size: 0.875rem;
    margin-bottom: 1rem;
    color: var(--color-gray-500);
}
.stu-act-detail__crumb a {
    color: #0f766e;
    text-decoration: none;
}
.stu-act-detail__crumb a:hover { text-decoration: underline; }
.stu-act-detail__crumb a:focus-visible {
    outline: 3px solid #0d9488;
    outline-offset: 2px;
}
.stu-act-detail__head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.stu-act-detail__title {
    margin: 0 0 0.35rem;
    font-size: 1.375rem;
    font-weight: 700;
    text-wrap: balance;
}
.stu-act-detail__meta {
    margin: 0;
    font-size: 0.9375rem;
    color: var(--color-gray-500);
}
.stu-act-detail__status {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.04em;
}
.stu-act-detail__desc {
    color: var(--color-gray-700);
    font-size: 0.9375rem;
    line-height: 1.55;
    margin-bottom: 1.25rem;
}
.stu-act-detail__panel {
    background: var(--color-white);
    border: 1px solid var(--color-gray-200);
    border-radius: 14px;
    padding: 1.35rem 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}
.stu-act-detail__notice {
    border-radius: 10px;
    padding: 1rem 1.1rem;
    margin-bottom: 1rem;
    line-height: 1.5;
    font-size: 0.9375rem;
}
.stu-act-detail__notice-title {
    margin: 0 0 0.35rem;
    font-weight: 700;
    font-size: 1.05rem;
}
.stu-act-detail__notice p { margin: 0; }
.stu-act-detail__notice-sub {
    margin-top: 0.35rem !important;
    font-size: 0.875rem;
    color: var(--color-gray-600);
}
.stu-act-detail__notice--action {
    background: #f0fdfa;
    border: 1px solid #99f6e4;
    color: #134e4a;
}
.stu-act-detail__notice--success {
    background: #ecfdf5;
    border: 1px solid #86efac;
    color: #14532d;
}
.stu-act-detail__notice--warn {
    background: #fffbeb;
    border: 1px solid #fde68a;
    color: #78350f;
}
.stu-act-detail__notice--muted {
    background: var(--color-gray-50);
    border: 1px solid var(--color-gray-200);
    color: var(--color-gray-700);
}
.stu-act-detail__notice a { color: #0f766e; font-weight: 600; }
.stu-act-detail__form { margin: 0; }
.stu-act-detail__submit {
    width: 100%;
    min-height: 3rem;
    font-size: 1.0625rem;
    font-weight: 600;
}
.stu-act-detail__codes {
    list-style: none;
    padding: 0;
    margin: 0;
}
.stu-act-detail__code-row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.65rem;
    padding: 0.85rem 0;
    border-top: 1px solid var(--color-gray-100);
}
.stu-act-detail__code-row:first-child { border-top: 0; padding-top: 0; }
.stu-act-detail__code {
    font-size: 1.25rem;
    font-family: ui-monospace, monospace;
    font-variant-numeric: tabular-nums;
    padding: 0.35rem 0.65rem;
    background: var(--color-gray-50);
    border-radius: 8px;
    flex: 1;
    min-width: 0;
    word-break: break-all;
}
.stu-act-detail__copy { touch-action: manipulation; }
.stu-act-detail__copy:focus-visible {
    outline: 3px solid #0d9488;
    outline-offset: 2px;
}
.stu-act-detail__code-time {
    width: 100%;
    font-size: 0.75rem;
    color: var(--color-gray-500);
}
@media (min-width: 480px) {
    .stu-act-detail__code-time { width: auto; margin-left: auto; }
}

/* Coupon Card Styling */
.coupon-card {
    perspective: 1000px;
    width: 100%;
    max-width: 420px;
    margin: 0.5rem 0;
}
.coupon-card__inner {
    position: relative;
    width: 100%;
    height: 100px;
    transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    transform-style: preserve-3d;
}
.coupon-card.is-flipped .coupon-card__inner {
    transform: rotateY(180deg);
}
.coupon-card__front, .coupon-card__back {
    position: absolute;
    width: 100%;
    height: 100%;
    -webkit-backface-visibility: hidden;
    backface-visibility: hidden;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    display: flex;
    align-items: center;
    border: 1.5px dashed #99f6e4;
    box-sizing: border-box;
}

/* Front side styling (Coupon) */
.coupon-card__front {
    background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
    color: #0f766e;
    cursor: pointer;
    justify-content: space-between;
    padding: 0 1.25rem;
    transition: box-shadow 0.3s, transform 0.2s;
    overflow: hidden;
}
.coupon-card__front:hover {
    box-shadow: 0 6px 16px rgba(13, 148, 136, 0.15);
    transform: translateY(-2px);
}
.coupon-card__front:focus-visible {
    outline: 3px solid #0d9488;
    outline-offset: 2px;
}
.coupon-card__front:active {
    transform: translateY(0);
}

/* Side cutouts for ticket style */
.coupon-card__front::before, .coupon-card__front::after,
.coupon-card__back::before, .coupon-card__back::after {
    content: '';
    position: absolute;
    top: 50%;
    width: 16px;
    height: 16px;
    background: var(--color-white, #fff);
    border-radius: 50%;
    transform: translateY(-50%);
    z-index: 10;
    box-sizing: border-box;
}
.coupon-card__front::before, .coupon-card__back::before {
    left: -9px;
    border-right: 1.5px dashed #99f6e4;
}
.coupon-card__front::after, .coupon-card__back::after {
    right: -9px;
    border-left: 1.5px dashed #99f6e4;
}
.coupon-card__back::before {
    border-right-style: solid;
    border-right-color: #cbd5e1;
}
.coupon-card__back::after {
    border-left-style: solid;
    border-left-color: #cbd5e1;
}

.coupon-card__ticket-pattern {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    opacity: 0.04;
    background-image: radial-gradient(circle at 100% 150%, #000 24%, white 24%, white 28%, #000 28%, #000 36%, white 36%, white 40%, transparent 40%, transparent);
    background-size: 16px 16px;
    pointer-events: none;
}
.coupon-card__content {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    text-align: left;
}
.coupon-card__icon {
    font-size: 2rem;
    user-select: none;
    animation: coupon-pulse 2.5s infinite ease-in-out;
}
@keyframes coupon-pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
.coupon-card__text {
    display: flex;
    flex-direction: column;
}
.coupon-card__label {
    font-weight: 700;
    font-size: 1rem;
    color: #115e59;
}
.coupon-card__hint {
    font-size: 0.8rem;
    color: #0d9488;
    margin-top: 0.15rem;
}
.coupon-card__reveal-badge {
    background: #0d9488;
    color: #fff;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    font-weight: 600;
    font-size: 0.8125rem;
    box-shadow: 0 2px 4px rgba(13, 148, 136, 0.15);
    transition: background-color 0.2s;
    white-space: nowrap;
}
.coupon-card__front:hover .coupon-card__reveal-badge {
    background: #0f766e;
}

/* Back side styling (Revealed code) */
.coupon-card__back {
    background: var(--color-white, #fff);
    transform: rotateY(180deg);
    flex-direction: column;
    justify-content: center;
    padding: 0.75rem 1.25rem;
    border: 1px solid #cbd5e1;
}
.coupon-card__code-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 0.75rem;
}
.coupon-card__back .stu-act-detail__code {
    flex: 1;
    font-size: 1.2rem;
    font-weight: 700;
    text-align: center;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    letter-spacing: 0.05em;
    padding: 0.45rem 0.75rem;
    margin: 0;
}
.coupon-card__actions {
    display: flex;
    align-items: center;
}
.coupon-card__actions .stu-act-detail__copy {
    white-space: nowrap;
    padding: 0.45rem 0.85rem;
    font-weight: 600;
}
.coupon-card__footer {
    font-size: 0.7rem;
    color: var(--color-gray-400, #94a3b8);
    margin-top: 0.35rem;
    text-align: left;
    width: 100%;
    padding-left: 0.25rem;
}
</style>
<script>
window.revealCoupon = function(id) {
    var card = document.getElementById('coupon-card-' + id);
    if (card) {
        card.classList.add('is-flipped');
    }
};

(function() {
    document.querySelectorAll('.coupon-card__front').forEach(function(el) {
        el.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                el.click();
            }
        });
    });

    document.querySelectorAll('.stu-act-detail__copy').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = btn.getAttribute('data-copy-target');
            var el = id ? document.getElementById(id) : null;
            if (!el) return;
            var text = el.textContent || '';
            function done(ok) {
                var prev = btn.textContent;
                btn.textContent = ok ? 'คัดลอกแล้ว' : 'คัดลอกไม่ได้';
                setTimeout(function() { btn.textContent = prev; }, 2000);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() { done(true); }).catch(function() { done(false); });
            } else {
                done(false);
            }
        });
    });
})();
</script>
<?= $this->endSection() ?>

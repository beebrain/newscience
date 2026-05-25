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
                    <li class="stu-act-detail__code-row">
                        <code class="stu-act-detail__code" id="barcode-code-<?= (int) ($b['id'] ?? 0) ?>"><?= esc($code) ?></code>
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm stu-act-detail__copy"
                            data-copy-target="barcode-code-<?= (int) ($b['id'] ?? 0) ?>"
                            aria-label="คัดลอกรหัส <?= esc($code) ?>"
                        >คัดลอก</button>
                        <?php if (! empty($b['claimed_at'])): ?>
                            <span class="stu-act-detail__code-time">รับเมื่อ <?= esc($b['claimed_at']) ?></span>
                        <?php endif; ?>
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
</style>
<script>
(function() {
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

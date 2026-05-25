<?= $this->extend('student/layouts/portal_layout') ?>

<?= $this->section('content') ?>
<?php include __DIR__ . '/../partials/activity_ui.php'; ?>
<?php
$stateMeta = student_activity_state_meta();
$filter    = $filter ?? 'all';
$counts    = $counts ?? ['all' => 0, 'action' => 0, 'done' => 0, 'other' => 0];
$filters   = [
    'all'    => ['label' => 'ทั้งหมด', 'count' => $counts['all'] ?? 0],
    'action' => ['label' => 'ต้องดำเนินการ', 'count' => $counts['action'] ?? 0],
    'done'   => ['label' => 'รับรหัสแล้ว', 'count' => $counts['done'] ?? 0],
    'other'  => ['label' => 'อื่นๆ', 'count' => $counts['other'] ?? 0],
];
?>
<div class="stu-act">
    <header class="stu-act__head">
        <div>
            <h1 class="stu-act__title">กิจกรรมของฉัน</h1>
            <p class="stu-act__lead">กรอกรหัสจากผู้จัดเพื่อเข้าร่วม หรือเลือกกิจกรรมด้านล่างเพื่อรับรหัส/บาร์โค้ด</p>
        </div>
        <a href="<?= base_url('student') ?>" class="btn btn-secondary stu-act__back">กลับ Portal</a>
    </header>

    <section class="stu-act__join" aria-labelledby="join-code-heading">
        <h2 id="join-code-heading" class="stu-act__join-title">มีรหัสเข้าร่วมกิจกรรม?</h2>
        <form method="post" action="<?= base_url('student/barcodes/join') ?>" class="stu-act__join-form">
            <?= csrf_field() ?>
            <div class="stu-act__join-row">
                <label for="join_code" class="stu-act__join-label">รหัสเข้าร่วม</label>
                <input
                    type="text"
                    id="join_code"
                    name="join_code"
                    class="form-control stu-act__join-input"
                    value="<?= esc(old('join_code', '')) ?>"
                    placeholder="เช่น SCI-OPEN-2026…"
                    autocomplete="off"
                    spellcheck="false"
                    autocapitalize="characters"
                    maxlength="32"
                    inputmode="text"
                    required
                >
                <button type="submit" class="btn btn-primary stu-act__join-btn">เข้าร่วม</button>
            </div>
            <p class="stu-act__join-hint">ใช้ตัวพิมพ์ใหญ่ตามที่ผู้จัดแจ้ง — ไม่ต้องเว้นวรรค</p>
        </form>
    </section>

    <?php if (empty($portal_events)): ?>
        <div class="stu-act__empty" role="status">
            <p>ยังไม่มีกิจกรรมที่เปิดให้เข้าร่วม — ถ้ามีรหัสจากผู้จัด ให้กรอกในช่องด้านบน</p>
        </div>
    <?php else: ?>
        <nav class="stu-act__filters" aria-label="กรองรายการกิจกรรม">
            <?php foreach ($filters as $key => $f): ?>
                <?php
                $isActive = $filter === $key;
                $href     = $key === 'all'
                    ? base_url('student/barcodes')
                    : base_url('student/barcodes?filter=' . $key);
                ?>
                <a
                    href="<?= esc($href) ?>"
                    class="stu-act__filter<?= $isActive ? ' is-active' : '' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>
                ><?= esc($f['label']) ?> <span class="stu-act__filter-count"><?= (int) $f['count'] ?></span></a>
            <?php endforeach; ?>
        </nav>

        <ul class="stu-act__list" role="list">
            <?php foreach ($portal_events as $row):
                $ev      = $row['event'];
                $st      = $row['state']['state'] ?? 'locked';
                $meta    = $stateMeta[$st] ?? ['label' => $st, 'hint' => '', 'tone' => 'muted'];
                $group   = $row['filter'] ?? 'other';
                if ($filter !== 'all' && $group !== $filter) {
                    continue;
                }
                $eid     = (int) ($ev['id'] ?? 0);
                $dateStr = student_activity_format_date($ev['event_date'] ?? null);
                $desc    = trim(strip_tags((string) ($ev['description'] ?? '')));
                if ($desc !== '' && function_exists('mb_strlen') && mb_strlen($desc, 'UTF-8') > 100) {
                    $desc = mb_substr($desc, 0, 100, 'UTF-8') . '…';
                } elseif ($desc !== '' && strlen($desc) > 100) {
                    $desc = substr($desc, 0, 97) . '…';
                }
                ?>
                <li class="stu-act__item" data-tone="<?= esc($meta['tone']) ?>">
                    <a href="<?= base_url('student/barcodes/event/' . $eid) ?>" class="stu-act__card">
                        <div class="stu-act__card-top">
                            <h3 class="stu-act__card-title"><?= esc($ev['title'] ?? 'กิจกรรม') ?></h3>
                            <span class="stu-act__badge stu-act__badge--<?= esc($meta['tone']) ?>"><?= esc($meta['label']) ?></span>
                        </div>
                        <?php if ($dateStr !== ''): ?>
                            <p class="stu-act__card-date">
                                <time datetime="<?= esc($ev['event_date'] ?? '') ?>"><?= esc($dateStr) ?></time>
                            </p>
                        <?php endif; ?>
                        <?php if ($desc !== ''): ?>
                            <p class="stu-act__card-desc"><?= esc($desc) ?></p>
                        <?php endif; ?>
                        <p class="stu-act__card-hint"><?= esc($meta['hint']) ?></p>
                        <span class="stu-act__card-cta">
                            <?php if ($st === 'opened'): ?>
                                ดูรหัสของฉัน
                            <?php elseif (in_array($st, ['ready_claim', 'confirm_receipt'], true)): ?>
                                รับรหัส
                            <?php else: ?>
                                ดูรายละเอียด
                            <?php endif; ?>
                            <span aria-hidden="true">→</span>
                        </span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php
        $visible = 0;
        foreach ($portal_events as $row) {
            if ($filter === 'all' || ($row['filter'] ?? '') === $filter) {
                $visible++;
            }
        }
        if ($visible === 0):
            ?>
            <div class="stu-act__empty" role="status">
                <p>ไม่มีกิจกรรมในหมวดนี้ — ลองเลือก “ทั้งหมด” หรือกรอกรหัสเข้าร่วม</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<style>
.stu-act { --act-action: #0d9488; --act-action-dark: #0f766e; }
.stu-act__head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.stu-act__title {
    margin: 0 0 0.35rem;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-gray-800);
    text-wrap: balance;
}
.stu-act__lead {
    margin: 0;
    max-width: 36rem;
    color: var(--color-gray-600);
    font-size: 0.9375rem;
    line-height: 1.5;
}
.stu-act__back { flex-shrink: 0; }
.stu-act__join {
    background: linear-gradient(135deg, #f0fdfa 0%, #fff 55%);
    border: 1px solid #99f6e4;
    border-radius: 14px;
    padding: 1.25rem 1.35rem;
    margin-bottom: 1.5rem;
}
.stu-act__join-title {
    margin: 0 0 0.75rem;
    font-size: 1.0625rem;
    font-weight: 600;
    color: #134e4a;
}
.stu-act__join-row {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
}
@media (min-width: 520px) {
    .stu-act__join-row {
        grid-template-columns: auto 1fr auto;
        align-items: end;
    }
    .stu-act__join-label { padding-bottom: 0.55rem; }
}
.stu-act__join-label {
    font-weight: 600;
    font-size: 0.875rem;
    color: var(--color-gray-700);
}
.stu-act__join-input {
    font-family: ui-monospace, monospace;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    min-height: 2.75rem;
}
.stu-act__join-input:focus-visible {
    outline: 3px solid var(--act-action);
    outline-offset: 2px;
}
.stu-act__join-btn { min-height: 2.75rem; white-space: nowrap; }
.stu-act__join-hint {
    margin: 0.65rem 0 0;
    font-size: 0.8125rem;
    color: var(--color-gray-500);
}
.stu-act__filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.stu-act__filter {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
    border: 1px solid var(--color-gray-200);
    background: var(--color-white);
    color: var(--color-gray-700);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    touch-action: manipulation;
}
.stu-act__filter:hover {
    border-color: var(--act-action);
    color: var(--act-action-dark);
}
.stu-act__filter:focus-visible {
    outline: 3px solid var(--act-action);
    outline-offset: 2px;
}
.stu-act__filter.is-active {
    background: var(--act-action);
    border-color: var(--act-action);
    color: #fff;
}
.stu-act__filter-count {
    font-variant-numeric: tabular-nums;
    font-size: 0.75rem;
    opacity: 0.9;
}
.stu-act__list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.stu-act__card {
    display: block;
    padding: 1.125rem 1.25rem;
    border-radius: 12px;
    border: 1px solid var(--color-gray-200);
    background: var(--color-white);
    text-decoration: none;
    color: inherit;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: border-color 0.2s, box-shadow 0.2s;
}
.stu-act__card:hover {
    border-color: var(--act-action);
    box-shadow: 0 6px 18px rgba(13, 148, 136, 0.12);
}
.stu-act__card:focus-visible {
    outline: 3px solid var(--act-action);
    outline-offset: 2px;
}
.stu-act__card-top {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.5rem;
    margin-bottom: 0.35rem;
}
.stu-act__card-title {
    margin: 0;
    font-size: 1.0625rem;
    font-weight: 600;
    flex: 1;
    min-width: 0;
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
.stu-act__card-date {
    margin: 0 0 0.35rem;
    font-size: 0.875rem;
    color: var(--color-gray-500);
}
.stu-act__card-desc {
    margin: 0 0 0.35rem;
    font-size: 0.8125rem;
    color: var(--color-gray-600);
    line-height: 1.45;
}
.stu-act__card-hint {
    margin: 0 0 0.5rem;
    font-size: 0.8125rem;
    color: var(--color-gray-500);
}
.stu-act__card-cta {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--act-action-dark);
}
.stu-act__empty {
    text-align: center;
    padding: 2rem 1rem;
    color: var(--color-gray-600);
    border: 1px dashed var(--color-gray-300);
    border-radius: 12px;
    background: var(--color-gray-50);
}
@media (prefers-reduced-motion: reduce) {
    .stu-act__card { transition: none; }
}
</style>
<?= $this->endSection() ?>

<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$q = static function (?string $y, ?string $df, ?string $dt, string $sc): string {
    $p = ['scope' => $sc];
    if ($y !== null && $y !== '') {
        $p['year'] = $y;
    }
    if ($df !== null && $df !== '') {
        $p['date_from'] = $df;
    }
    if ($dt !== null && $dt !== '') {
        $p['date_to'] = $dt;
    }

    return http_build_query($p);
};
$reportBack = base_url('admin/academic-services/report?' . $q($year_filter, $date_from, $date_to, $scope));
$treeUrl    = base_url('admin/academic-services/report/person-tree');
$peopleResetUrl = base_url('admin/academic-services/report/people?scope=filtered');
$peopleCount    = count($people ?? []);
?>

<div class="as-rp">
    <header class="as-rp__hero">
        <div class="as-rp__hero-text">
            <p class="as-rp__eyebrow">รายงานบริการวิชาการ</p>
            <h1 class="as-rp__title"><?= esc($page_title ?? 'บุคคลที่เกี่ยวข้อง') ?></h1>
            <p class="as-rp__lead">รายชื่อไม่ซ้ำตามบัญชีในระบบ — คลิกชื่อเพื่อดูแผนภาพการมีส่วนร่วมในรายการบริการ</p>
            <ul class="as-rp__chips" aria-label="ตัวกรองที่ใช้อยู่">
                <li class="as-rp__chip"><?= esc($scope_label ?? '') ?></li>
                <li class="as-rp__chip"><?= ! empty($year_filter) ? 'ปี ' . esc($year_filter) : 'ทุกปีการศึกษา' ?></li>
                <li class="as-rp__chip">
                    <?php if (! empty($date_from) || ! empty($date_to)): ?>
                        วันที่ <?= esc($date_from ?: '…') ?> — <?= esc($date_to ?: '…') ?>
                    <?php else: ?>
                        ทุกช่วงวันที่บริการ
                    <?php endif; ?>
                </li>
            </ul>
        </div>
        <div class="as-rp__hero-actions">
            <a href="<?= esc($reportBack) ?>" class="btn btn-secondary as-rp__btn-ghost">← สรุปรายงาน</a>
            <a href="<?= esc(base_url('admin/academic-services')) ?>" class="btn btn-light as-rp__btn-ghost">จัดการรายการ</a>
        </div>
    </header>

    <div class="as-rp__layout">
        <aside class="as-rp__aside" aria-labelledby="as-rp-filters-heading">
            <section class="as-rp__card as-rp__card--filters">
                <div class="as-rp__card-head">
                    <h2 id="as-rp-filters-heading" class="as-rp__card-title">ตัวกรอง</h2>
                    <a href="<?= esc($peopleResetUrl) ?>" class="as-rp__link-reset">รีเซ็ต</a>
                </div>
                <p class="as-rp__card-desc">ส่งแบบ GET — ใช้ชุดเดียวกับหน้าสรุปรายงานหลัก</p>
                <form method="get" action="<?= esc(base_url('admin/academic-services/report/people')) ?>" class="as-rp__filters">
                    <div class="form-group as-rp__field">
                        <label for="pf_scope" class="form-label">ขอบเขตข้อมูล</label>
                        <select name="scope" id="pf_scope" class="form-control as-rp__control">
                            <option value="filtered" <?= ($scope ?? '') === 'filtered' ? 'selected' : '' ?>>ทุกรายการที่ผ่านตัวกรอง</option>
                            <option value="person_responsible" <?= ($scope ?? '') === 'person_responsible' ? 'selected' : '' ?>>เฉพาะผู้รับผิดชอบระดับบุคคล</option>
                        </select>
                    </div>
                    <div class="form-group as-rp__field">
                        <label for="pf_year" class="form-label">ปีการศึกษา (พ.ศ.)</label>
                        <select name="year" id="pf_year" class="form-control as-rp__control">
                            <option value="">ทุกปี</option>
                            <?php foreach ($years ?? [] as $y): ?>
                                <option value="<?= esc($y) ?>" <?= ($year_filter ?? '') === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group as-rp__field as-rp__field--span">
                        <span class="form-label">ช่วงวันที่บริการ</span>
                        <div class="as-rp__date-row">
                            <div>
                                <label for="pf_from" class="as-rp__sublabel">ตั้งแต่</label>
                                <input type="date" name="date_from" id="pf_from" class="form-control as-rp__control" value="<?= esc($date_from ?? '') ?>">
                            </div>
                            <div>
                                <label for="pf_to" class="as-rp__sublabel">ถึง</label>
                                <input type="date" name="date_to" id="pf_to" class="form-control as-rp__control" value="<?= esc($date_to ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="as-rp__filter-actions">
                        <button type="submit" class="btn btn-primary w-100 as-rp__submit">ใช้ตัวกรอง</button>
                    </div>
                </form>
            </section>
        </aside>

        <main class="as-rp__main">
            <section class="as-rp__card as-rp__card--kpi" aria-label="จำนวนบุคคลที่พบ">
                <div class="as-rp__kpi">
                    <span class="as-rp__kpi-label">จำนวนบุคคล (ไม่ซ้ำ)</span>
                    <span class="as-rp__kpi-value" id="asRpTotalCount"><?= number_format($peopleCount) ?></span>
                    <span class="as-rp__kpi-hint">ตามขอบเขตและช่วงวันที่ด้านซ้าย</span>
                </div>
            </section>

            <section class="as-rp__card as-rp__card--list" aria-labelledby="as-rp-list-heading">
                <div class="as-rp__list-head">
                    <div>
                        <h2 id="as-rp-list-heading" class="as-rp__card-title">รายชื่อบุคคล</h2>
                        <p class="as-rp__card-desc as-rp__card-desc--tight">
                            แสดง <strong id="asRpVisibleCount"><?= number_format($peopleCount) ?></strong>
                            จาก <strong id="asRpBaseTotal"><?= number_format($peopleCount) ?></strong> คน · ค้นหาในตารางได้ทันทีโดยไม่ต้องโหลดหน้าใหม่
                        </p>
                    </div>
                    <div class="as-rp__search-wrap">
                        <label for="asRpPeopleSearch" class="as-rp__search-label">ค้นหาในหน้านี้</label>
                        <input type="search" id="asRpPeopleSearch" class="form-control as-rp__search" placeholder="ชื่อหรืออีเมล…" autocomplete="off" <?= $peopleCount === 0 ? 'disabled' : '' ?>>
                    </div>
                </div>
                <p id="asRpSearchEmpty" class="as-rp__search-empty" hidden>ไม่พบรายการที่ตรงกับคำค้น — ลองคำอื่นหรือล้างช่องค้นหา</p>
                <div class="as-rp__table-scroll">
                    <table class="table as-rp__table">
                        <thead>
                            <tr>
                                <th scope="col">ชื่อ</th>
                                <th scope="col">อีเมล</th>
                                <th scope="col" class="as-rp__col-num">จำนวนรายการ</th>
                            </tr>
                        </thead>
                        <tbody class="as-rp__tbody" id="asRpPeopleTbody">
                            <?php if (empty($people)): ?>
                                <tr><td colspan="3" class="as-rp__empty">ไม่มีข้อมูลในช่วงที่กรอง</td></tr>
                            <?php else: ?>
                                <?php foreach ($people as $p): ?>
                                    <?php
                                    $searchHay = trim(($p['person_name'] ?? '') . ' ' . ($p['email'] ?? ''));
                                    $searchHay = preg_replace('/\s+/u', ' ', $searchHay) ?? $searchHay;
                                    ?>
                                    <tr data-person-key="<?= esc($p['person_key'], 'attr') ?>" data-search="<?= esc($searchHay, 'attr') ?>">
                                        <td class="as-rp__td-name">
                                            <button type="button"
                                                    class="as-rp__name-btn js-person-tree"
                                                    data-person-key="<?= esc($p['person_key'], 'attr') ?>">
                                                <?= esc($p['person_name']) ?>
                                            </button>
                                        </td>
                                        <td class="as-rp__td-mail"><?= esc($p['email'] ?? '—') ?></td>
                                        <td class="as-rp__col-num">
                                            <span class="as-rp__pill"><?= number_format((int) ($p['service_count'] ?? 0)) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</div>

<dialog id="personTreeDialog" class="as-rp-dialog" aria-labelledby="personTreeTitle">
    <div class="as-rp-dialog__inner">
        <header class="as-rp-dialog__head">
            <h2 id="personTreeTitle" class="as-rp-dialog__title">การมีส่วนร่วม</h2>
            <button type="button" class="as-rp-dialog__close" id="personTreeClose" aria-label="ปิด">&times;</button>
        </header>
        <p id="personTreeSubtitle" class="as-rp-dialog__sub"></p>
        <p id="personTreeLoading" class="as-rp-dialog__loading">กำลังโหลด…</p>
        <p id="personTreeError" class="as-rp-dialog__err" hidden></p>
        <div id="personTreeSvgWrap" class="as-rp-dialog__svg-wrap" hidden>
            <p class="as-rp-dialog__zoom-hint" id="personTreeZoomHint">ล้อเมาส์หรือ pinch เพื่อซูม · ลากเพื่อเลื่อน · ดับเบิลคลิกเพื่อรีเซ็ต</p>
            <svg id="personTreeSvg" role="img" aria-label="แผนภาพ tree การมีส่วนร่วม"></svg>
        </div>
        <p class="as-rp-dialog__credit">
            Tree layout อ้างอิงแนวทาง <a href="https://observablehq.com/@d3/tree-component" target="_blank" rel="noopener noreferrer">D3 tree component</a> (Mike Bostock / Observable)
        </p>
    </div>
</dialog>

<style>
.as-rp {
    --as-rp-radius: 14px;
    --as-rp-radius-sm: 10px;
    --as-rp-border: var(--color-gray-200, #e5e7eb);
    --as-rp-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    max-width: 1180px;
    margin: 0 auto;
    padding-bottom: 2rem;
}

.as-rp__hero {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.25rem;
    margin-bottom: 1.5rem;
    padding: 1.35rem 1.5rem;
    border-radius: var(--as-rp-radius);
    border: 1px solid var(--as-rp-border);
    background: linear-gradient(135deg, var(--color-white, #fff) 0%, var(--color-gray-50, #f8fafc) 55%, #eef2ff 100%);
    box-shadow: var(--as-rp-shadow);
}

.as-rp__hero-text { min-width: min(100%, 28rem); flex: 1 1 18rem; }

.as-rp__eyebrow {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--color-gray-500, #64748b);
    margin: 0 0 0.35rem;
}

.as-rp__title {
    font-size: clamp(1.25rem, 2.5vw, 1.6rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    margin: 0 0 0.5rem;
    color: var(--color-gray-900, #0f172a);
    line-height: 1.2;
}

.as-rp__lead {
    margin: 0 0 0.85rem;
    font-size: 0.9375rem;
    line-height: 1.55;
    color: var(--color-gray-600, #475569);
    max-width: 42rem;
}

.as-rp__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
    list-style: none;
    margin: 0;
    padding: 0;
}

.as-rp__chip {
    display: inline-flex;
    align-items: center;
    padding: 0.28rem 0.65rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--color-gray-700, #334155);
    background: rgba(255, 255, 255, 0.85);
    border: 1px solid var(--as-rp-border);
    box-shadow: 0 1px 0 rgba(255, 255, 255, 0.8) inset;
}

.as-rp__hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    align-items: center;
    flex-shrink: 0;
}

.as-rp__btn-ghost { border-radius: var(--as-rp-radius-sm) !important; }

.as-rp__layout {
    display: grid;
    gap: 1.25rem;
    align-items: start;
}

@media (min-width: 992px) {
    .as-rp__layout {
        grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
    }
    .as-rp__aside {
        position: sticky;
        top: 0.75rem;
    }
}

.as-rp__card {
    background: var(--color-white, #fff);
    border: 1px solid var(--as-rp-border);
    border-radius: var(--as-rp-radius);
    padding: 1.15rem 1.25rem;
    box-shadow: var(--as-rp-shadow);
}

.as-rp__card--filters .as-rp__filters { margin-top: 0.35rem; }

.as-rp__card-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}

.as-rp__card-title {
    font-size: 1rem;
    font-weight: 700;
    margin: 0;
    color: var(--color-gray-900, #0f172a);
}

.as-rp__link-reset {
    font-size: 0.8125rem;
    font-weight: 600;
    color: #4f46e5;
    text-decoration: none;
    white-space: nowrap;
}
.as-rp__link-reset:hover { text-decoration: underline; color: #4338ca; }

.as-rp__card-desc {
    margin: 0 0 0.85rem;
    font-size: 0.8125rem;
    color: var(--color-gray-600, #64748b);
    line-height: 1.45;
}

.as-rp__card-desc--tight { margin: 0.15rem 0 0; }

.as-rp__filters {
    display: flex;
    flex-direction: column;
    gap: 0.85rem;
}

.as-rp__field { margin-bottom: 0 !important; }

.as-rp__field--span .as-rp__date-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.65rem;
    margin-top: 0.35rem;
}

.as-rp__sublabel {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-gray-500, #64748b);
    margin-bottom: 0.25rem;
}

.as-rp__control {
    border-radius: var(--as-rp-radius-sm) !important;
    border-color: var(--as-rp-border) !important;
}
.as-rp__control:focus {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.2) !important;
}

.as-rp__filter-actions { margin-top: 0.25rem; }

.as-rp__submit { font-weight: 600; border-radius: var(--as-rp-radius-sm) !important; }

.as-rp__main { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }

.as-rp__card--kpi {
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
}

.as-rp__kpi {
    display: grid;
    gap: 0.15rem;
    align-content: start;
}

.as-rp__kpi-label {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-gray-500, #64748b);
}

.as-rp__kpi-value {
    font-size: clamp(1.75rem, 4vw, 2.25rem);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
    letter-spacing: -0.03em;
    color: #312e81;
    line-height: 1.1;
}

.as-rp__kpi-hint {
    font-size: 0.8125rem;
    color: var(--color-gray-600, #64748b);
}

.as-rp__card--list { padding-bottom: 0.65rem; }

.as-rp__list-head {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 0.65rem;
    padding: 0 0.05rem;
}

.as-rp__search-wrap {
    flex: 1 1 14rem;
    max-width: 22rem;
    min-width: min(100%, 12rem);
}

.as-rp__search-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--color-gray-500, #64748b);
    margin-bottom: 0.3rem;
}

.as-rp__search {
    border-radius: var(--as-rp-radius-sm) !important;
    border-color: var(--as-rp-border) !important;
}
.as-rp__search:focus {
    border-color: #818cf8 !important;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.18) !important;
}

.as-rp__search-empty {
    margin: 0 0 0.65rem;
    padding: 0.65rem 0.85rem;
    font-size: 0.875rem;
    color: #92400e;
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-radius: var(--as-rp-radius-sm);
}

.as-rp__table-scroll {
    overflow: auto;
    max-height: min(62vh, 560px);
    margin: 0 -0.15rem;
    padding: 0 0.15rem 0.15rem;
    border-radius: var(--as-rp-radius-sm);
    border: 1px solid var(--as-rp-border);
    background: var(--color-gray-50, #f8fafc);
}

.as-rp__table {
    margin: 0 !important;
    font-size: 0.9375rem;
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
    min-width: 520px;
    background: var(--color-white, #fff);
}

.as-rp__table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background: #f1f5f9;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-gray-600, #475569);
    border-bottom: 2px solid var(--as-rp-border) !important;
    box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);
    padding: 0.65rem 0.85rem !important;
    white-space: nowrap;
}

.as-rp__table tbody td {
    padding: 0.65rem 0.85rem !important;
    vertical-align: middle;
    border-color: #f1f5f9 !important;
}

.as-rp__table tbody tr[data-search]:hover {
    background: #f8fafc;
}

.as-rp__td-name { font-weight: 500; }

.as-rp__td-mail {
    font-size: 0.875rem;
    color: var(--color-gray-600, #64748b);
    word-break: break-word;
}

.as-rp__col-num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    width: 7.5rem;
    white-space: nowrap;
}

.as-rp__pill {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 8px;
    font-size: 0.8125rem;
    font-weight: 700;
    color: #1e3a5f;
    background: #e0f2fe;
    border: 1px solid #bae6fd;
}

.as-rp__name-btn {
    border: none;
    background: none;
    padding: 0.15rem 0;
    margin: 0;
    font: inherit;
    font-weight: 600;
    color: #2563eb;
    cursor: pointer;
    text-align: left;
    border-radius: 6px;
    text-decoration: none;
    box-shadow: none;
}
.as-rp__name-btn:hover {
    color: #1d4ed8;
    text-decoration: underline;
    text-underline-offset: 3px;
}
.as-rp__name-btn:focus-visible {
    outline: 2px solid #6366f1;
    outline-offset: 2px;
}

.as-rp__empty {
    text-align: center;
    color: var(--color-gray-600, #64748b);
    padding: 2.5rem 1rem !important;
    font-size: 0.9375rem;
}

@media (prefers-reduced-motion: reduce) {
    .as-rp__name-btn,
    .as-rp__link-reset { transition: none; }
}

.as-rp-dialog {
    border: none;
    border-radius: 14px;
    padding: 0;
    max-width: min(960px, 98vw);
    width: min(960px, 98vw);
    max-height: min(92vh, 900px);
    margin: 0;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}
.as-rp-dialog::backdrop { background: rgba(15, 23, 42, 0.45); }
.as-rp-dialog__inner { padding: 1.25rem 1.35rem; max-height: 90vh; overflow: auto; display: flex; flex-direction: column; gap: 0.5rem; }
.as-rp-dialog__head { display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; }
.as-rp-dialog__title { margin: 0; font-size: 1.1rem; font-weight: 700; }
.as-rp-dialog__close { border: none; background: var(--color-gray-100); width: 2.25rem; height: 2.25rem; border-radius: 8px; font-size: 1.35rem; line-height: 1; cursor: pointer; }
.as-rp-dialog__sub { margin: 0; font-size: 0.8125rem; color: var(--color-gray-600); }
.as-rp-dialog__loading { margin: 0; font-size: 0.875rem; }
.as-rp-dialog__err { margin: 0; color: #b91c1c; font-size: 0.875rem; }
.as-rp-dialog__svg-wrap { overflow: hidden; max-height: 58vh; min-height: 240px; border: 1px solid var(--color-gray-200); border-radius: 8px; background: #fafafa; touch-action: none; display: flex; flex-direction: column; }
.as-rp-dialog__zoom-hint { margin: 0; padding: 0.4rem 0.65rem; font-size: 0.7rem; color: var(--color-gray-500); background: var(--color-gray-50, #f8fafc); border-bottom: 1px solid var(--color-gray-200); flex-shrink: 0; }
#personTreeSvg { display: block; flex: 1 1 auto; min-height: 0; width: 100%; height: 100%; max-height: calc(58vh - 2.25rem); font-family: var(--font-primary, system-ui, sans-serif); cursor: grab; }
#personTreeSvg:active { cursor: grabbing; }
.as-rp-dialog__credit { margin: 0.5rem 0 0; font-size: 0.7rem; color: var(--color-gray-500); }
.as-rp-dialog__credit a { color: inherit; }
.person-tree-link { fill: none; stroke: #94a3b8; stroke-width: 1.5px; stroke-opacity: 0.85; }
.person-tree-node circle { fill: #fff; stroke: #64748b; stroke-width: 1.5px; }
.person-tree-node--internal circle { fill: #e2e8f0; stroke: #475569; }
.person-tree-node text { font-size: 12px; dominant-baseline: middle; paint-order: stroke fill; stroke: #fff; stroke-width: 3px; fill: #0f172a; }
.person-tree-node text .person-tree-line2 { fill: #334155; font-size: 11px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/d3@7"></script>
<script>
(function() {
    var treeUrl = <?= json_encode($treeUrl) ?>;
    var scope = <?= json_encode($scope ?? 'filtered') ?>;
    var year = <?= json_encode($year_filter) ?>;
    var dateFrom = <?= json_encode($date_from ?? '') ?>;
    var dateTo = <?= json_encode($date_to ?? '') ?>;

    function filterQs() {
        var p = ['scope=' + encodeURIComponent(scope)];
        if (year) p.push('year=' + encodeURIComponent(year));
        if (dateFrom) p.push('date_from=' + encodeURIComponent(dateFrom));
        if (dateTo) p.push('date_to=' + encodeURIComponent(dateTo));
        return p.join('&');
    }

    var dialog = document.getElementById('personTreeDialog');
    var closeBtn = document.getElementById('personTreeClose');
    var loadingEl = document.getElementById('personTreeLoading');
    var errEl = document.getElementById('personTreeError');
    var wrapEl = document.getElementById('personTreeSvgWrap');
    var subEl = document.getElementById('personTreeSubtitle');

    function wrapNodeLabel(textSel, str, maxCharsPerLine, anchor) {
        var s = String(str || '');
        textSel.selectAll('tspan').remove();
        textSel.text(null);
        var x0 = textSel.attr('x');
        if (s.length <= maxCharsPerLine) {
            textSel.append('tspan').attr('x', x0).attr('dy', 0).text(s);
            return;
        }
        var cut = maxCharsPerLine;
        var i = cut;
        while (i > 24 && s[i] !== ' ' && s[i] !== '　') i--;
        if (i <= 24) i = cut;
        var a = s.slice(0, i).trim();
        var b = s.slice(i).trim();
        if (!b) {
            textSel.append('tspan').attr('x', x0).attr('dy', 0).text(s);
            return;
        }
        if (anchor === 'end') {
            textSel.append('tspan').attr('x', x0).attr('dy', '-0.55em').text(a);
            textSel.append('tspan').attr('class', 'person-tree-line2').attr('x', x0).attr('dy', '1.15em').text(b);
        } else {
            textSel.append('tspan').attr('x', x0).attr('dy', 0).text(a);
            textSel.append('tspan').attr('class', 'person-tree-line2').attr('x', x0).attr('dy', '1.15em').text(b);
        }
    }

    function maxLabelLength(rootNode) {
        var m = 0;
        rootNode.each(function(d) {
            var n = (d.data && d.data.name) ? String(d.data.name) : '';
            if (n.length > m) m = n.length;
        });
        return m;
    }

    function renderD3Tree(data) {
        var svg = d3.select('#personTreeSvg');
        var zoomHint = document.getElementById('personTreeZoomHint');
        function setZoomHint(on) {
            if (zoomHint) zoomHint.hidden = !on;
        }
        svg.on('.zoom', null);
        svg.on('dblclick', null);
        svg.selectAll('*').remove();
        if (!data || !data.name) {
            setZoomHint(false);
            return;
        }
        var root = d3.hierarchy(data);
        if (!root.children || root.children.length === 0) {
            setZoomHint(false);
            svg.attr('viewBox', '0 0 480 100').attr('width', '100%').attr('height', 100);
            svg.append('text').attr('x', 16).attr('y', 48).attr('fill', '#64748b').text('ไม่มีรายการบริการในช่วงที่กรอง');
            return;
        }
        setZoomHint(true);
        var mlen = maxLabelLength(root);
        var dx = Math.max(32, 22 + Math.min(root.height + 1, 8) * 2);
        var dy = Math.min(640, Math.max(300, 14 * Math.min(mlen, 42) + 120));
        var treeLayout = d3.tree().nodeSize([dx, dy]);
        treeLayout(root);
        var x0 = Infinity, x1 = -Infinity, y0 = Infinity, y1 = -Infinity;
        root.each(function(d) {
            if (d.x > x1) x1 = d.x;
            if (d.x < x0) x0 = d.x;
            if (d.y > y1) y1 = d.y;
            if (d.y < y0) y0 = d.y;
        });
        if (!isFinite(x0) || !isFinite(x1)) { x0 = 0; x1 = dx * 2; }
        if (!isFinite(y0) || !isFinite(y1)) { y0 = 0; y1 = dy; }
        var padR = Math.min(480, 100 + mlen * 8);
        var margin = { top: 40, right: padR, bottom: 40, left: Math.max(72, 24 + Math.min(mlen, 20) * 6) };
        var height = x1 - x0 + margin.top + margin.bottom + dx + 28;
        var width = y1 - y0 + margin.left + margin.right;
        svg.attr('viewBox', [0, 0, width, height])
            .attr('width', '100%')
            .attr('preserveAspectRatio', 'xMidYMid meet')
            .attr('height', Math.max(260, Math.min(height, 720)));
        var zoomRoot = svg.append('g').attr('class', 'person-tree-zoom-root');
        var g = zoomRoot.append('g')
            .attr('transform', 'translate(' + (margin.left - y0) + ',' + (margin.top - x0) + ')');
        g.selectAll('path.person-tree-link')
            .data(root.links())
            .join('path')
            .attr('class', 'person-tree-link')
            .attr('d', d3.linkHorizontal()
                .x(function(d) { return d.y; })
                .y(function(d) { return d.x; }));
        var node = g.selectAll('g.person-tree-node')
            .data(root.descendants())
            .join('g')
            .attr('class', function(d) {
                return 'person-tree-node' + (d.children ? ' person-tree-node--internal' : '');
            })
            .attr('transform', function(d) { return 'translate(' + d.y + ',' + d.x + ')'; });
        node.append('circle').attr('r', 5);
        node.each(function(d) {
            var gNode = d3.select(this);
            var label = (d.data && d.data.name) ? String(d.data.name) : '';
            var isRoot = d.depth === 0;
            var hasKids = !!d.children;
            var anchor = isRoot && hasKids ? 'end' : 'start';
            var xPos = isRoot && hasKids ? -14 : 14;
            var tx = gNode.append('text')
                .attr('dy', '0.35em')
                .attr('text-anchor', anchor)
                .attr('x', xPos);
            var maxLine = isRoot ? 32 : (hasKids ? 40 : 48);
            wrapNodeLabel(tx, label, maxLine, anchor);
            tx.append('title').text(label);
        });
        var zoom = d3.zoom()
            .scaleExtent([0.15, 6])
            .on('zoom', function(event) {
                zoomRoot.attr('transform', event.transform);
            });
        svg.call(zoom);
        svg.on('dblclick.zoom', null);
        svg.on('dblclick', function(event) {
            event.preventDefault();
            svg.transition().duration(180).call(zoom.transform, d3.zoomIdentity);
        });
    }

    function openTree(personKey, displayName) {
        if (!dialog || typeof d3 === 'undefined') return;
        if (subEl) subEl.textContent = displayName || '';
        if (loadingEl) loadingEl.hidden = false;
        if (errEl) { errEl.hidden = true; errEl.textContent = ''; }
        if (wrapEl) wrapEl.hidden = true;
        dialog.showModal();
        var url = treeUrl + '?person_key=' + encodeURIComponent(personKey) + '&' + filterQs();
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (loadingEl) loadingEl.hidden = true;
                if (!res || !res.success || !res.tree) throw new Error((res && res.message) ? res.message : 'โหลดไม่สำเร็จ');
                renderD3Tree(res.tree);
                if (wrapEl) wrapEl.hidden = false;
            })
            .catch(function(e) {
                if (loadingEl) loadingEl.hidden = true;
                if (errEl) { errEl.hidden = false; errEl.textContent = e.message || 'เกิดข้อผิดพลาด'; }
            });
    }

    document.querySelectorAll('.js-person-tree').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var k = btn.getAttribute('data-person-key');
            openTree(k, btn.textContent.trim());
        });
    });

    if (closeBtn && dialog) {
        closeBtn.addEventListener('click', function() { dialog.close(); });
    }

    (function initPeopleTableSearch() {
        var input = document.getElementById('asRpPeopleSearch');
        var tbody = document.getElementById('asRpPeopleTbody');
        var visibleEl = document.getElementById('asRpVisibleCount');
        var emptyEl = document.getElementById('asRpSearchEmpty');
        if (!input || !tbody) return;

        function norm(s) {
            return String(s || '').toLowerCase();
        }

        function apply() {
            var q = norm(input.value.trim());
            var rows = tbody.querySelectorAll('tr[data-search]');
            var n = 0;
            for (var i = 0; i < rows.length; i++) {
                var tr = rows[i];
                var hay = norm(tr.getAttribute('data-search'));
                var show = !q || hay.indexOf(q) !== -1;
                tr.hidden = !show;
                if (show) n++;
            }
            if (visibleEl) visibleEl.textContent = n.toLocaleString('th-TH');
            if (emptyEl) emptyEl.hidden = !q || n > 0;
        }

        input.addEventListener('input', apply);
        input.addEventListener('search', apply);
    })();
})();
</script>

<?= $this->endSection() ?>

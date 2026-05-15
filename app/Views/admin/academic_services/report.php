<?= $this->extend('admin/layouts/admin_layout') ?>

<?php
$tableTotal = 0;
if (! empty($table_rows)) {
    foreach ($table_rows as $r) {
        $tableTotal += (int) ($r['count'] ?? 0);
    }
}
?>

<?= $this->section('content') ?>

<div class="as-report">
    <header class="as-report__header">
        <div class="as-report__header-text">
            <p class="as-report__eyebrow">รายงาน</p>
            <h1 class="as-report__title">สรุปบริการวิชาการ</h1>
            <p class="as-report__lead">ดูจำนวนรายการตามมิติที่เลือก กรองตามปีการศึกษาและช่วงวันที่บริการ</p>
        </div>
        <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary as-report__back">← กลับไปจัดการรายการ</a>
    </header>

    <section class="as-report__kpis" aria-label="สรุปภาพรวม">
        <article class="as-report__kpi as-report__kpi--primary">
            <span class="as-report__kpi-label">จำนวนรายการทั้งหมด</span>
            <span class="as-report__kpi-value"><?= number_format($total) ?></span>
            <span class="as-report__kpi-hint">รายการบริการวิชาการในระบบ</span>
        </article>
        <article class="as-report__kpi as-report__kpi--clickable" id="kpiInvolvedCard" data-people-scope="filtered" tabindex="0" role="link" aria-label="เปิดหน้ารายชื่อบุคคลตามตัวกรอง">
            <span class="as-report__kpi-label">บุคลากรที่เกี่ยวข้อง <span class="as-report__kpi-badge">ไม่ซ้ำ</span></span>
            <span class="as-report__kpi-value"><?= number_format($distinct_participants) ?></span>
            <span class="as-report__kpi-hint">นับจาก uid ในระบบทั้งโครงการ — คลิกเพื่อไปหน้ารายชื่อบุคคล (ตามตัวกรอง) แล้วเปิด tree รายการมีส่วนร่วมต่อคน</span>
        </article>
    </section>

    <section class="as-report__panel as-report__panel--filters" aria-labelledby="as-report-filters-heading">
        <h2 id="as-report-filters-heading" class="as-report__panel-title">ตัวกรอง</h2>
        <p class="as-report__panel-desc">ใช้กับทั้งกราฟและตารางด้านล่าง</p>
        <div class="as-report__filter-grid">
            <div class="form-group as-report__field">
                <label for="dimension" class="form-label">มิติของข้อมูล</label>
                <select name="dimension" id="dimension" class="form-control">
                    <?php foreach ($dimension_options as $val => $label): ?>
                        <option value="<?= esc($val) ?>" <?= ($dimension ?? '') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group as-report__field">
                <label for="year" class="form-label">ปีการศึกษา (พ.ศ.)</label>
                <select name="year" id="year" class="form-control">
                    <option value="">ทุกปี</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= esc($y) ?>" <?= ($year_filter ?? '') === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group as-report__field">
                <label for="date_from" class="form-label">วันที่บริการ ตั้งแต่</label>
                <input type="date" name="date_from" id="date_from" class="form-control"
                       value="<?= esc($date_from ?? '') ?>">
            </div>
            <div class="form-group as-report__field">
                <label for="date_to" class="form-label">ถึงวันที่</label>
                <input type="date" name="date_to" id="date_to" class="form-control"
                       value="<?= esc($date_to ?? '') ?>">
            </div>
        </div>
    </section>

    <div class="as-report__main">
        <section class="as-report__panel as-report__panel--chart" aria-labelledby="as-report-chart-heading">
            <div class="as-report__panel-head">
                <h2 id="as-report-chart-heading" class="as-report__panel-title">กราฟ</h2>
                <span class="as-report__panel-tag"><?= esc($dimension_label ?? 'มิติ') ?></span>
            </div>
            <div class="as-report__chart-wrap">
                <canvas id="reportChart" aria-label="กราฟสรุปข้อมูลบริการวิชาการ"></canvas>
            </div>
        </section>

        <section class="as-report__panel as-report__panel--table" aria-labelledby="as-report-table-heading">
            <div class="as-report__panel-head as-report__panel-head--split">
                <div>
                    <h2 id="as-report-table-heading" class="as-report__panel-title">ตารางสรุป</h2>
                    <p class="as-report__panel-desc as-report__panel-desc--tight">เรียงตามจำนวนรายการในมิติที่เลือก<?php if (($dimension ?? '') === 'responsible_type'): ?> — คลิกแถว <strong>ระดับบุคคล</strong> เพื่อไปหน้ารายชื่อ (กลุ่มผู้รับผิดชอบระดับบุคคล)<?php endif; ?></p>
                </div>
                <a id="btnExportExcel" href="<?= base_url('admin/academic-services/report/export?dimension=' . urlencode($dimension ?? 'service_type') . ($year_filter ? '&year=' . urlencode($year_filter) : '') . (! empty($date_from) ? '&date_from=' . urlencode($date_from) : '') . (! empty($date_to) ? '&date_to=' . urlencode($date_to) : '')) ?>" class="btn btn-success btn-sm as-report__export" download>
                    ดาวน์โหลด CSV
                </a>
            </div>
            <div class="as-report__table-scroll">
                <table class="table as-report__table" id="reportTable">
                    <thead>
                        <tr>
                            <th scope="col"><?= esc($dimension_label ?? 'มิติ') ?></th>
                            <th scope="col" class="as-report__col-num">จำนวนรายการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($table_rows)): ?>
                            <tr>
                                <td colspan="2" class="as-report__empty">ยังไม่มีข้อมูลในมิตินี้</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($table_rows as $row):
                                $bk = $row['bucket_key'] ?? '';
                                $clickPerson = ($dimension ?? '') === 'responsible_type' && $bk === 'person';
                                ?>
                                <tr class="<?= $clickPerson ? 'as-report__tr--clickable' : '' ?>"
                                    <?php if ($clickPerson): ?>data-people-scope="person_responsible" tabindex="0" role="link" aria-label="ไปหน้ารายชื่อบุคคล ระดับบุคคล"<?php endif; ?>>
                                    <td><?= esc($row['label']) ?></td>
                                    <td class="as-report__col-num"><?= number_format($row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <?php if (! empty($table_rows)): ?>
                        <tfoot>
                            <tr>
                                <th scope="row">รวม</th>
                                <td class="as-report__col-num"><?= number_format($tableTotal) ?></td>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </section>
    </div>
</div>

<style>
.as-report {
    max-width: 1200px;
    margin: 0 auto;
}
.as-report__header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem 1.5rem;
    margin-bottom: 1.75rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--color-gray-200, #e5e7eb);
}
.as-report__eyebrow {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--color-gray-500, #6b7280);
    margin: 0 0 0.25rem 0;
}
.as-report__title {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--color-gray-900, #111827);
    margin: 0 0 0.35rem 0;
    line-height: 1.25;
}
.as-report__lead {
    margin: 0;
    font-size: 0.9375rem;
    color: var(--color-gray-600, #4b5563);
    max-width: 36rem;
    line-height: 1.5;
}
.as-report__back {
    flex-shrink: 0;
    align-self: center;
}
.as-report__kpis {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}
.as-report__kpi {
    background: var(--color-white, #fff);
    border: 1px solid var(--color-gray-200, #e5e7eb);
    border-radius: 12px;
    padding: 1.125rem 1.25rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.as-report__kpi--primary {
    border-color: #d4c4a8;
    background: linear-gradient(180deg, #fffdf7 0%, var(--color-white, #fff) 100%);
}
.as-report__kpi-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: var(--color-gray-600, #4b5563);
    margin-bottom: 0.35rem;
}
.as-report__kpi-badge {
    display: inline-block;
    font-size: 0.6875rem;
    font-weight: 600;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    background: var(--color-gray-100, #f3f4f6);
    color: var(--color-gray-700, #374151);
    vertical-align: middle;
}
.as-report__kpi-value {
    display: block;
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--color-gray-900, #111827);
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.as-report__kpi-hint {
    display: block;
    margin-top: 0.5rem;
    font-size: 0.75rem;
    color: var(--color-gray-500, #6b7280);
    line-height: 1.45;
}

.as-report__panel {
    background: var(--color-white, #fff);
    border: 1px solid var(--color-gray-200, #e5e7eb);
    border-radius: 12px;
    padding: 1.25rem 1.35rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
}
.as-report__panel--filters {
    margin-bottom: 1.5rem;
}
.as-report__panel-title {
    font-size: 1.0625rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: var(--color-gray-900, #111827);
}
.as-report__panel-desc {
    margin: 0 0 1rem 0;
    font-size: 0.8125rem;
    color: var(--color-gray-500, #6b7280);
}
.as-report__panel-desc--tight {
    margin-bottom: 0;
}
.as-report__panel-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem 1rem;
    margin-bottom: 1rem;
}
.as-report__panel-head--split {
    align-items: flex-end;
}
.as-report__panel-tag {
    font-size: 0.75rem;
    font-weight: 600;
    padding: 0.25rem 0.65rem;
    border-radius: 6px;
    background: var(--color-gray-100, #f3f4f6);
    color: var(--color-gray-700, #374151);
}

.as-report__filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem 1.25rem;
}
.as-report__field {
    margin-bottom: 0 !important;
}

.as-report__main {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
}
@media (min-width: 992px) {
    .as-report__main {
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        align-items: start;
    }
}

.as-report__chart-wrap {
    position: relative;
    width: 100%;
    height: min(380px, 52vh);
    min-height: 280px;
}
.as-report__export {
    flex-shrink: 0;
}

.as-report__table-scroll {
    overflow-x: auto;
    margin: 0 -0.15rem;
    border-radius: 8px;
    border: 1px solid var(--color-gray-200, #e5e7eb);
}
.as-report__table {
    margin: 0;
}
.as-report__table thead th {
    background: var(--color-gray-50, #f9fafb);
    border-bottom: 2px solid var(--color-gray-200, #e5e7eb);
}
.as-report__table tbody tr:nth-child(even) {
    background: var(--color-gray-50, #f9fafb);
}
.as-report__table tbody tr:hover {
    background: #fffbeb;
}
.as-report__col-num {
    text-align: right;
    font-variant-numeric: tabular-nums;
    white-space: nowrap;
    width: 8.5rem;
}
.as-report__table tfoot th,
.as-report__table tfoot td {
    font-weight: 700;
    background: var(--color-gray-100, #f3f4f6);
    border-top: 2px solid var(--color-gray-300, #d1d5db);
    border-bottom: none;
}
.as-report__empty {
    text-align: center;
    color: var(--color-gray-600, #4b5563);
    padding: 2rem 1rem !important;
}

.as-report__kpi--clickable {
    cursor: pointer;
    transition: box-shadow 0.15s ease, border-color 0.15s ease, transform 0.1s ease;
    outline: none;
}
.as-report__kpi--clickable:hover {
    border-color: var(--color-gray-400, #9ca3af);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.as-report__kpi--clickable:focus-visible {
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.45);
}

.as-report__tr--clickable {
    cursor: pointer;
    outline: none;
}
.as-report__tr--clickable:hover td {
    text-decoration: underline;
    text-underline-offset: 2px;
}
.as-report__tr--clickable:focus-visible {
    outline: 2px solid rgb(59, 130, 246);
    outline-offset: -2px;
}

</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var chartLabels = <?= json_encode($chart_labels ?? []) ?>;
    var chartData = <?= json_encode($chart_data ?? []) ?>;

    var ctx = document.getElementById('reportChart');
    if (!ctx) return;

    var reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'จำนวนรายการ',
                data: chartData,
                backgroundColor: 'rgba(59, 130, 246, 0.55)',
                borderColor: 'rgb(37, 99, 235)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: false
                }
            },
            scales: {
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 14
                    },
                    grid: { display: false }
                },
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                }
            }
        }
    });

    var dimensionNames = <?= json_encode($dimension_options ?? []) ?>;
    var baseUrl = <?= json_encode(base_url('admin/academic-services/report-data')) ?>;
    var exportBase = <?= json_encode(base_url('admin/academic-services/report/export')) ?>;
    var reportPeopleBase = <?= json_encode(base_url('admin/academic-services/report/people')) ?>;

    function reportPeopleQuery(scope) {
        var parts = ['scope=' + encodeURIComponent(scope)];
        var yr = document.getElementById('year').value;
        var df = document.getElementById('date_from') ? document.getElementById('date_from').value : '';
        var dt = document.getElementById('date_to') ? document.getElementById('date_to').value : '';
        if (yr) parts.push('year=' + encodeURIComponent(yr));
        if (df) parts.push('date_from=' + encodeURIComponent(df));
        if (dt) parts.push('date_to=' + encodeURIComponent(dt));
        return reportPeopleBase + '?' + parts.join('&');
    }

    function goReportPeople(scope) {
        window.location.href = reportPeopleQuery(scope);
    }

    var kpiInvolved = document.getElementById('kpiInvolvedCard');
    if (kpiInvolved) {
        kpiInvolved.addEventListener('click', function() {
            goReportPeople(kpiInvolved.getAttribute('data-people-scope') || 'filtered');
        });
        kpiInvolved.addEventListener('keydown', function(ev) {
            if (ev.key === 'Enter' || ev.key === ' ') {
                ev.preventDefault();
                goReportPeople(kpiInvolved.getAttribute('data-people-scope') || 'filtered');
            }
        });
    }

    var reportTableEl = document.getElementById('reportTable');
    if (reportTableEl) {
        reportTableEl.addEventListener('click', function(ev) {
            var tr = ev.target && ev.target.closest ? ev.target.closest('tr[data-people-scope]') : null;
            if (!tr || !reportTableEl.contains(tr)) return;
            goReportPeople(tr.getAttribute('data-people-scope') || 'person_responsible');
        });
        reportTableEl.addEventListener('keydown', function(ev) {
            if (ev.key !== 'Enter' && ev.key !== ' ') return;
            var tr = ev.target && ev.target.closest ? ev.target.closest('tr[data-people-scope]') : null;
            if (!tr || !reportTableEl.contains(tr)) return;
            ev.preventDefault();
            goReportPeople(tr.getAttribute('data-people-scope') || 'person_responsible');
        });
    }

    function escHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function fmtNum(n) {
        return Number(n || 0).toLocaleString('th-TH');
    }

    function updateReport() {
        var dim = document.getElementById('dimension').value;
        var yr = document.getElementById('year').value;
        var df = document.getElementById('date_from') ? document.getElementById('date_from').value : '';
        var dt = document.getElementById('date_to') ? document.getElementById('date_to').value : '';
        var url = baseUrl + '?dimension=' + encodeURIComponent(dim);
        if (yr) url += '&year=' + encodeURIComponent(yr);
        if (df) url += '&date_from=' + encodeURIComponent(df);
        if (dt) url += '&date_to=' + encodeURIComponent(dt);
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            var dimTitle = dimensionNames[dim] || dim;
            reportChart.data.labels = res.labels || [];
            reportChart.data.datasets[0].data = res.data || [];
            reportChart.options.plugins.title = { display: false };
            reportChart.update();

            var tagEl = document.querySelector('.as-report__panel--chart .as-report__panel-tag');
            if (tagEl) tagEl.textContent = dimTitle;

            var tbody = document.querySelector('#reportTable tbody');
            var tfoot = document.querySelector('#reportTable tfoot');
            if (tbody) {
                var rows = res.rows || [];
                if (tfoot) tfoot.remove();
                if (!rows.length) {
                    tbody.innerHTML = '<tr><td colspan="2" class="as-report__empty">ยังไม่มีข้อมูลในมิตินี้</td></tr>';
                } else {
                    var sum = 0;
                    tbody.innerHTML = rows.map(function(row) {
                        var c = parseInt(row.count, 10) || 0;
                        sum += c;
                        var pk = row.bucket_key || '';
                        var clickable = (dim === 'responsible_type' && pk === 'person');
                        var trOpen = clickable
                            ? ' class="as-report__tr--clickable" data-people-scope="person_responsible" tabindex="0" role="link" aria-label="ไปหน้ารายชื่อบุคคล ระดับบุคคล"'
                            : '';
                        return '<tr' + trOpen + '><td>' + escHtml(row.label) + '</td><td class="as-report__col-num">' + fmtNum(c) + '</td></tr>';
                    }).join('');
                    var tf = document.createElement('tfoot');
                    tf.innerHTML = '<tr><th scope="row">รวม</th><td class="as-report__col-num">' + fmtNum(sum) + '</td></tr>';
                    document.getElementById('reportTable').appendChild(tf);
                }
            }

            var thLabel = document.querySelector('#reportTable thead th:first-child');
            if (thLabel) thLabel.textContent = dimTitle;

            var exportUrl = exportBase + '?dimension=' + encodeURIComponent(dim);
            if (yr) exportUrl += '&year=' + encodeURIComponent(yr);
            if (df) exportUrl += '&date_from=' + encodeURIComponent(df);
            if (dt) exportUrl += '&date_to=' + encodeURIComponent(dt);
            document.getElementById('btnExportExcel').href = exportUrl;
        });
    }

    document.getElementById('dimension').addEventListener('change', updateReport);
    document.getElementById('year').addEventListener('change', updateReport);
    var dfEl = document.getElementById('date_from');
    var dtEl = document.getElementById('date_to');
    if (dfEl) dfEl.addEventListener('change', updateReport);
    if (dtEl) dtEl.addEventListener('change', updateReport);
})();
</script>

<?= $this->endSection() ?>

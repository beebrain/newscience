<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$filters = $report['filters'];
$summary = $report['summary'];
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 style="margin-bottom: 0.25rem;">รายงานผู้เข้าชมเว็บไซต์</h2>
            <p style="margin: 0; color: var(--color-gray-600);">สถิติจากหน้า public website เท่านั้น ไม่รวม Admin, Dashboard, API และระบบภายใน</p>
        </div>
        <a id="btnExportVisitReport" href="<?= base_url('admin/visit-reports/export?start_date=' . urlencode($filters['start_date']) . '&end_date=' . urlencode($filters['end_date']) . '&dimension=' . urlencode($filters['dimension'])) ?>" class="btn btn-success btn-sm" download>
            ออกรายงาน Excel (CSV)
        </a>
    </div>

    <div class="card-body">
        <div class="visit-report-filters" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; margin-bottom: 1.5rem; padding: 1rem; background: var(--color-gray-100, #f3f4f6); border-radius: 10px;">
            <div class="form-group" style="margin: 0;">
                <label for="start_date" class="form-label">วันที่เริ่มต้น</label>
                <input type="date" id="start_date" class="form-control" value="<?= esc($filters['start_date']) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="end_date" class="form-label">วันที่สิ้นสุด</label>
                <input type="date" id="end_date" class="form-control" value="<?= esc($filters['end_date']) ?>">
            </div>
            <div class="form-group" style="margin: 0;">
                <label for="dimension" class="form-label">มิติรายงาน</label>
                <select id="dimension" class="form-control" style="min-width: 210px;">
                    <?php foreach ($report['dimension_options'] as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= $filters['dimension'] === $value ? 'selected' : '' ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="button" id="btnRefreshVisitReport" class="btn btn-primary">แสดงรายงาน</button>
        </div>

        <div class="visit-kpi-grid">
            <div class="visit-kpi-card">
                <span>จำนวนเข้าชมรวม</span>
                <strong id="kpiTotalViews"><?= number_format($summary['total_views']) ?></strong>
            </div>
            <div class="visit-kpi-card">
                <span>ผู้เข้าชมไม่ซ้ำ</span>
                <strong id="kpiUniqueVisitors"><?= number_format($summary['unique_visitors']) ?></strong>
            </div>
            <div class="visit-kpi-card">
                <span>เฉลี่ยต่อวัน</span>
                <strong id="kpiAveragePerDay"><?= number_format($summary['average_per_day'], 1) ?></strong>
            </div>
            <div class="visit-kpi-card">
                <span>จำนวนวันที่รายงาน</span>
                <strong id="kpiDays"><?= number_format($summary['days']) ?></strong>
            </div>
        </div>

        <div class="visit-report-grid">
            <section class="visit-panel">
                <h3>แนวโน้มรายวัน</h3>
                <div class="visit-chart-box"><canvas id="trendChart"></canvas></div>
            </section>
            <section class="visit-panel">
                <h3 id="dimensionTitle">สรุปตาม<?= esc($report['dimension_label']) ?></h3>
                <div class="visit-chart-box"><canvas id="dimensionChart"></canvas></div>
            </section>
        </div>

        <div class="visit-report-grid">
            <section class="visit-panel">
                <h3>สรุปตามประเภทเนื้อหา</h3>
                <div class="table-responsive">
                    <table class="table" id="contentTable">
                        <thead><tr><th>ประเภท</th><th style="text-align:right;">เข้าชม</th><th style="text-align:right;">ไม่ซ้ำ</th></tr></thead>
                        <tbody><?= view('admin/visit_reports/partials/breakdown_rows', ['rows' => $report['content_breakdown']['rows']]) ?></tbody>
                    </table>
                </div>
            </section>
            <section class="visit-panel">
                <h3>สรุปอุปกรณ์และแหล่งที่มา</h3>
                <div class="visit-small-tables">
                    <div>
                        <h4>อุปกรณ์</h4>
                        <table class="table" id="deviceTable"><tbody><?= view('admin/visit_reports/partials/simple_rows', ['rows' => $report['device_breakdown']['rows']]) ?></tbody></table>
                    </div>
                    <div>
                        <h4>แหล่งที่มา</h4>
                        <table class="table" id="sourceTable"><tbody><?= view('admin/visit_reports/partials/simple_rows', ['rows' => $report['source_breakdown']['rows']]) ?></tbody></table>
                    </div>
                </div>
            </section>
        </div>

        <section class="visit-panel">
            <h3>หน้า/เนื้อหาที่เข้าชมสูงสุด</h3>
            <div class="table-responsive">
                <table class="table" id="topPagesTable">
                    <thead>
                        <tr>
                            <th>ชื่อหน้า/เนื้อหา</th>
                            <th>Route</th>
                            <th>ประเภท</th>
                            <th style="text-align:right;">เข้าชม</th>
                            <th style="text-align:right;">ไม่ซ้ำ</th>
                        </tr>
                    </thead>
                    <tbody><?= view('admin/visit_reports/partials/top_page_rows', ['rows' => $report['top_pages']]) ?></tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<style>
.visit-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1.5rem; }
.visit-kpi-card { padding: 1rem; border: 1px solid var(--color-gray-200, #e5e7eb); border-radius: 12px; background: white; }
.visit-kpi-card span { display: block; color: var(--color-gray-600); font-size: 0.875rem; margin-bottom: 0.4rem; }
.visit-kpi-card strong { font-size: 1.65rem; line-height: 1; }
.visit-report-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem; margin-bottom: 1rem; }
.visit-panel { padding: 1rem; border: 1px solid var(--color-gray-200, #e5e7eb); border-radius: 12px; background: white; margin-bottom: 1rem; }
.visit-panel h3 { margin: 0 0 0.9rem 0; font-size: 1rem; }
.visit-panel h4 { margin: 0 0 0.5rem 0; font-size: 0.9rem; }
.visit-chart-box { height: 320px; }
.visit-small-tables { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var report = <?= json_encode($report) ?>;
    var dataUrl = <?= json_encode(base_url('admin/visit-reports/data')) ?>;
    var exportUrl = <?= json_encode(base_url('admin/visit-reports/export')) ?>;

    function nf(value, decimals) {
        return Number(value || 0).toLocaleString('th-TH', {
            minimumFractionDigits: decimals || 0,
            maximumFractionDigits: decimals || 0
        });
    }
    function esc(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function(ch) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'}[ch];
        });
    }
    function currentQuery() {
        var params = new URLSearchParams();
        params.set('start_date', document.getElementById('start_date').value);
        params.set('end_date', document.getElementById('end_date').value);
        params.set('dimension', document.getElementById('dimension').value);
        return params.toString();
    }
    function simpleRows(rows) {
        rows = rows || [];
        if (!rows.length) return '<tr><td colspan="3" style="color: var(--color-gray-600);">ยังไม่มีข้อมูล</td></tr>';
        return rows.map(function(row) {
            return '<tr><td>' + esc(row.label) + '</td><td style="text-align:right;">' + nf(row.views) + '</td><td style="text-align:right;">' + nf(row.unique_visitors) + '</td></tr>';
        }).join('');
    }
    function topPageRows(rows) {
        rows = rows || [];
        if (!rows.length) return '<tr><td colspan="5" style="color: var(--color-gray-600);">ยังไม่มีข้อมูล</td></tr>';
        return rows.map(function(row) {
            return '<tr><td>' + esc(row.title) + '</td><td><code>' + esc(row.route) + '</code></td><td>' + esc(row.content_type) + '</td><td style="text-align:right;">' + nf(row.views) + '</td><td style="text-align:right;">' + nf(row.unique_visitors) + '</td></tr>';
        }).join('');
    }

    var trendChart = new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: report.trend.labels || [],
            datasets: [
                { label: 'เข้าชม', data: report.trend.views || [], borderColor: 'rgb(37, 99, 235)', backgroundColor: 'rgba(37, 99, 235, 0.12)', tension: 0.25, fill: true },
                { label: 'ไม่ซ้ำ', data: report.trend.unique_visitors || [], borderColor: 'rgb(16, 185, 129)', backgroundColor: 'rgba(16, 185, 129, 0.08)', tension: 0.25 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    var dimensionChart = new Chart(document.getElementById('dimensionChart'), {
        type: 'bar',
        data: {
            labels: report.dimension.labels || [],
            datasets: [{ label: 'เข้าชม', data: report.dimension.data || [], backgroundColor: 'rgba(234, 179, 8, 0.65)', borderColor: 'rgb(202, 138, 4)', borderWidth: 1 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });

    function render(next) {
        report = next;
        document.getElementById('kpiTotalViews').textContent = nf(report.summary.total_views);
        document.getElementById('kpiUniqueVisitors').textContent = nf(report.summary.unique_visitors);
        document.getElementById('kpiAveragePerDay').textContent = nf(report.summary.average_per_day, 1);
        document.getElementById('kpiDays').textContent = nf(report.summary.days);
        document.getElementById('dimensionTitle').textContent = 'สรุปตาม' + report.dimension_label;

        trendChart.data.labels = report.trend.labels || [];
        trendChart.data.datasets[0].data = report.trend.views || [];
        trendChart.data.datasets[1].data = report.trend.unique_visitors || [];
        trendChart.update();

        dimensionChart.data.labels = report.dimension.labels || [];
        dimensionChart.data.datasets[0].data = report.dimension.data || [];
        dimensionChart.update();

        document.querySelector('#contentTable tbody').innerHTML = simpleRows(report.content_breakdown.rows);
        document.querySelector('#deviceTable tbody').innerHTML = simpleRows(report.device_breakdown.rows);
        document.querySelector('#sourceTable tbody').innerHTML = simpleRows(report.source_breakdown.rows);
        document.querySelector('#topPagesTable tbody').innerHTML = topPageRows(report.top_pages);
        document.getElementById('btnExportVisitReport').href = exportUrl + '?' + currentQuery();
    }

    document.getElementById('btnRefreshVisitReport').addEventListener('click', function() {
        fetch(dataUrl + '?' + currentQuery(), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(response) { return response.json(); })
            .then(function(payload) {
                if (payload && payload.success) render(payload.data);
            });
    });
})();
</script>

<?= $this->endSection() ?>

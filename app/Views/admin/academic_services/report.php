<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>แบบรายงานสรุป บริการวิชาการ</h2>
        <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary">← จัดการบริการวิชาการ</a>
    </div>

    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>

        <div class="report-summary" style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem;">
            <div class="report-stat" style="padding: 0.75rem 1.25rem; background: var(--color-gray-100, #f3f4f6); border-radius: 8px;">
                <span style="font-size: 0.875rem; color: var(--color-gray-600);">จำนวนรายการทั้งหมด</span>
                <div style="font-size: 1.35rem; font-weight: 600;"><?= number_format($total) ?></div>
            </div>
            <div class="report-stat" style="padding: 0.75rem 1.25rem; background: var(--color-gray-100, #f3f4f6); border-radius: 8px;">
                <span style="font-size: 0.875rem; color: var(--color-gray-600);">บุคลากรที่ร่วมให้บริการ (ไม่ซ้ำ)</span>
                <div style="font-size: 1.35rem; font-weight: 600;"><?= number_format($distinct_participants) ?></div>
            </div>
        </div>

        <div class="report-filters card" style="margin-bottom: 1.5rem; padding: 1rem;">
            <h3 style="margin: 0 0 1rem 0; font-size: 1rem;">สรุปในหลากหลายมิติ</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
                <div class="form-group" style="margin: 0;">
                    <label for="dimension" class="form-label" style="display: block; margin-bottom: 0.25rem;">มิติของข้อมูล</label>
                    <select name="dimension" id="dimension" class="form-control" style="min-width: 220px;">
                        <?php foreach ($dimension_options as $val => $label): ?>
                            <option value="<?= esc($val) ?>" <?= ($dimension ?? '') === $val ? 'selected' : '' ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="margin: 0;">
                    <label for="year" class="form-label" style="display: block; margin-bottom: 0.25rem;">ปีการศึกษา (กรอง)</label>
                    <select name="year" id="year" class="form-control" style="min-width: 140px;">
                        <option value="">ทุกปี</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= esc($y) ?>" <?= ($year_filter ?? '') === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="report-chart-section" style="margin-bottom: 1.5rem;">
            <h3 class="form-section-title">กราฟสรุปตาม<?= esc($dimension_label ?? 'มิติ') ?></h3>
            <div style="max-width: 600px; height: 320px;">
                <canvas id="reportChart" aria-label="กราฟสรุปข้อมูลบริการวิชาการ"></canvas>
            </div>
        </div>

        <div class="report-table-section">
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 0.75rem;">
                <h3 class="form-section-title" style="margin: 0;">ตารางสรุป</h3>
                <a id="btnExportExcel" href="<?= base_url('admin/academic-services/report/export?dimension=' . urlencode($dimension ?? 'service_type') . ($year_filter ? '&year=' . urlencode($year_filter) : '')) ?>" class="btn btn-success btn-sm" download>
                    ออกรายงาน Excel (CSV)
                </a>
            </div>
            <div class="table-responsive">
                <table class="table" id="reportTable">
                    <thead>
                        <tr>
                            <th><?= esc($dimension_label ?? 'มิติ') ?></th>
                            <th style="width: 120px; text-align: right;">จำนวนรายการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($table_rows)): ?>
                            <tr><td colspan="2" style="color: var(--color-gray-600);">ยังไม่มีข้อมูลในมิตินี้</td></tr>
                        <?php else: ?>
                            <?php foreach ($table_rows as $row): ?>
                                <tr>
                                    <td><?= esc($row['label']) ?></td>
                                    <td style="text-align: right;"><?= number_format($row['count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.form-section-title { font-size: 1rem; margin-bottom: 0.75rem; padding-bottom: 0.35rem; border-bottom: 1px solid var(--color-gray-200, #e5e7eb); }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function() {
    var chartLabels = <?= json_encode($chart_labels ?? []) ?>;
    var chartData = <?= json_encode($chart_data ?? []) ?>;
    var dimensionLabel = <?= json_encode($dimension_label ?? '') ?>;

    var ctx = document.getElementById('reportChart');
    if (!ctx) return;

    var reportChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'จำนวนรายการ',
                data: chartData,
                backgroundColor: 'rgba(59, 130, 246, 0.6)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'สรุปตาม' + dimensionLabel
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });

    var dimensionNames = <?= json_encode($dimension_options ?? []) ?>;
    var baseUrl = <?= json_encode(base_url('admin/academic-services/report-data')) ?>;
    var exportBase = <?= json_encode(base_url('admin/academic-services/report/export')) ?>;

    function updateReport() {
        var dim = document.getElementById('dimension').value;
        var yr = document.getElementById('year').value;
        var url = baseUrl + '?dimension=' + encodeURIComponent(dim);
        if (yr) url += '&year=' + encodeURIComponent(yr);
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            reportChart.data.labels = res.labels || [];
            reportChart.data.datasets[0].data = res.data || [];
            reportChart.options.plugins.title.text = 'สรุปตาม' + (dimensionNames[dim] || dim);
            reportChart.update();

            var tbody = document.querySelector('#reportTable tbody');
            if (tbody) {
                var rows = res.rows || [];
                tbody.innerHTML = rows.length ? rows.map(function(row) {
                    return '<tr><td>' + (row.label || '').replace(/</g, '&lt;') + '</td><td style="text-align:right">' + (row.count || 0) + '</td></tr>';
                }).join('') : '<tr><td colspan="2" style="color: var(--color-gray-600);">ยังไม่มีข้อมูลในมิตินี้</td></tr>';
            }

            var exportUrl = exportBase + '?dimension=' + encodeURIComponent(dim);
            if (yr) exportUrl += '&year=' + encodeURIComponent(yr);
            document.getElementById('btnExportExcel').href = exportUrl;
        });
    }

    document.getElementById('dimension').addEventListener('change', updateReport);
    document.getElementById('year').addEventListener('change', updateReport);
})();
</script>

<?= $this->endSection() ?>

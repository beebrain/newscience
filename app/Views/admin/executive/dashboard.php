<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <h2 style="margin: 0;"><?= esc($page_title ?? 'Dashboard ผู้บริหาร') ?></h2>
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <label for="exec-period" style="font-size: 0.875rem; color: var(--color-gray-600);">ช่วงเวลา:</label>
            <select id="exec-period" class="form-control" style="width: auto; min-width: 140px;">
                <option value="all">ทั้งหมด</option>
                <option value="year">ปีนี้</option>
                <option value="quarter">ไตรมาสนี้</option>
                <option value="month">เดือนนี้</option>
            </select>
        </div>
    </div>

    <!-- Tabs -->
    <div class="exec-tabs" style="display: flex; flex-wrap: wrap; gap: 0.25rem; padding: 0 1.5rem; border-bottom: 1px solid var(--color-gray-200); background: var(--color-gray-50);">
        <button type="button" class="exec-tab active" data-section="overview" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">ภาพรวม</button>
        <button type="button" class="exec-tab" data-section="personnel" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">บุคลากร</button>
        <button type="button" class="exec-tab" data-section="programs" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">หลักสูตร</button>
        <button type="button" class="exec-tab" data-section="news" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">ข่าว</button>
        <button type="button" class="exec-tab" data-section="edoc" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">E-Doc</button>
        <button type="button" class="exec-tab" data-section="certificates" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">ใบรับรอง</button>
        <button type="button" class="exec-tab" data-section="research" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">งานวิจัย</button>
        <button type="button" class="exec-tab" data-section="pageviews" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">ผู้เข้าชม</button>
        <button type="button" class="exec-tab" data-section="program-summary" style="padding: 0.75rem 1rem; border: none; background: none; cursor: pointer; font-size: 0.9375rem; color: var(--color-gray-600); border-bottom: 2px solid transparent; margin-bottom: -1px;">สรุปรายหลักสูตร</button>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Overview: Stat cards (always visible on overview tab) -->
        <div id="exec-overview" class="exec-section">
            <div id="exec-stat-cards" class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div class="stat-card" style="background: var(--color-blue-50); border: 1px solid var(--color-blue-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-blue-600);" id="stat-personnel">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">บุคลากร</div></div>
                <div class="stat-card" style="background: var(--color-green-50); border: 1px solid var(--color-green-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-green-600);" id="stat-programs">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">หลักสูตร</div></div>
                <div class="stat-card" style="background: var(--color-amber-50); border: 1px solid var(--color-amber-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-amber-700);" id="stat-students">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">นักศึกษา</div></div>
                <div class="stat-card" style="background: var(--color-purple-50); border: 1px solid var(--color-purple-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-purple-600);" id="stat-news">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">ข่าว</div></div>
                <div class="stat-card" style="background: var(--color-cyan-50); border: 1px solid var(--color-cyan-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-cyan-700);" id="stat-edoc">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">เอกสาร E-Doc</div></div>
                <div class="stat-card" style="background: var(--color-rose-50); border: 1px solid var(--color-rose-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-rose-600);" id="stat-certificates">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">ใบรับรอง</div></div>
                <div class="stat-card" style="background: var(--color-indigo-50); border: 1px solid var(--color-indigo-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-indigo-600);" id="stat-research">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">งานวิจัย</div></div>
                <div class="stat-card" style="background: var(--color-teal-50); border: 1px solid var(--color-teal-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-teal-700);" id="stat-page-views">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">ผู้เข้าชม</div></div>
                <div class="stat-card" style="background: var(--color-slate-50); border: 1px solid var(--color-slate-200); border-radius: 8px; padding: 1rem;"><div class="stat-number" style="font-size: 1.75rem; font-weight: 600; color: var(--color-slate-700);" id="stat-total-users">—</div><div class="stat-label" style="color: var(--color-gray-600); font-size: 0.875rem;">ผู้ใช้ทั้งหมด</div></div>
            </div>
        </div>

        <!-- Personnel section -->
        <div id="exec-personnel" class="exec-section" style="display: none;">
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">บุคลากรตามสาขา</h3><div id="chart-personnel-dept" style="min-height: 280px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">บุคลากรตามตำแหน่ง</h3><div id="chart-personnel-position" style="min-height: 280px;"></div></div>
            </div>
            <div class="card" style="margin-top: 1.5rem; padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">บุคลากรต่อหลักสูตร</h3><div id="chart-personnel-program" style="min-height: 300px;"></div></div>
        </div>

        <!-- Programs section -->
        <div id="exec-programs" class="exec-section" style="display: none;">
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">หลักสูตรตามระดับ</h3><div id="chart-programs-level" style="min-height: 260px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">เว็บไซต์หลักสูตร (เผยแพร่/ทั้งหมด)</h3><div id="chart-programs-pages" style="min-height: 260px;"></div></div>
            </div>
            <div class="card" style="margin-top: 1.5rem; padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">กิจกรรมต่อหลักสูตร</h3><div id="chart-programs-activities" style="min-height: 280px;"></div></div>
        </div>

        <!-- News section -->
        <div id="exec-news" class="exec-section" style="display: none;">
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ข่าวตาม Tag</h3><div id="chart-news-tag" style="min-height: 280px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ข่าวรายเดือน</h3><div id="chart-news-month" style="min-height: 280px;"></div></div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ข่าวยอดนิยม (Top 10)</h3><div id="table-news-top" style="min-height: 200px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">กิจกรรมที่จะมาถึง</h3><div id="table-events-upcoming" style="min-height: 200px;"></div></div>
            </div>
        </div>

        <!-- E-Doc section -->
        <div id="exec-edoc" class="exec-section" style="display: none;">
            <div id="edoc-paper-summary" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;"></div>
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">เอกสารตามประเภท</h3><div id="chart-edoc-type" style="min-height: 280px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">แนวโน้มรายเดือน</h3><div id="chart-edoc-month" style="min-height: 280px;"></div></div>
            </div>
            <div class="card" style="margin-top: 1.5rem; padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ผู้สร้างเอกสารสูงสุด</h3><div id="chart-edoc-owners" style="min-height: 280px;"></div></div>
        </div>

        <!-- Certificates section -->
        <div id="exec-certificates" class="exec-section" style="display: none;">
            <div id="barcode-claim-rate" style="margin-bottom: 1.5rem;"></div>
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ใบรับรองตามสถานะ</h3><div id="chart-cert-status" style="min-height: 260px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ใบรับรองรายเดือน</h3><div id="chart-cert-month" style="min-height: 260px;"></div></div>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">Cert Events</h3><div id="table-cert-events" style="min-height: 180px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">Barcode Events</h3><div id="table-barcode-events" style="min-height: 180px;"></div></div>
            </div>
        </div>

        <!-- Research section -->
        <div id="exec-research" class="exec-section" style="display: none;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                <div id="research-stat-cards" style="display: flex; flex-wrap: wrap; gap: 1rem;"></div>
                <button type="button" id="exec-research-refresh" class="btn btn-secondary" style="padding: 0.5rem 1rem;">โหลดข้อมูลใหม่</button>
            </div>
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ผลงานตามประเภท</h3><div id="chart-research-type" style="min-height: 260px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ผลงานตามปี</h3><div id="chart-research-year" style="min-height: 260px;"></div></div>
            </div>
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem; margin-top: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">บุคลากรที่มีผลงานสูง (Top 10)</h3><div id="chart-research-personnel" style="min-height: 280px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ผลงานต่อหลักสูตร</h3><div id="chart-research-program" style="min-height: 280px;"></div></div>
            </div>
        </div>

        <!-- Pageviews / Visitors section -->
        <div id="exec-pageviews" class="exec-section" style="display: none;">
            <div id="pageviews-stat-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;"></div>
            <div class="row-two" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">แนวโน้มการเข้าชมรายวัน</h3><div id="chart-pageviews-day" style="min-height: 280px;"></div></div>
                <div class="card" style="padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">ประเภทผู้เข้าชม</h3><div id="chart-pageviews-breakdown" style="min-height: 260px;"></div></div>
            </div>
            <div class="card" style="margin-top: 1.5rem; padding: 1rem;"><h3 style="margin: 0 0 1rem 0; font-size: 1rem;">หน้ายอดนิยม (Top 20)</h3><div id="table-pageviews-top" style="min-height: 200px;"></div></div>
        </div>

        <!-- Per-program summary section -->
        <div id="exec-program-summary" class="exec-section" style="display: none;">
            <div class="card" style="padding: 1rem; overflow-x: auto;">
                <h3 style="margin: 0 0 1rem 0; font-size: 1rem;">สรุปมิติทั้งหมดต่อหลักสูตร</h3>
                <div id="table-program-summary" style="min-height: 200px;"></div>
            </div>
        </div>
    </div>
</div>

<style>
.exec-tab.active { color: var(--color-primary, #2563eb) !important; font-weight: 600; border-bottom-color: var(--color-primary, #2563eb) !important; }
.exec-section .card { border: 1px solid var(--color-gray-200); border-radius: 8px; }
#table-news-top table, #table-events-upcoming table, #table-cert-events table, #table-barcode-events table, #table-pageviews-top table, #table-program-summary table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
#table-news-top th, #table-events-upcoming th, #table-cert-events th, #table-barcode-events th, #table-pageviews-top th, #table-program-summary th { text-align: left; padding: 0.5rem; border-bottom: 1px solid var(--color-gray-200); }
#table-news-top td, #table-events-upcoming td, #table-cert-events td, #table-barcode-events td, #table-pageviews-top td, #table-program-summary td { padding: 0.5rem; border-bottom: 1px solid var(--color-gray-100); }
#table-program-summary .cell-num { text-align: right; }
#table-program-summary .cell-yes { color: var(--color-green-600); }
#table-program-summary .cell-zero { color: var(--color-gray-400); }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= base_url('assets/vendor/apexcharts/dist/apexcharts.min.js') ?>"></script>
<script>
(function() {
    var baseUrl = '<?= base_url() ?>';
    var apiBase = baseUrl + 'api/executive/';
    var period = 'all';
    var chartInstances = {};

    function getPeriod() { return document.getElementById('exec-period') ? document.getElementById('exec-period').value : 'all'; }

    function fetchApi(endpoint, params) {
        var q = new URLSearchParams(params || {});
        if (endpoint !== 'overview' && endpoint !== 'research' && endpoint !== 'program-summary') q.set('period', getPeriod());
        return fetch(apiBase + endpoint + '?' + q.toString(), { credentials: 'same-origin' }).then(function(r) {
            if (!r.ok) throw new Error('API error');
            return r.json();
        });
    }

    function renderNumber(elId, n) {
        var el = document.getElementById(elId);
        if (el) el.textContent = typeof n === 'number' ? n.toLocaleString('th-TH') : (n || '—');
    }

    function loadOverview() {
        fetchApi('overview').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            renderNumber('stat-personnel', d.personnel);
            renderNumber('stat-programs', d.programs);
            renderNumber('stat-students', d.students);
            renderNumber('stat-news', d.news);
            renderNumber('stat-edoc', d.edoc);
            renderNumber('stat-certificates', d.certificates);
            renderNumber('stat-research', d.research);
            renderNumber('stat-page-views', d.page_views);
            renderNumber('stat-total-users', d.total_users);
        }).catch(function() {
            ['stat-personnel','stat-programs','stat-students','stat-news','stat-edoc','stat-certificates','stat-research','stat-page-views','stat-total-users'].forEach(function(id) { var el = document.getElementById(id); if (el) el.textContent = '—'; });
        });
    }

    function destroyChart(key) {
        if (chartInstances[key]) { chartInstances[key].destroy(); chartInstances[key] = null; }
    }

    function barChart(elId, labels, series, opts) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!labels.length) { el.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>'; return; }
        var options = {
            chart: { type: 'bar', height: 280, toolbar: { show: false } },
            plotOptions: { bar: { horizontal: opts && opts.horizontal ? true : false, borderRadius: 4 } },
            dataLabels: { enabled: false },
            xaxis: { categories: labels },
            series: [{ name: opts && opts.seriesName ? opts.seriesName : 'จำนวน', data: series }]
        };
        if (opts && opts.colors) options.colors = opts.colors;
        chartInstances[elId] = new ApexCharts(el, options);
        chartInstances[elId].render();
    }

    function donutChart(elId, labels, series, opts) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!labels.length) { el.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>'; return; }
        chartInstances[elId] = new ApexCharts(el, {
            chart: { type: 'donut', height: 260 },
            labels: labels,
            series: series,
            legend: { position: 'bottom' },
            dataLabels: { enabled: true }
        });
        chartInstances[elId].render();
    }

    function lineChart(elId, categories, series, opts) {
        var el = document.getElementById(elId);
        if (!el) return;
        destroyChart(elId);
        if (!categories.length) { el.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>'; return; }
        chartInstances[elId] = new ApexCharts(el, {
            chart: { type: 'line', height: 280, toolbar: { show: false } },
            stroke: { curve: 'smooth', width: 2 },
            xaxis: { categories: categories },
            series: [{ name: opts && opts.seriesName ? opts.seriesName : 'จำนวน', data: series }],
            dataLabels: { enabled: false }
        });
        chartInstances[elId].render();
    }

    function loadPersonnel() {
        fetchApi('personnel').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            barChart('chart-personnel-dept', d.by_department.map(function(x) { return x.name; }), d.by_department.map(function(x) { return x.count; }));
            donutChart('chart-personnel-position', d.by_position.map(function(x) { return x.name; }), d.by_position.map(function(x) { return x.count; }));
            barChart('chart-personnel-program', d.per_program.map(function(x) { return x.name; }), d.per_program.map(function(x) { return x.count; }), { horizontal: true });
        });
    }

    function loadPrograms() {
        fetchApi('programs').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            donutChart('chart-programs-level', d.by_level.map(function(x) { return x.name; }), d.by_level.map(function(x) { return x.count; }));
            var total = d.program_pages_total || 0;
            var pub = d.program_pages_published || 0;
            if (total > 0) {
                donutChart('chart-programs-pages', ['เผยแพร่', 'ยังไม่เผยแพร่'], [pub, total - pub]);
            } else {
                var el = document.getElementById('chart-programs-pages');
                if (el) el.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>';
            }
            barChart('chart-programs-activities', d.activities_per_program.map(function(x) { return x.name; }), d.activities_per_program.map(function(x) { return x.count; }), { horizontal: true });
        });
    }

    function loadNews() {
        fetchApi('news').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            barChart('chart-news-tag', d.by_tag.map(function(x) { return x.name; }), d.by_tag.map(function(x) { return x.count; }));
            lineChart('chart-news-month', d.by_month.map(function(x) { return x.month; }), d.by_month.map(function(x) { return x.count; }));
            var topEl = document.getElementById('table-news-top');
            if (topEl) {
                if (!d.top_viewed || !d.top_viewed.length) topEl.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>';
                else topEl.innerHTML = '<table><thead><tr><th>หัวข้อ</th><th>ยอดเข้าชม</th></tr></thead><tbody>' + d.top_viewed.map(function(r) { return '<tr><td>' + (r.title || '').substring(0, 50) + (r.title && r.title.length > 50 ? '…' : '') + '</td><td>' + (r.view_count || 0) + '</td></tr>'; }).join('') + '</tbody></table>';
            }
            var evEl = document.getElementById('table-events-upcoming');
            if (evEl) {
                if (!d.upcoming_events || !d.upcoming_events.length) evEl.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีกิจกรรมที่จะมาถึง</p>';
                else evEl.innerHTML = '<table><thead><tr><th>กิจกรรม</th><th>วันที่</th></tr></thead><tbody>' + d.upcoming_events.map(function(r) { return '<tr><td>' + (r.title || '') + '</td><td>' + (r.event_date || '') + '</td></tr>'; }).join('') + '</tbody></table>';
            }
        });
    }

    function loadEdoc() {
        fetchApi('edoc').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            var sumEl = document.getElementById('edoc-paper-summary');
            if (sumEl && d.paper_summary) {
                sumEl.innerHTML = '<div style="padding:0.75rem;background:var(--color-gray-50);border-radius:8px;"><strong>' + (d.paper_summary.total_docs || 0) + '</strong> เอกสาร</div><div style="padding:0.75rem;background:var(--color-gray-50);border-radius:8px;"><strong>' + (d.paper_summary.total_pages || 0) + '</strong> หน้า</div><div style="padding:0.75rem;background:var(--color-gray-50);border-radius:8px;"><strong>' + (d.paper_summary.total_paper || 0) + '</strong> กระดาษ (รวม)</div>';
            }
            donutChart('chart-edoc-type', d.by_type.map(function(x) { return x.name; }), d.by_type.map(function(x) { return x.count; }));
            var months = d.by_month || [];
            lineChart('chart-edoc-month', months.map(function(x) { return 'เดือน ' + (x.month || ''); }), months.map(function(x) { return x.count || 0; }));
            barChart('chart-edoc-owners', d.top_owners.map(function(x) { return x.name; }), d.top_owners.map(function(x) { return x.count; }), { horizontal: true });
        });
    }

    function loadResearch() {
        var refresh = document.getElementById('exec-research-refresh');
        if (refresh) refresh.disabled = true;
        fetchApi('research', {}).then(function(res) {
            if (refresh) refresh.disabled = false;
            if (!res.success || !res.data) return;
            var d = res.data;
            var cardsEl = document.getElementById('research-stat-cards');
            if (cardsEl) cardsEl.innerHTML = '<div style="padding:0.75rem;background:var(--color-indigo-50);border-radius:8px;"><strong>' + (d.total_publications || 0) + '</strong> ผลงานรวม</div><div style="padding:0.75rem;background:var(--color-gray-50);border-radius:8px;"><strong>' + (d.unique_researchers || 0) + '</strong> ผู้มีผลงาน</div>';
            donutChart('chart-research-type', (d.by_type || []).map(function(x) { return x.name; }), (d.by_type || []).map(function(x) { return x.count; }));
            barChart('chart-research-year', (d.by_year || []).map(function(x) { return x.year; }), (d.by_year || []).map(function(x) { return x.count; }));
            barChart('chart-research-personnel', (d.by_personnel || []).map(function(x) { return (x.name || '').substring(0, 25) + (x.name && x.name.length > 25 ? '…' : ''); }), (d.by_personnel || []).map(function(x) { return x.count; }), { horizontal: true });
            barChart('chart-research-program', (d.by_program || []).map(function(x) { return x.name; }), (d.by_program || []).map(function(x) { return x.count; }), { horizontal: true });
        }).catch(function() { if (refresh) refresh.disabled = false; });
    }

    function loadPageviews() {
        fetchApi('pageviews').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            var cardsEl = document.getElementById('pageviews-stat-cards');
            if (cardsEl) cardsEl.innerHTML = '<div class="stat-card" style="background:var(--color-teal-50);border:1px solid var(--color-teal-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;color:var(--color-teal-700);">' + (d.total_views || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">การเข้าชมหน้า</div></div><div class="stat-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;">' + (d.unique_visitors || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">ผู้เข้าชมไม่ซ้ำ</div></div><div class="stat-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;">' + (d.total_users_admin || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">ผู้ใช้ (Admin)</div></div><div class="stat-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;">' + (d.total_students || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">นักศึกษา</div></div><div class="stat-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;">' + (d.news_views_total || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">ยอดดูข่าว</div></div><div class="stat-card" style="background:var(--color-gray-50);border:1px solid var(--color-gray-200);border-radius:8px;padding:1rem;"><div style="font-size:1.5rem;font-weight:600;">' + (d.edoc_views_total || 0).toLocaleString('th-TH') + '</div><div style="color:var(--color-gray-600);font-size:0.875rem;">ยอดดู E-Doc</div></div>';
            var byDay = d.by_day || [];
            lineChart('chart-pageviews-day', byDay.map(function(x) { return x.date; }), byDay.map(function(x) { return x.views; }), { seriesName: 'การเข้าชม' });
            var ub = d.user_breakdown || {};
            var breakdownLabels = ['ผู้ดูแลระบบ', 'นักศึกษา', 'ผู้เยี่ยมชม'];
            var breakdownKeys = ['admin', 'student', 'guest'];
            donutChart('chart-pageviews-breakdown', breakdownKeys.map(function(k, i) { return breakdownLabels[i] + ' (' + (ub[k] || 0) + ')'; }), breakdownKeys.map(function(k) { return ub[k] || 0; }));
            var topEl = document.getElementById('table-pageviews-top');
            if (topEl) {
                if (!d.by_page || !d.by_page.length) topEl.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>';
                else topEl.innerHTML = '<table><thead><tr><th>URL</th><th>จำนวน</th></tr></thead><tbody>' + d.by_page.map(function(r) { var u = (r.url || '').substring(0, 60); return '<tr><td title="' + (r.url || '').replace(/"/g, '&quot;') + '">' + u + (r.url && r.url.length > 60 ? '…' : '') + '</td><td>' + (r.count || 0) + '</td></tr>'; }).join('') + '</tbody></table>';
            }
        });
    }

    function loadProgramSummary() {
        fetchApi('program-summary', {}).then(function(res) {
            if (!res.success || !res.data || !res.data.programs) return;
            var rows = res.data.programs;
            var thead = '<thead><tr><th>หลักสูตร</th><th>ระดับ</th><th>บุคลากร</th><th>นักศึกษา</th><th>กิจกรรม</th><th>ดาวน์โหลด</th><th>สิ่งอำนวยความสะดวก</th><th>เผยแพร่เว็บ</th><th>ข่าว</th><th>งานวิจัย</th></tr></thead>';
            var tbody = '<tbody>';
            var totals = { personnel_count: 0, student_count: 0, activity_count: 0, download_count: 0, facility_count: 0, news_count: 0, research_count: 0 };
            rows.forEach(function(r) {
                totals.personnel_count += r.personnel_count || 0;
                totals.student_count += r.student_count || 0;
                totals.activity_count += r.activity_count || 0;
                totals.download_count += r.download_count || 0;
                totals.facility_count += r.facility_count || 0;
                totals.news_count += r.news_count || 0;
                totals.research_count += r.research_count || 0;
                var pub = r.page_published ? '<span class="cell-yes">ใช่</span>' : '<span class="cell-zero">ไม่</span>';
                var cn = function(n) { return n > 0 ? 'cell-num' : 'cell-num cell-zero'; };
                tbody += '<tr><td>' + (r.program_name || '') + '</td><td>' + (r.program_level || '') + '</td><td class="' + cn(r.personnel_count) + '">' + (r.personnel_count || 0) + '</td><td class="' + cn(r.student_count) + '">' + (r.student_count || 0) + '</td><td class="' + cn(r.activity_count) + '">' + (r.activity_count || 0) + '</td><td class="' + cn(r.download_count) + '">' + (r.download_count || 0) + '</td><td class="' + cn(r.facility_count) + '">' + (r.facility_count || 0) + '</td><td>' + pub + '</td><td class="' + cn(r.news_count) + '">' + (r.news_count || 0) + '</td><td class="' + cn(r.research_count) + '">' + (r.research_count || 0) + '</td></tr>';
            });
            tbody += '<tr style="font-weight:600;background:var(--color-gray-100);"><td colspan="2">รวม</td><td class="cell-num">' + totals.personnel_count + '</td><td class="cell-num">' + totals.student_count + '</td><td class="cell-num">' + totals.activity_count + '</td><td class="cell-num">' + totals.download_count + '</td><td class="cell-num">' + totals.facility_count + '</td><td></td><td class="cell-num">' + totals.news_count + '</td><td class="cell-num">' + totals.research_count + '</td></tr></tbody>';
            var tableEl = document.getElementById('table-program-summary');
            if (tableEl) tableEl.innerHTML = rows.length ? '<table>' + thead + tbody + '</table>' : '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูลหลักสูตร</p>';
        });
    }

    function loadCertificates() {
        fetchApi('certificates').then(function(res) {
            if (!res.success || !res.data) return;
            var d = res.data;
            var rateEl = document.getElementById('barcode-claim-rate');
            if (rateEl && d.barcode_claim_rate) {
                var r = d.barcode_claim_rate;
                rateEl.innerHTML = '<div class="card" style="padding:1rem;"><h3 style="margin:0 0 0.5rem 0;font-size:1rem;">อัตรา Barcode ที่รับแล้ว</h3><p style="margin:0;font-size:0.875rem;">' + (r.claimed || 0) + ' / ' + (r.total || 0) + ' (' + (r.rate || 0) + '%)</p><div style="height:8px;background:var(--color-gray-200);border-radius:4px;overflow:hidden;margin-top:0.5rem;"><div style="height:100%;width:' + (r.rate || 0) + '%;background:var(--color-primary,#2563eb);"></div></div></div>';
            }
            donutChart('chart-cert-status', d.by_status.map(function(x) { return x.name; }), d.by_status.map(function(x) { return x.count; }));
            lineChart('chart-cert-month', (d.by_month || []).map(function(x) { return x.month; }), (d.by_month || []).map(function(x) { return x.count; }));
            var ceEl = document.getElementById('table-cert-events');
            if (ceEl) {
                if (!d.cert_events || !d.cert_events.length) ceEl.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>';
                else ceEl.innerHTML = '<table><thead><tr><th>กิจกรรม</th><th>วันที่</th><th>ออกแล้ว</th><th>รอ</th></tr></thead><tbody>' + d.cert_events.map(function(r) { return '<tr><td>' + (r.title || '') + '</td><td>' + (r.event_date || '') + '</td><td>' + (r.issued_count || 0) + '</td><td>' + (r.pending_count || 0) + '</td></tr>'; }).join('') + '</tbody></table>';
            }
            var beEl = document.getElementById('table-barcode-events');
            if (beEl) {
                if (!d.barcode_events || !d.barcode_events.length) beEl.innerHTML = '<p style="color:var(--color-gray-500);padding:1rem;">ไม่มีข้อมูล</p>';
                else beEl.innerHTML = '<table><thead><tr><th>กิจกรรม</th><th>วันที่</th><th>รวม</th><th>รับแล้ว</th></tr></thead><tbody>' + d.barcode_events.map(function(r) { return '<tr><td>' + (r.title || '') + '</td><td>' + (r.event_date || '') + '</td><td>' + (r.barcode_total || 0) + '</td><td>' + (r.barcode_claimed || 0) + '</td></tr>'; }).join('') + '</tbody></table>';
            }
        });
    }

    function showSection(sectionId) {
        document.querySelectorAll('.exec-section').forEach(function(el) { el.style.display = 'none'; });
        document.querySelectorAll('.exec-tab').forEach(function(t) { t.classList.remove('active'); });
        var panel = document.getElementById('exec-' + sectionId);
        var tab = document.querySelector('.exec-tab[data-section="' + sectionId + '"]');
        if (panel) panel.style.display = 'block';
        if (tab) tab.classList.add('active');
        if (sectionId === 'overview') loadOverview();
        else if (sectionId === 'personnel') loadPersonnel();
        else if (sectionId === 'programs') loadPrograms();
        else if (sectionId === 'news') loadNews();
        else if (sectionId === 'edoc') loadEdoc();
        else if (sectionId === 'certificates') loadCertificates();
        else if (sectionId === 'research') loadResearch();
        else if (sectionId === 'pageviews') loadPageviews();
        else if (sectionId === 'program-summary') loadProgramSummary();
    }

    document.querySelectorAll('.exec-tab').forEach(function(btn) {
        btn.addEventListener('click', function() { showSection(this.getAttribute('data-section')); });
    });
    var periodEl = document.getElementById('exec-period');
    if (periodEl) periodEl.addEventListener('change', function() {
        var sec = document.querySelector('.exec-tab.active');
        if (sec) showSection(sec.getAttribute('data-section'));
    });

    var researchRefreshBtn = document.getElementById('exec-research-refresh');
    if (researchRefreshBtn) researchRefreshBtn.addEventListener('click', function() {
        fetch(apiBase + 'research?refresh=1', { credentials: 'same-origin' }).then(function(r) { return r.json(); }).then(function(res) {
            if (res.success) loadResearch();
        });
    });

    loadOverview();
})();
</script>
<?= $this->endSection() ?>

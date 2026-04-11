<?= $this->extend('layouts/user_layout') ?>

<?= $this->section('content') ?>
<?php
$apiOk = !empty($api_configured);
$email = $sync_email ?? '';
?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <div class="w-1 h-6 bg-slate-600 rounded-full"></div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800">ซิงค์ CV กับ Research Record</h1>
            </div>
            <p class="text-sm text-gray-500 ml-3">จับคู่ด้วยอีเมล <strong class="text-gray-800"><?= esc($email) ?></strong> — เลือกฝั่งที่ชนะต่อแถวแล้วบันทึกลง newScience หรือใช้ดึง/ส่งทั้งหมด</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('dashboard/profile') ?>" class="text-sm px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">โปรไฟล์</a>
            <a href="<?= base_url('dashboard/profile/cv') ?>" class="text-sm px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50">จัดการ CV</a>
        </div>
    </div>

    <?php if (!$apiOk): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900 text-sm">
            <p class="font-semibold mb-2">ยังไม่ได้ตั้งค่า Research API</p>
            <p>ตั้งค่าใน <code class="bg-amber-100 px-1 rounded">.env</code>: <code class="bg-amber-100 px-1 rounded">RESEARCH_API_BASE_URL</code> (URL ฐานของ Research Record), <code class="bg-amber-100 px-1 rounded">RESEARCH_API_KEY</code>, และ faculty id/code ตาม <code class="bg-amber-100 px-1 rounded">ResearchApi</code></p>
            <p class="mt-2">แนะนำให้ตั้ง <code class="bg-amber-100 px-1 rounded">RESEARCH_SYNC_HMAC_SECRET</code> ให้ตรงกับฝั่ง Research Record (<code class="bg-amber-100 px-1 rounded">RESEARCH_SYNC_HMAC_SECRET</code>) เพื่อลงนามพารามิเตอร์ email+exp</p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
        <div class="flex flex-wrap gap-2">
            <button type="button" id="rrsync-btn-compare" class="px-4 py-2 rounded-lg bg-slate-700 text-white text-sm font-semibold hover:bg-slate-800 disabled:opacity-50" <?= $apiOk ? '' : 'disabled' ?>>โหลดเปรียบเทียบ</button>
            <button type="button" id="rrsync-btn-pull" class="px-4 py-2 rounded-lg border border-amber-500 text-amber-800 text-sm font-semibold hover:bg-amber-50 disabled:opacity-50" <?= $apiOk ? '' : 'disabled' ?>>ดึงทั้งหมดจาก RR → NS</button>
            <button type="button" id="rrsync-btn-push" class="px-4 py-2 rounded-lg border border-blue-600 text-blue-800 text-sm font-semibold hover:bg-blue-50 disabled:opacity-50" <?= $apiOk ? '' : 'disabled' ?>>ส่งทั้งหมดจาก NS → RR</button>
        </div>
        <p id="rrsync-status" class="text-sm text-gray-600"></p>

        <div id="rrsync-orcid-row" class="hidden border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">ORCID iD หลังรวม</p>
            <label class="inline-flex items-center gap-2 mr-6 text-sm"><input type="radio" name="orcid_choice" value="ns" checked> ใช้จาก newScience</label>
            <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="orcid_choice" value="rr"> ใช้จาก Research Record</label>
        </div>

        <div id="rrsync-table-wrap" class="hidden overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-gray-600">ประเภท</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">หัวข้อ</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">newScience</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">Research Record</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">ใช้ฝั่ง</th>
                    </tr>
                </thead>
                <tbody id="rrsync-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div id="rrsync-pub-wrap" class="hidden border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">ผลงานตีพิมพ์ (นำเข้าเป็นรายการ CV ใต้หัวข้อ research)</p>
            <div id="rrsync-pub-list" class="max-h-48 overflow-y-auto text-sm space-y-1"></div>
        </div>

        <button type="button" id="rrsync-btn-apply" class="hidden px-5 py-2.5 rounded-lg bg-yellow-500 text-gray-900 font-semibold hover:bg-yellow-600">บันทึกการเลือกลง newScience</button>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function () {
    var state = { ns_bundle: null, rr_bundle: null, publications: [], merge_rows: [] };

    function setStatus(t) {
        var el = document.getElementById('rrsync-status');
        if (el) el.textContent = t || '';
    }

    function defaultChoice(row) {
        if (row.kind === 'publication') return 'rr';
        if (row.has_rr && !row.has_ns) return 'rr';
        if (row.has_ns && !row.has_rr) return 'ns';
        return 'ns';
    }

    function renderTable() {
        var tb = document.getElementById('rrsync-tbody');
        var wrap = document.getElementById('rrsync-table-wrap');
        var applyBtn = document.getElementById('rrsync-btn-apply');
        var orcidRow = document.getElementById('rrsync-orcid-row');
        if (!tb || !wrap) return;
        tb.innerHTML = '';
        (state.merge_rows || []).forEach(function (row) {
            var tr = document.createElement('tr');
            tr.className = 'hover:bg-gray-50';
            var kindLabel = row.kind === 'section' ? 'หัวข้อ' : 'รายการ';
            var sel = document.createElement('select');
            sel.className = 'border border-gray-200 rounded-lg px-2 py-1 text-sm rrsync-choice';
            sel.dataset.id = row.id;
            [['ns', 'newScience'], ['rr', 'Research Record'], ['skip', 'ข้าม (ไม่รวม)']].forEach(function (opt) {
                var o = document.createElement('option');
                o.value = opt[0];
                o.textContent = opt[1];
                sel.appendChild(o);
            });
            sel.value = defaultChoice(row);
            tr.innerHTML = '<td class="px-3 py-2 text-gray-600">' + kindLabel + '</td>' +
                '<td class="px-3 py-2 font-medium text-gray-900">' + escapeHtml(row.title || '') + '</td>' +
                '<td class="px-3 py-2 text-gray-600 max-w-xs">' + escapeHtml(row.summary_ns || '—') + '</td>' +
                '<td class="px-3 py-2 text-gray-600 max-w-xs">' + escapeHtml(row.summary_rr || '—') + '</td>' +
                '<td class="px-3 py-2"></td>';
            tr.querySelector('td:last-child').appendChild(sel);
            tb.appendChild(tr);
        });
        wrap.classList.remove('hidden');
        orcidRow.classList.remove('hidden');
        applyBtn.classList.remove('hidden');
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderPubs() {
        var list = document.getElementById('rrsync-pub-list');
        var wrap = document.getElementById('rrsync-pub-wrap');
        if (!list || !wrap) return;
        list.innerHTML = '';
        if (!state.publications || state.publications.length === 0) {
            wrap.classList.add('hidden');
            return;
        }
        wrap.classList.remove('hidden');
        state.publications.forEach(function (p) {
            var id = 'pub|' + (p.external_key || '');
            var lab = document.createElement('label');
            lab.className = 'flex items-start gap-2 cursor-pointer';
            lab.innerHTML = '<input type="checkbox" class="rrsync-pub-imp mt-1" data-pub-id="' + escapeHtml(id) + '" checked>' +
                '<span>' + escapeHtml(p.title || '') + ' <span class="text-gray-400">' + escapeHtml(String(p.publication_year || '') + ' ' + String(p.doi || '')) + '</span></span>';
            list.appendChild(lab);
        });
    }

    async function postJson(url, body) {
        var res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: body ? JSON.stringify(body) : '{}',
            credentials: 'same-origin'
        });
        return res.json().catch(function () { return {}; });
    }

    document.getElementById('rrsync-btn-compare') && document.getElementById('rrsync-btn-compare').addEventListener('click', async function () {
        setStatus('กำลังโหลด…');
        var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/compare') ?>', {});
        if (!data.success) {
            setStatus(data.message || 'ผิดพลาด');
            return;
        }
        state.ns_bundle = data.ns_bundle;
        state.rr_bundle = data.rr_bundle;
        state.merge_rows = data.merge_rows || [];
        state.publications = data.publications || [];
        setStatus('NS hash: ' + (data.ns_hash || '').substring(0, 12) + '… | RR hash: ' + (data.rr_hash || '').substring(0, 12) + '…');
        renderTable();
        renderPubs();
    });

    document.getElementById('rrsync-btn-pull') && document.getElementById('rrsync-btn-pull').addEventListener('click', async function () {
        if (!confirm('แทนที่ CV ทั้งหมดบน newScience ด้วยข้อมูลจาก Research Record?')) return;
        setStatus('กำลังดึง…');
        var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/pull-all') ?>', {});
        setStatus(data.message || (data.success ? 'สำเร็จ' : 'ผิดพลาด'));
        if (data.success) location.reload();
    });

    document.getElementById('rrsync-btn-push') && document.getElementById('rrsync-btn-push').addEventListener('click', async function () {
        if (!confirm('แทนที่ CV ทั้งหมดบน Research Record ด้วยข้อมูลจาก newScience?')) return;
        setStatus('กำลังส่ง…');
        var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/push-all') ?>', {});
        setStatus(data.message || (data.success ? 'สำเร็จ' : 'ผิดพลาด'));
    });

    document.getElementById('rrsync-btn-apply') && document.getElementById('rrsync-btn-apply').addEventListener('click', async function () {
        if (!state.ns_bundle || !state.rr_bundle) {
            setStatus('กรุณากดโหลดเปรียบเทียบก่อน');
            return;
        }
        var decisions = [];
        document.querySelectorAll('select.rrsync-choice').forEach(function (sel) {
            decisions.push({ id: sel.dataset.id, choice: sel.value });
        });
        var orcidEl = document.querySelector('input[name="orcid_choice"]:checked');
        var pubChoices = [];
        document.querySelectorAll('.rrsync-pub-imp').forEach(function (cb) {
            pubChoices.push({ id: cb.getAttribute('data-pub-id'), choice: cb.checked ? 'rr' : 'skip' });
        });
        setStatus('กำลังบันทึก…');
        var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/apply') ?>', {
            ns_bundle: state.ns_bundle,
            rr_bundle: state.rr_bundle,
            decisions: decisions,
            orcid_choice: orcidEl ? orcidEl.value : 'ns',
            publications: state.publications,
            publication_choices: pubChoices
        });
        setStatus(data.message || '');
        if (data.success) location.reload();
    });
})();
</script>
<?= $this->endSection() ?>

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
                <div class="w-1 h-6 bg-emerald-600 rounded-full"></div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 text-balance">ซิงค์ CV จาก <span translate="no">กบศ</span></h1>
            </div>
            <p class="text-sm text-gray-500 ml-3">จับคู่ด้วยอีเมล <strong class="text-gray-800"><?= esc($email) ?></strong> — ใช้ปุ่ม <strong>ซิงค์ให้ตรงกัน</strong> เมื่อต้องการดึง CV/ผลงานจาก กบศ และส่งประวัติการศึกษา (ผลงานบันทึกที่ กบศ โดยตรง)</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?= base_url('dashboard/profile/cv') ?>"
               class="text-sm px-3 py-2 rounded-lg bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-yellow-500 focus-visible:ring-offset-2 transition-colors">จัดการ CV</a>
            <a href="<?= base_url('dashboard/profile') ?>"
               class="text-sm px-3 py-2 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-gray-400 focus-visible:ring-offset-2 transition-colors">โปรไฟล์</a>
        </div>
    </div>

    <?php if (!$apiOk): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 text-amber-900 text-sm">
            <p class="font-semibold mb-2">ยังไม่ได้ตั้งค่า Research API</p>
            <p>ตั้งค่าใน <code class="bg-amber-100 px-1 rounded">.env</code> สำหรับการเชื่อมกบศ: <code class="bg-amber-100 px-1 rounded">RESEARCH_API_BASE_URL</code>, <code class="bg-amber-100 px-1 rounded">RESEARCH_API_KEY</code> และต้องมีอย่างใดอย่างหนึ่งของ <code class="bg-amber-100 px-1 rounded">RESEARCH_API_FACULTY_ID</code> / <code class="bg-amber-100 px-1 rounded">RESEARCH_API_FACULTY_CODE</code> ให้ตรงกับคณะในระบบกบศ (ถ้าไม่ตรงจะได้ error <code class="bg-amber-100 px-1 rounded">FACULTY_NOT_FOUND</code>)</p>
            <p class="mt-2">แนะนำให้ตั้ง <code class="bg-amber-100 px-1 rounded">RESEARCH_SYNC_HMAC_SECRET</code> ให้ตรงกับฝั่ง กบศ (<code class="bg-amber-100 px-1 rounded">RESEARCH_SYNC_HMAC_SECRET</code>) เพื่อลงนามพารามิเตอร์ email+exp</p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-4">
        <?php if ($apiOk): ?>
            <div class="rounded-xl border border-violet-200 bg-violet-50/80 px-4 py-3 text-sm text-violet-950">
                <p class="font-semibold mb-1">ซิงค์ให้ตรงกัน (แนะนำ)</p>
                <p class="text-violet-900/90">ดึง CV แบบเสริม (ฐานข้อมูลคณะเป็นหลัก) → ดึงผลงานจาก กบศ (pull-only) → ส่งประวัติการศึกษาไป กบศ</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-950">
                <p class="font-semibold mb-1">เรื่องการลบ — อ่านก่อนกดซิงค์</p>
                <ul class="list-disc pl-5 space-y-1 text-amber-900/90">
                    <li><strong>ลบใน ฐานข้อมูลคณะ</strong> แล้วซิงค์: ถ้า กบศ ยังมีรายการนั้น อาจ<strong>กลับมา</strong>หลังดึงจาก กบศ (ลบผลงานใน CV จะถอนออกจาก catalog ที่ส่งไป กบศ ด้วย)</li>
                    <li><strong>ลบใน กบศ</strong> อย่างเดียว: ซิงค์อาจ<strong>ไม่ลบ</strong>ใน ฐานข้อมูลคณะ — ต้องลบใน CV หรือเลือกฝั่งในโหมดเปรียบเทียบ</li>
                    <li>เนื้อหาผลงานใหม่/แก้ไขทำที่ <strong>ระบบ กบศ</strong> — ปุ่มส่งด้านล่างส่งเฉพาะประวัติการศึกษา</li>
                    <li>อย่ากดซิงค์ซ้ำพร้อมกัน — รอจบรอบก่อน (มีล็อกดึง CV)</li>
                </ul>
            </div>
        <?php endif; ?>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" id="rrsync-btn-reconcile"
                    class="px-4 py-2.5 rounded-lg bg-violet-700 text-white text-sm font-semibold hover:bg-violet-800 disabled:opacity-50 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 transition-colors"
                    <?= $apiOk ? '' : 'disabled' ?>>ซิงค์ให้ตรงกัน (NS ↔ กบศ)</button>
            <button type="button" id="rrsync-btn-pull"
                    class="px-4 py-2.5 rounded-lg bg-emerald-700 text-white text-sm font-semibold hover:bg-emerald-800 disabled:opacity-50 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 transition-colors"
                    <?= $apiOk ? '' : 'disabled' ?>>ดึงทั้งหมดจาก <span translate="no">กบศ</span></button>
            <button type="button" id="rrsync-btn-compare"
                    class="px-4 py-2 rounded-lg border border-slate-300 bg-white text-slate-800 text-sm font-semibold hover:bg-slate-50 disabled:opacity-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2 transition-colors"
                    <?= $apiOk ? '' : 'disabled' ?>>เปรียบเทียบแล้วเลือกรายแถว</button>
            <button type="button" id="rrsync-btn-push"
                    class="px-4 py-2.5 rounded-lg bg-slate-800 text-white text-sm font-semibold hover:bg-slate-900 disabled:opacity-50 shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2 transition-colors"
                    <?= $apiOk ? '' : 'disabled' ?>>ส่งประวัติการศึกษาไป <span translate="no">กบศ</span></button>
        </div>
        <p class="text-xs text-gray-500">การส่งไป กบศ: อัปเดตหัวข้อ <strong>ประวัติการศึกษา</strong> จาก ฐานข้อมูลคณะเท่านั้น — ผลงานตีพิมพ์บันทึก/แก้ที่ กบศ จากหน้า CV</p>

        <p id="rrsync-status" class="text-sm text-gray-600" role="status" aria-live="polite"></p>

        <div id="rrsync-orcid-row" class="hidden border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">ORCID iD หลังรวม</p>
            <label class="inline-flex items-center gap-2 mr-6 text-sm"><input type="radio" name="orcid_choice" value="ns" checked> ใช้จาก ฐานข้อมูลคณะ</label>
            <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="orcid_choice" value="rr"> ใช้จาก กบศ</label>
        </div>

        <div id="rrsync-table-wrap" class="hidden overflow-x-auto border border-gray-200 rounded-xl">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-left">
                    <tr>
                        <th class="px-3 py-2 font-semibold text-gray-600">ประเภท</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">หัวข้อ</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">ฐานข้อมูลคณะ</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">กบศ</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">สถานะ</th>
                        <th class="px-3 py-2 font-semibold text-gray-600">ใช้ฝั่ง</th>
                    </tr>
                </thead>
                <tbody id="rrsync-tbody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div id="rrsync-pub-wrap" class="hidden border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">ผลงานจาก กบศ (เลือกนำเข้าเป็นรายการ CV ใต้หัวข้อ research)</p>
            <div id="rrsync-pub-list" class="max-h-48 overflow-y-auto text-sm space-y-1"></div>
        </div>

        <button type="button" id="rrsync-btn-apply"
                class="hidden px-5 py-2.5 rounded-lg bg-amber-500 text-gray-900 font-semibold hover:bg-amber-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 transition-colors">บันทึกการเลือกลง ฐานข้อมูลคณะ</button>
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
        if (row.suggested) return row.suggested;
        if (row.kind === 'publication') return 'rr';
        if (row.has_rr && !row.has_ns) return 'rr';
        if (row.has_ns && !row.has_rr) return 'ns';
        return 'ns';
    }

    function statusBadge(row) {
        var label = row.status_label || 'ไม่ทราบสถานะ';
        var cls = 'bg-slate-100 text-slate-700 border-slate-200';
        if (row.content_status === 'same') cls = 'bg-emerald-50 text-emerald-800 border-emerald-200';
        else if (row.newer_side === 'conflict') cls = 'bg-rose-50 text-rose-800 border-rose-200';
        else if (row.newer_side === 'ns') cls = 'bg-blue-50 text-blue-800 border-blue-200';
        else if (row.newer_side === 'rr') cls = 'bg-violet-50 text-violet-800 border-violet-200';
        else if (row.content_status === 'differ') cls = 'bg-amber-50 text-amber-800 border-amber-200';
        else if (row.presence === 'ns_only') cls = 'bg-blue-50 text-blue-800 border-blue-200';
        else if (row.presence === 'rr_only') cls = 'bg-violet-50 text-violet-800 border-violet-200';

        var title = [];
        if (row.updated_at_ns) title.push('NS: ' + row.updated_at_ns);
        if (row.updated_at_rr) title.push('กบศ: ' + row.updated_at_rr);

        return '<span class="inline-flex items-center rounded-full border px-2 py-0.5 text-xs font-semibold ' + cls + '"' +
            (title.length ? ' title="' + escapeHtml(title.join(' | ')) + '"' : '') + '>' + escapeHtml(label) + '</span>';
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
            [['ns', 'ฐานข้อมูลคณะ'], ['rr', 'กบศ'], ['skip', 'ข้าม (ไม่รวม)']].forEach(function (opt) {
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
                '<td class="px-3 py-2 whitespace-nowrap">' + statusBadge(row) + '</td>' +
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
        setStatus('แฮช ฐานข้อมูลคณะ: ' + (data.ns_hash || '').substring(0, 12) + '… | แฮช กบศ: ' + (data.rr_hash || '').substring(0, 12) + '…');
        renderTable();
        renderPubs();
    });

    var reconcileConfirm = 'ซิงค์ให้ NS กับ กบศ ตรงกัน?\n\n'
        + '1) ดึงจาก กบศ แบบเสริม (ข้อมูลที่กรอกใน ฐานข้อมูลคณะเป็นหลัก)\n'
        + '2) ดึงผลงานจาก กบศ (ไม่ส่งผลงานจาก ฐานข้อมูลคณะ)\n'
        + '3) ส่งประวัติการศึกษาไป กบศ\n\n'
        + 'คำเตือน: ถ้าลบใน ฐานข้อมูลคณะ แต่ กบศ ยังมี — รายการอาจกลับมา\n'
        + 'ถ้าลบใน กบศ อย่างเดียว — รายการอาจยังอยู่ใน ฐานข้อมูลคณะ';

    document.getElementById('rrsync-btn-reconcile') && document.getElementById('rrsync-btn-reconcile').addEventListener('click', async function () {
        if (!confirm(reconcileConfirm)) return;
        var btn = this;
        setStatus('กำลังซิงค์ให้ตรงกัน… (อาจใช้เวลาสักครู่)');
        btn.disabled = true;
        try {
            var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/reconcile-all') ?>', {});
            setStatus(data.message || (data.success ? 'สำเร็จ' : 'ผิดพลาด'));
            if (data.success) location.reload();
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('rrsync-btn-pull') && document.getElementById('rrsync-btn-pull').addEventListener('click', async function () {
        if (!confirm('ซิงค์จาก กบศ แบบเสริม (รักษาข้อมูลที่กรอกใน ฐานข้อมูลคณะ) และนำเข้าผลงานตีพิมพ์จาก กบศ (ถ้ามี)?')) return;
        var btn = this;
        setStatus('กำลังดึง…');
        btn.disabled = true;
        try {
            var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/pull-all') ?>', {});
            setStatus(data.message || (data.success ? 'สำเร็จ' : 'ผิดพลาด'));
            if (data.success) location.reload();
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('rrsync-btn-push') && document.getElementById('rrsync-btn-push').addEventListener('click', async function () {
        if (!confirm('ส่งประวัติการศึกษาจาก ฐานข้อมูลคณะ ไป กบศ?\n\nผลงานตีพิมพ์ไม่ถูกส่งจากปุ่มนี้ — ใช้ปุ่มเพิ่ม/แก้ที่ กบศ ในหน้า CV')) return;
        var btn = this;
        setStatus('กำลังส่งประวัติการศึกษา…');
        btn.disabled = true;
        try {
            var data = await postJson('<?= base_url('dashboard/profile/research-record-sync/push-all') ?>', {});
            setStatus(data.message || (data.success ? 'สำเร็จ' : 'ผิดพลาด'));
        } finally {
            btn.disabled = false;
        }
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
        var applyBtn = this;
        applyBtn.disabled = true;
        setStatus('กำลังบันทึก…');
        try {
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
        } finally {
            applyBtn.disabled = false;
        }
    });
})();
</script>
<?= $this->endSection() ?>

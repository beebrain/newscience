<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<div class="card" id="barcode-events-card">
    <div class="card-header">
        <h2>จัดการบาร์โค้ด (Barcode Events)</h2>
        <button type="button" class="btn btn-primary" id="btn-create-event">สร้าง Event</button>
    </div>
    <div class="card-body" style="padding: 0;" id="barcode-events-table-wrap">
        <?php if (empty($events)): ?>
            <div class="empty-state" id="barcode-events-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <h3 style="font-size: 1.125rem; color: var(--color-gray-700); margin-bottom: 0.5rem;">ยังไม่มี Event แจกบาร์โค้ด</h3>
                <p style="margin-bottom: 1rem;">สร้าง Event แรกเพื่อเริ่มนำเข้าบาร์โค้ดและระบุผู้มีสิทธิ์</p>
                <button type="button" class="btn btn-primary" id="btn-create-event-empty">สร้าง Event แรก</button>
            </div>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>วันที่</th>
                        <th>ชื่อ EVENT</th>
                        <th>บาร์โค้ด (ทั้งหมด/ผูกแล้ว)</th>
                        <th>ผู้มีสิทธิ์</th>
                        <th>สถานะ</th>
                        <th style="min-width: 340px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="barcode-events-tbody">
                    <?php foreach ($events as $ev): ?>
                        <?php if (!is_array($ev)) continue; ?>
                        <?php $eventJson = htmlspecialchars(json_encode([
                            'id' => (int)($ev['id'] ?? 0),
                            'title' => $ev['title'] ?? '',
                            'description' => $ev['description'] ?? '',
                            'event_date' => $ev['event_date'] ?? '',
                            'status' => $ev['status'] ?? 'draft',
                        ]), ENT_QUOTES, 'UTF-8'); ?>
                        <tr>
                            <td><?= esc($ev['event_date'] ?? '') ?></td>
                            <td><strong><?= esc($ev['title'] ?? '') ?></strong></td>
                            <td><?= (int)($ev['barcode_total'] ?? 0) ?> / <?= (int)($ev['barcode_assigned'] ?? 0) ?></td>
                            <td><?= (int)($ev['eligibles_count'] ?? 0) ?></td>
                            <td>
                                <span class="badge <?= ($ev['status'] ?? '') === 'active' ? 'badge-success' : (($ev['status'] ?? '') === 'closed' ? 'badge-secondary' : 'badge-warning') ?>">
                                    <?= esc($ev['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td style="white-space: normal;">
                                <div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">
                                    <button type="button" class="btn btn-secondary btn-sm" data-be-action="view" data-be-id="<?= (int)($ev['id'] ?? 0) ?>">ดู</button>
                                    <button type="button" class="btn btn-secondary btn-sm btn-edit-event" data-event="<?= $eventJson ?>">แก้ไข</button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-be-action="eligibles" data-be-id="<?= (int)($ev['id'] ?? 0) ?>">จัดการผู้มีสิทธิ์</button>
                                    <button type="button" class="btn btn-secondary btn-sm" data-be-action="barcodes" data-be-id="<?= (int)($ev['id'] ?? 0) ?>">จัดการบาร์โค้ด</button>
                                    <button type="button" class="btn btn-danger btn-sm" data-be-action="delete" data-be-id="<?= (int)($ev['id'] ?? 0) ?>">ลบ</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<!-- Modal สร้าง Event -->
<div class="modal-backdrop" id="modal-create" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-labelledby="modal-create-title">
        <div class="modal-header">
            <h3 id="modal-create-title">สร้าง Event แจกบาร์โค้ด</h3>
            <button type="button" class="modal-close" data-close="modal-create" aria-label="ปิด">&times;</button>
        </div>
        <form id="form-create-event">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="create_title" class="form-label">ชื่อ Event *</label>
                    <input type="text" id="create_title" name="title" class="form-control" required maxlength="500">
                </div>
                <div class="form-group">
                    <label for="create_description" class="form-label">รายละเอียด</label>
                    <textarea id="create_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="create_event_date" class="form-label">วันที่ *</label>
                    <input type="date" id="create_event_date" name="event_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="create_status" class="form-label">สถานะ *</label>
                    <select id="create_status" name="status" class="form-control" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <p id="create-error" class="form-text" style="color: var(--color-red, #b91c1c); display: none;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close="modal-create">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" id="btn-create-submit">สร้าง Event</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal แก้ไข Event -->
<div class="modal-backdrop" id="modal-edit" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-labelledby="modal-edit-title">
        <div class="modal-header">
            <h3 id="modal-edit-title">แก้ไข Event</h3>
            <button type="button" class="modal-close" data-close="modal-edit" aria-label="ปิด">&times;</button>
        </div>
        <form id="form-edit-event">
            <input type="hidden" name="event_id" id="edit_event_id" value="">
            <?= csrf_field() ?>
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_title" class="form-label">ชื่อ Event *</label>
                    <input type="text" id="edit_title" name="title" class="form-control" required maxlength="500">
                </div>
                <div class="form-group">
                    <label for="edit_description" class="form-label">รายละเอียด</label>
                    <textarea id="edit_description" name="description" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="edit_event_date" class="form-label">วันที่ *</label>
                    <input type="date" id="edit_event_date" name="event_date" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="edit_status" class="form-label">สถานะ *</label>
                    <select id="edit_status" name="status" class="form-control" required>
                        <option value="draft">Draft</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                    </select>
                </div>
                <p id="edit-error" class="form-text" style="color: var(--color-red, #b91c1c); display: none;"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-close="modal-edit">ยกเลิก</button>
                <button type="submit" class="btn btn-primary" id="btn-edit-submit">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal ดู Event -->
<div class="modal-backdrop" id="modal-view" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-labelledby="modal-view-title" style="max-width: 820px; max-height: 92vh;">
        <div class="modal-header">
            <h3 id="modal-view-title">รายละเอียด Event</h3>
            <button type="button" class="modal-close" data-close="modal-view" aria-label="ปิด">&times;</button>
        </div>
        <div class="modal-body" style="max-height: calc(92vh - 8rem); overflow-y: auto;">
            <p id="modal-view-loading" style="display: none;">กำลังโหลด…</p>
            <div id="modal-view-body" style="display: none;">
                <p><strong>วันที่:</strong> <span id="modal-view-date"></span> &nbsp; <strong>สถานะ:</strong> <span id="modal-view-status"></span></p>
                <p id="modal-view-desc-wrap" style="display: none;"><span id="modal-view-desc"></span></p>
                <p style="font-size: 0.9rem; color: var(--color-gray-600);">
                    บาร์โค้ดทั้งหมด: <span id="modal-view-bt"></span> | ผูกแล้ว: <span id="modal-view-ba"></span>
                    | ยืนยันรับแล้ว: <span id="modal-view-bc"></span> | ผู้มีสิทธิ์: <span id="modal-view-el"></span>
                </p>
                <h4 style="font-size: 1rem; margin: 1.25rem 0 0.5rem;">การจับคู่ผู้มีสิทธิ์กับบาร์โค้ด</h4>
                <p id="modal-view-pairings-truncated" class="form-text" style="display: none; color: var(--color-gray-600); font-size: 0.85rem;"></p>
                <p id="modal-view-pairings-empty" style="display: none; color: var(--color-gray-600); font-size: 0.9rem;">ยังไม่มีผู้มีสิทธิ์ใน Event นี้ — จะแสดงการจับคู่เมื่อมีรายชื่อผู้มีสิทธิ์</p>
                <div id="modal-view-pairings-wrap" style="display: none; overflow-x: auto;">
                    <table class="table" style="font-size: 0.875rem;">
                        <thead>
                            <tr>
                                <th>ผู้มีสิทธิ์</th>
                                <th>อีเมล</th>
                                <th>บาร์โค้ดที่ผูก</th>
                            </tr>
                        </thead>
                        <tbody id="modal-view-pairings-tbody"></tbody>
                    </table>
                </div>
                <div id="modal-view-other-wrap" style="display: none; margin-top: 1rem;">
                    <h4 style="font-size: 0.95rem; margin-bottom: 0.5rem; color: var(--color-red, #b91c1c);">การผูกกับผู้ที่ไม่อยู่ในรายการผู้มีสิทธิ์</h4>
                    <div style="overflow-x: auto;">
                        <table class="table" style="font-size: 0.875rem;">
                            <thead>
                                <tr>
                                    <th>ผู้ใช้</th>
                                    <th>อีเมล</th>
                                    <th>บาร์โค้ด</th>
                                    <th>หมายเหตุ</th>
                                </tr>
                            </thead>
                            <tbody id="modal-view-other-tbody"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <p id="modal-view-error" class="form-text" style="color: var(--color-red, #b91c1c); display: none;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-close="modal-view">ปิด</button>
        </div>
    </div>
</div>

<!-- Modal จัดการผู้มีสิทธิ์ -->
<div class="modal-backdrop" id="modal-eligibles" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-labelledby="modal-eligibles-title" style="max-width: 720px; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="modal-eligibles-title">จัดการผู้มีสิทธิ์</h3>
            <button type="button" class="modal-close" data-close="modal-eligibles" aria-label="ปิด">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modal-eligibles-loading" style="display: none;">กำลังโหลด…</p>
            <p id="modal-eligibles-error" class="form-text" style="color: var(--color-red, #b91c1c); display: none;"></p>
            <div id="modal-eligibles-form-block" style="display: none;">
                <div class="form-group student-autocomplete-wrap" style="margin-bottom: 1rem;">
                    <label for="eligibles_student_autocomplete" class="form-label">เพิ่มนักศึกษา (เลือกจากรายชื่อ)</label>
                    <input type="text" id="eligibles_student_autocomplete" class="form-control" placeholder="พิมพ์ชื่อ หรืออีเมล…" autocomplete="off">
                    <div id="eligibles_student_autocomplete_list" class="student-autocomplete-list" role="listbox" aria-hidden="true"></div>
                    <div style="font-size: 0.8rem; color: var(--color-gray-500); margin-top: 0.35rem;">
                        <span id="eligibles_student_selected_label" style="display: none;">เลือกแล้ว: <strong id="eligibles_student_selected_name"></strong> <a href="#" id="eligibles_student_clear_choice" style="margin-left: 0.5rem;">ล้างการเลือก</a></span>
                        <span id="eligibles_student_type_hint">พิมพ์เพื่อกรองรายชื่อ</span>
                    </div>
                    <input type="hidden" id="eligibles_student_user_id" value="">
                    <button type="button" class="btn btn-primary" id="eligibles_btn_add_id" style="margin-top: 0.5rem;">เพิ่มจากรายชื่อ</button>
                </div>
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label for="eligibles_emails" class="form-label">หรือเพิ่มด้วย Email (หนึ่งบรรทัดต่อหนึ่งอีเมล หรือคั่นด้วยคอมมา)</label>
                    <textarea id="eligibles_emails" class="form-control" rows="3" placeholder="user1@example.com"></textarea>
                    <button type="button" class="btn btn-primary" id="eligibles_btn_add_email" style="margin-top: 0.5rem;">เพิ่มจาก Email</button>
                </div>
            </div>
            <div id="modal-eligibles-table-wrap" style="display: none;">
                <h4 style="font-size: 1rem; margin-bottom: 0.5rem;">รายชื่อผู้มีสิทธิ์ (<span id="eligibles_count_label">0</span>)</h4>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>นักศึกษา</th>
                                <th>อีเมล</th>
                                <th style="width: 90px;">ลบ</th>
                            </tr>
                        </thead>
                        <tbody id="eligibles_tbody"></tbody>
                    </table>
                </div>
            </div>
            <p id="modal-eligibles-msg" class="form-text" style="display: none;"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-close="modal-eligibles">ปิด</button>
        </div>
    </div>
</div>

<!-- Modal จัดการบาร์โค้ด -->
<div class="modal-backdrop" id="modal-barcodes" aria-hidden="true">
    <div class="modal-box" role="dialog" aria-labelledby="modal-barcodes-title" style="max-width: 900px; max-height: 92vh; overflow-y: auto;">
        <div class="modal-header">
            <h3 id="modal-barcodes-title">จัดการบาร์โค้ด</h3>
            <button type="button" class="modal-close" data-close="modal-barcodes" aria-label="ปิด">&times;</button>
        </div>
        <div class="modal-body">
            <p id="modal-barcodes-loading" style="display: none;">กำลังโหลด…</p>
            <p id="modal-barcodes-error" class="form-text" style="color: var(--color-red, #b91c1c); display: none;"></p>
            <div id="modal-barcodes-content" style="display: none;">
                <p class="form-label" style="margin-bottom: 0.5rem;">ขั้นที่ 1 — อัปโหลดไฟล์ แล้วระบบจะถอดรหัส (API หรือไฟล์ตรง)</p>
                <form id="form-modal-parse-file" enctype="multipart/form-data" style="margin-bottom: 1.25rem;">
                    <div class="form-group">
                        <label for="modal_barcode_file" class="form-label">เลือกไฟล์</label>
                        <input type="file" id="modal_barcode_file" name="barcode_file" class="form-control" accept=".pdf,.txt,.csv,.json,.xlsx,.xls,application/pdf,text/plain,text/csv,application/json,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel" style="max-width: 100%;">
                    </div>
                    <button type="submit" class="btn btn-primary" id="btn-modal-parse-file">อัปโหลดและถอดข้อมูล</button>
                </form>
                <p id="modal-parse-msg" class="form-text" style="display: none;"></p>

                <p class="form-label" style="margin-bottom: 0.5rem;">ขั้นที่ 2 — ตรวจสอบรายการรหัส แล้วกดนำเข้า</p>
                <form id="form-modal-import-barcode">
                    <div class="form-group">
                        <label for="modal_json_barcodes" class="form-label">รายการรหัส (หนึ่งบรรทัดต่อหนึ่งรหัส หรือ JSON)</label>
                        <textarea id="modal_json_barcodes" name="json_barcodes" class="form-control" rows="6" placeholder="BC0001&#10;BC0002"></textarea>
                    </div>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                        <button type="submit" class="btn btn-primary" id="btn-modal-import">นำเข้า Barcode</button>
                        <button type="button" class="btn btn-secondary btn-sm" id="btn-modal-load-dummy">โหลด Dummy ทดสอบ</button>
                    </div>
                </form>
                <p id="modal-import-msg" class="form-text" style="display: none; margin-top: 0.5rem;"></p>

                <h4 style="font-size: 1rem; margin: 1.25rem 0 0.5rem;">รายการบาร์โค้ด</h4>
                <p id="modal-barcodes-summary" style="font-size: 0.875rem; color: var(--color-gray-600);"></p>
                <div style="overflow-x: auto; max-height: 280px; overflow-y: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>รหัส</th>
                                <th>ผูก</th>
                                <th>ยืนยันรับ</th>
                                <th style="min-width: 160px;">การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody id="modal_barcodes_tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-close="modal-barcodes">ปิด</button>
        </div>
    </div>
</div>

<style>
.student-autocomplete-wrap { position: relative; }
.student-autocomplete-list {
    position: absolute;
    left: 0;
    right: 0;
    top: 100%;
    margin-top: 2px;
    max-height: 220px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid var(--color-gray-300, #d1d5db);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 200;
    display: none;
    list-style: none;
    margin: 0;
    padding: 0.25rem 0;
}
.student-autocomplete-list.show { display: block; }
.student-autocomplete-list [role="option"] {
    padding: 0.5rem 1rem;
    cursor: pointer;
    font-size: 0.9rem;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}
.student-autocomplete-list [role="option"]:hover,
.student-autocomplete-list [role="option"].highlight { background: #f1f5f9; }
</style>

<script>
(function() {
    var baseUrl = '<?= base_url() ?>';
    var saBase = baseUrl + 'student-admin/barcode-events/';
    var storeUrl = saBase + 'store';
    var updateUrlBase = saBase + 'update/';
    var ajaxEventsTableUrl = saBase + 'ajax/events-table';
    var ajaxEventUrl = saBase + 'ajax/event/';
    var ajaxBarcodesUrl = saBase + 'ajax/barcodes/';
    var ajaxEligiblesDataUrl = saBase + 'ajax/eligibles-data/';
    var ajaxDeleteEventUrl = saBase + 'ajax/delete-event/';
    var eventCache = {};

    function openModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.add('is-open');
            el.setAttribute('aria-hidden', 'false');
        }
    }

    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.classList.remove('is-open');
            el.setAttribute('aria-hidden', 'true');
        }
    }

    function getCsrf() {
        var input = document.querySelector('input[name="csrf_test_name"]');
        return input ? input.value : '';
    }

    function ajaxHeaders() {
        return { 'X-Requested-With': 'XMLHttpRequest' };
    }

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        var d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }

    /** แสดงรายการบาร์โค้ดที่ผูกกับผู้มีสิทธิ์ (HTML สำหรับใส่ใน td) */
    function formatPairingBarcodesCell(barcodes) {
        if (!barcodes || barcodes.length === 0) {
            return '<span style="color: var(--color-gray-500);">ยังไม่ได้รับบาร์โค้ด</span>';
        }
        return barcodes.map(function(b) {
            var asg = b.assigned_at ? escapeHtml(String(b.assigned_at)) : '—';
            var claim = b.claimed_at
                ? ('ยืนยันรับแล้ว (' + escapeHtml(String(b.claimed_at)) + ')')
                : '<span style="color: var(--color-gray-600);">รอยืนยันรับ</span>';
            return '<div style="margin-bottom: 0.35rem;"><code>' + escapeHtml(String(b.code || '')) + '</code> — ผูกเมื่อ ' + asg + ' · ' + claim + '</div>';
        }).join('');
    }

    function renderViewPairings(res) {
        var rows = res.eligible_pairings || [];
        var other = res.other_assignments || [];
        var trunc = document.getElementById('modal-view-pairings-truncated');
        var empty = document.getElementById('modal-view-pairings-empty');
        var wrap = document.getElementById('modal-view-pairings-wrap');
        var tbody = document.getElementById('modal-view-pairings-tbody');
        var otherWrap = document.getElementById('modal-view-other-wrap');
        var otherTb = document.getElementById('modal-view-other-tbody');

        if (res.pairings_truncated) {
            trunc.textContent = 'แสดงผู้มีสิทธิ์เพียง 500 รายการแรก — ตรวจสอบเพิ่มเติมได้จากเมนูจัดการผู้มีสิทธิ์';
            trunc.style.display = 'block';
        } else {
            trunc.style.display = 'none';
        }

        tbody.innerHTML = '';
        if (rows.length === 0) {
            empty.style.display = 'block';
            wrap.style.display = 'none';
        } else {
            empty.style.display = 'none';
            wrap.style.display = 'block';
            rows.forEach(function(row) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + escapeHtml(row.display_name || '') + '</td><td>' + escapeHtml(row.email || '') + '</td><td>' + formatPairingBarcodesCell(row.barcodes) + '</td>';
                tbody.appendChild(tr);
            });
        }

        otherTb.innerHTML = '';
        if (other.length === 0) {
            otherWrap.style.display = 'none';
        } else {
            otherWrap.style.display = 'block';
            other.forEach(function(row) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td>' + escapeHtml(row.display_name || '') + '</td><td>' + escapeHtml(row.email || '') + '</td><td>' + formatPairingBarcodesCell(row.barcodes) + '</td><td>' + escapeHtml(row.note || '') + '</td>';
                otherTb.appendChild(tr);
            });
        }
    }

    function badgeClass(status) {
        if (status === 'active') return 'badge-success';
        if (status === 'closed') return 'badge-secondary';
        return 'badge-warning';
    }

    function cacheEvents(events) {
        eventCache = {};
        (events || []).forEach(function(ev) {
            var id = parseInt(ev.id, 10);
            if (id) eventCache[id] = ev;
        });
    }

    function buildEventRowHtml(ev) {
        var id = parseInt(ev.id, 10);
        var eventPayload = {
            id: id,
            title: ev.title || '',
            description: ev.description || '',
            event_date: ev.event_date || '',
            status: ev.status || 'draft'
        };
        var eventJson = escapeHtml(JSON.stringify(eventPayload));
        var total = parseInt(ev.barcode_total, 10) || 0;
        var assigned = parseInt(ev.barcode_assigned, 10) || 0;
        var elig = parseInt(ev.eligibles_count, 10) || 0;
        var st = ev.status || 'draft';
        return '<tr>' +
            '<td>' + escapeHtml(ev.event_date || '') + '</td>' +
            '<td><strong>' + escapeHtml(ev.title || '') + '</strong></td>' +
            '<td>' + total + ' / ' + assigned + '</td>' +
            '<td>' + elig + '</td>' +
            '<td><span class="badge ' + badgeClass(st) + '">' + escapeHtml(st) + '</span></td>' +
            '<td style="white-space: normal;"><div style="display: flex; flex-wrap: wrap; gap: 0.35rem;">' +
            '<button type="button" class="btn btn-secondary btn-sm" data-be-action="view" data-be-id="' + id + '">ดู</button>' +
            '<button type="button" class="btn btn-secondary btn-sm btn-edit-event" data-event="' + eventJson + '">แก้ไข</button>' +
            '<button type="button" class="btn btn-secondary btn-sm" data-be-action="eligibles" data-be-id="' + id + '">จัดการผู้มีสิทธิ์</button>' +
            '<button type="button" class="btn btn-secondary btn-sm" data-be-action="barcodes" data-be-id="' + id + '">จัดการบาร์โค้ด</button>' +
            '<button type="button" class="btn btn-danger btn-sm" data-be-action="delete" data-be-id="' + id + '">ลบ</button>' +
            '</div></td></tr>';
    }

    function renderEventsWrap(events) {
        var wrap = document.getElementById('barcode-events-table-wrap');
        if (!wrap) return;
        cacheEvents(events);
        if (!events || events.length === 0) {
            wrap.innerHTML = '<div class="empty-state" id="barcode-events-empty">' +
                '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>' +
                '<h3 style="font-size: 1.125rem; color: var(--color-gray-700); margin-bottom: 0.5rem;">ยังไม่มี Event แจกบาร์โค้ด</h3>' +
                '<p style="margin-bottom: 1rem;">สร้าง Event แรกเพื่อเริ่มนำเข้าบาร์โค้ดและระบุผู้มีสิทธิ์</p>' +
                '<button type="button" class="btn btn-primary" id="btn-create-event-empty">สร้าง Event แรก</button></div>';
            var btnE = document.getElementById('btn-create-event-empty');
            if (btnE) btnE.addEventListener('click', function() { openModal('modal-create'); });
            return;
        }
        var rows = events.map(buildEventRowHtml).join('');
        wrap.innerHTML = '<table class="table"><thead><tr>' +
            '<th>วันที่</th><th>ชื่อ EVENT</th><th>บาร์โค้ด (ทั้งหมด/ผูกแล้ว)</th><th>ผู้มีสิทธิ์</th><th>สถานะ</th><th style="min-width: 340px;">จัดการ</th></tr></thead>' +
            '<tbody id="barcode-events-tbody">' + rows + '</tbody></table>';
        bindEditButtonsIn(wrap);
    }

    function refreshEventsTable() {
        return fetch(ajaxEventsTableUrl, { headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.events) {
                    renderEventsWrap(res.events);
                }
            })
            .catch(function() {});
    }

    function bindEditButtonsIn(root) {
        root.querySelectorAll('.btn-edit-event').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var data = this.getAttribute('data-event');
                if (!data) return;
                try {
                    var ev = JSON.parse(data);
                    document.getElementById('edit_event_id').value = ev.id || '';
                    document.getElementById('edit_title').value = ev.title || '';
                    document.getElementById('edit_description').value = ev.description || '';
                    document.getElementById('edit_event_date').value = ev.event_date || '';
                    document.getElementById('edit_status').value = ev.status || 'draft';
                    document.getElementById('edit-error').style.display = 'none';
                    openModal('modal-edit');
                } catch (e) {}
            });
        });
    }

    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            closeModal(this.getAttribute('data-close'));
        });
    });

    document.getElementById('btn-create-event').addEventListener('click', function() { openModal('modal-create'); });
    var btnEmpty0 = document.getElementById('btn-create-event-empty');
    if (btnEmpty0) btnEmpty0.addEventListener('click', function() { openModal('modal-create'); });

    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    bindEditButtonsIn(document);

    document.getElementById('barcode-events-card').addEventListener('click', function(e) {
        var actBtn = e.target.closest('[data-be-action]');
        if (!actBtn || !document.getElementById('barcode-events-table-wrap').contains(actBtn)) return;
        var action = actBtn.getAttribute('data-be-action');
        var bid = parseInt(actBtn.getAttribute('data-be-id'), 10);
        if (!bid) return;
        if (action === 'view') {
            openViewModal(bid);
        } else if (action === 'eligibles') {
            openEligiblesModal(bid);
        } else if (action === 'barcodes') {
            openBarcodesModal(bid);
        } else if (action === 'delete') {
            if (!confirm('ลบ Event นี้และบาร์โค้ดทั้งหมด?')) return;
            var fd = new FormData();
            fd.set('csrf_test_name', getCsrf());
            fetch(ajaxDeleteEventUrl + bid, { method: 'POST', body: fd, headers: ajaxHeaders() })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) refreshEventsTable();
                    else alert(res.error || 'ลบไม่สำเร็จ');
                })
                .catch(function() { alert('ลบไม่สำเร็จ'); });
        }
    });

    function openViewModal(id) {
        document.getElementById('modal-view-loading').style.display = '';
        document.getElementById('modal-view-body').style.display = 'none';
        document.getElementById('modal-view-error').style.display = 'none';
        openModal('modal-view');
        fetch(ajaxEventUrl + id, { headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                document.getElementById('modal-view-loading').style.display = 'none';
                if (!res.success || !res.event) {
                    document.getElementById('modal-view-error').textContent = res.error || 'โหลดไม่สำเร็จ';
                    document.getElementById('modal-view-error').style.display = 'block';
                    return;
                }
                var ev = res.event;
                document.getElementById('modal-view-title').textContent = ev.title || 'รายละเอียด Event';
                document.getElementById('modal-view-date').textContent = ev.event_date || '—';
                document.getElementById('modal-view-status').textContent = ev.status || '—';
                var desc = (ev.description || '').trim();
                var dw = document.getElementById('modal-view-desc-wrap');
                if (desc) {
                    document.getElementById('modal-view-desc').innerHTML = escapeHtml(desc).replace(/\n/g, '<br>');
                    dw.style.display = 'block';
                } else {
                    dw.style.display = 'none';
                }
                document.getElementById('modal-view-bt').textContent = String(ev.barcode_total ?? 0);
                document.getElementById('modal-view-ba').textContent = String(ev.barcode_assigned ?? 0);
                document.getElementById('modal-view-bc').textContent = String(ev.barcode_claimed ?? 0);
                document.getElementById('modal-view-el').textContent = String(ev.eligibles_count ?? 0);
                renderViewPairings(res);
                document.getElementById('modal-view-body').style.display = 'block';
            })
            .catch(function() {
                document.getElementById('modal-view-loading').style.display = 'none';
                document.getElementById('modal-view-error').textContent = 'โหลดไม่สำเร็จ';
                document.getElementById('modal-view-error').style.display = 'block';
            });
    }

    var currentEligibleEventId = null;
    var eligibleStudentsPool = [];
    var eligiblesHighlightIdx = -1;

    function filterEligibleStudents(q) {
        q = (q || '').trim().toLowerCase();
        if (q === '') return eligibleStudentsPool.slice(0, 20);
        return eligibleStudentsPool.filter(function(s) {
            var email = (s.email || '').toLowerCase();
            var display = (s.display_name || '').toLowerCase();
            return email.indexOf(q) !== -1 || display.indexOf(q) !== -1;
        }).slice(0, 20);
    }

    function showEligibleList(items) {
        var listEl = document.getElementById('eligibles_student_autocomplete_list');
        if (!listEl) return;
        listEl.innerHTML = '';
        listEl.setAttribute('aria-hidden', 'false');
        listEl.classList.add('show');
        items.forEach(function(s) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.role = 'option';
            btn.textContent = s.display_name || s.email || '';
            btn.dataset.id = String(s.id);
            btn.dataset.display = s.display_name || s.email || '';
            listEl.appendChild(btn);
        });
        eligiblesHighlightIdx = -1;
    }

    function hideEligibleList() {
        var listEl = document.getElementById('eligibles_student_autocomplete_list');
        if (!listEl) return;
        listEl.classList.remove('show');
        listEl.setAttribute('aria-hidden', 'true');
        listEl.innerHTML = '';
        eligiblesHighlightIdx = -1;
    }

    function selectEligibleStudent(id, displayName) {
        document.getElementById('eligibles_student_user_id').value = id || '';
        var input = document.getElementById('eligibles_student_autocomplete');
        if (input) input.value = displayName || '';
        var selLabel = document.getElementById('eligibles_student_selected_label');
        var selName = document.getElementById('eligibles_student_selected_name');
        var hint = document.getElementById('eligibles_student_type_hint');
        if (id && selLabel && selName) {
            selName.textContent = displayName || '';
            selLabel.style.display = '';
            if (hint) hint.style.display = 'none';
        } else {
            if (selLabel) selLabel.style.display = 'none';
            if (hint) hint.style.display = '';
        }
        hideEligibleList();
    }

    function renderEligiblesTable(rows) {
        var tb = document.getElementById('eligibles_tbody');
        if (!tb) return;
        tb.innerHTML = '';
        (rows || []).forEach(function(row) {
            var tr = document.createElement('tr');
            tr.innerHTML = '<td>' + escapeHtml(row.display_name) + '</td><td>' + escapeHtml(row.email) + '</td><td>' +
                '<button type="button" class="btn btn-danger btn-sm eligibles-remove" data-sid="' + row.student_user_id + '">ลบ</button></td>';
            tb.appendChild(tr);
        });
        var c = document.getElementById('eligibles_count_label');
        if (c) c.textContent = String((rows || []).length);
    }

    function loadEligiblesModalData(id) {
        document.getElementById('modal-eligibles-loading').style.display = '';
        document.getElementById('modal-eligibles-error').style.display = 'none';
        document.getElementById('modal-eligibles-form-block').style.display = 'none';
        document.getElementById('modal-eligibles-table-wrap').style.display = 'none';
        document.getElementById('modal-eligibles-msg').style.display = 'none';
        return fetch(ajaxEligiblesDataUrl + id, { headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                document.getElementById('modal-eligibles-loading').style.display = 'none';
                if (!res.success) {
                    document.getElementById('modal-eligibles-error').textContent = res.error || 'โหลดไม่สำเร็จ';
                    document.getElementById('modal-eligibles-error').style.display = 'block';
                    if (res.event) {
                        document.getElementById('modal-eligibles-title').textContent = 'จัดการผู้มีสิทธิ์ — ' + (res.event.title || '');
                    }
                    return;
                }
                var ev = res.event;
                document.getElementById('modal-eligibles-title').textContent = 'จัดการผู้มีสิทธิ์ — ' + (ev.title || '');
                eligibleStudentsPool = res.students || [];
                selectEligibleStudent('', '');
                document.getElementById('eligibles_emails').value = '';
                document.getElementById('modal-eligibles-form-block').style.display = 'block';
                document.getElementById('modal-eligibles-table-wrap').style.display = 'block';
                renderEligiblesTable(res.eligibles || []);
            })
            .catch(function() {
                document.getElementById('modal-eligibles-loading').style.display = 'none';
                document.getElementById('modal-eligibles-error').textContent = 'โหลดไม่สำเร็จ';
                document.getElementById('modal-eligibles-error').style.display = 'block';
            });
    }

    function openEligiblesModal(id) {
        currentEligibleEventId = id;
        openModal('modal-eligibles');
        loadEligiblesModalData(id);
    }

    var eligInput = document.getElementById('eligibles_student_autocomplete');
    var eligList = document.getElementById('eligibles_student_autocomplete_list');
    if (eligInput && eligList) {
        eligInput.addEventListener('input', function() {
            showEligibleList(filterEligibleStudents(this.value));
        });
        eligInput.addEventListener('focus', function() {
            showEligibleList(filterEligibleStudents(this.value));
        });
        eligInput.addEventListener('keydown', function(e) {
            if (!eligList.classList.contains('show')) return;
            var opts = eligList.querySelectorAll('[role="option"]');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                eligiblesHighlightIdx = eligiblesHighlightIdx < opts.length - 1 ? eligiblesHighlightIdx + 1 : 0;
                if (opts[eligiblesHighlightIdx]) {
                    opts[eligiblesHighlightIdx].classList.add('highlight');
                    opts[eligiblesHighlightIdx].scrollIntoView({ block: 'nearest' });
                    opts.forEach(function(o, i) { if (i !== eligiblesHighlightIdx) o.classList.remove('highlight'); });
                }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                eligiblesHighlightIdx = eligiblesHighlightIdx > 0 ? eligiblesHighlightIdx - 1 : Math.max(0, opts.length - 1);
                if (opts[eligiblesHighlightIdx]) {
                    opts[eligiblesHighlightIdx].classList.add('highlight');
                    opts[eligiblesHighlightIdx].scrollIntoView({ block: 'nearest' });
                    opts.forEach(function(o, i) { if (i !== eligiblesHighlightIdx) o.classList.remove('highlight'); });
                }
            } else if (e.key === 'Enter' && eligiblesHighlightIdx >= 0 && opts[eligiblesHighlightIdx]) {
                e.preventDefault();
                var o = opts[eligiblesHighlightIdx];
                selectEligibleStudent(o.dataset.id, o.dataset.display);
            } else if (e.key === 'Escape') {
                hideEligibleList();
            }
        });
        eligList.addEventListener('click', function(e) {
            var opt = e.target.closest('[role="option"]');
            if (!opt) return;
            selectEligibleStudent(opt.dataset.id, opt.dataset.display);
        });
    }
    document.addEventListener('click', function(e) {
        var listEl = document.getElementById('eligibles_student_autocomplete_list');
        var inp = document.getElementById('eligibles_student_autocomplete');
        if (listEl && listEl.classList.contains('show') && !listEl.contains(e.target) && e.target !== inp) {
            hideEligibleList();
        }
    });
    var eligClear = document.getElementById('eligibles_student_clear_choice');
    if (eligClear) {
        eligClear.addEventListener('click', function(e) {
            e.preventDefault();
            selectEligibleStudent('', '');
            if (eligInput) eligInput.focus();
        });
    }

    function showEligiblesMsg(text, isErr) {
        var m = document.getElementById('modal-eligibles-msg');
        m.textContent = text || '';
        m.style.display = text ? 'block' : 'none';
        m.style.color = isErr ? 'var(--color-red, #b91c1c)' : 'var(--color-gray-600)';
    }

    document.getElementById('eligibles_btn_add_id').addEventListener('click', function() {
        if (!currentEligibleEventId) return;
        var sid = parseInt(document.getElementById('eligibles_student_user_id').value, 10);
        if (!sid) {
            showEligiblesMsg('กรุณาเลือกนักศึกษา', true);
            return;
        }
        var fd = new FormData();
        fd.set('csrf_test_name', getCsrf());
        fd.set('by', 'id');
        fd.set('student_user_id', String(sid));
        fetch(saBase + 'add-eligible/' + currentEligibleEventId, { method: 'POST', body: fd, headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                showEligiblesMsg(res.message || (res.success ? 'สำเร็จ' : (res.error || 'ผิดพลาด')), !res.success);
                if (res.success) {
                    loadEligiblesModalData(currentEligibleEventId);
                    refreshEventsTable();
                }
            })
            .catch(function() { showEligiblesMsg('ส่งข้อมูลไม่สำเร็จ', true); });
    });

    document.getElementById('eligibles_btn_add_email').addEventListener('click', function() {
        if (!currentEligibleEventId) return;
        var emails = document.getElementById('eligibles_emails').value;
        var fd = new FormData();
        fd.set('csrf_test_name', getCsrf());
        fd.set('by', 'email');
        fd.set('emails', emails);
        fetch(saBase + 'add-eligible/' + currentEligibleEventId, { method: 'POST', body: fd, headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                showEligiblesMsg(res.message || '', !res.success);
                loadEligiblesModalData(currentEligibleEventId);
                refreshEventsTable();
            })
            .catch(function() { showEligiblesMsg('ส่งข้อมูลไม่สำเร็จ', true); });
    });

    document.getElementById('modal-eligibles').addEventListener('click', function(e) {
        var rm = e.target.closest('.eligibles-remove');
        if (!rm || !document.getElementById('modal-eligibles').contains(rm)) return;
        var sid = parseInt(rm.getAttribute('data-sid'), 10);
        if (!sid || !currentEligibleEventId) return;
        if (!confirm('ลบออกจากรายการมีสิทธิ์?')) return;
        var fd = new FormData();
        fd.set('csrf_test_name', getCsrf());
        fetch(saBase + 'remove-eligible/' + currentEligibleEventId + '/' + sid, { method: 'POST', body: fd, headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success) {
                    loadEligiblesModalData(currentEligibleEventId);
                    refreshEventsTable();
                } else alert(res.error || 'ลบไม่สำเร็จ');
            })
            .catch(function() { alert('ลบไม่สำเร็จ'); });
    });

    var currentBarcodeEventId = null;

    function renderBarcodesTable(rows, eventId) {
        var tb = document.getElementById('modal_barcodes_tbody');
        if (!tb) return;
        tb.innerHTML = '';
        (rows || []).forEach(function(b) {
            var tr = document.createElement('tr');
            var hasStudent = !!b.student_user_id;
            var claim = b.claimed_at ? ('ยืนยันแล้ว ' + escapeHtml(b.claimed_at)) : '—';
            var actions = '';
            if (hasStudent) {
                actions += '<button type="button" class="btn btn-secondary btn-sm bc-unassign" data-bid="' + b.id + '">ยกเลิกผูก</button> ';
            }
            actions += '<button type="button" class="btn btn-danger btn-sm bc-delete" data-bid="' + b.id + '">ลบ</button>';
            tr.innerHTML = '<td><code>' + escapeHtml(b.code) + '</code></td><td>' + (hasStudent ? ('ID ' + parseInt(b.student_user_id, 10)) : '—') + '</td><td>' + claim + '</td><td>' + actions + '</td>';
            tb.appendChild(tr);
        });
    }

    function loadBarcodesModalData(id) {
        document.getElementById('modal-barcodes-loading').style.display = '';
        document.getElementById('modal-barcodes-error').style.display = 'none';
        document.getElementById('modal-barcodes-content').style.display = 'none';
        document.getElementById('modal-parse-msg').style.display = 'none';
        document.getElementById('modal-import-msg').style.display = 'none';
        return fetch(ajaxBarcodesUrl + id, { headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                document.getElementById('modal-barcodes-loading').style.display = 'none';
                if (!res.success) {
                    document.getElementById('modal-barcodes-error').textContent = res.error || 'โหลดไม่สำเร็จ';
                    document.getElementById('modal-barcodes-error').style.display = 'block';
                    return;
                }
                var ev = res.event || {};
                document.getElementById('modal-barcodes-title').textContent = 'จัดการบาร์โค้ด — ' + (ev.title || '');
                var sum = document.getElementById('modal-barcodes-summary');
                var extra = res.truncated ? ' (แสดง ' + res.barcodes.length + ' จาก ' + res.total + ' รายการ)' : ' (' + (res.total || 0) + ' รายการ)';
                sum.textContent = 'ทั้งหมด: ' + (ev.barcode_total ?? 0) + ' | ผูกแล้ว: ' + (ev.barcode_assigned ?? 0) + extra;
                renderBarcodesTable(res.barcodes, id);
                document.getElementById('modal-barcodes-content').style.display = 'block';
            })
            .catch(function() {
                document.getElementById('modal-barcodes-loading').style.display = 'none';
                document.getElementById('modal-barcodes-error').textContent = 'โหลดไม่สำเร็จ';
                document.getElementById('modal-barcodes-error').style.display = 'block';
            });
    }

    function openBarcodesModal(id) {
        currentBarcodeEventId = id;
        document.getElementById('modal_json_barcodes').value = '';
        var f = document.getElementById('modal_barcode_file');
        if (f) f.value = '';
        openModal('modal-barcodes');
        loadBarcodesModalData(id);
    }

    document.getElementById('form-modal-parse-file').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentBarcodeEventId) return;
        var fi = document.getElementById('modal_barcode_file');
        if (!fi || !fi.files || !fi.files[0]) {
            document.getElementById('modal-parse-msg').textContent = 'เลือกไฟล์ก่อน';
            document.getElementById('modal-parse-msg').style.display = 'block';
            return;
        }
        var fd = new FormData();
        fd.set('csrf_test_name', getCsrf());
        fd.set('barcode_file', fi.files[0]);
        var msg = document.getElementById('modal-parse-msg');
        msg.style.display = 'none';
        fetch(saBase + 'parse-file/' + currentBarcodeEventId, { method: 'POST', body: fd, headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.success && res.codes) {
                    document.getElementById('modal_json_barcodes').value = (res.codes || []).join('\n');
                    msg.textContent = res.message || ('ได้ ' + res.codes.length + ' รหัส');
                    msg.style.color = 'var(--color-gray-700)';
                } else {
                    msg.textContent = res.error || 'ถอดข้อมูลไม่สำเร็จ';
                    msg.style.color = 'var(--color-red, #b91c1c)';
                }
                msg.style.display = 'block';
            })
            .catch(function() {
                msg.textContent = 'อัปโหลดไม่สำเร็จ';
                msg.style.color = 'var(--color-red, #b91c1c)';
                msg.style.display = 'block';
            });
    });

    document.getElementById('form-modal-import-barcode').addEventListener('submit', function(e) {
        e.preventDefault();
        if (!currentBarcodeEventId) return;
        var fd = new FormData();
        fd.set('csrf_test_name', getCsrf());
        fd.set('json_barcodes', document.getElementById('modal_json_barcodes').value);
        var im = document.getElementById('modal-import-msg');
        im.style.display = 'none';
        fetch(saBase + 'import/' + currentBarcodeEventId, { method: 'POST', body: fd, headers: ajaxHeaders() })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                im.textContent = res.message || (res.success ? 'นำเข้าแล้ว' : (res.error || 'ผิดพลาด'));
                im.style.color = res.success ? 'var(--color-gray-700)' : 'var(--color-red, #b91c1c)';
                im.style.display = 'block';
                if (res.success) {
                    loadBarcodesModalData(currentBarcodeEventId);
                    refreshEventsTable();
                }
            })
            .catch(function() {
                im.textContent = 'ส่งไม่สำเร็จ';
                im.style.color = 'var(--color-red, #b91c1c)';
                im.style.display = 'block';
            });
    });

    document.getElementById('btn-modal-load-dummy').addEventListener('click', function() {
        var count = prompt('จำนวนรหัส Dummy (1–500)', '20');
        if (count === null) return;
        count = Math.min(500, Math.max(1, parseInt(count, 10) || 20));
        fetch(baseUrl + 'api/barcode-dummy?count=' + count)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('modal_json_barcodes').value = JSON.stringify(data, null, 2);
            })
            .catch(function() { alert('โหลด Dummy ไม่สำเร็จ'); });
    });

    document.getElementById('modal-barcodes').addEventListener('click', function(e) {
        var u = e.target.closest('.bc-unassign');
        var d = e.target.closest('.bc-delete');
        var btn = u || d;
        if (!btn || !currentBarcodeEventId) return;
        var bid = parseInt(btn.getAttribute('data-bid'), 10);
        if (!bid) return;
        if (u) {
            if (!confirm('ยกเลิกการผูกบาร์โค้ดนี้?')) return;
            var fd = new FormData();
            fd.set('csrf_test_name', getCsrf());
            fetch(saBase + 'unassign/' + currentBarcodeEventId + '/' + bid, { method: 'POST', body: fd, headers: ajaxHeaders() })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        loadBarcodesModalData(currentBarcodeEventId);
                        refreshEventsTable();
                    } else alert(res.error || 'ไม่สำเร็จ');
                })
                .catch(function() { alert('ไม่สำเร็จ'); });
        } else {
            if (!confirm('ลบบาร์โค้ดนี้?')) return;
            var fd2 = new FormData();
            fd2.set('csrf_test_name', getCsrf());
            fetch(saBase + 'delete-barcode/' + currentBarcodeEventId + '/' + bid, { method: 'POST', body: fd2, headers: ajaxHeaders() })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        loadBarcodesModalData(currentBarcodeEventId);
                        refreshEventsTable();
                    } else alert(res.error || 'ไม่สำเร็จ');
                })
                .catch(function() { alert('ไม่สำเร็จ'); });
        }
    });

    document.getElementById('form-create-event').addEventListener('submit', function(e) {
        e.preventDefault();
        var errEl = document.getElementById('create-error');
        var submitBtn = document.getElementById('btn-create-submit');
        errEl.style.display = 'none';
        submitBtn.disabled = true;
        var formData = new FormData(this);
        formData.set('csrf_test_name', getCsrf());
        fetch(storeUrl, {
            method: 'POST',
            body: formData,
            headers: ajaxHeaders()
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeModal('modal-create');
                document.getElementById('form-create-event').reset();
                refreshEventsTable();
            } else {
                errEl.textContent = res.error || (res.errors ? Object.values(res.errors).flat().join(' ') : 'เกิดข้อผิดพลาด');
                errEl.style.display = 'block';
            }
        })
        .catch(function() {
            errEl.textContent = 'ส่งข้อมูลไม่สำเร็จ';
            errEl.style.display = 'block';
        })
        .finally(function() { submitBtn.disabled = false; });
    });

    document.getElementById('form-edit-event').addEventListener('submit', function(e) {
        e.preventDefault();
        var errEl = document.getElementById('edit-error');
        var submitBtn = document.getElementById('btn-edit-submit');
        var eventId = document.getElementById('edit_event_id').value;
        errEl.style.display = 'none';
        submitBtn.disabled = true;
        var formData = new FormData(this);
        formData.set('csrf_test_name', getCsrf());
        formData.delete('event_id');
        fetch(updateUrlBase + eventId, {
            method: 'POST',
            body: formData,
            headers: ajaxHeaders()
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeModal('modal-edit');
                refreshEventsTable();
            } else {
                errEl.textContent = res.error || (res.errors ? Object.values(res.errors).flat().join(' ') : 'เกิดข้อผิดพลาด');
                errEl.style.display = 'block';
            }
        })
        .catch(function() {
            errEl.textContent = 'ส่งข้อมูลไม่สำเร็จ';
            errEl.style.display = 'block';
        })
        .finally(function() { submitBtn.disabled = false; });
    });

    <?php if (!empty($events)): ?>
    cacheEvents(<?= json_encode($events, JSON_UNESCAPED_UNICODE) ?>);
    <?php endif; ?>
})();
</script>
<?= $this->endSection() ?>

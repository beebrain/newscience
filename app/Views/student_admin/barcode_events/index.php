<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2>จัดการบาร์โค้ด (Barcode Events)</h2>
        <button type="button" class="btn btn-primary" id="btn-create-event">สร้าง Event</button>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($events)): ?>
            <div class="empty-state">
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
                        <th>ชื่อ Event</th>
                        <th>บาร์โค้ด (ทั้งหมด/ผูกแล้ว)</th>
                        <th>ผู้มีสิทธิ์</th>
                        <th>สถานะ</th>
                        <th style="width: 220px;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
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
                            <td><?= (int)($ev['barcode_assigned'] ?? 0) ?> / <?= (int)($ev['barcode_total'] ?? 0) ?></td>
                            <td><?= (int)($ev['eligibles_count'] ?? 0) ?></td>
                            <td>
                                <span class="badge <?= ($ev['status'] ?? '') === 'active' ? 'badge-success' : (($ev['status'] ?? '') === 'closed' ? 'badge-secondary' : 'badge-warning') ?>">
                                    <?= esc($ev['status'] ?? 'draft') ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= base_url('student-admin/barcode-events/' . $ev['id']) ?>" class="btn btn-secondary btn-sm">ดู</a>
                                <button type="button" class="btn btn-secondary btn-sm btn-edit-event" data-event="<?= $eventJson ?>">แก้ไข</button>
                                <a href="<?= base_url('student-admin/barcode-events/eligibles/' . $ev['id']) ?>" class="btn btn-secondary btn-sm">ผู้มีสิทธิ์</a>
                                <a href="<?= base_url('student-admin/barcode-events/delete/' . $ev['id']) ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('ลบ Event นี้และบาร์โค้ดทั้งหมด?')">ลบ</a>
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

<script>
(function() {
    var baseUrl = '<?= base_url() ?>';
    var storeUrl = baseUrl + 'student-admin/barcode-events/store';
    var updateUrlBase = baseUrl + 'student-admin/barcode-events/update/';

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

    document.querySelectorAll('[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            closeModal(this.getAttribute('data-close'));
        });
    });

    document.getElementById('btn-create-event').addEventListener('click', function() { openModal('modal-create'); });
    var btnEmpty = document.getElementById('btn-create-event-empty');
    if (btnEmpty) btnEmpty.addEventListener('click', function() { openModal('modal-create'); });

    document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
        backdrop.addEventListener('click', function(e) {
            if (e.target === this) closeModal(this.id);
        });
    });

    document.querySelectorAll('.btn-edit-event').forEach(function(btn) {
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
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeModal('modal-create');
                window.location.reload();
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
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                closeModal('modal-edit');
                window.location.reload();
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
})();
</script>
<?= $this->endSection() ?>

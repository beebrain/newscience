<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css">

<div class="card calendar-card">
    <div class="card-header" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
        <h2 style="margin: 0;">ปฏิทินนัดหมายกิจกรรมผู้บริหาร</h2>
        <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
            <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.9375rem;">
                <span>แสดง:</span>
                <select id="calendarUserFilter" class="form-control" style="width: auto; min-width: 180px;">
                    <option value="">ทุกคน (รวม)</option>
                    <?php foreach ($users as $u): ?>
                    <option value="<?= (int) $u['uid'] ?>"><?= esc($u['name_th'] ?: $u['email']) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="button" class="btn btn-primary" id="btnNewEvent">
                + สร้างกิจกรรม
            </button>
        </div>
    </div>
    <div class="card-body" style="padding: 1rem;">
        <div id="calendar" style="min-height: 500px;"></div>
    </div>
</div>

<!-- Modal สร้าง/แก้ไข (ปุ่มบันทึก/ยกเลิก/ลบ อยู่ใน Card เดียวกับฟอร์ม) -->
<?= view('admin/components/modal_base', [
    'modal_id' => 'calendarEventModal',
    'title'    => 'สร้างกิจกรรม',
    'size'     => 'lg',
    'content'  => view('admin/calendar/_event_form', [
        'event'    => null,
        'users'    => $users,
        'is_admin' => $is_admin ?? true,
    ]),
]) ?>

<style>
.calendar-card .card-body { overflow: visible; }
#calendar { font-family: var(--font-primary, 'Sarabun', sans-serif); }
.fc { --fc-border-color: var(--dash-border, #e5e7eb); }
.fc .fc-button-primary { background: var(--color-primary, #eab308); border-color: var(--color-primary); color: #1a1a1a; }
.fc .fc-button-primary:hover { filter: brightness(1.1); }
.fc-toolbar-title { font-size: 1.25rem; }
.form-control { width: 100%; padding: 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 6px; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.25rem; font-weight: 500; }
.text-danger { color: #dc2626; }
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core/locales/th.global.min.js"></script>
<script>
(function() {
    var baseUrl = '<?= base_url() ?>';
    var apiBase = baseUrl + 'api/calendar/';
    var isAdmin = <?= !empty($is_admin) ? 'true' : 'false' ?>;

    var calendarEl = document.getElementById('calendar');
    var userFilter = document.getElementById('calendarUserFilter');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'th',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
        },
        buttonText: {
            today: 'วันนี้',
            month: 'เดือน',
            week: 'สัปดาห์',
            day: 'วัน',
            list: 'รายการ'
        },
        editable: true,
        selectable: true,
        selectMirror: true,
        dayMaxEvents: true,
        weekends: true,
        slotMinTime: '06:00:00',
        slotMaxTime: '22:00:00',
        events: function(info, successCallback, failureCallback) {
            var userId = userFilter ? userFilter.value : '';
            var url = apiBase + 'events?start=' + encodeURIComponent(info.startStr) + '&end=' + encodeURIComponent(info.endStr);
            if (userId) url += '&user_id=' + encodeURIComponent(userId);
            fetch(url, { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(events) {
                    if (Array.isArray(events)) successCallback(events);
                    else successCallback([]);
                })
                .catch(function() { successCallback([]); });
        },
        select: function(info) {
            openModal('calendarEventModal');
            document.getElementById('calendarEventModal_title').textContent = 'สร้างกิจกรรม';
            document.getElementById('calendarEventId').value = '';
            document.getElementById('cal_title').value = '';
            document.getElementById('cal_description').value = '';
            document.getElementById('cal_location').value = '';
            document.getElementById('cal_color').value = '#3b82f6';
            document.getElementById('cal_color_hex').textContent = '#3b82f6';
            document.getElementById('cal_all_day').checked = false;
            var start = info.start;
            var end = info.end;
            document.getElementById('cal_start').value = formatDateTimeLocal(start);
            document.getElementById('cal_end').value = formatDateTimeLocal(end);
            document.getElementById('cal_start').type = 'datetime-local';
            document.getElementById('cal_end').type = 'datetime-local';
            if (typeof window.setCalendarParticipantEmails === 'function') window.setCalendarParticipantEmails([]);
            setFormSubmitMode('create');
        },
        eventClick: function(info) {
            var id = info.event.id;
            fetch(apiBase + 'event/' + id, { credentials: 'same-origin' })
                .then(function(r) {
                    if (r.status === 403 || r.status === 404) { alert('ไม่สามารถแก้ไขได้'); return null; }
                    return r.json();
                })
                .then(function(event) {
                    if (!event) return;
                    openModal('calendarEventModal');
                    document.getElementById('calendarEventModal_title').textContent = 'แก้ไขกิจกรรม';
                    document.getElementById('calendarEventId').value = event.id;
                    document.getElementById('cal_title').value = event.title || '';
                    document.getElementById('cal_description').value = event.description || '';
                    document.getElementById('cal_location').value = event.location || '';
                    document.getElementById('cal_color').value = event.color || '#3b82f6';
                    document.getElementById('cal_color_hex').textContent = event.color || '#3b82f6';
                    document.getElementById('cal_all_day').checked = !!event.all_day;
                    document.getElementById('cal_start').value = event.start_datetime ? (event.start_datetime + '').replace(' ', 'T').slice(0, 16) : '';
                    document.getElementById('cal_end').value = event.end_datetime ? (event.end_datetime + '').replace(' ', 'T').slice(0, 16) : '';
                    if (typeof window.setCalendarParticipantEmails === 'function') {
                        window.setCalendarParticipantEmails(event.participant_emails || []);
                    }
                    setFormSubmitMode('update', event.id);
                    var delBtn = document.getElementById('calendarEventDelete');
                    if (delBtn) {
                        delBtn.style.display = 'inline-block';
                        delBtn.onclick = function() {
                            if (!confirm('ต้องการลบกิจกรรมนี้?')) return;
                            var f = new FormData();
                            f.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
                            fetch(apiBase + 'delete/' + event.id, { method: 'POST', credentials: 'same-origin', body: f })
                                .then(function(r) { return r.json(); })
                                .then(function(data) { if (data.success) { closeModal('calendarEventModal'); calendar.refetchEvents(); } else alert(data.error || 'ลบไม่สำเร็จ'); });
                        };
                    }
                });
        },
        eventDrop: function(info) {
            if (!info.event.id) return;
            var start = info.event.start;
            var end = info.event.end;
            var form = new FormData();
            form.append('title', info.event.title);
            form.append('start_datetime', start.toISOString().slice(0, 19).replace('T', ' '));
            form.append('end_datetime', end.toISOString().slice(0, 19).replace('T', ' '));
            form.append('all_day', info.event.allDay ? '1' : '');
            form.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fetch(apiBase + 'update/' + info.event.id, { method: 'POST', body: form, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) alert(data.error || 'อัปเดตไม่สำเร็จ');
                });
        },
        eventResize: function(info) {
            if (!info.event.id) return;
            var start = info.event.start;
            var end = info.event.end;
            var form = new FormData();
            form.append('title', info.event.title);
            form.append('start_datetime', start.toISOString().slice(0, 19).replace('T', ' '));
            form.append('end_datetime', end.toISOString().slice(0, 19).replace('T', ' '));
            form.append('all_day', info.event.allDay ? '1' : '');
            form.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
            fetch(apiBase + 'update/' + info.event.id, { method: 'POST', body: form, credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data.success) alert(data.error || 'อัปเดตไม่สำเร็จ');
                });
        }
    });

    function formatDateTimeLocal(d) {
        if (!d) return '';
        var y = d.getFullYear();
        var m = String(d.getMonth() + 1).padStart(2, '0');
        var day = String(d.getDate()).padStart(2, '0');
        var h = String(d.getHours()).padStart(2, '0');
        var min = String(d.getMinutes()).padStart(2, '0');
        return y + '-' + m + '-' + day + 'T' + h + ':' + min;
    }

    var calendarEventSubmit = document.getElementById('calendarEventSubmit');
    function setFormSubmitMode(mode, id) {
        calendarEventSubmit.onclick = function() {
            if (mode === 'create') submitCalendarEvent('create');
            else submitCalendarEvent('update', id);
        };
    }

    function submitCalendarEvent(mode, id) {
        var form = document.getElementById('calendarEventForm');
        var title = document.getElementById('cal_title').value.trim();
        if (!title) { alert('กรุณากรอกชื่อกิจกรรม'); return; }
        var start = document.getElementById('cal_start').value;
        var end = document.getElementById('cal_end').value;
        if (!start || !end) { alert('กรุณาเลือกวันเวลา'); return; }
        var formData = new FormData();
        formData.append('title', title);
        formData.append('description', document.getElementById('cal_description').value);
        formData.append('location', document.getElementById('cal_location').value);
        formData.append('color', document.getElementById('cal_color').value);
        formData.append('all_day', document.getElementById('cal_all_day').checked ? '1' : '0');
        formData.append('start_datetime', start.replace('T', ' '));
        formData.append('end_datetime', end.replace('T', ' '));
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        var participantInputs = document.querySelectorAll('input[name="participants[]"]');
        participantInputs.forEach(function(inp) {
            if (inp.value) formData.append('participants[]', inp.value);
        });
        var url = mode === 'create' ? apiBase + 'store' : apiBase + 'update/' + id;
        fetch(url, { method: 'POST', body: formData, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    closeModal('calendarEventModal');
                    calendar.refetchEvents();
                } else {
                    alert(data.error || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(function() { alert('เกิดข้อผิดพลาด'); });
    }

    if (userFilter) {
        userFilter.addEventListener('change', function() {
            calendar.refetchEvents();
        });
    }

    var delBtn = document.getElementById('calendarEventDelete');
    if (delBtn) delBtn.style.display = 'none';

    document.getElementById('btnNewEvent').addEventListener('click', function() {
        openModal('calendarEventModal');
        if (delBtn) delBtn.style.display = 'none';
        document.getElementById('calendarEventModal_title').textContent = 'สร้างกิจกรรม';
        document.getElementById('calendarEventId').value = '';
        document.getElementById('cal_title').value = '';
        document.getElementById('cal_description').value = '';
        document.getElementById('cal_location').value = '';
        document.getElementById('cal_color').value = '#3b82f6';
        document.getElementById('cal_color_hex').textContent = '#3b82f6';
        document.getElementById('cal_all_day').checked = false;
        var now = new Date();
        var end = new Date(now.getTime() + 60 * 60 * 1000);
        document.getElementById('cal_start').value = formatDateTimeLocal(now);
        document.getElementById('cal_end').value = formatDateTimeLocal(end);
        document.getElementById('cal_start').type = 'datetime-local';
        document.getElementById('cal_end').type = 'datetime-local';
        if (typeof window.setCalendarParticipantEmails === 'function') window.setCalendarParticipantEmails([]);
        setFormSubmitMode('create');
    });

    calendar.render();
})();
</script>
<?= $this->endSection() ?>

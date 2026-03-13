<?php
/**
 * Calendar Event Form - ใช้ใน modal สร้าง/แก้ไขกิจกรรม
 * @var array|null $event ข้อมูลกิจกรรม (null = สร้างใหม่)
 * @var array $users รายชื่อ user สำหรับ tag (admin ได้ทั้งหมด, user ได้แค่ตัวเอง)
 * @var bool $is_admin
 */
$isEdit = isset($event) && $event && ! empty($event['id']);
$participantEmails = $event['participant_emails'] ?? [];
?>
<div class="calendar-event-card" style="background: #fff; border-radius: 8px; border: 1px solid #e5e7eb;">
    <form id="calendarEventForm" method="post" action="" style="padding: 1.25rem;">
        <?= csrf_field() ?>
        <input type="hidden" name="event_id" id="calendarEventId" value="<?= $isEdit ? (int) $event['id'] : '' ?>">

        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="cal_title">ชื่อกิจกรรม <span class="text-danger">*</span></label>
            <input type="text" id="cal_title" name="title" class="form-control" required
                   value="<?= esc($event['title'] ?? '') ?>"
                   placeholder="เช่น ประชุมคณะกรรมการ">
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="cal_description">รายละเอียด</label>
            <textarea id="cal_description" name="description" class="form-control" rows="2"
                      placeholder="รายละเอียดเพิ่มเติม"><?= esc($event['description'] ?? '') ?></textarea>
        </div>

        <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
            <div class="form-group">
                <label for="cal_start">เริ่ม</label>
                <input type="datetime-local" id="cal_start" name="start_datetime" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="cal_end">สิ้นสุด</label>
                <input type="datetime-local" id="cal_end" name="end_datetime" class="form-control" required>
            </div>
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label style="display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" id="cal_all_day" name="all_day" value="1" <?= !empty($event['all_day']) ? 'checked' : '' ?>>
                ทั้งวัน (All day)
            </label>
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="cal_location">สถานที่</label>
            <input type="text" id="cal_location" name="location" class="form-control"
                   value="<?= esc($event['location'] ?? '') ?>"
                   placeholder="ห้องประชุม หรือลิงก์ออนไลน์">
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label for="cal_color">สี (แสดงบนปฏิทิน)</label>
            <input type="color" id="cal_color" name="color" value="<?= esc($event['color'] ?? '#3b82f6') ?>" style="width: 60px; height: 36px; padding: 2px; cursor: pointer;">
            <span id="cal_color_hex" style="margin-left: 0.5rem; font-size: 0.875rem;"><?= esc($event['color'] ?? '#3b82f6') ?></span>
        </div>

        <div class="form-group" style="margin-bottom: 1rem;">
            <label>ผู้เกี่ยวข้อง (Tag)</label>
            <?php if (!empty($is_admin) && count($users) > 1): ?>
            <div class="cal-participants-autocomplete">
                <div id="cal_participants_chips" class="cal-chips" style="display: flex; flex-wrap: wrap; gap: 0.35rem; min-height: 32px; padding: 0.35rem 0; margin-bottom: 0.35rem;"></div>
                <div style="position: relative;">
                    <input type="text" id="cal_participants_input" class="form-control" autocomplete="off"
                           placeholder="พิมพ์ชื่อหรืออีเมลเพื่อเพิ่มผู้เกี่ยวข้อง...">
                    <ul id="cal_participants_dropdown" class="cal-autocomplete-dropdown" style="display: none;"></ul>
                </div>
                <div id="cal_participants_hidden" class="cal-participants-hidden"></div>
            </div>
            <small class="form-text text-muted">พิมพ์แล้วเลือกจากรายการ หรือกด Enter เพื่อเพิ่ม</small>
            <?php elseif (!empty($users)): ?>
            <?php $u = $users[0]; ?>
            <input type="hidden" name="participants[]" value="<?= esc($u['email'] ?? '') ?>">
            <p class="form-control-static" style="margin: 0; padding: 0.5rem 0;"><?= esc($u['name_th'] ?: ($u['email'] ?? '')) ?> (เฉพาะตนเอง)</p>
            <?php endif; ?>
        </div>

        <div class="calendar-event-form-actions" style="position: sticky; bottom: 0; display: flex; align-items: center; justify-content: flex-end; gap: 0.75rem; padding-top: 1rem; margin-top: 1rem; margin-left: -1.25rem; margin-right: -1.25rem; margin-bottom: -1.25rem; padding-left: 1.25rem; padding-right: 1.25rem; padding-bottom: 1.25rem; border-top: 1px solid #e5e7eb; background: #fff; z-index: 1;">
            <button type="button" class="btn btn-danger" id="calendarEventDelete" style="margin-right: auto; display: none;">ลบกิจกรรม</button>
            <button type="button" class="btn btn-secondary" onclick="closeModal('calendarEventModal')">ยกเลิก</button>
            <button type="button" class="btn btn-primary" id="calendarEventSubmit">บันทึก</button>
        </div>
    </form>
</div>

<style>
.cal-chips .cal-chip {
    display: inline-flex; align-items: center; gap: 0.25rem;
    background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px;
    padding: 0.25rem 0.5rem; font-size: 0.875rem;
}
.cal-chips .cal-chip-remove {
    background: none; border: none; cursor: pointer; padding: 0; line-height: 1; color: #6b7280;
    font-size: 1rem; border-radius: 2px;
}
.cal-chips .cal-chip-remove:hover { color: #dc2626; background: #fee2e2; }
.cal-autocomplete-dropdown {
    position: absolute; left: 0; right: 0; top: 100%; z-index: 100;
    max-height: 200px; overflow-y: auto;
    background: #fff; border: 1px solid #d1d5db; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
    list-style: none; margin: 2px 0 0; padding: 0;
}
.cal-autocomplete-dropdown li {
    padding: 0.5rem 0.75rem; cursor: pointer; border-bottom: 1px solid #f3f4f6;
}
.cal-autocomplete-dropdown li:last-child { border-bottom: none; }
.cal-autocomplete-dropdown li:hover,
.cal-autocomplete-dropdown li.active { background: #fef9c3; }
</style>

<script>
(function() {
    var colorEl = document.getElementById('cal_color');
    var hexEl = document.getElementById('cal_color_hex');
    if (colorEl && hexEl) {
        colorEl.addEventListener('input', function() {
            hexEl.textContent = this.value;
        });
    }
    var allDay = document.getElementById('cal_all_day');
    var startEl = document.getElementById('cal_start');
    var endEl = document.getElementById('cal_end');
    if (allDay && startEl && endEl) {
        function toggleTime() {
            var isAllDay = allDay.checked;
            startEl.type = isAllDay ? 'date' : 'datetime-local';
            endEl.type = isAllDay ? 'date' : 'datetime-local';
        }
        allDay.addEventListener('change', toggleTime);
        toggleTime();
    }

    // Autocomplete for participants
    var participantsInput = document.getElementById('cal_participants_input');
    var participantsDropdown = document.getElementById('cal_participants_dropdown');
    var participantsChips = document.getElementById('cal_participants_chips');
    var participantsHidden = document.getElementById('cal_participants_hidden');
    if (participantsInput && participantsDropdown && participantsChips && participantsHidden) {
        var calUsers = <?= json_encode(array_map(static function ($u) {
            return ['email' => $u['email'] ?? '', 'name_th' => $u['name_th'] ?? '', 'name_en' => $u['name_en'] ?? ''];
        }, $users)) ?>;
        var selectedEmails = <?= json_encode($participantEmails) ?>;

        function renderChips() {
            participantsChips.innerHTML = '';
            selectedEmails.forEach(function(email) {
                var u = calUsers.find(function(x) { return x.email === email; });
                var label = (u && (u.name_th || u.name_en)) ? (u.name_th || u.name_en) : email;
                var span = document.createElement('span');
                span.className = 'cal-chip';
                span.innerHTML = '<span>' + (label.replace(/</g, '&lt;')) + '</span> <button type="button" class="cal-chip-remove" data-email="' + (email.replace(/"/g, '&quot;')) + '" aria-label="ลบ">&times;</button>';
                participantsChips.appendChild(span);
            });
            participantsHidden.innerHTML = '';
            selectedEmails.forEach(function(email) {
                var inp = document.createElement('input');
                inp.type = 'hidden';
                inp.name = 'participants[]';
                inp.value = email;
                participantsHidden.appendChild(inp);
            });
        }

        function filterUsers(q) {
            q = (q || '').trim().toLowerCase();
            if (!q) return calUsers.filter(function(u) { return selectedEmails.indexOf(u.email) === -1; });
            return calUsers.filter(function(u) {
                if (selectedEmails.indexOf(u.email) !== -1) return false;
                return (u.email && u.email.toLowerCase().indexOf(q) !== -1) ||
                       (u.name_th && u.name_th.toLowerCase().indexOf(q) !== -1) ||
                       (u.name_en && u.name_en.toLowerCase().indexOf(q) !== -1);
            });
        }

        function showDropdown(items) {
            participantsDropdown.innerHTML = '';
            if (items.length === 0) { participantsDropdown.style.display = 'none'; return; }
            participantsDropdown.style.display = 'block';
            items.forEach(function(u) {
                var li = document.createElement('li');
                li.textContent = (u.name_th || u.name_en) ? (u.name_th + (u.name_en ? ' (' + u.name_en + ')' : '')) : u.email;
                li.dataset.email = u.email;
                li.addEventListener('click', function() {
                    if (selectedEmails.indexOf(u.email) === -1) {
                        selectedEmails.push(u.email);
                        renderChips();
                    }
                    participantsInput.value = '';
                    participantsDropdown.style.display = 'none';
                    participantsInput.focus();
                });
                participantsDropdown.appendChild(li);
            });
        }

        participantsChips.addEventListener('click', function(e) {
            var btn = e.target.closest('.cal-chip-remove');
            if (btn && btn.dataset.email) {
                selectedEmails = selectedEmails.filter(function(em) { return em !== btn.dataset.email; });
                renderChips();
            }
        });

        participantsInput.addEventListener('input', function() {
            showDropdown(filterUsers(this.value));
        });
        participantsInput.addEventListener('focus', function() {
            showDropdown(filterUsers(this.value));
        });
        participantsInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var first = participantsDropdown.querySelector('li');
                if (first) {
                    var email = first.dataset.email;
                    if (selectedEmails.indexOf(email) === -1) {
                        selectedEmails.push(email);
                        renderChips();
                    }
                    participantsInput.value = '';
                    participantsDropdown.style.display = 'none';
                }
            }
            if (e.key === 'Escape') participantsDropdown.style.display = 'none';
        });

        document.addEventListener('click', function(e) {
            if (participantsDropdown && !e.target.closest('.cal-participants-autocomplete')) {
                participantsDropdown.style.display = 'none';
            }
        });

        renderChips();

        window.setCalendarParticipantEmails = function(emails) {
            selectedEmails = Array.isArray(emails) ? emails.slice() : [];
            renderChips();
        };
    }
})();
</script>

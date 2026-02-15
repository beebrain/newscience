<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<?php $existing_ids = $existing_ids ?? []; ?>
<div class="card eligibles-page-card">
    <div class="card-header">
        <h2>ผู้มีสิทธิ์รับบาร์โค้ด — <?= esc($event['title']) ?></h2>
        <a href="<?= base_url('student-admin/barcode-events/' . $event['id']) ?>" class="btn btn-secondary">กลับ Event</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('student-admin/barcode-events/add-eligible/' . $event['id']) ?>" method="post" id="form-add-eligible">
            <?= csrf_field() ?>
            <div class="form-group student-autocomplete-wrap" style="margin-bottom: 1rem;">
                <label for="student_autocomplete" class="form-label">เพิ่มนักศึกษา (เลือกจากรายชื่อ)</label>
                <input type="text" id="student_autocomplete" class="form-control" placeholder="พิมพ์ชื่อ หรืออีเมล เพื่อค้นหาและเลือกนักศึกษา..." autocomplete="off" style="max-width: 100%;">
                <div id="student_autocomplete_list" class="student-autocomplete-list" role="listbox" aria-hidden="true"></div>
                <div style="font-size: 0.8rem; color: var(--color-gray-500); margin-top: 0.35rem;">
                    <span id="student_selected_label" style="display: none;">เลือกแล้ว: <strong id="student_selected_name"></strong> <a href="#" id="student_clear_choice" style="margin-left: 0.5rem;">ล้างการเลือก</a></span>
                    <span id="student_type_hint">พิมพ์ตัวอักษรเพื่อกรองชื่อที่เกี่ยวข้องขึ้นมา</span>
                </div>
                <input type="hidden" name="student_user_id" id="student_user_id" value="">
                <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-top: 0.5rem;">
                    <button type="submit" name="by" value="id" class="btn btn-primary">เพิ่มจากรายชื่อ</button>
                </div>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="emails" class="form-label">หรือเพิ่มด้วย Email (หนึ่งบรรทัดต่อหนึ่งอีเมล หรือคั่นด้วยคอมมา)</label>
                <textarea id="emails" name="emails" class="form-control" rows="3" placeholder="user1@example.com&#10;user2@example.com"></textarea>
                <button type="submit" name="by" value="email" class="btn btn-primary" style="margin-top: 0.5rem;">เพิ่มจาก Email</button>
            </div>
        </form>
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
    max-height: 280px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid var(--color-gray-300, #d1d5db);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 100;
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
.eligibles-page-card + .card { margin-top: 1.75rem; }
</style>

<div class="card">
    <div class="card-header"><h3>รายชื่อผู้มีสิทธิ์ (<?= count($eligibles) ?>)</h3></div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($eligibles)): ?>
            <p style="padding: 1rem;">ยังไม่มีผู้มีสิทธิ์ — เลือกนักศึกษาด้านบนเพื่อเพิ่ม</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>นักศึกษา</th>
                        <th>อีเมล</th>
                        <th style="width: 100px;">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eligibles as $e): ?>
                        <tr>
                            <td><?= esc($e['student_display_name'] ?? '') ?></td>
                            <td><?= esc($e['student']['email'] ?? '') ?></td>
                            <td>
                                <form action="<?= base_url('student-admin/barcode-events/remove-eligible/' . $event['id'] . '/' . $e['student_user_id']) ?>" method="post" style="display: inline;">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('ลบออกจากรายการมีสิทธิ์?')">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<script>
(function() {
    var studentsForAutocomplete = <?= json_encode(array_values(array_map(function ($s) {
        return [
            'id' => (int)($s['id'] ?? 0),
            'email' => $s['email'] ?? '',
            'display_name' => $s['display_name'] ?? $s['email'] ?? ''
        ];
    }, array_filter($students ?? [], function ($s) use ($existing_ids) {
        return !in_array($s['id'], $existing_ids ?? [], true);
    })))) ?>;
    var input = document.getElementById('student_autocomplete');
    var listEl = document.getElementById('student_autocomplete_list');
    var hiddenEl = document.getElementById('student_user_id');
    var selectedLabel = document.getElementById('student_selected_label');
    var selectedName = document.getElementById('student_selected_name');
    var typeHint = document.getElementById('student_type_hint');
    var clearLink = document.getElementById('student_clear_choice');
    var students = studentsForAutocomplete || [];
    var highlightIdx = -1;

    function filterStudents(q) {
        q = (q || '').trim().toLowerCase();
        if (q === '') return students.slice(0, 20);
        return students.filter(function(s) {
            var email = (s.email || '').toLowerCase();
            var display = (s.display_name || '').toLowerCase();
            return email.indexOf(q) !== -1 || display.indexOf(q) !== -1;
        }).slice(0, 20);
    }

    function showList(items) {
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
        highlightIdx = -1;
    }

    function hideList() {
        if (listEl) {
            listEl.classList.remove('show');
            listEl.setAttribute('aria-hidden', 'true');
            listEl.innerHTML = '';
        }
        highlightIdx = -1;
    }

    function selectStudent(id, displayName) {
        if (hiddenEl) hiddenEl.value = id || '';
        if (input) input.value = displayName || '';
        if (id && selectedLabel && selectedName) {
            selectedName.textContent = displayName || '';
            selectedLabel.style.display = '';
            if (typeHint) typeHint.style.display = 'none';
        } else {
            if (selectedLabel) selectedLabel.style.display = 'none';
            if (typeHint) typeHint.style.display = '';
        }
        hideList();
    }

    function clearChoice() {
        selectStudent('', '');
        if (input) input.focus();
    }

    if (input && listEl) {
        input.addEventListener('input', function() {
            var q = input.value.trim();
            showList(filterStudents(q));
        });
        input.addEventListener('focus', function() {
            var q = input.value.trim();
            showList(filterStudents(q));
        });
        input.addEventListener('keydown', function(e) {
            if (!listEl.classList.contains('show')) return;
            var opts = listEl.querySelectorAll('[role="option"]');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                highlightIdx = highlightIdx < opts.length - 1 ? highlightIdx + 1 : 0;
                opts[highlightIdx].classList.add('highlight');
                opts[highlightIdx].scrollIntoView({ block: 'nearest' });
                opts.forEach(function(o, i) { if (i !== highlightIdx) o.classList.remove('highlight'); });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                highlightIdx = highlightIdx > 0 ? highlightIdx - 1 : opts.length - 1;
                opts[highlightIdx].classList.add('highlight');
                opts[highlightIdx].scrollIntoView({ block: 'nearest' });
                opts.forEach(function(o, i) { if (i !== highlightIdx) o.classList.remove('highlight'); });
            } else if (e.key === 'Enter' && highlightIdx >= 0 && opts[highlightIdx]) {
                e.preventDefault();
                var o = opts[highlightIdx];
                selectStudent(o.dataset.id, o.dataset.display);
            } else if (e.key === 'Escape') {
                hideList();
            }
        });
    }

    if (listEl) {
        listEl.addEventListener('click', function(e) {
            var opt = e.target.closest('[role="option"]');
            if (!opt) return;
            selectStudent(opt.dataset.id, opt.dataset.display);
        });
    }

    document.addEventListener('click', function(e) {
        if (listEl && listEl.classList.contains('show') && !listEl.contains(e.target) && e.target !== input) {
            hideList();
        }
    });

    if (clearLink) {
        clearLink.addEventListener('click', function(e) {
            e.preventDefault();
            clearChoice();
        });
    }
})();
</script>
<?= $this->endSection() ?>

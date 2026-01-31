<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>แก้ไขตำแหน่งในโครงสร้างองค์กร</h2>
        <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
    <div class="card-body">
        <form action="<?= base_url('admin/organization/update/' . $person['id']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-700);">รูปภาพ</h3>
            <?php
            $personImg = $person['image'] ?? '';
            $personImgUrl = '';
            if ($personImg !== '') {
                if (strpos($personImg, 'http') === 0) $personImgUrl = $personImg;
                elseif (strpos($personImg, 'uploads/') === 0) $personImgUrl = base_url($personImg);
                else $personImgUrl = base_url('uploads/' . ltrim($personImg, '/'));
            }
            ?>
            <div class="form-group">
                <?php if ($personImgUrl): ?>
                    <div style="margin-bottom: 0.75rem;">
                        <img src="<?= esc($personImgUrl) ?>" alt="" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 2px solid var(--color-gray-200);">
                        <p style="font-size: 0.85rem; color: var(--color-gray-500); margin-top: 0.25rem;">รูปปัจจุบัน</p>
                    </div>
                <?php endif; ?>
                <label class="form-label" for="image"><?= $personImgUrl ? 'เปลี่ยนรูป' : 'อัปโหลดรูป' ?></label>
                <input type="file" name="image" id="image" class="form-control" accept="image/jpeg,image/png,image/gif,image/webp">
                <small style="color: var(--color-gray-500);">JPG, PNG, GIF หรือ WebP ไม่เกิน 5MB รูปที่อัปโหลดจะเก็บที่ uploads/staff</small>
            </div>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-700);">ชื่อ-นามสกุล</h3>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="title">คำนำหน้าชื่อ</label>
                    <input type="text" name="title" id="title" class="form-control" value="<?= esc(old('title', $person['title'] ?? '')) ?>" placeholder="เช่น ผศ. ดร.">
                </div>
                <div class="form-group">
                    <label class="form-label" for="email">อีเมล</label>
                    <input type="email" name="email" id="email" class="form-control" value="<?= esc(old('email', $person['email'] ?? '')) ?>" placeholder="name@example.com">
                    <small style="color: var(--color-gray-500);">เก็บไว้ใช้สำหรับลิงก์ภายหลัง (ยังไม่แสดงบนหน้าโครงสร้าง)</small>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name">ชื่อ (ไทย)</label>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?= esc(old('first_name', $person['first_name'] ?? '')) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name">นามสกุล (ไทย)</label>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?= esc(old('last_name', $person['last_name'] ?? '')) ?>" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="first_name_en">ชื่อ (อังกฤษ)</label>
                    <input type="text" name="first_name_en" id="first_name_en" class="form-control" value="<?= esc(old('first_name_en', $person['first_name_en'] ?? '')) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label" for="last_name_en">นามสกุล (อังกฤษ)</label>
                    <input type="text" name="last_name_en" id="last_name_en" class="form-control" value="<?= esc(old('last_name_en', $person['last_name_en'] ?? '')) ?>">
                </div>
            </div>

            <h3 style="font-size: 1rem; margin: 1.5rem 0 1rem; color: var(--color-gray-700);">หลักสูตร/สังกัด (อาจารย์ 1 คน สังกัดได้หลายหลักสูตร)</h3>
            <p style="font-size: 0.85rem; color: var(--color-gray-500); margin: -0.5rem 0 0.75rem;">ถ้าตำแหน่งเป็น <strong>ประธานหลักสูตร</strong> กรุณาเพิ่มหลักสูตรและเลือกบทบาท &quot;ประธานหลักสูตร&quot; เพื่อระบุว่าประธานของหลักสูตรใด</p>
            <?php if (!empty($programs)): ?>
            <?php
            $assignments = old('program_assignments');
            $rows = [];
            if (is_array($assignments) && !empty($assignments['program_id'])) {
                foreach ($assignments['program_id'] as $i => $pid) {
                    $pid = (int)$pid;
                    if ($pid <= 0) continue;
                    $rows[] = [
                        'program_id' => $pid,
                        'role_in_curriculum' => $assignments['role_in_curriculum'][$i] ?? '',
                    ];
                }
            }
            if (empty($rows) && !empty($personnel_programs)) {
                foreach ($personnel_programs as $pp) {
                    $pid = (int)($pp['program_id'] ?? 0);
                    if ($pid <= 0) continue;
                    $rows[] = [
                        'program_id' => $pid,
                        'role_in_curriculum' => $pp['role_in_curriculum'] ?? '',
                    ];
                }
            }
            if (empty($rows) && !empty($person['program_id'])) {
                $rows[] = ['program_id' => (int)$person['program_id'], 'role_in_curriculum' => ''];
            }
            $programLabels = [];
            foreach ($programs as $pr) {
                $l = $pr['name_th'] ?? $pr['name'] ?? '';
                if (!empty($pr['department_name'] ?? $pr['department_name_th'] ?? '')) {
                    $l .= ' (' . ($pr['department_name'] ?? $pr['department_name_th'] ?? '') . ')';
                }
                $programLabels[(int)$pr['id']] = $l;
            }
            ?>
            <style>
            .program-tags-wrap { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; min-height: 3rem; padding: 0.75rem; margin-bottom: 0.75rem; border: 2px dashed #94a3b8; border-radius: 10px; background: #f1f5f9; transition: background 0.15s; }
            .program-tags-wrap.drag-over { background: #e2e8f0; border-color: #2563eb; }
            .program-tags-wrap .drop-hint { color: #64748b; font-size: 0.85rem; width: 100%; margin: 0; }
            .program-tag { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.5rem 0.25rem 0.6rem; background: #2563eb; color: #fff; border-radius: 999px; font-size: 0.8rem; }
            .program-tag .program-tag-role-select { background: rgba(255,255,255,0.25); color: #fff; border: 1px solid rgba(255,255,255,0.5); border-radius: 6px; padding: 0.1rem 0.35rem; font-size: 0.7rem; max-width: 120px; }
            .program-tag .program-tag-role-select option { background: #1e293b; color: #fff; }
            .program-tag .program-tag-remove { border: none; background: rgba(255,255,255,0.3); color: #fff; width: 1.25rem; height: 1.25rem; border-radius: 50%; cursor: pointer; padding: 0; line-height: 1; font-size: 1rem; display: flex; align-items: center; justify-content: center; }
            .program-tag .program-tag-remove:hover { background: rgba(255,255,255,0.5); }
            .program-buttons-wrap { margin-top: 1rem; }
            .program-draggable-btn { display: inline-block; margin: 0.25rem; padding: 0.35rem 0.75rem; font-size: 0.8rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; cursor: grab; transition: background 0.15s, border-color 0.15s; }
            .program-draggable-btn:hover { background: #f1f5f9; border-color: #94a3b8; }
            .program-draggable-btn.added { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
            </style>
            <label class="form-label" style="display: block; margin-bottom: 0.25rem;">หลักสูตรที่เลือก <span style="color: var(--color-gray-500); font-weight: normal;">(วางหลักสูตรที่นี่)</span></label>
            <div id="program-tags" class="program-tags-wrap" data-drop-zone="1">
                <?php if (empty($rows)): ?>
                <span class="drop-hint" id="program-tags-hint">ลากปุ่มหลักสูตรจากด้านล่างมาวางที่นี่ หรือคลิกปุ่มเพื่อเพิ่ม</span>
                <?php else: ?>
                <span class="drop-hint" id="program-tags-hint" style="display: none;">ลากปุ่มหลักสูตรจากด้านล่างมาวางที่นี่ หรือคลิกปุ่มเพื่อเพิ่ม</span>
                <?php foreach ($rows as $row):
                    $label = $programLabels[$row['program_id']] ?? 'หลักสูตร';
                ?>
                <span class="program-tag">
                    <span><?= esc($label) ?></span>
                    <select name="program_assignments[role_in_curriculum][]" class="program-tag-role-select">
                        <?php foreach ($role_in_curriculum_options as $val => $lab): ?>
                            <option value="<?= esc($val) ?>" <?= ($row['role_in_curriculum'] ?? '') === $val ? 'selected' : '' ?>><?= esc($lab) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="program-tag-remove" title="ลบ">&times;</button>
                </span>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div id="program-assignments-hidden">
                <?php foreach ($rows as $row): ?>
                <div class="program-assignment-row">
                    <input type="hidden" name="program_assignments[program_id][]" value="<?= (int)$row['program_id'] ?>">
                </div>
                <?php endforeach; ?>
            </div>
            <div class="form-group" style="margin-bottom: 0.5rem;">
                <label class="form-label" for="role-select">บทบาท (เริ่มต้น)</label>
                <select id="role-select" class="form-control" style="max-width: 200px;">
                    <?php foreach ($role_in_curriculum_options as $val => $lab): ?>
                        <option value="<?= esc($val) ?>"><?= esc($lab) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="program-buttons-wrap">
                <label class="form-label">เลือกหลักสูตร — ลากไปใส่กล่องด้านบน หรือคลิกเพื่อเพิ่ม</label>
                <div id="program-buttons">
                    <?php foreach ($programs as $pr):
                        $label = $pr['name_th'] ?? $pr['name'] ?? '';
                        if (!empty($pr['department_name'] ?? $pr['department_name_th'] ?? '')) {
                            $label .= ' (' . ($pr['department_name'] ?? $pr['department_name_th'] ?? '') . ')';
                        }
                        $already = in_array((int)$pr['id'], array_column($rows, 'program_id'), true);
                    ?>
                        <button type="button" class="program-draggable-btn <?= $already ? 'added' : '' ?>" draggable="true" data-program-id="<?= (int)$pr['id'] ?>" data-program-label="<?= esc($label) ?>"><?= esc($label) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <select id="role-options-template" style="display: none;" aria-hidden="true">
                <?php foreach ($role_in_curriculum_options as $val => $lab): ?>
                    <option value="<?= esc($val) ?>"><?= esc($lab) ?></option>
                <?php endforeach; ?>
            </select>
            <small style="color: var(--color-gray-500); display: block; margin-top: 0.5rem;">สังกัด/แผนกจะใช้จากหลักสูตรแรกในรายการ</small>
            <?php endif; ?>

            <h3 style="font-size: 1rem; margin: 1.5rem 0 1rem; color: var(--color-gray-700);">ตำแหน่งในโครงสร้าง</h3>
            <div class="form-group">
                <label class="form-label" for="position">ตำแหน่ง (ไทย)</label>
                <select name="position" id="position" class="form-control">
                    <?php foreach ($position_options as $value => $label): ?>
                        <option value="<?= esc($value) ?>" <?= (old('position', $person['position'] ?? '') === $value ? 'selected' : '' ) ?>><?= esc($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-gray-500);">ตำแหน่งนี้จะกำหนดระดับในโครงสร้าง: คณบดี = ชั้นบน, รองคณบดี = ชั้นกลาง, ผู้ช่วยคณบดี = ชั้นล่าง</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="position_en">ตำแหน่ง (อังกฤษ)</label>
                <input type="text" name="position_en" id="position_en" class="form-control" value="<?= esc(old('position_en', $person['position_en'] ?? '')) ?>" placeholder="เช่น Dean, Associate Dean">
            </div>

            <div class="form-group">
                <label class="form-label" for="sort_order">ลำดับการแสดง (ตัวเลข)</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= (int)($person['sort_order'] ?? 0) ?>" min="0" style="max-width: 120px;">
                <small style="color: var(--color-gray-500);">เลขน้อยแสดงก่อนภายในระดับเดียวกัน</small>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script>
function initProgramTagsEdit() {
    var tagsEl = document.getElementById('program-tags');
    var hiddenEl = document.getElementById('program-assignments-hidden');
    var roleSelect = document.getElementById('role-select');
    var programButtons = document.getElementById('program-buttons');
    var hintEl = document.getElementById('program-tags-hint');
    var roleTemplate = document.getElementById('role-options-template');
    if (!tagsEl || !hiddenEl || !programButtons || !roleTemplate) return;

    function getSelectedIds() {
        var ids = [];
        hiddenEl.querySelectorAll('input[name="program_assignments[program_id][]"]').forEach(function(inp) { ids.push(inp.value); });
        return ids;
    }

    function escapeHtml(s) {
        if (!s) return '';
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function addTag(programId, programLabel, roleValue) {
        if (!programId) return;
        var selected = getSelectedIds();
        if (selected.indexOf(String(programId)) !== -1) return;
        var wrap = document.createElement('div');
        wrap.className = 'program-assignment-row';
        wrap.innerHTML = '<input type="hidden" name="program_assignments[program_id][]" value="' + escapeHtml(String(programId)) + '">';
        hiddenEl.appendChild(wrap);
        var span = document.createElement('span');
        span.className = 'program-tag';
        var roleSelectClone = roleTemplate.cloneNode(true);
        roleSelectClone.id = '';
        roleSelectClone.className = 'program-tag-role-select';
        roleSelectClone.name = 'program_assignments[role_in_curriculum][]';
        if (roleValue !== undefined && roleValue !== null) roleSelectClone.value = roleValue;
        span.appendChild(document.createTextNode(programLabel || 'หลักสูตร'));
        span.appendChild(roleSelectClone);
        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'program-tag-remove';
        removeBtn.title = 'ลบ';
        removeBtn.textContent = '\u00D7';
        span.appendChild(removeBtn);
        if (hintEl) hintEl.style.display = 'none';
        tagsEl.appendChild(span);
        removeBtn.onclick = function() {
            var idx = Array.prototype.indexOf.call(tagsEl.children, span);
            if (idx !== -1 && idx > 0) {
                if (hiddenEl.children[idx - 1]) hiddenEl.removeChild(hiddenEl.children[idx - 1]);
            }
            span.remove();
            if (tagsEl.querySelectorAll('.program-tag').length === 0 && hintEl) hintEl.style.display = '';
            updateButtons();
        };
        updateButtons();
    }

    function updateButtons() {
        var selected = getSelectedIds();
        programButtons.querySelectorAll('.program-draggable-btn').forEach(function(btn) {
            var id = btn.getAttribute('data-program-id');
            if (selected.indexOf(id) !== -1) btn.classList.add('added');
            else btn.classList.remove('added');
        });
    }

    tagsEl.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
        tagsEl.classList.add('drag-over');
    });
    tagsEl.addEventListener('dragleave', function() { tagsEl.classList.remove('drag-over'); });
    tagsEl.addEventListener('drop', function(e) {
        e.preventDefault();
        tagsEl.classList.remove('drag-over');
        var programId = e.dataTransfer.getData('programId');
        var programLabel = e.dataTransfer.getData('programLabel') || e.dataTransfer.getData('text/plain') || '';
        var role = roleSelect ? roleSelect.value : '';
        addTag(programId, programLabel, role);
    });

    tagsEl.querySelectorAll('.program-tag-remove').forEach(function(btn) {
        btn.onclick = function() {
            var span = this.closest('.program-tag');
            var idx = Array.prototype.indexOf.call(tagsEl.children, span);
            if (idx !== -1 && idx > 0 && hiddenEl.children[idx - 1]) hiddenEl.removeChild(hiddenEl.children[idx - 1]);
            span.remove();
            if (tagsEl.querySelectorAll('.program-tag').length === 0 && hintEl) hintEl.style.display = '';
            updateButtons();
        };
    });

    programButtons.querySelectorAll('.program-draggable-btn').forEach(function(btn) {
        btn.addEventListener('dragstart', function(e) {
            if (btn.classList.contains('added')) { e.preventDefault(); return; }
            e.dataTransfer.setData('programId', btn.getAttribute('data-program-id'));
            e.dataTransfer.setData('programLabel', btn.getAttribute('data-program-label') || btn.textContent);
            e.dataTransfer.setData('text/plain', btn.getAttribute('data-program-label') || btn.textContent);
            e.dataTransfer.effectAllowed = 'copy';
        });
        btn.addEventListener('click', function() {
            if (btn.classList.contains('added')) return;
            var programId = btn.getAttribute('data-program-id');
            var programLabel = btn.getAttribute('data-program-label') || btn.textContent;
            var role = roleSelect ? roleSelect.value : '';
            addTag(programId, programLabel, role);
        });
    });
}
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProgramTagsEdit);
} else {
    initProgramTagsEdit();
}
</script>

<?= $this->endSection() ?>

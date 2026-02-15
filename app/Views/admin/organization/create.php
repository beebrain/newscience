<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>เพิ่มบุคลากรในโครงสร้างองค์กร</h2>
        <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
    <div class="card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/organization/store') ?>" method="post" enctype="multipart/form-data" id="org-create-form">
            <?= csrf_field() ?>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-700);">เลือกจากตาราง user</h3>
            <div class="form-group user-autocomplete-wrap">
                <label class="form-label" for="user_autocomplete">ค้นหาผู้ใช้ (พิมพ์ชื่อ หรืออีเมล)</label>
                <input type="text" id="user_autocomplete" class="form-control" placeholder="พิมพ์ชื่อ หรืออีเมล เพื่อค้นหาและเลือกผู้ใช้..." autocomplete="off" style="max-width: 100%;">
                <div id="user_autocomplete_list" class="user-autocomplete-list" role="listbox" aria-hidden="true"></div>
                <div id="user_autocomplete_hint" style="font-size: 0.8rem; color: var(--color-gray-500); margin-top: 0.35rem;">
                    <span id="user_selected_label" style="display: none;">เลือกแล้ว: <strong id="user_selected_name"></strong> <a href="#" id="user_clear_choice" style="margin-left: 0.5rem;">ล้างการเลือก</a></span>
                    <span id="user_type_hint">พิมพ์ตัวอักษรเพื่อกรองชื่อที่เกี่ยวข้องขึ้นมา</span>
                </div>
                <small style="color: var(--color-gray-500); display: block; margin-top: 0.5rem;">เลือกผู้ใช้จากตาราง user แล้วชื่อ/อีเมลจะถูกเติมให้ — แสดงทุก user ในระบบ (ถ้าเลือกผู้ใช้ที่มีในองค์กรแล้ว ระบบจะแจ้งเตือนเมื่อบันทึก)</small>
            </div>
            <input type="hidden" name="user_uid" id="user_uid" value="">
            <style>
                .user-autocomplete-wrap {
                    position: relative;
                }

                .user-autocomplete-list {
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

                .user-autocomplete-list.show {
                    display: block;
                }

                .user-autocomplete-list [role="option"] {
                    padding: 0.5rem 1rem;
                    cursor: pointer;
                    font-size: 0.9rem;
                    border: none;
                    background: none;
                    width: 100%;
                    text-align: left;
                }

                .user-autocomplete-list [role="option"]:hover,
                .user-autocomplete-list [role="option"].highlight {
                    background: #f1f5f9;
                }

                .user-autocomplete-list [role="option"].manual {
                    color: #64748b;
                }
            </style>

            <div class="form-group">
                <label class="form-label" style="font-weight: 500; color: var(--color-gray-700); margin-bottom: 0.5rem;">รูปภาพบุคลากร</label>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem; border: 2px dashed #cbd5e1; padding: 2rem; border-radius: 12px; background: #f8fafc; transition: all 0.2s;">
                    <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
                        <?php 
                        $fallbackImg = base_url('assets/images/placeholder.png');
                        $currentImg = $fallbackImg;
                        ?>
                        <img id="preview-image" src="<?= esc($currentImg) ?>" 
                             alt="Profile Preview" 
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; border: 4px solid #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); background-color: #fff; transition: opacity 0.2s;">
                        
                        <label for="image" style="position: absolute; bottom: 5px; right: 5px; background: #2563eb; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.1); transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M10.5 8.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                                <path d="M2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4H2zm.5 2a.5.5 0 1 1 0-1 .5.5 0 0 1 0 1zm9 2.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0z"/>
                            </svg>
                        </label>
                    </div>
                    
                    <div style="text-align: center;">
                         <input type="file" name="image" id="image" accept="image/jpeg,image/png,image/gif,image/webp" style="display: none;" onchange="previewImage(this)">
                         <div style="margin-bottom: 0.75rem;">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('image').click()">
                                <span>เลือกรูปภาพ</span>
                            </button>
                         </div>
                         <div style="font-size: 0.8rem; color: #64748b;">รองรับ JPG, PNG, GIF, WebP (ไม่เกิน 5MB)</div>
                    </div>
                </div>
            </div>

            <script>
                function previewImage(input) {
                    if (input.files && input.files[0]) {
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            var img = document.getElementById('preview-image');
                            img.style.opacity = '0';
                            setTimeout(function() { 
                                img.src = e.target.result;
                                img.style.opacity = '1'; 
                            }, 200);
                        }
                        reader.readAsDataURL(input.files[0]);
                    }
                }
            </script>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-700);">ชื่อ-นามสกุล</h3>
            <p style="font-size: 0.85rem; color: var(--color-gray-500); margin: -0.5rem 0 0.5rem;">ถ้าเลือกผู้ใช้จากตาราง user ด้านบน ชื่อจะดึงจาก user ให้อัตโนมัติ — ไม่ต้องกรอกซ้ำ</p>
            <div class="form-row name-with-prefix">
                <div class="form-group" style="flex: 0 0 auto; min-width: 180px;">
                    <label class="form-label" for="academic_title">คำนำหน้าชื่อ (ไทย)</label>
                    <select name="academic_title" id="academic_title" class="form-control">
                        <?php foreach ($academic_title_options as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= (old('academic_title', $person['academic_title'] ?? '') === $value ? 'selected' : '') ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 0;">
                    <label class="form-label" for="name">ชื่อ-นามสกุล (ไทย)</label>
                    <input type="text" name="name" id="name" class="form-control" value="<?= esc(old('name', $person['name'] ?? '')) ?>" placeholder="ชื่อ นามสกุล (หรือเลือกจาก user ด้านบน)">
                </div>
            </div>
            <div class="form-row name-with-prefix">
                <div class="form-group" style="flex: 0 0 auto; min-width: 180px;">
                    <label class="form-label" for="academic_title_en">คำนำหน้าชื่อ (English)</label>
                    <select name="academic_title_en" id="academic_title_en" class="form-control">
                        <?php foreach ($academic_title_options_en as $value => $label): ?>
                            <option value="<?= esc($value) ?>" <?= (old('academic_title_en', $person['academic_title_en'] ?? '') === $value ? 'selected' : '') ?>><?= esc($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="flex: 1; min-width: 0;">
                    <label class="form-label" for="name_en">ชื่อ-นามสกุล (อังกฤษ)</label>
                    <input type="text" name="name_en" id="name_en" class="form-control" value="<?= esc(old('name_en', $person['name_en'] ?? '')) ?>" placeholder="Optional English name">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label" for="email">อีเมล</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= esc(old('email', $person['email'] ?? '')) ?>" placeholder="name@example.com">
                <small style="color: var(--color-gray-500);">ใช้ลิงก์กับตาราง user — ถ้าอีเมลตรงกับ user ในระบบ จะเชื่อมบัญชี (personnel.user_uid)</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="phone">เบอร์โทร</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= esc(old('phone', $person['phone'] ?? '')) ?>" placeholder="02-xxx-xxxx">
            </div>

            <h3 style="font-size: 1rem; margin: 1.5rem 0 1rem; color: var(--color-gray-700);">สาขา/หลักสูตร (อาจารย์ 1 คน สังกัดได้หลายสาขา)</h3>
            <p style="font-size: 0.85rem; color: var(--color-gray-500); margin: -0.5rem 0 0.75rem;">หลักสูตร = สาขา — ถ้าตำแหน่งเป็น <strong>ประธานหลักสูตร</strong> กรุณาเพิ่มอย่างน้อยหนึ่งสาขาเพื่อระบุว่าประธานของสาขาใด</p>
            <?php if (!empty($programs)): ?>
                <style>
                    .program-tags-wrap {
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        gap: 0.5rem;
                        min-height: 3rem;
                        padding: 0.75rem;
                        margin-bottom: 0.75rem;
                        border: 2px dashed #94a3b8;
                        border-radius: 10px;
                        background: #f1f5f9;
                        transition: background 0.15s;
                    }

                    .program-tags-wrap.drag-over {
                        background: #e2e8f0;
                        border-color: #2563eb;
                    }

                    .program-tags-wrap .drop-hint {
                        color: #64748b;
                        font-size: 0.85rem;
                        width: 100%;
                        margin: 0;
                    }

                    .program-tag {
                        display: inline-flex;
                        align-items: center;
                        gap: 0.35rem;
                        padding: 0.25rem 0.5rem 0.25rem 0.6rem;
                        background: #2563eb;
                        color: #fff;
                        border-radius: 999px;
                        font-size: 0.8rem;
                    }

                    .program-tag .program-tag-remove {
                        border: none;
                        background: rgba(255, 255, 255, 0.3);
                        color: #fff;
                        width: 1.25rem;
                        height: 1.25rem;
                        border-radius: 50%;
                        cursor: pointer;
                        padding: 0;
                        line-height: 1;
                        font-size: 1rem;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }

                    .program-tag .program-tag-remove:hover {
                        background: rgba(255, 255, 255, 0.5);
                    }

                    .program-buttons-wrap {
                        margin-top: 1rem;
                    }

                    .program-buttons-wrap .form-label {
                        margin-bottom: 0.35rem;
                    }

                    .program-draggable-btn {
                        display: inline-block;
                        margin: 0.25rem;
                        padding: 0.35rem 0.75rem;
                        font-size: 0.8rem;
                        border-radius: 8px;
                        border: 1px solid #cbd5e1;
                        background: #fff;
                        cursor: grab;
                        transition: background 0.15s, border-color 0.15s;
                    }

                    .program-draggable-btn:hover {
                        background: #f1f5f9;
                        border-color: #94a3b8;
                    }

                    .program-draggable-btn:active {
                        cursor: grabbing;
                    }

                    .program-draggable-btn.added {
                        opacity: 0.5;
                        cursor: not-allowed;
                        pointer-events: none;
                    }
                </style>
                <label class="form-label" style="display: block; margin-bottom: 0.25rem;">สาขาที่เลือก <span style="color: var(--color-gray-500); font-weight: normal;">(วางสาขาจากด้านล่างที่นี่)</span></label>
                <div id="program-tags" class="program-tags-wrap" data-drop-zone="1">
                    <span class="drop-hint" id="program-tags-hint">ลากปุ่มสาขาจากด้านล่างมาวางที่นี่ หรือคลิกปุ่มเพื่อเพิ่ม</span>
                </div>
                <div id="program-assignments-hidden"></div>
                <div class="program-buttons-wrap">
                    <label class="form-label">เลือกสาขา — ลากไปใส่กล่องด้านบน หรือคลิกเพื่อเพิ่ม</label>
                    <div id="program-buttons">
                        <?php foreach ($programs as $pr):
                            $label = $pr['name_th'] ?? $pr['name'] ?? '';
                            if (!empty($pr['department_name'] ?? $pr['department_name_th'] ?? '')) {
                                $label .= ' (' . ($pr['department_name'] ?? $pr['department_name_th'] ?? '') . ')';
                            }
                        ?>
                            <button type="button" class="program-draggable-btn" draggable="true" data-program-id="<?= (int)$pr['id'] ?>" data-program-label="<?= esc($label) ?>"><?= esc($label) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" name="primary_program_id" id="primary_program_id" value="">
                <small style="color: var(--color-gray-500); display: block; margin-top: 0.5rem;">สาขาแรกจะเป็นสาขาหลัก (ใช้สำหรับสังกัด/แผนก)</small>
            <?php endif; ?>

            <h3 style="font-size: 1rem; margin: 1.5rem 0 1rem; color: var(--color-gray-700);">ตำแหน่งในโครงสร้าง</h3>
            <div class="form-group">
                <label class="form-label" for="position">ตำแหน่ง</label>
                <select name="position" id="position" class="form-control">
                    <?php foreach ($position_options as $groupLabel => $options): ?>
                        <optgroup label="<?= esc($groupLabel) ?>">
                            <?php foreach ($options as $value => $label): ?>
                                <option value="<?= esc($value) ?>" <?= (old('position', $person['position'] ?? '') === $value ? 'selected' : '') ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-gray-500);">ตำแหน่งในโครงสร้างองค์กร</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="position_detail">รายละเอียดตำแหน่ง</label>
                <input type="text" name="position_detail" id="position_detail" class="form-control" value="<?= esc(old('position_detail', $person['position_detail'] ?? '')) ?>" placeholder="เช่น ฝ่ายกิจกรรมนักศึกษา, ฝ่ายวิจัยและนวัตกรรม">
                <small style="color: var(--color-gray-500);">ระบุฝ่าย/หน่วย เช่น รองคณบดี ฝ่ายกิจกรรมนักศึกษา</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="sort_order">ลำดับการแสดง (ตัวเลข)</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= (int)(old('sort_order', $person['sort_order'] ?? 0)) ?>" min="0" style="max-width: 120px;">
                <small style="color: var(--color-gray-500);">เลขน้อยแสดงก่อนภายในระดับเดียวกัน</small>
            </div>

            <?php if (!empty($organization_units)): ?>
            <div class="form-group">
                <label class="form-label" for="organization_unit_id">หน่วยงานสังกัด (organization_unit_id)</label>
                <select name="organization_unit_id" id="organization_unit_id" class="form-control" style="max-width: 360px;">
                    <option value="">— ตามสาขาหลัก (อัตโนมัติ) —</option>
                    <?php foreach ($organization_units as $ou): ?>
                        <option value="<?= (int)($ou['id'] ?? 0) ?>" <?= (string)(old('organization_unit_id', $person['organization_unit_id'] ?? '')) === (string)($ou['id'] ?? '') ? 'selected' : '' ?>><?= esc($ou['name_th'] ?? $ou['code'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-gray-500);">บุคลากรสำนักงาน (หัวหน้าสำนักงาน/เจ้าหน้าที่) ให้เลือก "สำนักงานคณบดี"</small>
            </div>
            <?php endif; ?>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">เพิ่มบุคลากร</button>
                <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script>
    var programsForChair = <?= json_encode(array_values(array_map(function ($p) {
                                return ['id' => (int)($p['id'] ?? 0), 'name' => $p['name_th'] ?? $p['name_en'] ?? ''];
                            }, $programs ?? []))) ?>;

    var usersForAutocomplete = <?= json_encode(array_values(array_map(function ($u) {
                                    return [
                                        'uid' => (int)($u['uid'] ?? 0),
                                        'name' => $u['name'] ?? '',
                                        'name_en' => $u['name_en'] ?? '',
                                        'email' => $u['email'] ?? '',
                                        'display_name' => ($u['name'] ?? '') !== '' ? ($u['name'] . ' (' . ($u['email'] ?? '') . ')') : ($u['email'] ?? '')
                                    ];
                                }, $users_for_personnel ?? []))) ?>;

    (function() {
        var input = document.getElementById('user_autocomplete');
        var listEl = document.getElementById('user_autocomplete_list');
        var userUidHidden = document.getElementById('user_uid');
        var nameInput = document.getElementById('name');
        var nameEnInput = document.getElementById('name_en');
        var emailInput = document.getElementById('email');
        var selectedLabel = document.getElementById('user_selected_label');
        var selectedName = document.getElementById('user_selected_name');
        var typeHint = document.getElementById('user_type_hint');
        var clearLink = document.getElementById('user_clear_choice');
        var users = usersForAutocomplete || [];
        var highlightIdx = -1;

        function escapeHtml(s) {
            if (!s) return '';
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        function filterUsers(q) {
            q = (q || '').trim().toLowerCase();
            if (q === '') return users.slice(0, 20);
            return users.filter(function(u) {
                var name = (u.name || '').toLowerCase();
                var nameEn = (u.name_en || '').toLowerCase();
                var email = (u.email || '').toLowerCase();
                var display = (u.display_name || '').toLowerCase();
                return name.indexOf(q) !== -1 || nameEn.indexOf(q) !== -1 || email.indexOf(q) !== -1 || display.indexOf(q) !== -1;
            }).slice(0, 20);
        }

        function showList(items) {
            if (!listEl) return;
            listEl.innerHTML = '';
            listEl.setAttribute('aria-hidden', 'false');
            listEl.classList.add('show');
            var manual = document.createElement('button');
            manual.type = 'button';
            manual.role = 'option';
            manual.className = 'manual';
            manual.textContent = '— กรอกเอง (ไม่เลือกจาก user) —';
            manual.dataset.uid = '';
            listEl.appendChild(manual);
            items.forEach(function(u) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.role = 'option';
                btn.textContent = u.display_name || u.email || '';
                btn.dataset.uid = String(u.uid);
                btn.dataset.name = u.name || '';
                btn.dataset.nameEn = u.name_en || '';
                btn.dataset.email = u.email || '';
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

        function selectUser(uid, name, nameEn, email, displayName) {
            if (userUidHidden) userUidHidden.value = uid || '';
            if (nameInput) nameInput.value = name || '';
            if (nameEnInput) nameEnInput.value = nameEn || '';
            if (emailInput) emailInput.value = email || '';
            if (input) input.value = displayName || '';
            if (uid && selectedLabel && selectedName) {
                selectedName.textContent = displayName || name || email || '';
                selectedLabel.style.display = '';
                if (typeHint) typeHint.style.display = 'none';
            } else {
                if (selectedLabel) selectedLabel.style.display = 'none';
                if (typeHint) typeHint.style.display = '';
            }
            hideList();
        }

        function clearChoice() {
            selectUser('', '', '', '', '');
            if (input) input.value = '';
            if (input) input.focus();
        }

        if (input && listEl) {
            input.addEventListener('input', function() {
                var q = input.value.trim();
                var items = filterUsers(q);
                showList(items);
            });
            input.addEventListener('focus', function() {
                var q = input.value.trim();
                showList(filterUsers(q));
            });
            input.addEventListener('keydown', function(e) {
                if (!listEl.classList.contains('show')) return;
                var opts = listEl.querySelectorAll('[role="option"]');
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    highlightIdx = highlightIdx < opts.length - 1 ? highlightIdx + 1 : 0;
                    opts[highlightIdx].classList.add('highlight');
                    opts[highlightIdx].scrollIntoView({
                        block: 'nearest'
                    });
                    opts.forEach(function(o, i) {
                        if (i !== highlightIdx) o.classList.remove('highlight');
                    });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    highlightIdx = highlightIdx > 0 ? highlightIdx - 1 : opts.length - 1;
                    opts[highlightIdx].classList.add('highlight');
                    opts[highlightIdx].scrollIntoView({
                        block: 'nearest'
                    });
                    opts.forEach(function(o, i) {
                        if (i !== highlightIdx) o.classList.remove('highlight');
                    });
                } else if (e.key === 'Enter' && highlightIdx >= 0 && opts[highlightIdx]) {
                    e.preventDefault();
                    var o = opts[highlightIdx];
                    if (o.dataset.uid === '') clearChoice();
                    else selectUser(o.dataset.uid, o.dataset.name, o.dataset.nameEn, o.dataset.email, o.textContent);
                } else if (e.key === 'Escape') {
                    hideList();
                }
            });
        }

        if (listEl) {
            listEl.addEventListener('click', function(e) {
                var opt = e.target.closest('[role="option"]');
                if (!opt) return;
                if (opt.dataset.uid === '') clearChoice();
                else selectUser(opt.dataset.uid, opt.dataset.name, opt.dataset.nameEn, opt.dataset.email, opt.textContent);
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

    function initProgramTags() {
        var tagsEl = document.getElementById('program-tags');
        var hiddenEl = document.getElementById('program-assignments-hidden');
        var programButtons = document.getElementById('program-buttons');
        var hintEl = document.getElementById('program-tags-hint');
        if (!tagsEl || !hiddenEl || !programButtons) return;

        function getSelectedIds() {
            var ids = [];
            hiddenEl.querySelectorAll('input[name="program_assignments[program_id][]"]').forEach(function(inp) {
                ids.push(inp.value);
            });
            return ids;
        }

        function escapeHtml(s) {
            if (!s) return '';
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        function addTag(programId, programLabel) {
            if (!programId) return;
            var selected = getSelectedIds();
            if (selected.indexOf(String(programId)) !== -1) return;

            // Set first program as primary automatically
            var isFirst = selected.length === 0;
            if (isFirst) {
                document.getElementById('primary_program_id').value = programId;
            }

            var wrap = document.createElement('div');
            wrap.innerHTML = '<input type="hidden" name="program_assignments[program_id][]" value="' + escapeHtml(String(programId)) + '">';
            hiddenEl.appendChild(wrap);
            var span = document.createElement('span');
            span.className = 'program-tag';
            span.setAttribute('data-program-id', programId);

            // Add primary indicator
            if (isFirst) {
                var primaryBadge = document.createElement('span');
                primaryBadge.className = 'primary-badge';
                primaryBadge.style.cssText = 'background: #fbbf24; color: #1e293b; font-size: 0.65rem; padding: 0.1rem 0.3rem; border-radius: 4px; margin-right: 0.25rem;';
                primaryBadge.textContent = 'หลัก';
                span.appendChild(primaryBadge);
            }

            span.appendChild(document.createTextNode(programLabel || 'หลักสูตร'));

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
                    var hiddenRows = hiddenEl.children;
                    var hiddenIdx = idx - 1;
                    if (hiddenRows[hiddenIdx]) hiddenEl.removeChild(hiddenRows[hiddenIdx]);
                }
                span.remove();
                if (tagsEl.querySelectorAll('.program-tag').length === 0 && hintEl) hintEl.style.display = '';

                // Update primary if first tag was removed
                var remainingTags = tagsEl.querySelectorAll('.program-tag');
                if (remainingTags.length > 0) {
                    var firstTag = remainingTags[0];
                    if (!firstTag.querySelector('.primary-badge')) {
                        var primaryBadge = document.createElement('span');
                        primaryBadge.className = 'primary-badge';
                        primaryBadge.style.cssText = 'background: #fbbf24; color: #1e293b; font-size: 0.65rem; padding: 0.1rem 0.3rem; border-radius: 4px; margin-right: 0.25rem;';
                        primaryBadge.textContent = 'หลัก';
                        firstTag.insertBefore(primaryBadge, firstTag.firstChild);
                    }
                    document.getElementById('primary_program_id').value = firstTag.getAttribute('data-program-id');
                } else {
                    document.getElementById('primary_program_id').value = '';
                }

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
        tagsEl.addEventListener('dragleave', function() {
            tagsEl.classList.remove('drag-over');
        });
        tagsEl.addEventListener('drop', function(e) {
            e.preventDefault();
            tagsEl.classList.remove('drag-over');
            var programId = e.dataTransfer.getData('programId');
            var programLabel = e.dataTransfer.getData('programLabel') || e.dataTransfer.getData('text/plain') || '';
            addTag(programId, programLabel);
        });

        programButtons.querySelectorAll('.program-draggable-btn').forEach(function(btn) {
            btn.addEventListener('dragstart', function(e) {
                e.dataTransfer.setData('programId', btn.getAttribute('data-program-id'));
                e.dataTransfer.setData('programLabel', btn.getAttribute('data-program-label') || btn.textContent);
                e.dataTransfer.setData('text/plain', btn.getAttribute('data-program-label') || btn.textContent);
                e.dataTransfer.effectAllowed = 'copy';
            });
            btn.addEventListener('click', function() {
                if (btn.classList.contains('added')) return;
                var programId = btn.getAttribute('data-program-id');
                var programLabel = btn.getAttribute('data-program-label') || btn.textContent;
                addTag(programId, programLabel);
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProgramTags);
    } else {
        initProgramTags();
    }

    // ถ้าเลือกประธานหลักสูตร ต้องมีอย่างน้อย 1 หลักสูตร
    (function() {
        var form = document.getElementById('org-create-form');
        if (!form) return;
        form.addEventListener('submit', function(e) {
            var pos = document.getElementById('position');
            if (pos && pos.value === 'ประธานหลักสูตร') {
                var tags = document.querySelectorAll('#program-tags .program-tag');
                if (!tags.length) {
                    e.preventDefault();
                    alert('เมื่อตำแหน่งเป็น ประธานหลักสูตร กรุณาเพิ่มอย่างน้อยหนึ่งหลักสูตร');
                    return false;
                }
            }
        });
    })();
</script>

<?= $this->endSection() ?>
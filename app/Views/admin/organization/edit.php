<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>แก้ไขตำแหน่งในโครงสร้างองค์กร</h2>
        <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ย้อนกลับ</a>
    </div>
    <div class="card-body">
        <?php
        $primaryProgramId = null;
        foreach ($personnel_programs ?? [] as $pp) {
            if (!empty($pp['is_primary'])) {
                $primaryProgramId = (int)($pp['program_id'] ?? 0);
                break;
            }
        }
        if ($primaryProgramId === null && !empty($personnel_programs)) {
            $primaryProgramId = (int)($personnel_programs[0]['program_id'] ?? 0);
        }
        $currentOrgUnitName = '';
        $currentOrgUnitId = (int)($person['organization_unit_id'] ?? 0);
        if ($primaryProgramId > 0 && !empty($programs)) {
            foreach ($programs as $pr) {
                if ((int)($pr['id'] ?? 0) === $primaryProgramId) {
                    $currentOrgUnitName = $pr['department_name'] ?? $pr['department_name_th'] ?? $pr['department_name_en'] ?? '';
                    if ($currentOrgUnitId === 0) {
                        $currentOrgUnitId = (int)($pr['organization_unit_id'] ?? 0);
                    }
                    break;
                }
            }
        }
        ?>
        <?php if (!empty($organization_units)): ?>
        <p class="text-muted" style="margin-bottom: 1rem; font-size: 0.9rem;">โครงสร้างองค์กรใช้ <strong>5 หน่วยงาน</strong>: ผู้บริหาร, สำนักงานคณบดี, หัวหน้าหน่วยการจัดการงานวิจัย, หลักสูตรระดับปริญญาตรี, หลักสูตรระดับบัณฑิตศึกษา — การเลือกสาขาจะกำหนดหน่วยงานสังกัดโดยอัตโนมัติ</p>
        <?php endif; ?>
        <?php if ($currentOrgUnitName !== ''): ?>
        <p style="margin-bottom: 1rem; font-size: 0.9rem;"><strong>หน่วยงานสังกัดปัจจุบัน (จากสาขาหลัก):</strong> <?= esc($currentOrgUnitName) ?></p>
        <?php endif; ?>
        <?php if (session('errors')): ?>
            <div class="alert alert-danger" style="margin-bottom: 1rem;">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('admin/organization/update/' . $person['id']) ?>" method="post" enctype="multipart/form-data" id="org-edit-form">
            <?= csrf_field() ?>

            <h3 style="font-size: 1rem; margin-bottom: 1rem; color: var(--color-gray-700);">รูปภาพ</h3>
            <?php
            $personImg = $person['image'] ?? '';
            $personImgUrl = '';
            if ($personImg !== '') {
                if (strpos($personImg, 'http') === 0) $personImgUrl = $personImg;
                else $personImgUrl = base_url('serve/thumb/staff/' . basename($personImg));
            }
            ?>
            <div class="form-group">
                <label class="form-label" style="font-weight: 500; color: var(--color-gray-700); margin-bottom: 0.5rem;">รูปภาพบุคลากร</label>
                <div style="display: flex; flex-direction: column; align-items: center; gap: 1.5rem; border: 2px dashed #cbd5e1; padding: 2rem; border-radius: 12px; background: #f8fafc; transition: all 0.2s;">
                    <div style="position: relative; width: 160px; height: 160px; flex-shrink: 0;">
                        <?php 
                        $fallbackImg = base_url('assets/images/placeholder.png');
                        $currentImg = $personImgUrl ?: $fallbackImg;
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
                                <span>เลือกรูปภาพใหม่</span>
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
            <?php if (!empty($person['user_uid'])): ?>
                <p style="font-size: 0.85rem; color: var(--color-gray-500); margin: -0.5rem 0 0.5rem;">บุคลากรนี้ลิงก์กับตาราง user — ชื่อดึงจาก user ได้ (ไม่บังคับกรอก)</p>
            <?php endif; ?>
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
                    <input type="text" name="name" id="name" class="form-control" value="<?= esc(old('name', $person['name'] ?? '')) ?>" placeholder="ชื่อ นามสกุล">
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

            <h3 style="font-size: 1rem; margin: 1.5rem 0 1rem; color: var(--color-gray-700);">สาขาในหลักสูตร (อาจารย์ 1 คน สังกัดได้หลายสาขา)</h3>
            <?php if (!empty($programs)): ?>
                <?php
                $assignments = old('program_assignments');
                $rows = [];
                if (is_array($assignments) && !empty($assignments['program_id'])) {
                    foreach ($assignments['program_id'] as $i => $pid) {
                        $pid = (int)$pid;
                        if ($pid <= 0) continue;
                        $rows[] = ['program_id' => $pid];
                    }
                }
                if (empty($rows) && !empty($personnel_programs)) {
                    foreach ($personnel_programs as $pp) {
                        $pid = (int)($pp['program_id'] ?? 0);
                        if ($pid <= 0) continue;
                        $rows[] = ['program_id' => $pid];
                    }
                }
                if (empty($rows) && !empty($person['program_id'])) {
                    $rows[] = ['program_id' => (int)$person['program_id']];
                }
                $programLabels = [];
                foreach ($programs as $pr) {
                    $l = $pr['name_th'] ?? $pr['name'] ?? '';
                    $orgName = $pr['department_name'] ?? $pr['department_name_th'] ?? $pr['department_name_en'] ?? '';
                    if ($orgName !== '') {
                        $l .= ' (' . $orgName . ')';
                    }
                    $programLabels[(int)$pr['id']] = $l;
                }
                ?>
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

                    .program-draggable-btn.added {
                        opacity: 0.5;
                        cursor: not-allowed;
                        pointer-events: none;
                    }
                </style>
                <label class="form-label" style="display: block; margin-bottom: 0.25rem;">สาขาที่เลือก <span style="color: var(--color-gray-500); font-weight: normal;">(วางสาขาจากด้านล่างที่นี่)</span></label>
                <div id="program-tags" class="program-tags-wrap" data-drop-zone="1">
                    <?php if (empty($rows)): ?>
                        <span class="drop-hint" id="program-tags-hint">ลากปุ่มสาขาจากด้านล่างมาวางที่นี่ หรือคลิกปุ่มเพื่อเพิ่ม</span>
                    <?php else: ?>
                        <span class="drop-hint" id="program-tags-hint" style="display: none;">ลากปุ่มสาขาจากด้านล่างมาวางที่นี่ หรือคลิกปุ่มเพื่อเพิ่ม</span>
                        <?php foreach ($rows as $i => $row):
                            $label = $programLabels[$row['program_id']] ?? 'หลักสูตร';
                            $isPrimary = ($i === 0);
                        ?>
                            <span class="program-tag" data-program-id="<?= (int)$row['program_id'] ?>">
                                <?php if ($isPrimary): ?>
                                    <span class="primary-badge" style="background: #fbbf24; color: #1e293b; font-size: 0.65rem; padding: 0.1rem 0.3rem; border-radius: 4px; margin-right: 0.25rem;">หลัก</span>
                                <?php endif; ?>
                                <span><?= esc($label) ?></span>
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
                <div class="program-buttons-wrap">
                    <label class="form-label">เลือกสาขา — ลากไปใส่กล่องด้านบน หรือคลิกเพื่อเพิ่ม</label>
                    <div id="program-buttons">
                        <?php foreach ($programs as $pr):
                            $label = $pr['name_th'] ?? $pr['name'] ?? '';
                            $orgName = $pr['department_name'] ?? $pr['department_name_th'] ?? $pr['department_name_en'] ?? '';
                            if ($orgName !== '') {
                                $label .= ' (' . $orgName . ')';
                            }
                            $already = in_array((int)$pr['id'], array_column($rows, 'program_id'), true);
                        ?>
                            <button type="button" class="program-draggable-btn <?= $already ? 'added' : '' ?>" draggable="true" data-program-id="<?= (int)$pr['id'] ?>" data-program-label="<?= esc($label) ?>"><?= esc($label) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <input type="hidden" name="primary_program_id" id="primary_program_id" value="<?= !empty($rows) ? (int)$rows[0]['program_id'] : '' ?>">
                <small style="color: var(--color-gray-500); display: block; margin-top: 0.5rem;">สาขาแรกจะเป็นสาขาหลัก — ใช้กำหนดหน่วยงานสังกัด (หลักสูตรป.ตรี หรือ หลักสูตรบัณฑิตศึกษา)</small>
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
                <small style="color: var(--color-gray-500);">ถ้าเป็นประธานหลักสูตร กรุณาเพิ่มอย่างน้อยหนึ่งสาขาด้านบน</small>
            </div>
            <div class="form-group">
                <label class="form-label" for="position_detail">รายละเอียดตำแหน่ง</label>
                <input type="text" name="position_detail" id="position_detail" class="form-control" value="<?= esc(old('position_detail', $person['position_detail'] ?? '')) ?>" placeholder="เช่น ฝ่ายกิจกรรมนักศึกษา, ฝ่ายวิจัยและนวัตกรรม">
                <small style="color: var(--color-gray-500);">ระบุฝ่าย/หน่วย เช่น รองคณบดี ฝ่ายกิจกรรมนักศึกษา</small>
            </div>

            <div class="form-group">
                <label class="form-label" for="sort_order">ลำดับการแสดง (ตัวเลข)</label>
                <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= (int)($person['sort_order'] ?? 0) ?>" min="0" style="max-width: 120px;">
                <small style="color: var(--color-gray-500);">เลขน้อยแสดงก่อนภายในระดับเดียวกัน</small>
            </div>

            <?php if (!empty($organization_units)): ?>
            <div class="form-group">
                <label class="form-label" for="organization_unit_id">หน่วยงานสังกัด (organization_unit_id)</label>
                <select name="organization_unit_id" id="organization_unit_id" class="form-control" style="max-width: 360px;">
                    <option value="">— ตามสาขาหลัก (อัตโนมัติ) —</option>
                    <?php foreach ($organization_units as $ou): ?>
                        <option value="<?= (int)($ou['id'] ?? 0) ?>" <?= (string)(old('organization_unit_id', $currentOrgUnitId)) === (string)($ou['id'] ?? '') ? 'selected' : '' ?>><?= esc($ou['name_th'] ?? $ou['code'] ?? '') ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--color-gray-500);">บุคลากรสำนักงาน (หัวหน้าสำนักงาน/เจ้าหน้าที่) ให้เลือก "สำนักงานคณบดี" ถ้าไม่มีสาขา</small>
            </div>
            <?php endif; ?>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary">บันทึก</button>
                <a href="<?= base_url('admin/organization') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<script>
    var programsForChair = <?= json_encode(array_values(array_map(function ($p) {
                                return ['id' => (int)($p['id'] ?? 0), 'name' => $p['name_th'] ?? $p['name_en'] ?? ''];
                            }, $programs ?? []))) ?>;

    function initProgramTagsEdit() {
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
            wrap.className = 'program-assignment-row';
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
                    if (hiddenEl.children[idx - 1]) hiddenEl.removeChild(hiddenEl.children[idx - 1]);
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

        tagsEl.querySelectorAll('.program-tag-remove').forEach(function(btn) {
            btn.onclick = function() {
                var span = this.closest('.program-tag');
                var idx = Array.prototype.indexOf.call(tagsEl.children, span);
                if (idx !== -1 && idx > 0 && hiddenEl.children[idx - 1]) hiddenEl.removeChild(hiddenEl.children[idx - 1]);
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
        });

        programButtons.querySelectorAll('.program-draggable-btn').forEach(function(btn) {
            btn.addEventListener('dragstart', function(e) {
                if (btn.classList.contains('added')) {
                    e.preventDefault();
                    return;
                }
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
        document.addEventListener('DOMContentLoaded', initProgramTagsEdit);
    } else {
        initProgramTagsEdit();
    }
</script>

<?= $this->endSection() ?>
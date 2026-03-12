<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<?php
$isEdit = isset($service) && $service !== null;
$formAction = $isEdit ? base_url('admin/academic-services/update/' . $service['id']) : base_url('admin/academic-services/store');
$pageLabel = $isEdit ? 'แก้ไขรายการบริการวิชาการ' : 'เพิ่มรายการบริการวิชาการ';
$s = $service ?? [];
$currentYear = (int) date('Y') + 543;
$yearOptions = [];
for ($y = $currentYear - 2; $y <= $currentYear + 1; $y++) {
    $yearOptions[] = (string) $y;
}
?>

<div class="card">
    <div class="card-header">
        <h2><?= $pageLabel ?></h2>
        <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary">← กลับ</a>
    </div>

    <div class="card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="post" id="academicServiceForm">
            <?= csrf_field() ?>

            <!-- ส่วนที่ 1 ข้อมูลทั่วไป -->
            <div class="form-section" style="margin-bottom: 2rem;">
                <h3 class="form-section-title">ส่วนที่ 1 ข้อมูลทั่วไปของโครงการ/กิจกรรมบริการวิชาการ</h3>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label for="academic_year" class="form-label">ปีการศึกษา (พ.ศ.)</label>
                        <select id="academic_year" name="academic_year" class="form-control">
                            <option value="">— เลือก —</option>
                            <?php foreach ($yearOptions as $y): ?>
                                <option value="<?= esc($y) ?>" <?= ($s['academic_year'] ?? '') === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label for="service_date" class="form-label">วัน/เดือน/ปี ที่บริการวิชาการ <span class="required">*</span></label>
                        <input type="date" id="service_date" name="service_date" class="form-control" required
                               value="<?= esc($s['service_date'] ?? old('service_date') ?? date('Y-m-d')) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="title" class="form-label">ชื่อโครงการ/กิจกรรม/หัวข้อ <span class="required">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" required
                           value="<?= esc($s['title'] ?? old('title')) ?>" placeholder="ชื่อโครงการหรือกิจกรรม">
                </div>

                <div class="form-group">
                    <label class="form-label">เจ้าของโครงการ/กิจกรรม</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="project_owner_type" value="internal_faculty" <?= ($s['project_owner_type'] ?? '') === 'internal_faculty' ? 'checked' : '' ?>> โครงการภายในคณะ</label>
                        <label><input type="radio" name="project_owner_type" value="external" <?= ($s['project_owner_type'] ?? '') === 'external' ? 'checked' : '' ?>> โครงการภายนอกที่มาขอความอนุเคราะห์</label>
                    </div>
                    <input type="text" name="project_owner_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['project_owner_spec'] ?? old('project_owner_spec')) ?>"
                           placeholder="ระบุหน่วยงาน/โครงการ (เมื่อเลือกภายนอก)">
                </div>

                <div class="form-group">
                    <label class="form-label">สถานที่จัดกิจกรรม</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="venue_type" value="within_faculty" <?= ($s['venue_type'] ?? '') === 'within_faculty' ? 'checked' : '' ?>> ภายในคณะ</label>
                        <label><input type="radio" name="venue_type" value="within_university" <?= ($s['venue_type'] ?? '') === 'within_university' ? 'checked' : '' ?>> ภายในมหาวิทยาลัย (นอกคณะ)</label>
                        <label><input type="radio" name="venue_type" value="outside" <?= ($s['venue_type'] ?? '') === 'outside' ? 'checked' : '' ?>> ภายนอกมหาวิทยาลัย</label>
                    </div>
                    <input type="text" name="venue_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['venue_spec'] ?? old('venue_spec')) ?>" placeholder="ระบุสถานที่ (เมื่อเลือกภายนอก)">
                </div>

                <div class="form-group">
                    <label class="form-label">กลุ่มผู้รับการบริการวิชาการ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">เลือกประเภทแล้วระบุชื่อ (ค้นหาจากบุคลากรในระบบ)</p>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
                        <label><input type="radio" name="target_group_type" value="internal" <?= ($s['target_group_type'] ?? '') === 'internal' ? 'checked' : '' ?>> บุคลากรภายในมหาวิทยาลัย</label>
                        <label><input type="radio" name="target_group_type" value="external" <?= ($s['target_group_type'] ?? '') === 'external' ? 'checked' : '' ?>> บุคลากรภายนอกมหาวิทยาลัย</label>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <input type="text" id="targetGroupSearch" class="form-control" placeholder="พิมพ์ชื่อหรืออีเมล (อย่างน้อย 2 ตัวอักษร)" style="max-width: 280px;" autocomplete="off">
                    </div>
                    <div id="targetGroupSearchResults" class="participant-results" style="display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; min-width: 280px;"></div>
                    <div id="targetGroupTags" class="tag-list tag-list-block"></div>
                    <input type="hidden" name="target_group_spec" id="targetGroupSpec" value="<?= esc($s['target_group_spec'] ?? old('target_group_spec') ?? '') ?>">
                </div>
            </div>

            <!-- ส่วนที่ 2 การดำเนินงาน -->
            <div class="form-section" style="margin-bottom: 2rem;">
                <h3 class="form-section-title">ส่วนที่ 2 การดำเนินงานบริการวิชาการ</h3>

                <div class="form-group" id="responsibleBlock">
                    <label class="form-label">ผู้รับผิดชอบการดำเนินงาน</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
                        <label><input type="radio" name="responsible_type" value="faculty" <?= ($s['responsible_type'] ?? '') === 'faculty' ? 'checked' : '' ?>> ระดับคณะ</label>
                        <label><input type="radio" name="responsible_type" value="program" <?= ($s['responsible_type'] ?? '') === 'program' ? 'checked' : '' ?>> ระดับหลักสูตร</label>
                        <label><input type="radio" name="responsible_type" value="person" <?= ($s['responsible_type'] ?? '') === 'person' ? 'checked' : '' ?>> ระดับบุคคล</label>
                    </div>
                    <div id="responsibleProgramWrap" style="display: none; margin-bottom: 0.5rem;">
                        <span id="responsibleProgramLabel" style="margin-right: 0.5rem;"></span>
                        <button type="button" id="btnSetProgram" class="btn btn-secondary btn-sm">ระบุหลักสูตร</button>
                        <input type="hidden" name="responsible_program" id="responsibleProgram" value="<?= esc($s['responsible_program'] ?? old('responsible_program') ?? '') ?>">
                    </div>
                    <div id="responsiblePersonWrap" style="display: none;">
                        <p class="form-text" style="margin-bottom: 0.5rem;">ค้นหาจากระบบหรือเพิ่มชื่อเอง</p>
                        <div style="margin-bottom: 0.5rem;">
                            <input type="text" id="responsibleSearch" class="form-control" placeholder="พิมพ์ชื่อหรืออีเมล (อย่างน้อย 2 ตัวอักษร)" style="max-width: 280px;" autocomplete="off">
                            <button type="button" id="btnResponsibleManual" class="btn btn-secondary btn-sm" style="margin-left: 0.5rem;">เพิ่มชื่อเอง</button>
                        </div>
                        <div id="responsibleSearchResults" class="participant-results" style="display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; min-width: 280px;"></div>
                        <div id="responsibleTags" class="tag-list tag-list-block"></div>
                        <input type="hidden" name="responsible_person_text" id="responsiblePersonText" value="<?= esc($s['responsible_person_text'] ?? old('responsible_person_text') ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">ผู้ร่วมบริการวิชาการ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">เลือกชื่อบุคลากรในระบบเท่านั้น (ไม่แสดงชื่อที่อยู่ในผู้รับผิดชอบแล้ว)</p>
                    <div style="margin-bottom: 0.5rem;">
                        <input type="text" id="participantSearch" class="form-control" placeholder="พิมพ์ชื่อหรืออีเมล (อย่างน้อย 2 ตัวอักษร)" style="max-width: 280px;" autocomplete="off">
                    </div>
                    <div id="participantSearchResults" class="participant-results" style="display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; min-width: 280px;"></div>
                    <div id="participantsBody" class="tag-list tag-list-block" style="margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">ลักษณะการบริการวิชาการ</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem 1.5rem;">
                        <label><input type="radio" name="service_type" value="training_seminar" <?= ($s['service_type'] ?? '') === 'training_seminar' ? 'checked' : '' ?>> อบรม/สัมมนา</label>
                        <label><input type="radio" name="service_type" value="workshop" <?= ($s['service_type'] ?? '') === 'workshop' ? 'checked' : '' ?>> ฝึกปฏิบัติการ/Workshop</label>
                        <label><input type="radio" name="service_type" value="consultant" <?= ($s['service_type'] ?? '') === 'consultant' ? 'checked' : '' ?>> ที่ปรึกษาทางวิชาการ</label>
                        <label><input type="radio" name="service_type" value="lab_testing" <?= ($s['service_type'] ?? '') === 'lab_testing' ? 'checked' : '' ?>> บริการวิเคราะห์ทดสอบ/ห้องปฏิบัติการ</label>
                        <label><input type="radio" name="service_type" value="expert_evaluator" <?= ($s['service_type'] ?? '') === 'expert_evaluator' ? 'checked' : '' ?>> ผู้ทรงคุณวุฒิประเมินผล/ตัดสินการแข่งขัน</label>
                        <label><input type="radio" name="service_type" value="other" <?= ($s['service_type'] ?? '') === 'other' ? 'checked' : '' ?>> อื่นๆ</label>
                    </div>
                    <input type="text" name="service_type_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['service_type_spec'] ?? old('service_type_spec')) ?>" placeholder="ระบุ (เมื่อเลือกอื่นๆ)">
                </div>
            </div>

            <!-- ส่วนที่ 3 งบประมาณและค่าตอบแทน -->
            <div class="form-section" style="margin-bottom: 2rem;">
                <h3 class="form-section-title">ส่วนที่ 3 งบประมาณและค่าตอบแทน</h3>

                <div class="form-group">
                    <label class="form-label">แหล่งงบประมาณ</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="budget_source" value="university" <?= ($s['budget_source'] ?? '') === 'university' ? 'checked' : '' ?>> งบประมาณมหาวิทยาลัย</label>
                        <label><input type="radio" name="budget_source" value="faculty" <?= ($s['budget_source'] ?? '') === 'faculty' ? 'checked' : '' ?>> งบประมาณคณะ</label>
                        <label><input type="radio" name="budget_source" value="external" <?= ($s['budget_source'] ?? '') === 'external' ? 'checked' : '' ?>> งบประมาณจากหน่วยงานภายนอก</label>
                        <label><input type="radio" name="budget_source" value="other" <?= ($s['budget_source'] ?? '') === 'other' ? 'checked' : '' ?>> อื่นๆ</label>
                    </div>
                    <input type="text" name="budget_source_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['budget_source_spec'] ?? old('budget_source_spec')) ?>" placeholder="ระบุแหล่งงบประมาณ">
                </div>

                <div class="form-group">
                    <label class="form-label">ค่าตอบแทน</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="has_compensation" value="yes" <?= ($s['has_compensation'] ?? '') === 'yes' ? 'checked' : '' ?>> มีค่าตอบแทน</label>
                        <label><input type="radio" name="has_compensation" value="no" <?= ($s['has_compensation'] ?? '') === 'no' ? 'checked' : '' ?>> ไม่มีค่าตอบแทน</label>
                        <label><input type="radio" name="has_compensation" value="unknown" <?= ($s['has_compensation'] ?? '') === 'unknown' ? 'checked' : '' ?>> ไม่มีข้อมูล</label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">รายได้ที่เกิดขึ้นกับคณะ</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                        <label><input type="radio" name="revenue_option" value="none" <?= (($s['revenue_unknown'] ?? 0) == 0 && empty($s['revenue_amount'])) ? 'checked' : '' ?>> ไม่มี</label>
                        <label><input type="radio" name="revenue_option" value="amount" <?= !empty($s['revenue_amount']) ? 'checked' : '' ?>> มี</label>
                        <input type="number" name="revenue_amount" id="revenue_amount" class="form-control" step="0.01" min="0" placeholder="บาท" style="width: 140px;"
                               value="<?= isset($s['revenue_amount']) && $s['revenue_amount'] !== null && $s['revenue_amount'] !== '' ? esc($s['revenue_amount']) : '' ?>">
                        <label><input type="radio" name="revenue_option" value="unknown" <?= (($s['revenue_unknown'] ?? 0) == 1 ? 'checked' : '') ?>> ไม่มีข้อมูล</label>
                    </div>
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึก' ?></button>
                <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary">ยกเลิก</a>
            </div>
        </form>
    </div>
</div>

<style>
.form-section-title { font-size: 1rem; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 1px solid var(--color-gray-200, #e5e7eb); }
.participant-results { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.participant-results div { padding: 0.5rem 0.75rem; cursor: pointer; }
.participant-results div:hover { background: var(--color-gray-100, #f3f4f6); }
.tag-list .tag { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.25rem 0.5rem; background: var(--color-gray-200, #e5e7eb); border-radius: 6px; font-size: 0.875rem; }
.tag-list .tag button { background: none; border: none; cursor: pointer; padding: 0 0.2rem; color: #666; font-size: 1rem; line-height: 1; }
.tag-list .tag button:hover { color: #c00; }
.tag-list-block { display: flex; flex-direction: column; gap: 0.5rem; }
.tag-list-block .tag { display: flex; width: fit-content; }
.participant-row { display: flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0; border-bottom: 1px solid var(--color-gray-100, #f3f4f6); }
.participant-row .tag { flex: 1; }
</style>

<?= $this->endSection() ?>

<?php
$target_group_users = $s['target_group_users'] ?? [];
$responsible_users  = $s['responsible_users'] ?? [];
$initial_participants = array_map(function ($p) {
    return [
        'user_uid' => (int) ($p['user_uid'] ?? 0),
        'display_name' => $p['display_name'] ?? $p['display_label'] ?? '',
        'program_name' => $p['program_name'] ?? '',
        'role' => $p['role'] ?? 'co_participant',
    ];
}, $participants);
?>
<?= $this->section('scripts') ?>
<script>
(function() {
    var searchUrl = '<?= base_url('admin/academic-services/search-users') ?>';
    var initialTargetGroup = <?= json_encode($target_group_users) ?>;
    var initialResponsible = <?= json_encode($responsible_users) ?>;
    var initialParticipants = <?= json_encode($initial_participants) ?>;

    document.getElementById('revenue_amount').addEventListener('focus', function() {
        document.querySelector('input[name="revenue_option"][value="amount"]').checked = true;
    });

    // --- กลุ่มผู้รับการบริการวิชาการ: 2 ตัวเลือก + ระบุชื่อ (Tag) ---
    var targetGroupList = initialTargetGroup.slice();
    var $targetGroupTags = document.getElementById('targetGroupTags');
    var $targetGroupSpec = document.getElementById('targetGroupSpec');
    var $targetGroupSearch = document.getElementById('targetGroupSearch');
    var $targetGroupResults = document.getElementById('targetGroupSearchResults');
    var targetGroupSearchT = null;

    function renderTargetGroup() {
        $targetGroupTags.innerHTML = targetGroupList.map(function(t) {
            return '<span class="tag" data-uid="' + t.uid + '">' + (t.label || '').replace(/</g, '&lt;') + ' <button type="button" class="tag-remove" aria-label="ลบ">&times;</button></span>';
        }).join('');
        $targetGroupSpec.value = targetGroupList.length ? JSON.stringify(targetGroupList) : '';
        $targetGroupTags.querySelectorAll('.tag-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var uid = parseInt(btn.closest('.tag').getAttribute('data-uid'), 10);
                targetGroupList = targetGroupList.filter(function(x) { return x.uid !== uid; });
                renderTargetGroup();
            });
        });
    }
    function addTargetGroup(uid, label) {
        if (targetGroupList.some(function(x) { return x.uid === uid; })) return;
        targetGroupList.push({ uid: uid, label: label || '' });
        renderTargetGroup();
        $targetGroupSearch.value = '';
        $targetGroupResults.style.display = 'none';
    }
    function doTargetGroupSearch() {
        var q = ($targetGroupSearch.value || '').trim();
        if (q.length < 2) { $targetGroupResults.style.display = 'none'; return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(q)).then(function(r) { return r.json(); }).then(function(res) {
            if (res.status !== 'success' || !res.data || !res.data.length) {
                $targetGroupResults.innerHTML = '<div style="padding: 0.75rem;">ไม่พบรายชื่อ</div>';
            } else {
                $targetGroupResults.innerHTML = res.data.map(function(u) {
                    return '<div data-uid="' + u.uid + '" data-label="' + (u.label || '').replace(/"/g, '&quot;') + '">' + (u.label || u.email) + '</div>';
                }).join('');
                $targetGroupResults.querySelectorAll('div[data-uid]').forEach(function(div) {
                    div.addEventListener('click', function() { addTargetGroup(parseInt(div.getAttribute('data-uid'), 10), div.getAttribute('data-label')); });
                });
            }
            $targetGroupResults.style.display = 'block';
        });
    }
    renderTargetGroup();
    if ($targetGroupSearch) {
        $targetGroupSearch.addEventListener('input', function() { clearTimeout(targetGroupSearchT); targetGroupSearchT = setTimeout(doTargetGroupSearch, 300); });
        $targetGroupSearch.addEventListener('blur', function() { setTimeout(function() { $targetGroupResults.style.display = 'none'; }, 200); });
    }

    // --- ผู้รับผิดชอบ: 3 ตัวเลือก (คณะ / หลักสูตร + popup / บุคคล + tag หรือชื่อเอง) ---
    var responsibleList = initialResponsible.slice();
    var $responsibleTags = document.getElementById('responsibleTags');
    var $responsiblePersonText = document.getElementById('responsiblePersonText');
    var $responsibleSearch = document.getElementById('responsibleSearch');
    var $responsibleResults = document.getElementById('responsibleSearchResults');
    var $responsibleProgramWrap = document.getElementById('responsibleProgramWrap');
    var $responsiblePersonWrap = document.getElementById('responsiblePersonWrap');
    var $responsibleProgram = document.getElementById('responsibleProgram');
    var $responsibleProgramLabel = document.getElementById('responsibleProgramLabel');
    var responsibleSearchT = null;

    function getResponsibleType() {
        var r = document.querySelector('input[name="responsible_type"]:checked');
        return r ? r.value : 'faculty';
    }
    function updateResponsibleVisibility() {
        var t = getResponsibleType();
        $responsibleProgramWrap.style.display = t === 'program' ? 'block' : 'none';
        $responsiblePersonWrap.style.display = t === 'person' ? 'block' : 'none';
        if (t !== 'program') $responsibleProgram.value = '';
        if (t !== 'person') $responsiblePersonText.value = '';
        if (t === 'program' && $responsibleProgram.value) $responsibleProgramLabel.textContent = $responsibleProgram.value;
    }
    document.querySelectorAll('input[name="responsible_type"]').forEach(function(r) {
        r.addEventListener('change', updateResponsibleVisibility);
    });

    document.getElementById('btnSetProgram').addEventListener('click', function() {
        var current = $responsibleProgram.value || '';
        if (typeof Swal === 'undefined') {
            var v = prompt('กรอกชื่อหลักสูตร', current);
            if (v !== null) { $responsibleProgram.value = v; $responsibleProgramLabel.textContent = v; }
            return;
        }
        Swal.fire({
            title: 'ระบุหลักสูตร',
            input: 'text',
            inputValue: current,
            inputPlaceholder: 'ชื่อหลักสูตร/สาขาวิชา',
            showCancelButton: true,
            confirmButtonText: 'ตกลง',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed && result.value !== undefined) {
                var val = (result.value || '').trim();
                $responsibleProgram.value = val;
                $responsibleProgramLabel.textContent = val || '(ยังไม่ระบุ)';
            }
        });
    });

    function renderResponsible() {
        $responsibleTags.innerHTML = responsibleList.map(function(t, i) {
            return '<span class="tag" data-idx="' + i + '">' + (t.label || '').replace(/</g, '&lt;') + ' <button type="button" class="tag-remove" aria-label="ลบ">&times;</button></span>';
        }).join('');
        $responsiblePersonText.value = responsibleList.length ? JSON.stringify(responsibleList) : '';
        $responsibleTags.querySelectorAll('.tag-remove').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.closest('.tag').getAttribute('data-idx'), 10);
                responsibleList.splice(idx, 1);
                renderResponsible();
            });
        });
    }
    function addResponsible(uid, label) {
        if (responsibleList.some(function(x) { return x.uid === uid && x.label === label; })) return;
        responsibleList.push({ uid: uid, label: label || '' });
        renderResponsible();
        $responsibleSearch.value = '';
        $responsibleResults.style.display = 'none';
    }
    function doResponsibleSearch() {
        var q = ($responsibleSearch.value || '').trim();
        if (q.length < 2) { $responsibleResults.style.display = 'none'; return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(q)).then(function(r) { return r.json(); }).then(function(res) {
            if (res.status !== 'success' || !res.data || !res.data.length) {
                $responsibleResults.innerHTML = '<div style="padding: 0.75rem;">ไม่พบรายชื่อ</div>';
            } else {
                $responsibleResults.innerHTML = res.data.map(function(u) {
                    return '<div data-uid="' + u.uid + '" data-label="' + (u.label || '').replace(/"/g, '&quot;') + '">' + (u.label || u.email) + '</div>';
                }).join('');
                $responsibleResults.querySelectorAll('div[data-uid]').forEach(function(div) {
                    div.addEventListener('click', function() { addResponsible(parseInt(div.getAttribute('data-uid'), 10), div.getAttribute('data-label')); });
                });
            }
            $responsibleResults.style.display = 'block';
        });
    }
    document.getElementById('btnResponsibleManual').addEventListener('click', function() {
        if (typeof Swal === 'undefined') {
            var name = prompt('ชื่อ-นามสกุล');
            if (name && (name = name.trim())) addResponsible(0, name);
            return;
        }
        Swal.fire({
            title: 'เพิ่มชื่อเอง',
            input: 'text',
            inputPlaceholder: 'ชื่อ-นามสกุล',
            showCancelButton: true,
            confirmButtonText: 'เพิ่ม',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed && result.value && (result.value = result.value.trim())) addResponsible(0, result.value);
        });
    });
    renderResponsible();
    if ($responsibleSearch) {
        $responsibleSearch.addEventListener('input', function() { clearTimeout(responsibleSearchT); responsibleSearchT = setTimeout(doResponsibleSearch, 300); });
        $responsibleSearch.addEventListener('blur', function() { setTimeout(function() { $responsibleResults.style.display = 'none'; }, 200); });
    }
    updateResponsibleVisibility();
    if (getResponsibleType() === 'program' && $responsibleProgram.value) $responsibleProgramLabel.textContent = $responsibleProgram.value;
    if (getResponsibleType() === 'person' && responsibleList.length) $responsiblePersonWrap.style.display = 'block';

    // --- ผู้ร่วมบริการวิชาการ: จาก user table เท่านั้น, ไม่แสดงชื่อที่อยู่ในผู้รับผิดชอบ, 1 tag 1 บรรทัด ---
    var participantList = initialParticipants.slice();
    var $body = document.getElementById('participantsBody');
    var $participantSearch = document.getElementById('participantSearch');
    var $participantResults = document.getElementById('participantSearchResults');
    var participantSearchT = null;

    function getExcludeUids() {
        return responsibleList.filter(function(x) { return x.uid > 0; }).map(function(x) { return x.uid; });
    }
    function renderParticipantRows() {
        var html = '';
        participantList.forEach(function(p, i) {
            var label = (p.display_name || '-') + (p.program_name ? ' (' + p.program_name + ')' : '');
            html += '<div class="participant-row" data-idx="' + i + '">' +
                '<input type="hidden" name="participants[' + i + '][user_uid]" value="' + (p.user_uid || '') + '">' +
                '<input type="hidden" name="participants[' + i + '][display_name]" value="' + (p.display_name || '').replace(/"/g, '&quot;') + '">' +
                '<input type="hidden" name="participants[' + i + '][program_name]" value="' + (p.program_name || '').replace(/"/g, '&quot;') + '">' +
                '<input type="hidden" name="participants[' + i + '][role]" value="co_participant">' +
                '<span class="tag">' + label.replace(/</g, '&lt;') + '</span>' +
                '<button type="button" class="btn btn-danger btn-sm remove-participant">ลบ</button></div>';
        });
        $body.innerHTML = html;
        $body.querySelectorAll('.remove-participant').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var idx = parseInt(btn.closest('.participant-row').getAttribute('data-idx'), 10);
                participantList.splice(idx, 1);
                renderParticipantRows();
            });
        });
    }
    function addParticipant(uid, displayName, programName) {
        if (participantList.some(function(x) { return x.user_uid === uid; })) return;
        participantList.push({ user_uid: uid, display_name: displayName || '', program_name: programName || '', role: 'co_participant' });
        renderParticipantRows();
        $participantSearch.value = '';
        $participantResults.style.display = 'none';
    }
    function doParticipantSearch() {
        var q = ($participantSearch.value || '').trim();
        if (q.length < 2) { $participantResults.style.display = 'none'; return; }
        var exclude = getExcludeUids().join(',');
        var url = searchUrl + '?q=' + encodeURIComponent(q);
        if (exclude) url += '&exclude_uids=' + encodeURIComponent(exclude);
        fetch(url).then(function(r) { return r.json(); }).then(function(res) {
            if (res.status !== 'success' || !res.data || !res.data.length) {
                $participantResults.innerHTML = '<div style="padding: 0.75rem;">ไม่พบรายชื่อ (หรืออยู่ในผู้รับผิดชอบแล้ว)</div>';
            } else {
                $participantResults.innerHTML = res.data.map(function(u) {
                    return '<div data-uid="' + u.uid + '" data-label="' + (u.label || '').replace(/"/g, '&quot;') + '" data-program="">' + (u.label || u.email) + '</div>';
                }).join('');
                $participantResults.querySelectorAll('div[data-uid]').forEach(function(div) {
                    div.addEventListener('click', function() {
                        addParticipant(parseInt(div.getAttribute('data-uid'), 10), div.getAttribute('data-label'), div.getAttribute('data-program') || '');
                    });
                });
            }
            $participantResults.style.display = 'block';
        });
    }
    renderParticipantRows();
    if ($participantSearch) {
        $participantSearch.addEventListener('input', function() { clearTimeout(participantSearchT); participantSearchT = setTimeout(doParticipantSearch, 300); });
        $participantSearch.addEventListener('blur', function() { setTimeout(function() { $participantResults.style.display = 'none'; }, 200); });
    }
})();
</script>
<?= $this->endSection() ?>

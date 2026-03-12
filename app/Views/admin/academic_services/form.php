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
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="target_group_type" value="internal" <?= ($s['target_group_type'] ?? '') === 'internal' ? 'checked' : '' ?>> บุคคลภายในมหาวิทยาลัย</label>
                        <label><input type="radio" name="target_group_type" value="external" <?= ($s['target_group_type'] ?? '') === 'external' ? 'checked' : '' ?>> บุคคลภายนอกมหาวิทยาลัย</label>
                    </div>
                    <input type="text" name="target_group_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['target_group_spec'] ?? old('target_group_spec')) ?>" placeholder="ระบุกลุ่ม (เมื่อเลือกภายนอก)">
                </div>
            </div>

            <!-- ส่วนที่ 2 การดำเนินงาน -->
            <div class="form-section" style="margin-bottom: 2rem;">
                <h3 class="form-section-title">ส่วนที่ 2 การดำเนินงานบริการวิชาการ</h3>

                <div class="form-group">
                    <label class="form-label">ผู้รับผิดชอบการดำเนินงาน</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 1rem;">
                        <label><input type="radio" name="responsible_type" value="faculty" <?= ($s['responsible_type'] ?? '') === 'faculty' ? 'checked' : '' ?>> คณะ</label>
                        <label><input type="radio" name="responsible_type" value="program" <?= ($s['responsible_type'] ?? '') === 'program' ? 'checked' : '' ?>> หลักสูตร/สาขาวิชา</label>
                        <label><input type="radio" name="responsible_type" value="person" <?= ($s['responsible_type'] ?? '') === 'person' ? 'checked' : '' ?>> บุคคล</label>
                    </div>
                    <input type="text" name="responsible_program" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['responsible_program'] ?? old('responsible_program')) ?>" placeholder="ระบุหลักสูตร/สาขาวิชา">
                    <input type="text" name="responsible_person_text" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['responsible_person_text'] ?? old('responsible_person_text')) ?>" placeholder="ชื่อ-นามสกุล / หลักสูตร (เมื่อเลือกบุคคล)">
                </div>

                <div class="form-group">
                    <label class="form-label">ผู้ร่วมบริการวิชาการ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">ค้นหาชื่อหรืออีเมลบุคลากรในระบบ หรือกรอกชื่อ-หลักสูตรเอง</p>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.75rem;">
                        <input type="text" id="participantSearch" class="form-control" placeholder="พิมพ์ชื่อหรืออีเมล (อย่างน้อย 2 ตัวอักษร)" style="max-width: 280px;" autocomplete="off">
                        <button type="button" id="btnAddParticipantManual" class="btn btn-secondary">เพิ่มชื่อเอง</button>
                    </div>
                    <div id="participantSearchResults" class="participant-results" style="display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; min-width: 280px;"></div>
                    <table class="table" id="participantsTable" style="margin-top: 0.5rem;">
                        <thead>
                            <tr>
                                <th>ชื่อ-นามสกุล / หลักสูตร</th>
                                <th style="width: 100px;">บทบาท</th>
                                <th style="width: 80px;"></th>
                            </tr>
                        </thead>
                        <tbody id="participantsBody">
                            <?php foreach ($participants as $i => $p): ?>
                                <tr data-uid="<?= (int)($p['user_uid'] ?? 0) ?>">
                                    <td>
                                        <input type="hidden" name="participants[<?= $i ?>][user_uid]" value="<?= (int)($p['user_uid'] ?? 0) ?>">
                                        <input type="hidden" name="participants[<?= $i ?>][display_name]" value="<?= esc($p['display_name'] ?? $p['display_label'] ?? '') ?>">
                                        <input type="hidden" name="participants[<?= $i ?>][program_name]" value="<?= esc($p['program_name'] ?? '') ?>">
                                        <?= esc($p['display_label'] ?? $p['display_name'] ?? '-') ?>
                                        <?php if (!empty($p['program_name'])): ?> (<?= esc($p['program_name']) ?>)<?php endif; ?>
                                    </td>
                                    <td>
                                        <select name="participants[<?= $i ?>][role]" class="form-control form-control-sm">
                                            <option value="co_participant" <?= ($p['role'] ?? '') === 'co_participant' ? 'selected' : '' ?>>ผู้ร่วมงาน</option>
                                            <option value="responsible" <?= ($p['role'] ?? '') === 'responsible' ? 'selected' : '' ?>>ผู้รับผิดชอบ</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-participant">ลบ</button></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
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
</style>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    var searchUrl = '<?= base_url('admin/academic-services/search-users') ?>';
    var csrfToken = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var participantIndex = <?= count($participants) ?>;

    var $search = document.getElementById('participantSearch');
    var $results = document.getElementById('participantSearchResults');
    var $body = document.getElementById('participantsBody');
    var searchTimeout = null;

    function addParticipantRow(uid, displayName, programName, role) {
        role = role || 'co_participant';
        var tr = document.createElement('tr');
        tr.setAttribute('data-uid', uid || '0');
        tr.innerHTML =
            '<td>' +
            '<input type="hidden" name="participants[' + participantIndex + '][user_uid]" value="' + (uid || '') + '">' +
            '<input type="hidden" name="participants[' + participantIndex + '][display_name]" value="' + (displayName || '').replace(/"/g, '&quot;') + '">' +
            '<input type="hidden" name="participants[' + participantIndex + '][program_name]" value="' + (programName || '').replace(/"/g, '&quot;') + '">' +
            (displayName || '-') + (programName ? ' (' + programName + ')' : '') +
            '</td>' +
            '<td><select name="participants[' + participantIndex + '][role]" class="form-control form-control-sm">' +
            '<option value="co_participant"' + (role === 'co_participant' ? ' selected' : '') + '>ผู้ร่วมงาน</option>' +
            '<option value="responsible"' + (role === 'responsible' ? ' selected' : '') + '>ผู้รับผิดชอบ</option>' +
            '</select></td>' +
            '<td><button type="button" class="btn btn-danger btn-sm remove-participant">ลบ</button></td>';
        participantIndex++;
        $body.appendChild(tr);
        tr.querySelector('.remove-participant').addEventListener('click', function() { tr.remove(); });
        $results.style.display = 'none';
        $search.value = '';
    }

    function doSearch() {
        var q = ($search.value || '').trim();
        if (q.length < 2) {
            $results.style.display = 'none';
            return;
        }
        fetch(searchUrl + '?q=' + encodeURIComponent(q))
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (res.status !== 'success' || !res.data || !res.data.length) {
                    $results.innerHTML = '<div style="padding: 0.75rem;">ไม่พบรายชื่อ</div>';
                } else {
                    $results.innerHTML = res.data.map(function(u) {
                        return '<div data-uid="' + u.uid + '" data-label="' + (u.label || '').replace(/"/g, '&quot;') + '" data-program="">' + (u.label || u.email) + '</div>';
                    }).join('');
                    $results.querySelectorAll('div[data-uid]').forEach(function(div) {
                        div.addEventListener('click', function() {
                            addParticipantRow(div.getAttribute('data-uid'), div.getAttribute('data-label'), div.getAttribute('data-program') || '');
                        });
                    });
                }
                $results.style.display = 'block';
            });
    }

    if ($search) {
        $search.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(doSearch, 300);
        });
        $search.addEventListener('blur', function() {
            setTimeout(function() { $results.style.display = 'none'; }, 200);
        });
    }

    document.getElementById('btnAddParticipantManual').addEventListener('click', function() {
        var name = prompt('ชื่อ-นามสกุล (หรือชื่อหน่วยงาน):');
        if (name === null) return;
        name = (name || '').trim();
        var program = prompt('หลักสูตร/สาขา (ถ้ามี):') || '';
        if (name) addParticipantRow(null, name, program.trim(), 'co_participant');
    });

    document.querySelectorAll('#participantsBody .remove-participant').forEach(function(btn) {
        btn.addEventListener('click', function() { btn.closest('tr').remove(); });
    });

    document.getElementById('revenue_amount').addEventListener('focus', function() {
        document.querySelector('input[name="revenue_option"][value="amount"]').checked = true;
    });
})();
</script>
<?= $this->endSection() ?>

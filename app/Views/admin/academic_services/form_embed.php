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
$responsible_users  = $s['responsible_users'] ?? [];
$participants = $participants ?? [];
$initial_participants = array_map(function ($p) {
    return [
        'user_uid' => (int) ($p['user_uid'] ?? 0),
        'display_name' => $p['display_name'] ?? $p['display_label'] ?? '',
        'program_name' => $p['program_name'] ?? '',
        'role' => $p['role'] ?? 'co_participant',
    ];
}, $participants);
$attachments_list = $isEdit ? ($s['attachments'] ?? []) : [];
$serviceDateMode = 'single';
$oldMode = old('service_date_mode');
if ($oldMode === 'range' || $oldMode === 'single') {
    $serviceDateMode = $oldMode;
} elseif ($isEdit) {
    $e  = $s['service_date_end'] ?? null;
    $st = $s['service_date'] ?? null;
    if ($e !== null && $e !== '' && (string) $e !== (string) $st) {
        $serviceDateMode = 'range';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageLabel) ?></title>
    <base href="<?= base_url() ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="form-embed-body">
<div class="card form-embed-card">
    <div class="card-body form-embed-card-body">
        <?php if (session('errors')): ?>
            <div class="alert alert-danger">
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach (session('errors') as $e): ?>
                        <li><?= esc($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= $formAction ?>" method="post" id="academicServiceForm" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- ส่วนที่ 1 ข้อมูลทั่วไป -->
            <div class="form-section form-section-card">
                <h3 class="form-section-title">ส่วนที่ 1 ข้อมูลทั่วไปของโครงการ/กิจกรรมบริการวิชาการ</h3>

                <div class="form-block">
                    <div class="form-group" style="margin-bottom: 0.75rem;">
                        <span class="form-label" id="serviceDateModeLegend">รูปแบบช่วงเวลา</span>
                        <div class="service-date-mode-row" role="radiogroup" aria-labelledby="serviceDateModeLegend" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 0.35rem;">
                            <label class="form-check-label" style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <input type="radio" name="service_date_mode" value="single" id="service_date_mode_single" <?= $serviceDateMode === 'single' ? 'checked' : '' ?>>
                                วันเดียว
                            </label>
                            <label class="form-check-label" style="cursor: pointer; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <input type="radio" name="service_date_mode" value="range" id="service_date_mode_range" <?= $serviceDateMode === 'range' ? 'checked' : '' ?>>
                                ช่วงวันที่ (หลายวัน)
                            </label>
                        </div>
                    </div>
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
                        <div class="form-group" style="flex: 1; min-width: 160px;">
                            <label for="service_date" class="form-label" id="serviceDateStartLabelWrap">
                                <span id="serviceDateLabelSingle" style="display: <?= $serviceDateMode === 'range' ? 'none' : 'inline' ?>;">วันที่จัดกิจกรรม <span class="required">*</span></span>
                                <span id="serviceDateLabelRange" style="display: <?= $serviceDateMode === 'range' ? 'inline' : 'none' ?>;">ตั้งแต่วันที่ <span class="required">*</span></span>
                            </label>
                            <input type="date" id="service_date" name="service_date" class="form-control" required
                                   value="<?= esc($s['service_date'] ?? old('service_date') ?? date('Y-m-d')) ?>">
                        </div>
                    </div>
                    <div id="serviceDateEndWrap" class="form-group" style="margin-top: 0.75rem; max-width: 280px; display: <?= $serviceDateMode === 'range' ? 'block' : 'none' ?>;">
                        <label for="service_date_end" class="form-label">ถึงวันที่ <span class="required">*</span></label>
                        <input type="date" id="service_date_end" name="service_date_end" class="form-control"
                               value="<?= esc($s['service_date_end'] ?? old('service_date_end') ?? '') ?>"
                               <?= $serviceDateMode === 'range' ? 'required' : '' ?>>
                    </div>
                </div>

                <div class="form-block">
                    <div class="form-group">
                        <label for="title" class="form-label">ชื่อโครงการ/กิจกรรม/หัวข้อ <span class="required">*</span></label>
                        <input type="text" id="title" name="title" class="form-control" required
                               value="<?= esc($s['title'] ?? old('title')) ?>" placeholder="ชื่อโครงการหรือกิจกรรม">
                    </div>
                </div>

                <div class="form-block">
                    <label class="form-label">เจ้าของโครงการ/กิจกรรม</label>
                    <select name="project_owner_type" class="form-control" style="max-width: 360px;">
                        <option value="">— เลือก —</option>
                        <option value="internal_faculty" <?= ($s['project_owner_type'] ?? '') === 'internal_faculty' ? 'selected' : '' ?>>โครงการภายในคณะ</option>
                        <option value="external" <?= ($s['project_owner_type'] ?? '') === 'external' ? 'selected' : '' ?>>โครงการภายนอกที่มาขอความอนุเคราะห์</option>
                    </select>
                    <input type="text" name="project_owner_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['project_owner_spec'] ?? old('project_owner_spec')) ?>"
                           placeholder="ระบุหน่วยงาน/โครงการ (เมื่อเลือกภายนอก)">
                </div>

                <div class="form-block">
                    <label class="form-label">สถานที่จัดกิจกรรม</label>
                    <select name="venue_type" class="form-control" style="max-width: 360px;">
                        <option value="">— เลือก —</option>
                        <option value="within_faculty" <?= ($s['venue_type'] ?? '') === 'within_faculty' ? 'selected' : '' ?>>ภายในคณะ</option>
                        <option value="within_university" <?= ($s['venue_type'] ?? '') === 'within_university' ? 'selected' : '' ?>>ภายในมหาวิทยาลัย (นอกคณะ)</option>
                        <option value="outside" <?= ($s['venue_type'] ?? '') === 'outside' ? 'selected' : '' ?>>ภายนอกมหาวิทยาลัย</option>
                    </select>
                    <input type="text" name="venue_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['venue_spec'] ?? old('venue_spec')) ?>" placeholder="ระบุสถานที่ (เมื่อเลือกภายนอก)">
                </div>

                <div class="form-block">
                    <label class="form-label">กลุ่มผู้รับการบริการวิชาการ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">เลือกกลุ่มแล้วระบุรายละเอียด</p>
                    <select name="target_group_type" class="form-control" style="max-width: 360px; margin-bottom: 0.5rem;">
                        <option value="">— เลือก —</option>
                        <option value="internal" <?= ($s['target_group_type'] ?? '') === 'internal' ? 'selected' : '' ?>>ภายในมหาวิทยาลัย</option>
                        <option value="external" <?= ($s['target_group_type'] ?? '') === 'external' ? 'selected' : '' ?>>ภายนอกมหาวิทยาลัย</option>
                    </select>
                    <textarea name="target_group_spec" id="targetGroupSpec" class="form-control" rows="3" placeholder="ระบุรายละเอียดกลุ่มผู้รับการบริการ (หน่วยงาน/กลุ่มเป้าหมาย)"><?= esc($s['target_group_spec'] ?? old('target_group_spec') ?? '') ?></textarea>
                </div>
            </div>

            <!-- ส่วนที่ 2 การดำเนินงาน -->
            <div class="form-section form-section-card">
                <h3 class="form-section-title">ส่วนที่ 2 การดำเนินงานบริการวิชาการ</h3>

                <div class="form-block" id="responsibleBlock">
                    <label class="form-label">ผู้รับผิดชอบการดำเนินงาน</label>
                    <select id="responsibleTypeSelect" name="responsible_type" class="form-control" style="max-width: 360px; margin-bottom: 0.75rem;">
                        <option value="">— เลือก —</option>
                        <option value="faculty" <?= ($s['responsible_type'] ?? '') === 'faculty' ? 'selected' : '' ?>>ระดับคณะ</option>
                        <option value="program" <?= ($s['responsible_type'] ?? '') === 'program' ? 'selected' : '' ?>>ระดับหลักสูตร</option>
                        <option value="person" <?= ($s['responsible_type'] ?? '') === 'person' ? 'selected' : '' ?>>ระดับบุคคล</option>
                    </select>
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

                <div class="form-block">
                    <label class="form-label">ผู้ร่วมบริการวิชาการ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">เลือกชื่อบุคลากรในระบบเท่านั้น (ไม่แสดงชื่อที่อยู่ในผู้รับผิดชอบแล้ว)</p>
                    <div style="margin-bottom: 0.5rem;">
                        <input type="text" id="participantSearch" class="form-control" placeholder="พิมพ์ชื่อหรืออีเมล (อย่างน้อย 2 ตัวอักษร)" style="max-width: 280px;" autocomplete="off">
                    </div>
                    <div id="participantSearchResults" class="participant-results" style="display: none; position: absolute; z-index: 10; background: #fff; border: 1px solid #ccc; max-height: 200px; overflow-y: auto; min-width: 280px;"></div>
                    <div id="participantsBody" class="tag-list tag-list-block" style="margin-top: 0.5rem;"></div>
                </div>

                <div class="form-block">
                    <label class="form-label">ลักษณะการบริการวิชาการ</label>
                    <select name="service_type" class="form-control" style="max-width: 360px;">
                        <option value="">— เลือก —</option>
                        <option value="training_seminar" <?= ($s['service_type'] ?? '') === 'training_seminar' ? 'selected' : '' ?>>อบรม/สัมมนา</option>
                        <option value="workshop" <?= ($s['service_type'] ?? '') === 'workshop' ? 'selected' : '' ?>>ฝึกปฏิบัติการ/Workshop</option>
                        <option value="consultant" <?= ($s['service_type'] ?? '') === 'consultant' ? 'selected' : '' ?>>ที่ปรึกษาทางวิชาการ</option>
                        <option value="lab_testing" <?= ($s['service_type'] ?? '') === 'lab_testing' ? 'selected' : '' ?>>บริการวิเคราะห์ทดสอบ/ห้องปฏิบัติการ</option>
                        <option value="expert_evaluator" <?= ($s['service_type'] ?? '') === 'expert_evaluator' ? 'selected' : '' ?>>ผู้ทรงคุณวุฒิประเมินผล/ตัดสินการแข่งขัน</option>
                        <option value="lecturer" <?= ($s['service_type'] ?? '') === 'lecturer' ? 'selected' : '' ?>>วิทยากร</option>
                        <option value="other" <?= ($s['service_type'] ?? '') === 'other' ? 'selected' : '' ?>>อื่นๆ</option>
                    </select>
                    <input type="text" name="service_type_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['service_type_spec'] ?? old('service_type_spec')) ?>" placeholder="ระบุ (เมื่อเลือกอื่นๆ)">
                </div>
            </div>

            <!-- ส่วนที่ 3 งบประมาณและค่าตอบแทน -->
            <div class="form-section form-section-card">
                <h3 class="form-section-title">ส่วนที่ 3 งบประมาณและค่าตอบแทน</h3>

                <div class="form-block">
                    <label class="form-label">แหล่งงบประมาณ</label>
                    <select name="budget_source" class="form-control" style="max-width: 360px;">
                        <option value="">— เลือก —</option>
                        <option value="university" <?= ($s['budget_source'] ?? '') === 'university' ? 'selected' : '' ?>>งบประมาณมหาวิทยาลัย</option>
                        <option value="faculty" <?= ($s['budget_source'] ?? '') === 'faculty' ? 'selected' : '' ?>>งบประมาณคณะ</option>
                        <option value="external" <?= ($s['budget_source'] ?? '') === 'external' ? 'selected' : '' ?>>งบประมาณจากหน่วยงานภายนอก</option>
                        <option value="other" <?= ($s['budget_source'] ?? '') === 'other' ? 'selected' : '' ?>>อื่นๆ</option>
                    </select>
                    <input type="text" name="budget_source_spec" class="form-control" style="margin-top: 0.5rem;"
                           value="<?= esc($s['budget_source_spec'] ?? old('budget_source_spec')) ?>" placeholder="ระบุแหล่งงบประมาณ">
                </div>

                <div class="form-block">
                    <label class="form-label">ค่าตอบแทนและรายได้ที่เกิดขึ้นกับคณะ</label>
                    <p class="form-text" style="margin-bottom: 0.5rem;">ค่าตอบแทน: เลือกมีค่าตอบแทนต้องระบุจำนวนเงิน (บาท)</p>
                    <div style="margin-bottom: 0.75rem;">
                        <select name="has_compensation" id="has_compensation_select" class="form-control" style="max-width: 280px; display: inline-block; vertical-align: middle;">
                            <option value="">— เลือก —</option>
                            <option value="yes" <?= ($s['has_compensation'] ?? '') === 'yes' ? 'selected' : '' ?>>มีค่าตอบแทน</option>
                            <option value="no" <?= ($s['has_compensation'] ?? '') === 'no' ? 'selected' : '' ?>>ไม่มีค่าตอบแทน</option>
                            <option value="unknown" <?= ($s['has_compensation'] ?? '') === 'unknown' ? 'selected' : '' ?>>ไม่มีข้อมูล</option>
                        </select>
                        <input type="number" name="compensation_amount" id="compensation_amount" class="form-control" step="0.01" min="0" placeholder="จำนวนบาท" style="width: 140px; margin-left: 0.5rem; display: inline-block; vertical-align: middle;"
                               value="<?= isset($s['compensation_amount']) && $s['compensation_amount'] !== null && $s['compensation_amount'] !== '' ? esc($s['compensation_amount']) : '' ?>">
                    </div>
                    <p class="form-text" style="margin-bottom: 0.5rem;">รายได้ที่เกิดขึ้นกับคณะ</p>
                    <div>
                        <select name="revenue_option" id="revenue_option_select" class="form-control" style="max-width: 280px; display: inline-block; vertical-align: middle;">
                            <option value="none" <?= (($s['revenue_unknown'] ?? 0) == 0 && empty($s['revenue_amount'])) ? 'selected' : '' ?>>ไม่มี</option>
                            <option value="amount" <?= !empty($s['revenue_amount']) ? 'selected' : '' ?>>มี (ระบุจำนวน)</option>
                            <option value="unknown" <?= (($s['revenue_unknown'] ?? 0) == 1 ? 'selected' : '') ?>>ไม่มีข้อมูล</option>
                        </select>
                        <input type="number" name="revenue_amount" id="revenue_amount" class="form-control" step="0.01" min="0" placeholder="บาท" style="width: 140px; margin-left: 0.5rem; display: inline-block; vertical-align: middle;"
                               value="<?= isset($s['revenue_amount']) && $s['revenue_amount'] !== null && $s['revenue_amount'] !== '' ? esc($s['revenue_amount']) : '' ?>">
                    </div>
                </div>
            </div>

            <div class="form-section form-section-card">
                <h3 class="form-section-title">เอกสารประกอบ (ไม่บังคับ)</h3>
                <div class="form-block">
                    <p class="form-text" style="margin-bottom: 0.5rem;">อัปโหลดไฟล์ที่เกี่ยวข้องกับรายการนี้ (สูงสุด 10MB ต่อไฟล์; รองรับ pdf, Word, Excel, PowerPoint, รูปภาพ, zip, txt)</p>
                    <?php if ($isEdit && ! empty($attachments_list)): ?>
                    <ul id="existingAttachments" style="margin: 0 0 0.75rem 0; padding-left: 1.2rem; list-style: disc;">
                        <?php foreach ($attachments_list as $att): ?>
                            <li style="margin-bottom: 0.35rem;">
                                <a href="<?= esc(\App\Models\AcademicServiceAttachmentModel::serveUrl($att)) ?>" target="_blank" rel="noopener"><?= esc($att['original_name'] ?? 'ไฟล์') ?></a>
                                <span style="color: var(--color-gray-600); font-size: 0.875rem;">(<?= esc(\App\Models\AcademicServiceAttachmentModel::formatSize((int) ($att['file_size'] ?? 0))) ?>)</span>
                                <button type="button" class="btn btn-danger btn-sm btn-del-attachment" data-id="<?= (int) ($att['id'] ?? 0) ?>" style="margin-left: 0.5rem;">ลบ</button>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <label for="service_attachments" class="form-label">เพิ่มไฟล์แนบ</label>
                    <input type="file" name="service_attachments[]" id="service_attachments" class="form-control" multiple
                           accept=".pdf,.doc,.docx,.xlsx,.xls,.ppt,.pptx,.zip,.jpg,.jpeg,.png,.gif,.txt">
                </div>
            </div>

            <div class="form-actions" style="margin-top: 1rem;">
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'บันทึกการแก้ไข' : 'บันทึก' ?></button>
                <button type="button" class="btn btn-secondary" id="btnCancelEmbed">ยกเลิก</button>
            </div>
        </form>
    </div>
</div>

<style>
.form-embed-body { margin: 0; padding: 0.5rem; background: #fff; }
.form-embed-card { border: none; box-shadow: none; margin: 0; }
.form-embed-card-body { padding: 0.5rem !important; }
.form-section { margin-bottom: 1rem; }
.form-section-card { padding: 0.75rem; border: 1px solid var(--color-gray-200, #e5e7eb); border-radius: 6px; background: var(--color-gray-50, #f9fafb); }
.form-section-title { font-size: 1rem; margin-bottom: 0.5rem; padding-bottom: 0.35rem; border-bottom: 1px solid var(--color-gray-200, #e5e7eb); color: var(--color-gray-800, #1f2937); }
.form-block { padding: 0.5rem 0.75rem; margin-bottom: 0.75rem; background: #fff; border-radius: 6px; border: 1px solid var(--color-gray-100, #f3f4f6); }
.form-block:last-child { margin-bottom: 0; }
.form-block .form-label { font-weight: 500; }
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

<script>
(function() {
    var searchUrl = '<?= base_url('admin/academic-services/search-users') ?>';
    var deleteAttachBase = '<?= base_url('admin/academic-services/delete-attachment') ?>/';
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    var initialResponsible = <?= json_encode($responsible_users) ?>;
    var initialParticipants = <?= json_encode($initial_participants) ?>;

    document.querySelectorAll('.btn-del-attachment').forEach(function(btn) {
        btn.addEventListener('click', function() {
            if (!confirm('ลบไฟล์นี้จากระบบ?')) return;
            var id = btn.getAttribute('data-id');
            if (!id) return;
            var fd = new FormData();
            fd.append(csrfName, csrfHash);
            fetch(deleteAttachBase + id, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(res) {
                    if (res.success) {
                        var li = btn.closest('li');
                        if (li) li.remove();
                    } else {
                        alert(res.message || 'ลบไม่สำเร็จ');
                    }
                })
                .catch(function() { alert('เกิดข้อผิดพลาด'); });
        });
    });

    var $revenueOptionSelect = document.getElementById('revenue_option_select');
    var $revenueAmount = document.getElementById('revenue_amount');
    if ($revenueOptionSelect) $revenueOptionSelect.addEventListener('change', function() {
        if ($revenueAmount) $revenueAmount.style.visibility = this.value === 'amount' ? 'visible' : 'hidden';
    });
    if ($revenueAmount) {
        $revenueAmount.style.visibility = ($revenueOptionSelect && $revenueOptionSelect.value === 'amount') ? 'visible' : 'hidden';
        $revenueAmount.addEventListener('focus', function() {
            if ($revenueOptionSelect) $revenueOptionSelect.value = 'amount';
        });
    }

    var $hasCompensation = document.getElementById('has_compensation_select');
    var $compensationAmount = document.getElementById('compensation_amount');
    function toggleCompensationAmount() {
        if ($compensationAmount) $compensationAmount.style.visibility = ($hasCompensation && $hasCompensation.value === 'yes') ? 'visible' : 'hidden';
    }
    if ($hasCompensation) $hasCompensation.addEventListener('change', toggleCompensationAmount);
    toggleCompensationAmount();
    if ($compensationAmount) $compensationAmount.addEventListener('focus', function() { if ($hasCompensation) $hasCompensation.value = 'yes'; toggleCompensationAmount(); });

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
        var sel = document.getElementById('responsibleTypeSelect');
        return sel ? sel.value : '';
    }
    function updateResponsibleVisibility() {
        var t = getResponsibleType();
        $responsibleProgramWrap.style.display = t === 'program' ? 'block' : 'none';
        $responsiblePersonWrap.style.display = t === 'person' ? 'block' : 'none';
        if (t !== 'program') $responsibleProgram.value = '';
        if (t !== 'person') $responsiblePersonText.value = '';
        if (t === 'program' && $responsibleProgram.value) $responsibleProgramLabel.textContent = $responsibleProgram.value;
    }
    var sel = document.getElementById('responsibleTypeSelect');
    if (sel) sel.addEventListener('change', updateResponsibleVisibility);

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

    document.getElementById('btnCancelEmbed').addEventListener('click', function() {
        if (window.self !== window.top) {
            window.parent.postMessage('academic-close-modal', '*');
        }
    });

    (function() {
        var modeSingle = document.getElementById('service_date_mode_single');
        var modeRange = document.getElementById('service_date_mode_range');
        var wrap = document.getElementById('serviceDateEndWrap');
        var sd = document.getElementById('service_date');
        var ed = document.getElementById('service_date_end');
        var lblSingle = document.getElementById('serviceDateLabelSingle');
        var lblRange = document.getElementById('serviceDateLabelRange');
        function isRangeMode() {
            return modeRange && modeRange.checked;
        }
        function syncEndMin() {
            if (sd && ed && sd.value) {
                ed.min = sd.value;
            }
        }
        function applyServiceDateMode() {
            var range = isRangeMode();
            if (wrap) {
                wrap.style.display = range ? 'block' : 'none';
            }
            if (lblSingle) {
                lblSingle.style.display = range ? 'none' : 'inline';
            }
            if (lblRange) {
                lblRange.style.display = range ? 'inline' : 'none';
            }
            if (ed) {
                ed.required = !!range;
                if (!range) {
                    ed.value = '';
                    ed.removeAttribute('min');
                } else {
                    syncEndMin();
                }
            }
        }
        if (modeSingle) {
            modeSingle.addEventListener('change', applyServiceDateMode);
        }
        if (modeRange) {
            modeRange.addEventListener('change', applyServiceDateMode);
        }
        if (sd) {
            sd.addEventListener('change', syncEndMin);
        }
        applyServiceDateMode();
    })();

    var formEl = document.getElementById('academicServiceForm');
    if (formEl) {
        formEl.addEventListener('submit', function(ev) {
            ev.preventDefault();
            var submitBtn = formEl.querySelector('button[type="submit"]');
            if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'กำลังบันทึก...'; }
            var fd = new FormData(formEl);
            var xhr = new XMLHttpRequest();
            xhr.open('POST', formEl.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.onload = function() {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '<?= $isEdit ? "บันทึกการแก้ไข" : "บันทึก" ?>'; }
                try {
                    var res = JSON.parse(xhr.responseText);
                    if (res.success) {
                        if (res.warning) alert(res.warning);
                        window.parent.postMessage('academic-service-updated', '*');
                    } else {
                        var msg = res.message || 'บันทึกไม่สำเร็จ';
                        if (res.errors && typeof res.errors === 'object') {
                            msg = Object.keys(res.errors).map(function(k) { return res.errors[k]; }).join('\n');
                        }
                        alert(msg || 'บันทึกไม่สำเร็จ');
                    }
                } catch (e) {
                    alert('เกิดข้อผิดพลาด');
                }
            };
            xhr.onerror = function() {
                if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = '<?= $isEdit ? "บันทึกการแก้ไข" : "บันทึก" ?>'; }
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
            };
            xhr.send(fd);
        });
    }
})();
</script>
</body>
</html>

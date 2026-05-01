<?php
$s = $service ?? [];
$id = (int) ($s['id'] ?? 0);
$participants = $s['participants'] ?? [];
$attachments = $s['attachments'] ?? [];

$serviceTypeLabels = [
    'training_seminar' => 'อบรม/สัมมนา',
    'workshop'         => 'ฝึกปฏิบัติการ/Workshop',
    'consultant'       => 'ที่ปรึกษาทางวิชาการ',
    'lab_testing'      => 'บริการวิเคราะห์ทดสอบ/ห้องปฏิบัติการ',
    'expert_evaluator' => 'ผู้ทรงคุณวุฒิประเมินผล/ตัดสินการแข่งขัน',
    'lecturer'         => 'วิทยากร',
    'other'              => 'อื่นๆ',
];
$projectOwnerLabels = [
    'internal_faculty' => 'โครงการภายในคณะ',
    'external'         => 'โครงการภายนอกที่มาขอความอนุเคราะห์',
];
$venueLabels = [
    'within_faculty'     => 'ภายในคณะ',
    'within_university'  => 'ภายในมหาวิทยาลัย (นอกคณะ)',
    'outside'            => 'ภายนอกมหาวิทยาลัย',
];
$targetGroupLabels = [
    'internal' => 'ภายในมหาวิทยาลัย',
    'external' => 'ภายนอกมหาวิทยาลัย',
];
$responsibleLabels = [
    'faculty' => 'ระดับคณะ',
    'program' => 'ระดับหลักสูตร',
    'person'  => 'ระดับบุคคล',
];
$budgetLabels = [
    'university' => 'งบประมาณมหาวิทยาลัย',
    'faculty'    => 'งบประมาณคณะ',
    'external'   => 'งบประมาณจากหน่วยงานภายนอก',
    'other'      => 'อื่นๆ',
];
$compLabels = [
    'yes'     => 'มีค่าตอบแทน',
    'no'      => 'ไม่มีค่าตอบแทน',
    'unknown' => 'ไม่มีข้อมูล',
];

$typeKey = $s['service_type'] ?? '';
$typeDisplay = $serviceTypeLabels[$typeKey] ?? '';
if ($typeKey === 'other' && ! empty($s['service_type_spec'])) {
    $typeDisplay = 'อื่นๆ — ' . $s['service_type_spec'];
} elseif ($typeKey === 'other') {
    $typeDisplay = 'อื่นๆ';
} elseif ($typeDisplay === '' && ! empty($s['service_type_spec'])) {
    $typeDisplay = $s['service_type_spec'];
} elseif ($typeDisplay === '') {
    $typeDisplay = '—';
}

$responsibleTags = $s['responsible_users'] ?? [];
$responsibleText   = '';
if ($responsibleTags !== []) {
    $responsibleText = implode(', ', array_column($responsibleTags, 'label'));
} elseif (! empty($s['responsible_person_text'])) {
    $responsibleText = $s['responsible_person_text'];
}

$targetTags = $s['target_group_users'] ?? [];
$targetDetail = '';
if ($targetTags !== []) {
    $targetDetail = implode(', ', array_column($targetTags, 'label'));
}
if ($targetDetail === '' && ! empty($s['target_group_spec'])) {
    $targetDetail = $s['target_group_spec'];
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายละเอียดบริการวิชาการ</title>
    <base href="<?= base_url() ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/fonts.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/theme.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/admin.css') ?>">
</head>
<body class="form-embed-body">
<div class="card form-embed-card">
    <div class="card-body form-embed-card-body">
        <div class="detail-embed-toolbar" style="display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: flex-end; margin-bottom: 0.75rem;">
            <button type="button" class="btn btn-primary btn-sm" id="btnEditFromDetail">แก้ไขรายการนี้</button>
            <button type="button" class="btn btn-secondary btn-sm" id="btnCloseDetail">ปิด</button>
        </div>

        <div class="form-section form-section-card">
            <h3 class="form-section-title">ส่วนที่ 1 ข้อมูลทั่วไป</h3>
            <dl class="detail-dl">
                <dt>ปีการศึกษา (พ.ศ.)</dt><dd><?= esc($s['academic_year'] ?? '—') ?></dd>
                <dt>ช่วงวันที่บริการวิชาการ</dt>
                <dd><?php
                    $sd = $s['service_date'] ?? '';
                    $ed = $s['service_date_end'] ?? '';
                    if ($sd === '' || $sd === null) {
                        echo '—';
                    } else {
                        $ds = date('d/m/Y', strtotime($sd));
                        if ($ed === '' || $ed === null || $ed === $sd) {
                            echo esc($ds);
                        } else {
                            echo esc($ds . ' – ' . date('d/m/Y', strtotime($ed)));
                        }
                    }
                ?></dd>
                <dt>ชื่อโครงการ/กิจกรรม/หัวข้อ</dt><dd><strong><?= esc($s['title'] ?? '') ?></strong></dd>
                <dt>เจ้าของโครงการ/กิจกรรม</dt>
                <dd><?= esc($projectOwnerLabels[$s['project_owner_type'] ?? ''] ?? ($s['project_owner_type'] ?? '—')) ?>
                    <?php if (! empty($s['project_owner_spec'])): ?><br><span class="detail-sub"><?= esc($s['project_owner_spec']) ?></span><?php endif; ?>
                </dd>
                <dt>สถานที่จัดกิจกรรม</dt>
                <dd><?= esc($venueLabels[$s['venue_type'] ?? ''] ?? ($s['venue_type'] ?? '—')) ?>
                    <?php if (! empty($s['venue_spec'])): ?><br><span class="detail-sub"><?= esc($s['venue_spec']) ?></span><?php endif; ?>
                </dd>
                <dt>กลุ่มผู้รับการบริการวิชาการ</dt>
                <dd><?= esc($targetGroupLabels[$s['target_group_type'] ?? ''] ?? ($s['target_group_type'] ?? '—')) ?>
                    <?php if ($targetDetail !== ''): ?><br><span class="detail-sub"><?= nl2br(esc($targetDetail), false) ?></span><?php endif; ?>
                </dd>
            </dl>
        </div>

        <div class="form-section form-section-card">
            <h3 class="form-section-title">ส่วนที่ 2 การดำเนินงาน</h3>
            <dl class="detail-dl">
                <dt>ผู้รับผิดชอบการดำเนินงาน</dt>
                <dd><?= esc($responsibleLabels[$s['responsible_type'] ?? ''] ?? ($s['responsible_type'] ?? '—')) ?>
                    <?php if (($s['responsible_type'] ?? '') === 'program' && ! empty($s['responsible_program'])): ?>
                        <br><span class="detail-sub">หลักสูตร: <?= esc($s['responsible_program']) ?></span>
                    <?php endif; ?>
                    <?php if (($s['responsible_type'] ?? '') === 'person' && $responsibleText !== ''): ?>
                        <br><span class="detail-sub"><?= esc($responsibleText) ?></span>
                    <?php endif; ?>
                </dd>
                <dt>ผู้ร่วมบริการวิชาการ</dt>
                <dd>
                    <?php if ($participants === []): ?>
                        —
                    <?php else: ?>
                        <ul class="detail-list">
                            <?php foreach ($participants as $p): ?>
                                <li><?= esc(trim(($p['display_name'] ?? '') . ($p['program_name'] ? ' (' . $p['program_name'] . ')' : ''))) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </dd>
                <dt>ลักษณะการบริการวิชาการ</dt><dd><?= esc($typeDisplay) ?></dd>
            </dl>
        </div>

        <div class="form-section form-section-card">
            <h3 class="form-section-title">ส่วนที่ 3 งบประมาณและค่าตอบแทน</h3>
            <dl class="detail-dl">
                <dt>แหล่งงบประมาณ</dt>
                <dd><?= esc($budgetLabels[$s['budget_source'] ?? ''] ?? ($s['budget_source'] ?? '—')) ?>
                    <?php if (! empty($s['budget_source_spec'])): ?><br><span class="detail-sub"><?= esc($s['budget_source_spec']) ?></span><?php endif; ?>
                </dd>
                <dt>ค่าตอบแทน</dt>
                <dd><?= esc($compLabels[$s['has_compensation'] ?? ''] ?? ($s['has_compensation'] ?? '—')) ?>
                    <?php if (($s['has_compensation'] ?? '') === 'yes' && isset($s['compensation_amount']) && $s['compensation_amount'] !== null && $s['compensation_amount'] !== ''): ?>
                        <span class="detail-sub"> — <?= esc(number_format((float) $s['compensation_amount'], 2)) ?> บาท</span>
                    <?php endif; ?>
                </dd>
                <dt>รายได้ที่เกิดขึ้นกับคณะ</dt>
                <dd>
                    <?php if (! empty($s['revenue_unknown'])): ?>
                        ไม่มีข้อมูล
                    <?php elseif (isset($s['revenue_amount']) && $s['revenue_amount'] !== null && $s['revenue_amount'] !== ''): ?>
                        <?= esc(number_format((float) $s['revenue_amount'], 2)) ?> บาท
                    <?php else: ?>
                        ไม่มี
                    <?php endif; ?>
                </dd>
            </dl>
        </div>

        <div class="form-section form-section-card">
            <h3 class="form-section-title">เอกสารประกอบ</h3>
            <?php if ($attachments === []): ?>
                <p class="form-text" style="margin: 0;">ไม่มีไฟล์แนบ</p>
            <?php else: ?>
                <ul class="detail-list detail-attachments">
                    <?php foreach ($attachments as $att): ?>
                        <li>
                            <a href="<?= esc(\App\Models\AcademicServiceAttachmentModel::serveUrl($att)) ?>" target="_blank" rel="noopener"><?= esc($att['original_name'] ?? 'ไฟล์') ?></a>
                            <span class="detail-meta">(<?= esc(\App\Models\AcademicServiceAttachmentModel::formatSize((int) ($att['file_size'] ?? 0))) ?>)</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.form-embed-body { margin: 0; padding: 0.5rem; background: #fff; }
.form-embed-card { border: none; box-shadow: none; margin: 0; }
.form-embed-card-body { padding: 0.5rem !important; }
.form-section { margin-bottom: 1rem; }
.form-section-card { padding: 0.75rem; border: 1px solid var(--color-gray-200, #e5e7eb); border-radius: 6px; background: var(--color-gray-50, #f9fafb); }
.form-section-title { font-size: 1rem; margin-bottom: 0.5rem; padding-bottom: 0.35rem; border-bottom: 1px solid var(--color-gray-200, #e5e7eb); color: var(--color-gray-800, #1f2937); }
.detail-dl { margin: 0; display: grid; grid-template-columns: minmax(140px, 200px) 1fr; gap: 0.35rem 1rem; font-size: 0.9375rem; }
.detail-dl dt { color: var(--color-gray-600, #4b5563); font-weight: 500; margin: 0; }
.detail-dl dd { margin: 0; }
.detail-sub { color: var(--color-gray-700, #374151); font-weight: 400; }
.detail-list { margin: 0.25rem 0 0 1.1rem; padding: 0; }
.detail-list li { margin-bottom: 0.25rem; }
.detail-meta { color: var(--color-gray-600); font-size: 0.875rem; margin-left: 0.35rem; }
.detail-attachments a { word-break: break-word; }
@media (max-width: 560px) {
    .detail-dl { grid-template-columns: 1fr; }
    .detail-dl dt { padding-top: 0.5rem; border-top: 1px solid var(--color-gray-100); }
    .detail-dl dt:first-of-type { border-top: none; padding-top: 0; }
}
</style>

<script>
(function() {
    var serviceId = <?= json_encode($id) ?>;
    document.getElementById('btnCloseDetail').addEventListener('click', function() {
        if (window.self !== window.top) {
            window.parent.postMessage('academic-close-modal', '*');
        }
    });
    document.getElementById('btnEditFromDetail').addEventListener('click', function() {
        if (window.self !== window.top && serviceId) {
            window.parent.postMessage({ type: 'academic-open-edit', id: serviceId }, '*');
        }
    });
})();
</script>
</body>
</html>

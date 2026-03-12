<?php
helper('url');
$infoUser = $infoUser ?? [];
$userSubmissions = $userSubmissions ?? [];
$submittedPositions = $submittedPositions ?? [];
$availablePositions = $availablePositions ?? [];
$canSubmitNewRequest = $canSubmitNewRequest ?? false;
$selectedCurriculum = $selectedCurriculum ?? '';
$noRightsMessage = $noRightsMessage ?? null;
$canManageEvaluate = $can_manage_evaluate ?? false;
$userName = ($infoUser['gf_name'] ?? $infoUser['tf_name'] ?? '') . ' ' . ($infoUser['gl_name'] ?? $infoUser['tl_name'] ?? '');
$saveUrl = base_url('evaluate/lecture-evaluate/save');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบการประเมินการสอน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { font-family: 'Sarabun', sans-serif; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); min-height: 100vh; padding: 20px 0; }
        .header-title { background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%); border-radius: 15px; padding: 20px; margin-bottom: 30px; color: white; text-align: center; }
        .sidebar-card { background: #fff; border-radius: 15px; padding: 20px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .content-card { background: #fff; border-radius: 15px; padding: 30px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        .status-item { padding: 12px; margin-bottom: 8px; border-radius: 10px; background: #f8f9fa; border-left: 4px solid #6c757d; }
        .status-item.pending { background: #fff3e0; border-left-color: #ff9800; }
        .status-item.completed { background: #e8f5e9; border-left-color: #4caf50; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-title">
            <h2 class="mb-2"><i class="bi bi-clipboard-check me-2"></i>ระบบการประเมินการสอน</h2>
            <p class="mb-0 opacity-75">จัดการและติดตามการประเมินการสอนอย่างมีประสิทธิภาพ</p>
            <a href="<?= esc(base_url('evaluate/card')) ?>" class="btn btn-outline-light btn-sm mt-2 me-2"><i class="bi bi-person-lines-fill me-1"></i>แบบประเมินของตนเอง</a>
            <?php if ($canManageEvaluate): ?>
            <a href="<?= esc(base_url('evaluate/admin')) ?>" class="btn btn-outline-warning btn-sm mt-2"><i class="bi bi-gear me-1"></i>จัดการระบบประเมิน</a>
            <?php endif; ?>
        </div>
        <?php if ($noRightsMessage): ?>
            <div class="alert alert-warning"><?= esc($noRightsMessage) ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="sidebar-card">
                    <h6 class="text-primary mb-3"><i class="bi bi-person me-2"></i>ข้อมูลผู้ใช้</h6>
                    <p class="mb-0"><?= esc($userName ?: 'ผู้ใช้') ?></p>
                </div>
                <div class="sidebar-card">
                    <h6 class="text-success mb-3"><i class="bi bi-award me-2"></i>สถานะการประเมินตำแหน่ง</h6>
                    <?php
                    $allPositions = ['ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์'];
                    $byPos = [];
                    foreach ($userSubmissions as $s) {
                        if (!empty($s['position'])) $byPos[$s['position']] = $s;
                    }
                    foreach ($allPositions as $pos):
                        $sub = $byPos[$pos] ?? null;
                        $cls = $sub ? (($sub['status'] ?? 0) == 1 ? 'completed' : 'pending') : '';
                    ?>
                        <div class="status-item <?= $cls ?>">
                            <strong><?= esc($pos) ?></strong>
                            <small class="d-block text-muted">
                                <?php if ($sub): ?>
                                    <?= ($sub['status'] ?? 0) == 1 ? 'ประเมินเรียบร้อย' : 'รอการประเมิน' ?> · วันที่ส่ง: <?= esc($sub['submit_date'] ?? '-') ?>
                                <?php else: ?>
                                    ยังไม่ได้ส่งคำร้อง
                                <?php endif; ?>
                            </small>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 p-2 bg-light rounded small">
                        <strong>สรุป:</strong> <?= count($submittedPositions) ?>/<?= count($allPositions) ?> ตำแหน่ง
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <?php if ($canSubmitNewRequest): ?>
                    <div class="content-card">
                        <h6 class="text-primary mb-3"><i class="bi bi-send me-2"></i>ตำแหน่งที่สามารถส่งได้</h6>
                        <p class="text-muted small"><?= implode(', ', array_map('esc', $availablePositions)) ?></p>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#formModal">
                            <i class="bi bi-send me-1"></i>ส่งคำร้องใหม่
                        </button>
                    </div>
                <?php else: ?>
                    <div class="content-card text-center py-4">
                        <i class="bi bi-check-circle text-success" style="font-size: 3rem;"></i>
                        <h6 class="mt-2">ส่งครบทุกตำแหน่งแล้ว</h6>
                        <small class="text-muted">คุณได้ส่งคำร้องขอประเมินครบทุกตำแหน่งแล้ว</small>
                    </div>
                <?php endif; ?>

                <?php foreach ($userSubmissions as $idx => $sub): ?>
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6><i class="bi bi-check-circle text-success me-2"></i>คำร้องที่ <?= $idx + 1 ?> ได้ถูกส่งแล้ว</h6>
                                <p class="small text-muted mb-0">วันที่ส่ง: <?= esc($sub['submit_date'] ?? '-') ?> · ตำแหน่ง: <?= esc($sub['position'] ?? '-') ?></p>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm genpdf-btn" data-submission='<?= htmlspecialchars(json_encode($sub), ENT_QUOTES, 'UTF-8') ?>'>
                                <i class="bi bi-file-pdf me-1"></i>สร้าง PDF
                            </button>
                        </div>
                        <hr>
                        <div class="row small">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>สาขา:</strong> <?= esc($sub['position_major'] ?? '-') ?></p>
                                <p class="mb-1"><strong>รายวิชา:</strong> <?= esc($sub['subject_name'] ?? '-') ?></p>
                                <p class="mb-1"><strong>รหัสวิชา:</strong> <?= esc($sub['subject_id'] ?? '-') ?></p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>สถานะ:</strong>
                                    <span class="badge <?= (($sub['status'] ?? 0) == 1 ? 'bg-success' : 'bg-warning') ?>">
                                        <?= ($sub['status'] ?? 0) == 1 ? 'อนุมัติแล้ว' : 'รอการอนุมัติ' ?>
                                    </span>
                                </p>
                                <?php if (!empty($sub['file_doc'])): ?>
                                    <p class="mb-1"><a href="<?= esc(base_url('serve/uploads/documents/' . $sub['file_doc'])) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-download"></i> ดาวน์โหลด</a></p>
                                <?php endif; ?>
                                <?php if (!empty($sub['link_video'])): ?>
                                    <p class="mb-1"><a href="<?= esc($sub['link_video']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-play-circle"></i> วิดีโอ</a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Modal แบบฟอร์ม -->
    <div class="modal fade" id="formModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="bi bi-clipboard-list me-2"></i>แบบฟอร์มขอส่งการประเมินการสอน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="evaluationForm" enctype="multipart/form-data">
                        <input type="hidden" name="position" id="formPosition" value="">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label">ชื่อ</label><input type="text" class="form-control" name="first_name" required></div>
                            <div class="col-md-4"><label class="form-label">นามสกุล</label><input type="text" class="form-control" name="last_name" required></div>
                            <div class="col-md-4"><label class="form-label">ยศ/คำนำหน้า</label><input type="text" class="form-control" name="title_thai"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><label class="form-label">หลักสูตร</label><input type="text" class="form-control" name="curriculum_name" value="<?= esc($selectedCurriculum ?? '') ?>"></div>
                            <div class="col-md-6"><label class="form-label">ตำแหน่งที่ขอรับการประเมิน</label><input type="text" class="form-control" name="position_display" id="positionDisplay" readonly></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6"><label class="form-label">สาขาที่เสนอ</label><input type="text" class="form-control" name="position_major"></div>
                            <div class="col-md-6"><label class="form-label">รหัสสาขา</label><input type="text" class="form-control" name="position_major_id"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label">วันที่เริ่มเข้าทำงาน</label><input type="date" class="form-control" name="start_date"></div>
                            <div class="col-md-4"><label class="form-label">รหัสวิชา</label><input type="text" class="form-control" name="subject_id"></div>
                            <div class="col-md-4"><label class="form-label">ชื่อรายวิชา</label><input type="text" class="form-control" name="subject_name"></div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4"><label class="form-label">หน่วยกิต</label><input type="text" class="form-control" name="subject_credit"></div>
                            <div class="col-md-4"><label class="form-label">ผู้สอนร่วม</label><input type="text" class="form-control" name="subject_teacher" value="-"></div>
                            <div class="col-md-4"><label class="form-label">ลิงก์วิดีโอ</label><input type="url" class="form-control" name="link_video"></div>
                        </div>
                        <div class="mb-3"><label class="form-label">คำอธิบายรายวิชา</label><textarea class="form-control" name="subject_detail" rows="2"></textarea></div>
                        <div class="mb-3"><label class="form-label">ไฟล์เอกสารประกอบ <span class="text-danger">*</span></label><input type="file" class="form-control" name="filedoc" accept=".pdf,.doc,.docx" required></div>
                        <button type="submit" class="btn btn-primary" id="submitBtn">ส่งคำร้อง</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url() ?>pdfmake/build/pdfmake.min.js"></script>
    <script src="<?= base_url() ?>pdfmake/build/vfs_fonts.js"></script>
    <script src="<?= base_url() ?>pdfmake/scriptgen.js"></script>
    <script>
        var saveUrl = <?= json_encode($saveUrl) ?>;
        var availablePositions = <?= json_encode($availablePositions) ?>;
        $('#formModal').on('show.bs.modal', function() {
            if (availablePositions.length) {
                $('#formPosition').val(availablePositions[0]);
                $('#positionDisplay').val(availablePositions[0]);
            }
        });
        $('#evaluationForm').on('submit', function(e) {
            e.preventDefault();
            var pos = $('#formPosition').val();
            if (!pos) { alert('กรุณาเลือกตำแหน่ง'); return; }
            var fd = new FormData(this);
            fd.set('position', pos);
            $('#submitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังส่ง...');
            $.ajax({ url: saveUrl, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json' })
                .done(function(res) {
                    if (res.success) { alert(res.message); location.reload(); }
                    else { alert(res.message || 'เกิดข้อผิดพลาด'); $('#submitBtn').prop('disabled', false).text('ส่งคำร้อง'); }
                })
                .fail(function() { alert('เกิดข้อผิดพลาด'); $('#submitBtn').prop('disabled', false).text('ส่งคำร้อง'); });
        });
        $('.genpdf-btn').on('click', function() {
            var data = $(this).data('submission');
            if (typeof makepdf === 'function' && data) {
                var dataname = [(data.title_thai || '') + ' ' + (data.first_name || '') + ' ' + (data.last_name || ''), data.curriculum || '', data.start_date || '', data.position || '', data.position_major || '', data.position_major_id || '', 'ผู้ช่วยศาสตราจารย์'];
                var datasubject = [data.subject_id || '', data.subject_name || '', data.subject_credit || '', data.subject_teacher || '', data.subject_detail || ''];
                makepdf(dataname, datasubject, []);
            } else alert('ไม่พบฟังก์ชันสร้าง PDF');
        });
    </script>
</body>
</html>

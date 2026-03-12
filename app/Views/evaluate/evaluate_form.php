<?php
helper('url');
$paramList = $param ?? [];
$param = is_array($paramList) && isset($paramList[0]) ? $paramList[0] : null;
$info = $info ?? 'notfound';
$evaluateList = $evaluate ?? [];
$evaluate = is_array($evaluateList) && isset($evaluateList[0]) ? $evaluateList[0] : null;
$refparam = $refparam ?? [];
$encodeurl = $refparam['encodeurl'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แบบประเมินการสอน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($info === 'found' && $evaluate): ?>
                    <div class="card mb-4">
                        <div class="card-body">
                            <h4 class="mb-3">รายละเอียดผู้ขอรับการประเมิน</h4>
                            <dl class="row mb-0">
                                <dt class="col-sm-4">ชื่อผู้ขอเสนอ</dt>
                                <dd class="col-sm-8"><?= esc(($evaluate['first_name'] ?? '') . ' ' . ($evaluate['last_name'] ?? '')) ?></dd>
                                <dt class="col-sm-4">หลักสูตร</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['curriculum'] ?? '') ?></dd>
                                <dt class="col-sm-4">วันที่เริ่มเข้าทำงาน</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['start_date'] ?? '') ?></dd>
                                <dt class="col-sm-4">ตำแหน่งที่ขอรับการประเมิน</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['position_major'] ?? '') ?></dd>
                                <dt class="col-sm-4">รหัสรายวิชา</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['subject_id'] ?? '') ?></dd>
                                <dt class="col-sm-4">ชื่อรายวิชา</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['subject_name'] ?? '') ?></dd>
                                <dt class="col-sm-4">หน่วยกิต</dt>
                                <dd class="col-sm-8"><?= esc($evaluate['subject_credit'] ?? '') ?></dd>
                                <dt class="col-sm-4">ไฟล์เอกสาร</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($evaluate['file_doc'])): ?>
                                        <a href="<?= base_url('serve/uploads/documents/' . $evaluate['file_doc']) ?>" class="btn btn-primary btn-sm" target="_blank"><i class="bi bi-download"></i> ดาวน์โหลด</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </dd>
                                <dt class="col-sm-4">ลิงก์วิดีโอ</dt>
                                <dd class="col-sm-8">
                                    <?php if (!empty($evaluate['link_video'])): ?>
                                        <a href="<?= esc($evaluate['link_video']) ?>" class="btn btn-danger btn-sm" target="_blank"><i class="bi bi-play-circle"></i> เปิดวีดีโอ</a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </dd>
                            </dl>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <?php if (!$param || (int)($param['status'] ?? 0) < 2): ?>
                                <h4 class="mb-3">แบบฟอร์มสำหรับการประเมินการสอน<?= $param ? ' จากการประเมินของ ' . esc($param['name'] ?? '') : '' ?></h4>
                                <form id="frmdata" name="frmdata">
                                    <div class="mb-3">
                                        <label class="form-label">อัปโหลดข้อเสนอแนะ (ถ้ามี)</label>
                                        <input type="file" class="form-control" id="fileupload" name="fileupload" accept=".docx,.pdf">
                                        <input type="hidden" name="file_doc" id="filedoc" value="">
                                        <small class="text-muted">สนับสนุน .docx, .pdf</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ข้อเสนอแนะ <span class="text-danger">*</span></label>
                                        <textarea class="form-control" id="comment" name="comment" rows="3" required></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">ผลการประเมิน <span class="text-danger">*</span></label>
                                        <select class="form-select" id="score" name="score" required>
                                            <option value="">กรุณาเลือก</option>
                                            <option value="ผ่านแบบไม่แก้ไข">ผ่านแบบไม่แก้ไข</option>
                                            <option value="ผ่านแบบมีการแก้ไขและประชุม">ผ่านแบบมีการแก้ไขและประชุม</option>
                                        </select>
                                    </div>
                                    <input type="hidden" name="status" value="2">
                                    <input type="hidden" name="email" value="<?= esc($refparam['email'] ?? '') ?>">
                                    <input type="hidden" name="teaching_id" value="<?= esc($refparam['id'] ?? '') ?>">
                                    <button type="button" class="btn btn-primary" id="saveBtn">ส่งแบบประเมิน</button>
                                </form>
                                <?php if ($param): ?>
                                    <script>$(function(){ $('#comment').val(<?= json_encode($param['comment'] ?? '') ?>); $('#score').val(<?= json_encode($param['score'] ?? '') ?>); });</script>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    คุณได้ทำการประเมินแล้วเมื่อวันที่ <?= esc($param['comment_date'] ?? '') ?> ขอบคุณสำหรับการประเมิน
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-danger">
                        <h4>ไม่พบหน้าที่ต้องการ</h4>
                        <p>ขออภัย ไม่พบข้อมูลการประเมินที่คุณกำลังค้นหา กรุณาตรวจสอบลิงก์อีกครั้ง</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalconfirm" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <p class="mb-2">กรุณายืนยันในการส่งผลการประเมิน</p>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" class="btn btn-primary" id="saveForm">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var saveUrl = <?= json_encode(base_url('evaluate/saveEvaluate')) ?>;
        var encodeurl = <?= json_encode($encodeurl) ?>;
        var baseUrl = <?= json_encode(base_url()) ?>;
        $('#saveBtn').on('click', function() {
            if (!$('#score').val()) { alert('กรุณาเลือกผลการประเมิน'); return; }
            if (!$('#comment').val().trim()) { alert('กรุณากรอกข้อเสนอแนะ'); return; }
            $('#modalconfirm').modal('show');
        });
        $('#saveForm').on('click', function() {
            var data = { teaching_id: $('input[name="teaching_id"]').val(), email: $('input[name="email"]').val(),
                comment: $('#comment').val(), score: $('#score').val(), file_doc: $('#filedoc').val(), status: 2 };
            $('#saveForm').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> กำลังบันทึก...');
            $.post(saveUrl, data, function(res) {
                alert('บันทึกข้อมูลเรียบร้อยแล้ว');
                window.location.href = baseUrl + 'evaluate/evaluate/' + (encodeurl || '');
            }, 'json').fail(function() {
                alert('เกิดข้อผิดพลาด กรุณาลองใหม่');
                $('#saveForm').prop('disabled', false).text('ยืนยัน');
            });
        });
    </script>
</body>
</html>

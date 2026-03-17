<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php helper('url');
$base = base_url();
$adminBase = rtrim($base, '/') . '/evaluate/admin';
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .eval-card { border: none; border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); margin-bottom: 1.5rem; }
    .eval-card .card-header { background: var(--color-gray-100, #f3f4f6); color: var(--dash-text-primary, #1f2937); border: none; border-radius: 12px 12px 0 0; padding: 1rem 1.25rem; font-weight: 600; }
    .eval-table thead th { background-color: #E3F2FD; color: #1976D2; padding: 0.75rem 1rem; }
    .info-row { padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb; }
    .info-label { font-weight: 600; color: #495057; }
    .eval-modal .modal-header { background: #1976D2; color: white; border: none; }
</style>

<div class="eval-page-wrap">
    <div class="card eval-card">
        <div class="card-header"><i class="bi bi-list-ul me-2"></i> รายชื่อผู้ขอเสนอรับการประเมิน</div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover eval-table" id="evaluationTable">
                    <thead>
                        <tr>
                            <th>ชื่อ - สกุล</th>
                            <th>หลักสูตร</th>
                            <th>วันที่ยื่น</th>
                            <th>วันที่สิ้นสุด</th>
                            <th>ระยะเวลา</th>
                            <th>ตำแหน่ง</th>
                            <th>รายวิชา</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teachinglist as $rawdata):
                            $now = new DateTime();
                            if (!empty($rawdata['stop_date'])) $now = new DateTime($rawdata['stop_date']);
                            $date = new DateTime($rawdata['submit_date'] ?? 'now');
                            ?>
                            <tr>
                                <td><strong><?= esc(($rawdata['first_name'] ?? '') . ' ' . ($rawdata['last_name'] ?? '')) ?></strong></td>
                                <td><?= esc($rawdata['curriculum'] ?? '') ?></td>
                                <td><?= esc($rawdata['submit_date'] ?? '') ?></td>
                                <td>
                                    <?php if (empty($rawdata['stop_date'])): ?>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control datepicker-input" id="enddate<?= (int)$rawdata['id'] ?>" placeholder="เลือกวันที่" data-date-format="yyyy-mm-dd">
                                            <button type="button" class="btn btn-success btn-sm" onclick="savedate(<?= (int)$rawdata['id'] ?>)"><i class="bi bi-check-lg"></i></button>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-success"><?= esc($rawdata['stop_date']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-primary"><?= $date->diff($now)->format('%a') ?> วัน</span></td>
                                <td><?= esc($rawdata['position_major'] ?? '') ?></td>
                                <td><?= esc(($rawdata['subject_name'] ?? '') . ' (' . ($rawdata['subject_credit'] ?? '') . ')') ?></td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm me-1" onclick="getInfo(<?= (int)$rawdata['id'] ?>)" data-bs-toggle="modal" data-bs-target="#modalCenter"><i class="bi bi-info-circle"></i> รายละเอียด</button>
                                    <button type="button" class="btn btn-warning btn-sm" onclick="getResult(<?= (int)$rawdata['id'] ?>)"><i class="bi bi-clipboard-data"></i> ผลประเมิน</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal รายละเอียด -->
<div class="modal fade eval-modal" id="modalCenter" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> รายละเอียดการประเมิน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabRequestor"><i class="bi bi-person"></i> ผู้ขอรับการประเมิน</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabEvaluators"><i class="bi bi-people"></i> ผู้ประเมิน</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="tabRequestor">
                        <div class="info-row"><span class="info-label">ชื่อผู้ขอเสนอ: </span><span id="msg_sFname"></span> <span id="msg_slname"></span></div>
                        <div class="info-row"><span class="info-label">หลักสูตร: </span><span id="msg_scurriculum"></span></div>
                        <div class="info-row"><span class="info-label">วันที่เริ่มเข้าทำงาน: </span><span id="msg_sworkingdate"></span></div>
                        <div class="info-row"><span class="info-label">ตำแหน่งที่ขอรับการประเมิน: </span><span id="msg_sposition"></span></div>
                        <div class="info-row"><span class="info-label">สาขาที่เสนอ: </span><span id="msg_spositionmajor"></span></div>
                        <div class="info-row"><span class="info-label">รหัสรายวิชา: </span><span id="msg_ssubjectid"></span></div>
                        <div class="info-row"><span class="info-label">ชื่อรายวิชา: </span><span id="msg_ssubjectname"></span></div>
                        <div class="info-row"><span class="info-label">หน่วยกิต: </span><span id="msg_ssubjectcredit"></span></div>
                        <div class="info-row"><span class="info-label">ไฟล์เอกสาร: </span><span id="msg_slinkdoc">-</span></div>
                        <div class="info-row"><span class="info-label">ลิงก์วิดีโอ: </span><span id="msg_slinkvideo">-</span></div>
                    </div>
                    <div class="tab-pane fade" id="tabEvaluators">
                        <form id="frmdata">
                            <input type="hidden" id="idEvaluate" name="idEvaluate">
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                            <div class="row mb-3 align-items-center">
                                <div class="col-md-1"><span class="badge bg-primary">ผู้ประเมินคนที่ <?= $i ?></span></div>
                                <div class="col-md-8">
                                    <select id="ref<?= $i ?>" name="ref<?= $i ?>" class="form-select">
                                        <option value="">เลือกผู้ประเมิน...</option>
                                        <?php foreach ($allTeacher as $val): ?>
                                            <option value="<?= esc(strtolower($val['email'] ?? '')) ?>"><?= esc($val['name'] ?? '') ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-warning btn-sm w-100" onclick="indvidualSendmail(<?= $i ?>)"><i class="bi bi-envelope"></i> ส่งอีเมล</button>
                                </div>
                            </div>
                            <?php endfor; ?>
                            <hr>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <button type="button" class="btn btn-success" onclick="saveref()"><i class="bi bi-save"></i> บันทึกข้อมูล</button>
                                <button type="button" class="btn btn-info" id="genpdf"><i class="bi bi-printer"></i> พิมพ์เอกสาร</button>
                                <button type="button" class="btn btn-warning" onclick="sendmail()"><i class="bi bi-envelope-check"></i> ส่งอีเมลทั้งหมด</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal ผลการประเมิน -->
<div class="modal fade eval-modal" id="modalresult" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-clipboard-check"></i> ผลการประเมิน</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-4">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#navs1">ผู้ประเมินคนที่ 1</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#navs2">ผู้ประเมินคนที่ 2</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#navs3">ผู้ประเมินคนที่ 3</button></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="navs1"></div>
                    <div class="tab-pane fade" id="navs2"></div>
                    <div class="tab-pane fade" id="navs3"></div>
                </div>
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-info" onclick="genPDFresult()"><i class="bi bi-printer"></i> พิมพ์ผลการประเมิน</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal แจ้งเตือน -->
<div class="modal fade" id="modalconfirm" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> แจ้งเตือน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center" id="messageData"></div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/locales/bootstrap-datepicker.th.min.js"></script>
<script src="<?= $base ?>pdfmake/build/pdfmake.min.js"></script>
<script src="<?= $base ?>pdfmake/build/vfs_fonts.js"></script>
<script src="<?= $base ?>pdfmake/EvaluateDocument.js"></script>
<script>
    var global_id = 0;
    var adminBase = <?= json_encode($adminBase) ?>;
    var baseUrl = <?= json_encode($base) ?>;
    $(function() {
        $('.datepicker-input').datepicker({ format: 'yyyy-mm-dd', autoclose: true, language: 'th' });
    });
    function savedate(id) {
        var stopdate = $('#enddate' + id).val();
        if (!stopdate) { $('#messageData').html('<div class="alert alert-warning">กรุณาระบุวันที่</div>'); $('#modalconfirm').modal('show'); return; }
        $.post(adminBase + '/saveDate', { id: id, stopdate: stopdate }, function() {
            $('#messageData').html('<div class="alert alert-success">บันทึกเรียบร้อย</div>');
            $('#modalconfirm').modal('show');
            setTimeout(function() { location.reload(); }, 1500);
        });
    }
    function getInfo(id) {
        global_id = id;
        $('#ref1, #ref2, #ref3').val('');
        $.post(adminBase + '/getEvaluateInfo', { id: id }, function(data) {
            var url = baseUrl + 'serve/uploads/documents/';
            $('#msg_sFname').text(data.first_name || '');
            $('#msg_slname').text(data.last_name || '');
            $('#msg_scurriculum').text(data.curriculum || '');
            $('#msg_sworkingdate').text(data.start_date || '');
            $('#msg_sposition').text(data.position || '');
            $('#msg_spositionmajor').text(data.position_major || '');
            $('#msg_ssubjectid').text(data.subject_id || '');
            $('#msg_ssubjectname').text(data.subject_name || '');
            $('#msg_ssubjectcredit').text(data.subject_credit || '');
            $('#msg_slinkdoc').html(data.file_doc ? '<a href="' + url + data.file_doc + '" target="_blank" class="btn btn-sm btn-info">ดาวน์โหลด</a>' : '-');
            $('#msg_slinkvideo').html(data.link_video ? '<a href="' + (data.link_video) + '" target="_blank" class="btn btn-sm btn-warning">เปิดวีดีโอ</a>' : '-');
            $('#idEvaluate').val(id);
            if (data.referees && data.referees.length) {
                data.referees.forEach(function(ref) {
                    $('#ref' + (ref.ref_num || ref.refnum)).val((ref.email || '').toLowerCase());
                });
            }
        }, 'json');
    }
    function getResult(id) {
        global_id = id;
        var urlfile = baseUrl + 'serve/uploads/';
        var urllink = baseUrl + 'evaluate/';
        $.post(adminBase + '/getResult', { id: id }, function(data) {
            if (data.referees && data.referees.length) {
                data.referees.forEach(function(ref, i) {
                    var html = '<div class="card mb-2"><div class="card-body">';
                    html += '<div class="info-row">ชื่อผู้ประเมิน: ' + (ref.name || '-') + '</div>';
                    html += '<div class="info-row">อีเมล: ' + (ref.email || '-') + '</div>';
                    html += '<div class="info-row">ผลการประเมิน: <span class="badge bg-success">' + (ref.score || '-') + '</span></div>';
                    if (ref.file_doc) html += '<div class="info-row">ไฟล์: <a href="' + urlfile + ref.file_doc + '" target="_blank" class="btn btn-sm btn-info">ดาวน์โหลด</a></div>';
                    html += '<div class="info-row">ข้อเสนอแนะ: ' + (ref.comment || '-') + '</div>';
                    var payload = btoa(JSON.stringify({ id: id, email: ref.email }));
                    html += '<a href="' + urllink + payload + '" target="_blank" class="btn btn-sm btn-warning">เปิดแบบประเมิน</a></div></div>';
                    $('#navs' + (i + 1)).html(html);
                });
            }
            $('#modalresult').modal('show');
        }, 'json');
    }
    function saveref() {
        var obj = { idEvaluate: $('#idEvaluate').val(), ref1: $('#ref1').val(), ref2: $('#ref2').val(), ref3: $('#ref3').val(),
            nameref1: $('#ref1 option:selected').text(), nameref2: $('#ref2 option:selected').text(), nameref3: $('#ref3 option:selected').text() };
        $.post(adminBase + '/printRefAndSave', obj, function() {
            $('#messageData').html('<div class="alert alert-success">บันทึกข้อมูลเรียบร้อย</div>');
            $('#modalconfirm').modal('show');
        }, 'json');
    }
    function indvidualSendmail(num) {
        var email = $('#ref' + num + ' option:selected').val();
        var name = $('#ref' + num + ' option:selected').text();
        var id = $('#idEvaluate').val();
        if (!email) { $('#messageData').html('<div class="alert alert-warning">กรุณาเลือกผู้ประเมิน</div>'); $('#modalconfirm').modal('show'); return; }
        $.post(adminBase + '/sendmailEvaluate', { name: name, mail: email, id: id, refnum: num }, function() {
            $('#messageData').html('<div class="alert alert-success">ส่งอีเมลเรียบร้อย</div>');
            $('#modalconfirm').modal('show');
        }, 'json');
    }
    function sendmail() {
        var count = 0;
        for (var num = 1; num <= 3; num++) {
            var email = $('#ref' + num + ' option:selected').val();
            var name = $('#ref' + num + ' option:selected').text();
            var id = $('#idEvaluate').val();
            if (email) {
                $.post(adminBase + '/sendmailEvaluate', { name: name, mail: email, id: id, refnum: num }, function(){}, 'json');
                count++;
            }
        }
        $('#messageData').html('<div class="alert alert-success">ส่งอีเมลไปยังผู้ประเมิน ' + count + ' คน เรียบร้อย</div>');
        $('#modalconfirm').modal('show');
    }
    $('#genpdf').on('click', function() {
        $.post(adminBase + '/getEvaluateInfo', { id: global_id }, function(data) {
            var name = (data.title_thai || '') + ' ' + (data.first_name || '') + ' ' + (data.last_name || '');
            var title = data.position === 'ผู้ช่วยศาสตราจารย์' ? 'อาจารย์' : 'ผู้ช่วยศาสตราจารย์';
            var dataname = [name, data.curriculum || '', data.start_date || '', data.position || '', data.position_major || '', data.position_major_id || '', title];
            var datasubject = [data.subject_id || '', data.subject_name || '', data.subject_credit || '', data.subject_teacher || '', data.subject_detail || ''];
            var dataref = data.referees || [];
            if (typeof makepdf === 'function') makepdf(dataname, datasubject, dataref);
            else { $('#messageData').html('<div class="alert alert-danger">ไม่พบฟังก์ชัน makepdf</div>'); $('#modalconfirm').modal('show'); }
        }, 'json');
    });
    function genPDFresult() {
        if (typeof makePDFResult === 'function') makePDFResult(global_id);
        else { $('#messageData').html('<div class="alert alert-danger">ไม่พบฟังก์ชัน makePDFResult</div>'); $('#modalconfirm').modal('show'); }
    }
</script>
<?= $this->endSection() ?>

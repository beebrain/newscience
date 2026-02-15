<?= $this->extend('student_admin/layouts/student_admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header">
        <h2 style="margin: 0;"><?= esc($event['title']) ?></h2>
        <div>
            <a href="<?= base_url('student-admin/barcode-events/edit/' . $event['id']) ?>" class="btn btn-secondary">แก้ไข</a>
            <a href="<?= base_url('student-admin/barcode-events') ?>" class="btn btn-secondary">กลับรายการ</a>
        </div>
    </div>
    <div class="card-body">
        <p><strong>วันที่:</strong> <?= esc($event['event_date']) ?> &nbsp; <strong>สถานะ:</strong> <?= esc($event['status']) ?></p>
        <?php if (!empty($event['description'])): ?>
            <p><?= nl2br(esc($event['description'])) ?></p>
        <?php endif; ?>
        <p>บาร์โค้ดทั้งหมด: <?= (int)($event['barcode_total'] ?? 0) ?> | ผูกแล้ว: <?= (int)($event['barcode_assigned'] ?? 0) ?> | ยืนยันรับแล้ว: <?= (int)($event['barcode_claimed'] ?? 0) ?> | ผู้มีสิทธิ์: <?= (int)($event['eligibles_count'] ?? 0) ?></p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">นำเข้า Barcode</h3>
    </div>
    <div class="card-body">
        <div class="barcode-import-step" style="margin-bottom: 1.5rem;">
            <p class="form-label" style="margin-bottom: 0.75rem;">ขั้นที่ 1 — อัปโหลดไฟล์ แล้วระบบจะเรียก API ถอดข้อมูล (API ส่งกลับเป็น JSON)</p>
            <form action="<?= base_url('student-admin/barcode-events/parse-file/' . $event['id']) ?>" method="post" enctype="multipart/form-data" class="barcode-upload-form">
                <?= csrf_field() ?>
                <div class="form-group" style="margin-bottom: 0.75rem;">
                    <label for="barcode_file" class="form-label">เลือกไฟล์</label>
                    <input type="file" id="barcode_file" name="barcode_file" class="form-control" accept=".pdf,.txt,.csv,.json,application/pdf,text/plain,text/csv,application/json" style="max-width: 400px;">
                </div>
                <button type="submit" class="btn btn-primary">อัปโหลดและถอดข้อมูล</button>
            </form>
        </div>

        <div class="barcode-import-step">
            <p class="form-label" style="margin-bottom: 0.75rem;">ขั้นที่ 2 — ตรวจสอบรายการรหัส (แก้ไขได้) แล้วกดนำเข้า</p>
            <form action="<?= base_url('student-admin/barcode-events/import/' . $event['id']) ?>" method="post" id="form-import-barcode">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="json_barcodes" class="form-label">รายการรหัส (หนึ่งบรรทัดต่อหนึ่งรหัส หรือ JSON <code>{"barcodes": ["..."]}</code>)</label>
                    <textarea id="json_barcodes" name="json_barcodes" class="form-control" rows="8" placeholder="BC0001&#10;BC0002&#10;..."><?php
                        if (!empty($barcode_prefill)) {
                            echo esc(implode("\n", $barcode_prefill));
                        }
                    ?></textarea>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
                    <button type="submit" class="btn btn-primary">นำเข้า Barcode</button>
                    <button type="button" id="btn-load-dummy" class="btn btn-secondary btn-sm">โหลด Dummy ทดสอบ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 style="margin: 0; font-size: 1.125rem; font-weight: 600;">รายการบาร์โค้ด (<?= count($barcodes) ?> รายการ)</h3>
        <a href="<?= base_url('student-admin/barcode-events/eligibles/' . $event['id']) ?>" class="btn btn-secondary">จัดการผู้มีสิทธิ์</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <?php if (empty($barcodes)): ?>
            <p style="padding: 1rem;">ยังไม่มีบาร์โค้ด — ใช้ฟอร์มด้านบนอัปโหลดไฟล์หรือวางรายการรหัสแล้วกดนำเข้า</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>รหัสบาร์โค้ด</th>
                        <th>ผูกกับนักศึกษา</th>
                        <th>วันที่ผูก</th>
                        <th>ยืนยันรับ</th>
                        <th style="width: 140px;">การกระทำ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($barcodes, 0, 100) as $b): ?>
                        <tr>
                            <td><code><?= esc($b['code']) ?></code></td>
                            <td><?= $b['student_user_id'] ? 'ID ' . (int)$b['student_user_id'] : '—' ?></td>
                            <td><?= esc($b['assigned_at'] ?? '—') ?></td>
                            <td><?= !empty($b['claimed_at']) ? 'ยืนยันแล้ว ' . esc($b['claimed_at']) : '—' ?></td>
                            <td>
                                <?php if (!empty($b['student_user_id'])): ?>
                                    <form action="<?= base_url('student-admin/barcode-events/unassign/' . $event['id'] . '/' . $b['id']) ?>" method="post" style="display: inline;" onsubmit="return confirm('ยกเลิกการผูกบาร์โค้ดนี้?');">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-secondary btn-sm">ยกเลิกผูก</button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?= base_url('student-admin/barcode-events/delete-barcode/' . $event['id'] . '/' . $b['id']) ?>" method="post" style="display: inline;" onsubmit="return confirm('ลบบาร์โค้ดนี้?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-danger btn-sm">ลบ</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (count($barcodes) > 100): ?>
                <p style="padding: 1rem;">แสดง 100 รายการแรก จากทั้งหมด <?= count($barcodes) ?> รายการ</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>
<script>
document.getElementById('btn-load-dummy').addEventListener('click', function() {
    var count = prompt('จำนวนรหัส Dummy (1–500)', '20');
    if (count === null) return;
    count = Math.min(500, Math.max(1, parseInt(count, 10) || 20));
    fetch('<?= base_url('api/barcode-dummy') ?>?count=' + count)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            document.getElementById('json_barcodes').value = JSON.stringify(data, null, 2);
        })
        .catch(function() { alert('โหลด Dummy ไม่สำเร็จ'); });
});
</script>
<?= $this->endSection() ?>

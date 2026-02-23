<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin: 0;"><?= esc($event['title']) ?></h2>
            <small style="color: #666;">
                <?= $event['event_date'] ? date('d/m/Y', strtotime($event['event_date'])) : 'ไม่ระบุวันที่' ?> |
                เทมเพลต: <?= esc($event['template_name'] ?? '-') ?>
            </small>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= base_url('admin/cert-events/' . $event['id'] . '/edit') ?>" class="btn btn-secondary">แก้ไข</a>
            <?php if ($event['status'] !== 'issued'): ?>
                <a href="<?= base_url('admin/cert-events/' . $event['id'] . '/delete') ?>" class="btn btn-danger" onclick="return confirm('ยืนยันการลบ?')">ลบ</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-body">
        <!-- Event Info -->
        <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <div>
                    <strong>สถานะ:</strong><br>
                    <?php if ($event['status'] === 'draft'): ?>
                        <span class="badge badge-secondary">ร่าง</span>
                    <?php elseif ($event['status'] === 'open'): ?>
                        <span class="badge badge-success">เปิด</span>
                    <?php elseif ($event['status'] === 'issued'): ?>
                        <span class="badge badge-primary">ออก Cert แล้ว</span>
                    <?php elseif ($event['status'] === 'closed'): ?>
                        <span class="badge badge-warning">ปิด</span>
                    <?php else: ?>
                        <?= $event['status'] ?>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>ผู้ลงนาม:</strong><br>
                    <?php if ($event['signer_name']): ?>
                        <?= esc($event['signer_name'] . ' ' . $event['signer_lastname']) ?>
                    <?php else: ?>
                        <em>ไม่ระบุ</em>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>รายละเอียด:</strong><br>
                    <?php if ($event['description']): ?>
                        <?= nl2br(esc($event['description'])) ?>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <?php
        $pending = 0;
        $issued = 0;
        $failed = 0;
        foreach ($recipients as $r) {
            if ($r['status'] === 'pending') $pending++;
            elseif ($r['status'] === 'issued') $issued++;
            elseif ($r['status'] === 'failed') $failed++;
        }
        $total = count($recipients);
        ?>
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;">
            <div style="background: #e3f2fd; padding: 1rem; border-radius: 4px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #1976d2;"><?= $total ?></div>
                <div style="font-size: 12px; color: #666;">ผู้รับทั้งหมด</div>
            </div>
            <div style="background: #fff3e0; padding: 1rem; border-radius: 4px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #f57c00;"><?= $pending ?></div>
                <div style="font-size: 12px; color: #666;">รอออก Certificate</div>
            </div>
            <div style="background: #e8f5e9; padding: 1rem; border-radius: 4px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #388e3c;"><?= $issued ?></div>
                <div style="font-size: 12px; color: #666;">ออกแล้ว</div>
            </div>
            <div style="background: #ffebee; padding: 1rem; border-radius: 4px; text-align: center;">
                <div style="font-size: 24px; font-weight: bold; color: #d32f2f;"><?= $failed ?></div>
                <div style="font-size: 12px; color: #666;">ไม่สำเร็จ</div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <?php if ($event['status'] !== 'draft' && $pending > 0): ?>
                <a href="<?= base_url('admin/cert-events/' . $event['id'] . '/issue') ?>" class="btn btn-primary" onclick="return confirm('ยืนยันการออก Certificate ให้ผู้รับทั้งหมดที่รออยู่?')">
                    ออก Certificate ทั้งหมด (<?= $pending ?> ราย)
                </a>
            <?php endif; ?>
            <a href="<?= base_url('admin/cert-events/' . $event['id'] . '/import') ?>" class="btn btn-secondary">นำเข้ารายชื่อ (CSV)</a>
            <?php if ($total > 0): ?>
                <a href="<?= base_url('admin/cert-events/' . $event['id'] . '/export') ?>" class="btn btn-secondary">ส่งออกรายชื่อ</a>
            <?php endif; ?>
        </div>

        <!-- Add Recipient Form -->
        <?php if ($event['status'] !== 'closed' && $event['status'] !== 'issued'): ?>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem;">
                <h4 style="margin-top: 0;">เพิ่มผู้รับ</h4>
                <form method="post" action="<?= base_url('admin/cert-events/' . $event['id'] . '/add-recipient') ?>" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 0.5rem; align-items: end;">
                    <?= csrf_field() ?>
                    <div>
                        <label style="font-size: 12px;">ชื่อผู้รับ <span class="text-danger">*</span></label>
                        <input type="text" name="recipient_name" class="form-control" required>
                    </div>
                    <div>
                        <label style="font-size: 12px;">อีเมล</label>
                        <input type="email" name="recipient_email" class="form-control">
                    </div>
                    <div>
                        <label style="font-size: 12px;">รหัสนักศึกษา</label>
                        <input type="text" name="recipient_id_no" class="form-control">
                    </div>
                    <div>
                        <label style="font-size: 12px;">เลือกจากระบบ</label>
                        <select name="student_id" class="form-control">
                            <option value="">-- หรือเลือกจากรายชื่อ --</option>
                            <?php foreach ($students as $student): ?>
                                <option value="<?= $student['id'] ?>">
                                    <?= esc(($student['th_name'] ?? '') . ' ' . ($student['thai_lastname'] ?? '')) ?> (<?= $student['login_uid'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">เพิ่ม</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Recipients List -->
        <h4>รายชื่อผู้รับ (<?= $total ?> ราย)</h4>
        <?php if (empty($recipients)): ?>
            <div class="alert alert-info">ยังไม่มีผู้รับ กรุณาเพิ่มรายชื่อหรือนำเข้าจาก CSV</div>
        <?php else: ?>
            <table class="table" style="width: 100%; border-collapse: collapse; font-size: 14px;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 0.5rem;">#</th>
                        <th style="padding: 0.5rem;">ชื่อ</th>
                        <th style="padding: 0.5rem;">รหัส/อีเมล</th>
                        <th style="padding: 0.5rem; text-align: center;">สถานะ</th>
                        <th style="padding: 0.5rem;">เลขที่ Certificate</th>
                        <th style="padding: 0.5rem; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recipients as $i => $recipient): ?>
                        <tr style="border-bottom: 1px solid #dee2e6;">
                            <td style="padding: 0.5rem;"><?= $i + 1 ?></td>
                            <td style="padding: 0.5rem;"><?= esc($recipient['recipient_name']) ?></td>
                            <td style="padding: 0.5rem;">
                                <?= $recipient['recipient_id_no'] ? esc($recipient['recipient_id_no']) . '<br>' : '' ?>
                                <small style="color: #666;"><?= esc($recipient['recipient_email']) ?></small>
                            </td>
                            <td style="padding: 0.5rem; text-align: center;">
                                <?php
                                $statusClasses = [
                                    'pending' => '<span class="badge badge-warning">รอออก</span>',
                                    'issued'  => '<span class="badge badge-success">ออกแล้ว</span>',
                                    'failed'  => '<span class="badge badge-danger">ไม่สำเร็จ</span>',
                                ];
                                echo $statusClasses[$recipient['status']] ?? $recipient['status'];
                                ?>
                                <?php if ($recipient['error_message']): ?>
                                    <br><small style="color: #dc3545;"><?= esc($recipient['error_message']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.5rem;">
                                <?php if ($recipient['certificate_no']): ?>
                                    <code><?= esc($recipient['certificate_no']) ?></code>
                                    <?php if ($recipient['download_count']): ?>
                                        <br><small style="color: #666;">ดาวน์โหลด <?= $recipient['download_count'] ?> ครั้ง</small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.5rem; text-align: center;">
                                <?php if ($recipient['status'] === 'pending'): ?>
                                    <a href="<?= base_url('admin/cert-events/recipient/' . $recipient['id'] . '/remove') ?>" class="btn btn-sm btn-danger" onclick="return confirm('ลบผู้รับนี้?')">ลบ</a>
                                <?php elseif ($recipient['pdf_path']): ?>
                                    <a href="<?= base_url('uploads/' . $recipient['pdf_path']) ?>" target="_blank" class="btn btn-sm btn-primary">ดู PDF</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<style>
    .badge {
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 12px;
    }

    .badge-secondary {
        background: #6c757d;
        color: white;
    }

    .badge-success {
        background: #28a745;
        color: white;
    }

    .badge-primary {
        background: #007bff;
        color: white;
    }

    .badge-warning {
        background: #ffc107;
        color: black;
    }

    .badge-danger {
        background: #dc3545;
        color: white;
    }
</style>
<?= $this->endSection() ?>
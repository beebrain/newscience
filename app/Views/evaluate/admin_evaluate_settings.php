<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php helper('url');
$base = rtrim(base_url(), '/');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .settings-page-wrap {
        display: grid;
        gap: 1.5rem;
        font-family: var(--font-primary);
    }

    .settings-page-wrap,
    .settings-page-wrap input,
    .settings-page-wrap textarea,
    .settings-page-wrap select,
    .settings-page-wrap button,
    .settings-page-wrap .btn,
    .settings-page-wrap .badge {
        font-family: var(--font-primary);
    }

    .settings-card {
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .settings-card-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--color-gray-200);
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        color: white;
    }

    .settings-card-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
    }

    .settings-card-body {
        padding: 1.5rem;
    }

    .settings-section {
        margin-bottom: 2rem;
    }

    .settings-section:last-child {
        margin-bottom: 0;
    }

    .settings-section-title {
        font-weight: 600;
        font-size: 1rem;
        color: #0369a1;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e0f2fe;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-label {
        font-weight: 500;
        font-size: 0.875rem;
        color: var(--color-gray-700);
    }

    .form-text {
        font-size: 0.8rem;
    }

    .form-check-input:checked {
        background-color: #0ea5e9;
        border-color: #0ea5e9;
    }

    .placeholder-list {
        font-size: 0.8rem;
        color: var(--color-gray-600);
        background: #f8fafc;
        padding: 0.75rem;
        border-radius: 8px;
        margin-top: 0.5rem;
    }

    .placeholder-list code {
        color: #0369a1;
        background: #e0f2fe;
        padding: 0.1rem 0.3rem;
        border-radius: 4px;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0369a1 0%, #0ea5e9 100%);
        border: none;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 100%);
    }

    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .status-open {
        background: #dcfce7;
        color: #166534;
    }

    .status-closed {
        background: #fee2e2;
        color: #991b1b;
    }

    textarea.form-control {
        font-family: monospace;
        min-height: 150px;
    }
</style>

<div class="settings-page-wrap">
    <!-- Header -->
    <div class="settings-card">
        <div class="settings-card-header">
            <h4><i class="bi bi-gear-fill me-2"></i>ตั้งค่าระบบการประเมินการสอน</h4>
        </div>
        <div class="settings-card-body">
            <!-- Status Display -->
            <div class="mb-4">
                <div class="d-flex align-items-center gap-3">
                    <span>สถานะระบบ:</span>
                    <?php if ($settings['is_active']): ?>
                        <span class="status-indicator status-open">
                            <i class="bi bi-check-circle-fill"></i> เปิดรับคำร้อง
                        </span>
                    <?php else: ?>
                        <span class="status-indicator status-closed">
                            <i class="bi bi-x-circle-fill"></i> ปิดรับคำร้อง
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <form id="settingsForm">
                <!-- Section 1: System Open/Close -->
                <div class="settings-section">
                    <div class="settings-section-title">
                        <i class="bi bi-calendar-range"></i>
                        1. ข้อมูลการเปิด/ปิดระบบ
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= ($settings['is_active'] ?? 1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_active">เปิดรับคำร้อง</label>
                            </div>
                            <div class="form-text">ปิดระบบจะไม่สามารถส่งคำร้องใหม่ได้</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">วันที่เริ่มเปิดรับคำร้อง</label>
                            <input type="date" class="form-control" name="start_date" value="<?= esc($settings['start_date'] ?? '') ?>">
                            <div class="form-text">เว้นว่าง = เปิดทันที</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">วันที่สิ้นสุดการรับคำร้อง</label>
                            <input type="date" class="form-control" name="end_date" value="<?= esc($settings['end_date'] ?? '') ?>">
                            <div class="form-text">เว้นว่าง = ไม่กำหนด</div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Notification Emails -->
                <div class="settings-section">
                    <div class="settings-section-title">
                        <i class="bi bi-envelope-fill"></i>
                        2. Email ผู้รับแจ้งเตือน
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รายการ Email ที่จะได้รับแจ้งเตือนเมื่อมีผู้ส่งคำร้อง</label>
                        <textarea class="form-control" name="notification_emails" rows="3" placeholder="email1@example.com, email2@example.com"><?= esc($settings['notification_emails'] ?? '') ?></textarea>
                        <div class="form-text">แยกหลายอีเมลด้วยเครื่องหมาย comma (,)</div>
                    </div>
                </div>

                <!-- Section 3: Referee Email Template -->
                <div class="settings-section">
                    <div class="settings-section-title">
                        <i class="bi bi-person-badge"></i>
                        3. ข้อความอีเมลสำหรับผู้ทรงคุณวุฒิ (ผู้ประเมิน)
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หัวข้ออีเมล</label>
                        <input type="text" class="form-control" name="referee_email_subject" value="<?= esc($settings['referee_email_subject'] ?? 'ขอความอนุเคราะห์ประเมินการสอน - {position}') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เทมเพลตข้อความ</label>
                        <textarea class="form-control" name="referee_email_template" rows="6"><?= esc($settings['referee_email_template'] ?? '') ?></textarea>
                        <div class="placeholder-list">
                            <strong>Placeholder ที่ใช้ได้:</strong><br>
                            <code>{referee_name}</code> - ชื่อผู้ทรงคุณวุฒิ<br>
                            <code>{applicant_name}</code> - ชื่อผู้ขอรับการประเมิน<br>
                            <code>{position}</code> - ตำแหน่งที่ขอ<br>
                            <code>{subject_name}</code> - ชื่อวิชา<br>
                            <code>{subject_id}</code> - รหัสวิชา
                        </div>
                    </div>
                </div>

                <!-- Section 4: Applicant Email Template -->
                <div class="settings-section">
                    <div class="settings-section-title">
                        <i class="bi bi-person-check"></i>
                        4. ข้อความอีเมลสำหรับผู้ขอรับการประเมิน
                    </div>
                    <div class="mb-3">
                        <label class="form-label">หัวข้ออีเมล</label>
                        <input type="text" class="form-control" name="applicant_email_subject" value="<?= esc($settings['applicant_email_subject'] ?? 'ยืนยันการส่งคำร้องขอรับการประเมิน - {position}') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เทมเพลตข้อความ</label>
                        <textarea class="form-control" name="applicant_email_template" rows="6"><?= esc($settings['applicant_email_template'] ?? '') ?></textarea>
                        <div class="placeholder-list">
                            <strong>Placeholder ที่ใช้ได้:</strong><br>
                            <code>{applicant_name}</code> - ชื่อผู้ขอรับการประเมิน<br>
                            <code>{position}</code> - ตำแหน่งที่ขอ<br>
                            <code>{subject_name}</code> - ชื่อวิชา<br>
                            <code>{subject_id}</code> - รหัสวิชา<br>
                            <code>{submit_date}</code> - วันที่ส่งคำร้อง
                        </div>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>รีเซ็ต
                    </button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <i class="bi bi-save me-1"></i>บันทึกการตั้งค่า
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
const saveUrl = '<?= base_url('admin/evaluate/settings/save') ?>';
const originalData = <?= json_encode($settings) ?>;

$('#settingsForm').on('submit', function(e) {
    e.preventDefault();
    
    const btn = $('#saveBtn');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...');
    
    $.ajax({
        url: saveUrl,
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json'
    })
    .done(function(res) {
        if (res.success) {
            alert(res.message);
            location.reload();
        } else {
            alert(res.message || 'เกิดข้อผิดพลาด');
        }
    })
    .fail(function(xhr) {
        const msg = xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการบันทึก';
        alert(msg);
    })
    .always(function() {
        btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i>บันทึกการตั้งค่า');
    });
});

function resetForm() {
    if (confirm('ต้องการรีเซ็ตค่าเป็นค่าเริ่มต้น?')) {
        location.reload();
    }
}
</script>

<?= $this->endSection() ?>

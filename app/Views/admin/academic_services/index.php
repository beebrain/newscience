<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="card-header">
        <h2>ข้อมูลการบริการวิชาการ</h2>
        <button type="button" class="btn btn-primary" id="btnOpenCreateModal" aria-haspopup="dialog" aria-expanded="false">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            เพิ่มรายการ
        </button>
    </div>

    <div class="card-body">
        <?php if (session('success')): ?>
            <div class="alert alert-success"><?= esc(session('success')) ?></div>
        <?php endif; ?>
        <?php if (session('error')): ?>
            <div class="alert alert-danger"><?= esc(session('error')) ?></div>
        <?php endif; ?>

        <form method="get" action="<?= base_url('admin/academic-services') ?>" class="form-row" style="margin-bottom: 1.5rem; gap: 1rem; flex-wrap: wrap;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="year" class="form-label">ปีการศึกษา (พ.ศ.)</label>
                <select name="year" id="year" class="form-control" style="min-width: 120px;">
                    <option value="">ทุกปี</option>
                    <?php foreach ($years as $y): ?>
                        <option value="<?= esc($y) ?>" <?= ($selected_year ?? '') === $y ? 'selected' : '' ?>><?= esc($y) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0; flex: 1; min-width: 200px;">
                <label for="keyword" class="form-label">ค้นหาชื่อโครงการ/กิจกรรม</label>
                <input type="text" name="keyword" id="keyword" class="form-control" placeholder="พิมพ์คำค้น..."
                       value="<?= esc($keyword ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0; align-self: flex-end;">
                <button type="submit" class="btn btn-primary">ค้นหา</button>
                <a href="<?= base_url('admin/academic-services') ?>" class="btn btn-secondary">ล้าง</a>
            </div>
        </form>

        <?php if (empty($list)): ?>
            <div class="empty-state">
                <p>ยังไม่มีรายการบริการวิชาการ</p>
                <button type="button" class="btn btn-primary open-create-modal" style="margin-top: 1rem;">เพิ่มรายการ</button>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 90px;">วันที่</th>
                            <th style="width: 80px;">ปี (พ.ศ.)</th>
                            <th>ชื่อโครงการ/กิจกรรม</th>
                            <th style="width: 140px;">ลักษณะบริการ</th>
                            <th style="width: 80px;">ผู้ร่วมงาน</th>
                            <th style="width: 160px;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $serviceTypeLabels = [
                            'training_seminar' => 'อบรม/สัมมนา',
                            'workshop' => 'ฝึกปฏิบัติการ/Workshop',
                            'consultant' => 'ที่ปรึกษาทางวิชาการ',
                            'lab_testing' => 'วิเคราะห์ทดสอบ/ห้องปฏิบัติการ',
                            'expert_evaluator' => 'ผู้ทรงคุณวุฒิประเมินผล',
                            'other' => 'อื่นๆ',
                        ];
                        foreach ($list as $row):
                            $typeLabel = $serviceTypeLabels[$row['service_type'] ?? ''] ?? ($row['service_type_spec'] ?? $row['service_type'] ?? '—');
                            $count = $participant_counts[$row['id']] ?? 0;
                        ?>
                            <tr>
                                <td><?= $row['service_date'] ? date('d/m/Y', strtotime($row['service_date'])) : '—' ?></td>
                                <td><?= esc($row['academic_year'] ?? '—') ?></td>
                                <td><strong><?= esc($row['title']) ?></strong></td>
                                <td><?= esc($typeLabel) ?></td>
                                <td><?= $count ?></td>
                                <td>
                                    <a href="<?= base_url('admin/academic-services/edit/' . $row['id']) ?>" class="btn btn-secondary btn-sm">แก้ไข</a>
                                    <a href="<?= base_url('admin/academic-services/delete/' . $row['id']) ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('ยืนยันการลบรายการนี้?')">ลบ</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal เพิ่มรายการบริการวิชาการ -->
<div id="createServiceModal" class="academic-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="createModalTitle" aria-hidden="true" style="display: none;">
    <div class="academic-modal" style="max-width: 480px;">
        <div class="academic-modal-header">
            <h2 id="createModalTitle">เพิ่มรายการบริการวิชาการ</h2>
            <button type="button" class="academic-modal-close" id="btnCloseCreateModal" aria-label="ปิด">×</button>
        </div>
        <form action="<?= base_url('admin/academic-services/store') ?>" method="post" id="createServiceForm">
            <?= csrf_field() ?>
            <div class="academic-modal-body">
                <?php if (session('errors')): ?>
                    <div class="alert alert-danger" role="alert">
                        <ul style="margin: 0; padding-left: 1.2rem;">
                            <?php foreach (session('errors') as $e): ?>
                                <li><?= esc($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="modal_academic_year" class="form-label">ปีการศึกษา (พ.ศ.)</label>
                    <select id="modal_academic_year" name="academic_year" class="form-control">
                        <option value="">— เลือก —</option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?= esc($y) ?>" <?= (old('academic_year', $selected_year ?? '') === $y ? 'selected' : '') ?>><?= esc($y) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="modal_service_date" class="form-label">วัน/เดือน/ปี ที่บริการวิชาการ <span class="required">*</span></label>
                    <input type="date" id="modal_service_date" name="service_date" class="form-control" required
                           value="<?= esc(old('service_date', date('Y-m-d'))) ?>">
                </div>
                <div class="form-group">
                    <label for="modal_title" class="form-label">ชื่อโครงการ/กิจกรรม/หัวข้อ <span class="required">*</span></label>
                    <input type="text" id="modal_title" name="title" class="form-control" required
                           value="<?= esc(old('title')) ?>"
                           placeholder="ชื่อโครงการหรือกิจกรรม">
                </div>
            </div>
            <div class="academic-modal-footer">
                <button type="button" class="btn btn-secondary" id="btnCancelCreateModal">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึกและกรอกข้อมูลเพิ่ม</button>
            </div>
        </form>
    </div>
</div>

<style>
.academic-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.4);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    box-sizing: border-box;
}
.academic-modal-overlay[aria-hidden="false"] { display: flex !important; }
.academic-modal {
    background: var(--color-white, #fff);
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.15);
    width: 100%;
    max-height: calc(100vh - 2rem);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
.academic-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--color-gray-200, #e5e7eb);
}
.academic-modal-header h2 { margin: 0; font-size: 1.125rem; }
.academic-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    line-height: 1;
    cursor: pointer;
    padding: 0 0.25rem;
    color: var(--color-gray-600, #4b5563);
}
.academic-modal-close:hover { color: #111; }
.academic-modal-body { padding: 1.25rem; overflow-y: auto; }
.academic-modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--color-gray-200, #e5e7eb);
}
</style>

<script>
(function() {
    var modal = document.getElementById('createServiceModal');
    var btnOpen = document.getElementById('btnOpenCreateModal');
    var btnClose = document.getElementById('btnCloseCreateModal');
    var btnCancel = document.getElementById('btnCancelCreateModal');

    function openModal() {
        if (!modal) return;
        modal.style.display = '';
        modal.setAttribute('aria-hidden', 'false');
        if (btnOpen) btnOpen.setAttribute('aria-expanded', 'true');
        document.getElementById('modal_title').focus();
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        if (btnOpen) btnOpen.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', openModal);
    [].forEach.call(document.querySelectorAll('.open-create-modal'), function(b) { b.addEventListener('click', openModal); });
    if (btnClose) btnClose.addEventListener('click', closeModal);
    if (btnCancel) btnCancel.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    }

    <?php if (service('request')->getGet('openModal') === 'create' || session('errors')): ?>
    openModal();
    <?php endif; ?>
})();
</script>

<?= $this->endSection() ?>

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
            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_from" class="form-label">วันที่บริการ ตั้งแต่</label>
                <input type="date" name="date_from" id="date_from" class="form-control" style="min-width: 150px;"
                       value="<?= esc($selected_date_from ?? '') ?>">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_to" class="form-label">ถึงวันที่</label>
                <input type="date" name="date_to" id="date_to" class="form-control" style="min-width: 150px;"
                       value="<?= esc($selected_date_to ?? '') ?>">
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
                            <th style="min-width: 120px;">ช่วงวันที่</th>
                            <th style="width: 80px;">ปี (พ.ศ.)</th>
                            <th>ชื่อโครงการ/กิจกรรม</th>
                            <th style="width: 140px;">ลักษณะบริการ</th>
                            <th style="width: 80px;">ผู้ร่วมงาน</th>
                            <th style="width: 90px;">เอกสารแนบ</th>
                            <th style="min-width: 180px;">จัดการ</th>
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
                            'lecturer' => 'วิทยากร',
                            'other' => 'อื่นๆ',
                        ];
                        foreach ($list as $row):
                            $typeLabel = $serviceTypeLabels[$row['service_type'] ?? ''] ?? ($row['service_type_spec'] ?? $row['service_type'] ?? '—');
                            $count = $participant_counts[$row['id']] ?? 0;
                            $attCount = $attachment_counts[$row['id']] ?? 0;
                            $sd = $row['service_date'] ?? '';
                            $ed = $row['service_date_end'] ?? '';
                            if ($sd) {
                                $dateCell = date('d/m/Y', strtotime($sd));
                                if ($ed !== null && $ed !== '' && $ed !== $sd) {
                                    $dateCell .= ' – ' . date('d/m/Y', strtotime($ed));
                                }
                            } else {
                                $dateCell = '—';
                            }
                        ?>
                            <tr>
                                <td><?= esc($dateCell) ?></td>
                                <td><?= esc($row['academic_year'] ?? '—') ?></td>
                                <td>
                                    <button type="button" class="academic-row-title" data-id="<?= (int) $row['id'] ?>"><?= esc($row['title']) ?></button>
                                </td>
                                <td><?= esc($typeLabel) ?></td>
                                <td><?= $count ?></td>
                                <td><?= $attCount > 0 ? (int) $attCount . ' ไฟล์' : '—' ?></td>
                                <td>
                                    <button type="button" class="btn btn-secondary btn-sm btn-edit-service" data-id="<?= (int) $row['id'] ?>">แก้ไข</button>
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

<!-- Modal เดียว: เพิ่ม/แก้ไข (โหลดฟอร์มเต็มใน iframe — ไม่มีเมนูแอดมิน) -->
<div id="academicServiceModal" class="academic-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="academicModalTitle" aria-hidden="true" style="display: none;">
    <div class="academic-modal academic-modal-lg" style="max-width: 900px; height: 90vh;">
        <div class="academic-modal-header">
            <h2 id="academicModalTitle">เพิ่มรายการบริการวิชาการ</h2>
            <button type="button" class="academic-modal-close" id="btnCloseAcademicModal" aria-label="ปิด">×</button>
        </div>
        <div class="academic-modal-body" style="flex: 1; min-height: 0; padding: 0;">
            <iframe id="academicServiceFrame" name="academicServiceFrame" style="width: 100%; height: 100%; min-height: 70vh; border: none;"></iframe>
        </div>
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
/* Modal ที่มีแต่ iframe: ไม่ให้มี scroll ซ้อน — ให้ scroll เฉพาะใน iframe */
.academic-modal-lg .academic-modal-body { padding: 0; overflow: hidden; }
.academic-modal-footer {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
    padding: 1rem 1.25rem;
    border-top: 1px solid var(--color-gray-200, #e5e7eb);
}
button.academic-row-title {
    display: inline;
    margin: 0;
    padding: 0;
    border: none;
    background: none;
    font: inherit;
    font-weight: 600;
    color: var(--color-primary, #2563eb);
    text-align: left;
    cursor: pointer;
    text-decoration: underline;
    text-decoration-color: transparent;
    max-width: 100%;
    white-space: normal;
    word-break: break-word;
}
button.academic-row-title:hover,
button.academic-row-title:focus-visible {
    text-decoration-color: currentColor;
}

</style>

<script>
(function() {
    var modal = document.getElementById('academicServiceModal');
    var modalTitle = document.getElementById('academicModalTitle');
    var btnOpen = document.getElementById('btnOpenCreateModal');
    var btnClose = document.getElementById('btnCloseAcademicModal');
    var frame = document.getElementById('academicServiceFrame');
    var formViewBase = '<?= base_url('admin/academic-services/form-view') ?>';
    var detailViewBase = '<?= base_url('admin/academic-services/detail-view') ?>';

    function openModal(mode, id) {
        if (!modal || !frame) return;
        if (mode === 'detail' && id) {
            frame.src = detailViewBase + '/' + id;
            if (modalTitle) modalTitle.textContent = 'รายละเอียดบริการวิชาการ';
        } else if (mode === 'edit' && id) {
            frame.src = formViewBase + '/' + id;
            if (modalTitle) modalTitle.textContent = 'แก้ไขรายการบริการวิชาการ';
        } else {
            frame.src = formViewBase;
            if (modalTitle) modalTitle.textContent = 'เพิ่มรายการบริการวิชาการ';
        }
        modal.style.display = '';
        modal.setAttribute('aria-hidden', 'false');
        if (btnOpen) btnOpen.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    }
    function closeModal() {
        if (!modal) return;
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        if (btnOpen) btnOpen.setAttribute('aria-expanded', 'false');
        if (frame) frame.src = 'about:blank';
        document.body.style.overflow = '';
    }

    if (btnOpen) btnOpen.addEventListener('click', function() { openModal('create'); });
    [].forEach.call(document.querySelectorAll('.open-create-modal'), function(b) {
        b.addEventListener('click', function() { openModal('create'); });
    });
    if (btnClose) btnClose.addEventListener('click', closeModal);

    [].forEach.call(document.querySelectorAll('.academic-row-title'), function(b) {
        b.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (id) openModal('detail', id);
        });
    });
    [].forEach.call(document.querySelectorAll('.btn-edit-service'), function(b) {
        b.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            if (id) openModal('edit', id);
        });
    });

    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeModal();
        });
    }

    window.addEventListener('message', function(e) {
        var d = e.data;
        if (d && typeof d === 'object' && d.type === 'academic-open-edit' && d.id) {
            openModal('edit', String(d.id));
            return;
        }
        if (d === 'academic-service-updated' || d === 'academic-close-modal') {
            closeModal();
            if (d === 'academic-service-updated') window.location.reload();
        }
    });

    <?php if (service('request')->getGet('openModal') === 'create' || session('errors')): ?>
    openModal('create');
    <?php endif; ?>
})();
</script>

<?= $this->endSection() ?>

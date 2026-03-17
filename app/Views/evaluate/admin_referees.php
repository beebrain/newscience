<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>
<?php helper('url');
$base = rtrim(base_url(), '/');
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    .ref-page-wrap {
        display: grid;
        gap: 1.5rem;
        font-family: var(--font-primary);
    }

    .ref-page-wrap,
    .ref-page-wrap input,
    .ref-page-wrap textarea,
    .ref-page-wrap select,
    .ref-page-wrap button,
    .ref-page-wrap .btn,
    .ref-page-wrap .badge,
    .ref-page-wrap .modal-content {
        font-family: var(--font-primary);
    }

    .ref-card {
        border: 1px solid var(--color-gray-200);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
        margin-bottom: 0;
        overflow: hidden;
    }

    .ref-page-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--color-gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .ref-page-header__actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .search-wrap {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #f8f9fa;
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        max-width: 520px;
        width: 100%;
    }

    .search-wrap .form-control {
        border-radius: 6px;
        border: 1px solid #ced4da;
        min-height: 38px;
        box-shadow: none;
    }

    .search-wrap .form-control:focus,
    .ref-page-wrap .form-control:focus,
    .ref-page-wrap .form-select:focus {
        border-color: #4dabf7;
        box-shadow: 0 0 0 3px rgba(77, 171, 247, 0.12);
    }

    .news-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.45rem 0.8rem;
        border-radius: 999px;
        background: var(--color-blue-50, #e7f5ff);
        color: var(--color-blue-700, #1971c2);
        border: 1px solid var(--color-blue-200, #a5d8ff);
        font-size: 0.875rem;
        font-weight: 600;
    }

    .ref-card .card-header {
        background: #fff;
        color: var(--color-gray-900);
        border-bottom: 1px solid var(--color-gray-200);
        padding: 1rem 1.25rem;
        font-weight: 700;
    }

    .ref-table thead th {
        background: #f8f9fa;
        color: #495057;
        padding: 0.8rem 1rem;
        font-size: 0.875rem;
        border-bottom: 1px solid var(--color-gray-200);
    }

    .ref-table tbody td {
        vertical-align: middle;
        padding: 0.9rem 1rem;
    }

    .ref-table tbody tr:hover {
        background: #fcfcfd;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid transparent;
    }

    .badge-active {
        background: #e8f5e9;
        color: #2e7d32;
        border-color: #a5d6a7;
    }

    .badge-inactive {
        background: #fff5f5;
        color: #c92a2a;
        border-color: #ffc9c9;
    }

    .btn {
        border-radius: 8px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .btn-group .btn {
        border-radius: 8px !important;
    }

    .btn-primary {
        background: #339af0;
        border-color: #339af0;
    }

    .btn-primary:hover {
        background: #228be6;
        border-color: #228be6;
    }

    .btn-outline-primary {
        color: #228be6;
        border-color: #a5d8ff;
        background: #f8fbff;
    }

    .btn-outline-secondary {
        color: #495057;
        border-color: #ced4da;
        background: #fff;
    }

    .btn-outline-danger {
        background: #fff5f5;
    }

    .ref-modal .modal-content,
    #deleteModal .modal-content {
        border-radius: 12px;
        border: 1px solid var(--color-gray-200);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.18);
    }

    .ref-modal .modal-header,
    #deleteModal .modal-header {
        background: #fff;
        color: var(--color-gray-900);
        border-bottom: 1px solid var(--color-gray-200);
    }

    #deleteModal .modal-header {
        background: #fff5f5;
        color: #c92a2a;
    }

    .ref-modal .modal-body,
    #deleteModal .modal-body {
        background: #fcfcfd;
        padding: 1.25rem;
    }

    .ref-modal .modal-footer,
    #deleteModal .modal-footer {
        border-top: 1px solid var(--color-gray-200);
        padding: 1rem 1.25rem;
    }

    @media (max-width: 768px) {
        .ref-page-header {
            padding: 1rem;
        }

        .search-wrap {
            max-width: 100%;
        }
    }
</style>

<div class="ref-page-wrap">
    <div class="card ref-card">
        <div class="ref-page-header">
            <div>
                <h2 class="mb-1"><i class="bi bi-people-fill me-2"></i>จัดการผู้ทรงคุณวุฒิ</h2>
                <p class="text-muted mb-0">รายชื่อผู้ทรงคุณวุฒิที่ใช้ประเมินผลการสอน</p>
            </div>
            <div class="ref-page-header__actions">
                <span class="news-stat-pill"><strong><?= count($referees) ?></strong> คน</span>
                <a href="<?= $base ?>/evaluate/admin" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>กลับหน้าจัดการประเมิน</a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#refereeModal" onclick="resetForm()">
                    <i class="bi bi-plus-lg me-1"></i>เพิ่มผู้ทรงคุณวุฒิ
                </button>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card ref-card">
        <div class="card-body py-3">
            <form method="get" action="<?= $base ?>/evaluate/admin/referees" class="search-wrap">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="ค้นหาชื่อ, อีเมล, สถาบัน..." value="<?= esc($search ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-primary"><i class="bi bi-search"></i></button>
                <?php if (! empty($search)): ?>
                    <a href="<?= $base ?>/evaluate/admin/referees" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card ref-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-list-ul me-2"></i>รายชื่อผู้ทรงคุณวุฒิ (<?= count($referees) ?> คน)</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover ref-table mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ชื่อ</th>
                            <th>อีเมล</th>
                            <th>สถาบัน</th>
                            <th>ความเชี่ยวชาญ</th>
                            <th>โทรศัพท์</th>
                            <th>สถานะ</th>
                            <th>จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($referees)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">ไม่พบข้อมูลผู้ทรงคุณวุฒิ</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($referees as $i => $ref): ?>
                                <tr>
                                    <td><?= $i + 1 ?></td>
                                    <td><strong><?= esc($ref['name'] ?? '') ?></strong></td>
                                    <td><a href="mailto:<?= esc($ref['email'] ?? '') ?>"><?= esc($ref['email'] ?? '') ?></a></td>
                                    <td><?= esc($ref['institution'] ?? '-') ?></td>
                                    <td><small><?= esc($ref['expertise'] ?? '-') ?></small></td>
                                    <td><?= esc($ref['phone'] ?? '-') ?></td>
                                    <td>
                                        <?php if ((int)($ref['status'] ?? 0) === 1): ?>
                                            <span class="badge badge-active">ใช้งาน</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">ปิดใช้งาน</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" style="gap: 0.35rem;">
                                            <button class="btn btn-outline-primary" onclick="editReferee(<?= (int)$ref['id'] ?>)" title="แก้ไข"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-outline-<?= (int)($ref['status'] ?? 0) === 1 ? 'warning' : 'success' ?>" onclick="toggleStatus(<?= (int)$ref['id'] ?>)" title="<?= (int)($ref['status'] ?? 0) === 1 ? 'ปิดใช้งาน' : 'เปิดใช้งาน' ?>">
                                                <i class="bi bi-<?= (int)($ref['status'] ?? 0) === 1 ? 'pause-circle' : 'play-circle' ?>"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" onclick="deleteReferee(<?= (int)$ref['id'] ?>, '<?= esc($ref['name'] ?? '') ?>')" title="ลบ"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal สร้าง/แก้ไขผู้ทรงคุณวุฒิ -->
<div class="modal fade ref-modal" id="refereeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"><i class="bi bi-person-plus me-2"></i>เพิ่มผู้ทรงคุณวุฒิ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="refereeForm">
                    <input type="hidden" name="id" id="refId" value="0">
                    <div class="mb-3">
                        <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="refName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">อีเมล <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="refEmail" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถาบัน/หน่วยงาน</label>
                        <input type="text" class="form-control" name="institution" id="refInstitution">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ความเชี่ยวชาญ</label>
                        <textarea class="form-control" name="expertise" id="refExpertise" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">โทรศัพท์</label>
                        <input type="text" class="form-control" name="phone" id="refPhone">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">สถานะ</label>
                        <select class="form-select" name="status" id="refStatus">
                            <option value="1">ใช้งาน</option>
                            <option value="0">ปิดใช้งาน</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveReferee()"><i class="bi bi-check-lg me-1"></i>บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title"><i class="bi bi-exclamation-triangle me-1"></i>ยืนยันการลบ</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>ต้องการลบ "<span id="deleteRefName"></span>" หรือไม่?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-danger btn-sm" id="confirmDeleteBtn"><i class="bi bi-trash me-1"></i>ลบ</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const BASE = '<?= $base ?>';

    function resetForm() {
        document.getElementById('refereeForm').reset();
        document.getElementById('refId').value = '0';
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-person-plus me-2"></i>เพิ่มผู้ทรงคุณวุฒิ';
    }

    function editReferee(id) {
        fetch(`${BASE}/evaluate/admin/referees/get/${id}`)
            .then(r => r.json())
            .then(res => {
                if (res.success && res.data) {
                    const d = res.data;
                    document.getElementById('refId').value = d.id;
                    document.getElementById('refName').value = d.name || '';
                    document.getElementById('refEmail').value = d.email || '';
                    document.getElementById('refInstitution').value = d.institution || '';
                    document.getElementById('refExpertise').value = d.expertise || '';
                    document.getElementById('refPhone').value = d.phone || '';
                    document.getElementById('refStatus').value = d.status || '1';
                    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>แก้ไขผู้ทรงคุณวุฒิ';
                    new bootstrap.Modal(document.getElementById('refereeModal')).show();
                } else {
                    alert('ไม่พบข้อมูล');
                }
            })
            .catch(() => alert('เกิดข้อผิดพลาดในการโหลดข้อมูล'));
    }

    function saveReferee() {
        const form = document.getElementById('refereeForm');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const formData = new FormData(form);

        fetch(`${BASE}/evaluate/admin/referees/save`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    bootstrap.Modal.getInstance(document.getElementById('refereeModal'))?.hide();
                    location.reload();
                } else {
                    alert(res.message || 'ไม่สามารถบันทึกได้');
                }
            })
            .catch(() => alert('เกิดข้อผิดพลาด'));
    }

    function toggleStatus(id) {
        const formData = new FormData();
        formData.append('id', id);

        fetch(`${BASE}/evaluate/admin/referees/toggleStatus`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'ไม่สามารถเปลี่ยนสถานะได้');
                }
            })
            .catch(() => alert('เกิดข้อผิดพลาด'));
    }

    let deleteId = 0;

    function deleteReferee(id, name) {
        deleteId = id;
        document.getElementById('deleteRefName').textContent = name;
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
        if (deleteId <= 0) return;
        const formData = new FormData();
        formData.append('id', deleteId);

        fetch(`${BASE}/evaluate/admin/referees/delete`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(res => {
                bootstrap.Modal.getInstance(document.getElementById('deleteModal'))?.hide();
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.message || 'ไม่สามารถลบได้');
                }
            })
            .catch(() => alert('เกิดข้อผิดพลาด'));
    });
</script>
<?= $this->endSection() ?>
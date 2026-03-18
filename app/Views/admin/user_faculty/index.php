<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('title') ?>จัดการคณะผู้ใช้<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
.faculty-badge {
    display: inline-block;
    padding: 0.25rem 0.75rem;
    border-radius: 999px;
    font-size: 0.75rem;
    font-weight: 500;
}
.faculty-science {
    background: #e0f2fe;
    color: #0369a1;
}
.faculty-other {
    background: #f3f4f6;
    color: #6b7280;
}
.faculty-empty {
    background: #fef3c7;
    color: #d97706;
}
.user-card {
    transition: all 0.2s ease;
}
.user-card:hover {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="bi bi-building"></i> จัดการคณะผู้ใช้</h4>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ, อีเมล, nickname..." value="<?= esc($search ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <select name="faculty" class="form-select">
                        <option value="">-- ทุกคณะ --</option>
                        <option value="คณะวิทยาศาสตร์และเทคโนโลยี" <?= ($selectedFaculty ?? '') === 'คณะวิทยาศาสตร์และเทคโนโลยี' ? 'selected' : '' ?>>
                            คณะวิทยาศาสตร์และเทคโนโลยี
                        </option>
                        <?php foreach ($faculties as $f): ?>
                            <?php if ($f && $f !== 'คณะวิทยาศาสตร์และเทคโนโลยี'): ?>
                                <option value="<?= esc($f) ?>" <?= ($selectedFaculty ?? '') === $f ? 'selected' : '' ?>><?= esc($f) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> ค้นหา
                    </button>
                </div>
                <div class="col-md-3 text-end">
                    <button type="button" class="btn btn-success" onclick="openBulkUpdateModal()">
                        <i class="bi bi-check-all"></i> อัปเดตหลายคน
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Users Table -->
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th width="40">
                            <input type="checkbox" class="form-check-input" id="selectAll" onchange="toggleSelectAll()">
                        </th>
                        <th>ชื่อ-นามสกุล</th>
                        <th>อีเมล</th>
                        <th>Nickname</th>
                        <th>คณะ</th>
                        <th width="120">จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <?php
                        $facultyClass = 'faculty-empty';
                        if ($user['faculty'] === 'คณะวิทยาศาสตร์และเทคโนโลยี') {
                            $facultyClass = 'faculty-science';
                        } elseif ($user['faculty']) {
                            $facultyClass = 'faculty-other';
                        }
                        ?>
                        <tr class="user-card">
                            <td>
                                <input type="checkbox" class="form-check-input user-checkbox" value="<?= $user['uid'] ?>" data-name="<?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?>">
                            </td>
                            <td>
                                <strong><?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?></strong><br>
                                <small class="text-muted"><?= esc($user['login_uid'] ?? '') ?></small>
                            </td>
                            <td><?= esc($user['email'] ?? '') ?></td>
                            <td><?= esc($user['nickname'] ?? '-') ?></td>
                            <td>
                                <span class="faculty-badge <?= $facultyClass ?>">
                                    <?= esc($user['faculty'] ?? 'ยังไม่ระบุ') ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-primary" onclick="openEditModal(<?= $user['uid'] ?>, '<?= esc($user['faculty'] ?? '') ?>', '<?= esc(($user['tf_name'] ?? '') . ' ' . ($user['tl_name'] ?? '')) ?>')">
                                    <i class="bi bi-pencil"></i> แก้ไข
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">ไม่พบข้อมูลผู้ใช้</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> แก้ไขคณะ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="editUserName" class="fw-bold"></p>
                <input type="hidden" id="editUserId">
                <div class="mb-3">
                    <label class="form-label">คณะ</label>
                    <select id="editFaculty" class="form-select">
                        <option value="">-- ไม่ระบุ --</option>
                        <option value="คณะวิทยาศาสตร์และเทคโนโลยี">คณะวิทยาศาสตร์และเทคโนโลยี</option>
                        <option value="คณะมนุษยศาสตร์และสังคมศาสตร์">คณะมนุษยศาสตร์และสังคมศาสตร์</option>
                        <option value="คณะบริหารธุรกิจ">คณะบริหารธุรกิจ</option>
                        <option value="คณะเทคโนโลยีอุตสาหกรรม">คณะเทคโนโลยีอุตสาหกรรม</option>
                        <option value="คณะเกษตรศาสตร์">คณะเกษตรศาสตร์</option>
                        <option value="คณะวิทยาการสารสนเทศ">คณะวิทยาการสารสนเทศ</option>
                        <option value="คณะการบัญชีและการเงิน">คณะการบัญชีและการเงิน</option>
                        <option value="คณะศิลปกรรมศาสตร์">คณะศิลปกรรมศาสตร์</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveFaculty()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Update Modal -->
<div class="modal fade" id="bulkModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-all"></i> อัปเดตหลายคน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>เลือก <span id="selectedCount" class="fw-bold">0</span> คน</p>
                <div class="mb-3">
                    <label class="form-label">ตั้งคณะเป็น</label>
                    <select id="bulkFaculty" class="form-select">
                        <option value="">-- ไม่ระบุ --</option>
                        <option value="คณะวิทยาศาสตร์และเทคโนโลยี">คณะวิทยาศาสตร์และเทคโนโลยี</option>
                        <option value="คณะมนุษยศาสตร์และสังคมศาสตร์">คณะมนุษยศาสตร์และสังคมศาสตร์</option>
                        <option value="คณะบริหารธุรกิจ">คณะบริหารธุรกิจ</option>
                        <option value="คณะเทคโนโลยีอุตสาหกรรม">คณะเทคโนโลยีอุตสาหกรรม</option>
                        <option value="คณะเกษตรศาสตร์">คณะเกษตรศาสตร์</option>
                        <option value="คณะวิทยาการสารสนเทศ">คณะวิทยาการสารสนเทศ</option>
                        <option value="คณะการบัญชีและการเงิน">คณะการบัญชีและการเงิน</option>
                        <option value="คณะศิลปกรรมศาสตร์">คณะศิลปกรรมศาสตร์</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" onclick="saveBulkFaculty()">บันทึกทั้งหมด</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
const baseUrl = '<?= base_url() ?>';

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
}

function openEditModal(userId, faculty, userName) {
    document.getElementById('editUserId').value = userId;
    document.getElementById('editUserName').textContent = userName;
    document.getElementById('editFaculty').value = faculty;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openBulkUpdateModal() {
    const selected = document.querySelectorAll('.user-checkbox:checked');
    if (selected.length === 0) {
        alert('กรุณาเลือกผู้ใช้อย่างน้อย 1 คน');
        return;
    }
    document.getElementById('selectedCount').textContent = selected.length;
    new bootstrap.Modal(document.getElementById('bulkModal')).show();
}

async function saveFaculty() {
    const userId = document.getElementById('editUserId').value;
    const faculty = document.getElementById('editFaculty').value;

    try {
        const response = await fetch(`${baseUrl}admin/user-faculty/update-faculty`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&faculty=${encodeURIComponent(faculty)}`
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'ไม่สามารถบันทึกได้');
        }
    } catch (error) {
        alert('เกิดข้อผิดพลาด: ' + error.message);
    }
}

async function saveBulkFaculty() {
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkboxes).map(cb => cb.value);
    const faculty = document.getElementById('bulkFaculty').value;

    try {
        const response = await fetch(`${baseUrl}admin/user-faculty/bulk-update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_ids[]=${userIds.join('&user_ids[]=')}&faculty=${encodeURIComponent(faculty)}`
        });

        const result = await response.json();

        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'ไม่สามารถบันทึกได้');
        }
    } catch (error) {
        alert('เกิดข้อผิดพลาด: ' + error.message);
    }
}
</script>
<?= $this->endSection() ?>

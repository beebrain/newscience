<?= $this->extend('admin/layouts/admin_layout') ?>

<?= $this->section('content') ?>

<div class="card">
    <div class="news-page-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200);">
        <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
            <div>
                <h2>จัดการผู้ใช้</h2>
                <p class="form-hint" style="margin: 0.25rem 0 0 0;">จัดการผู้ใช้งานระบบ (User และ Student)</p>
            </div>
        </div>
    </div>

    <div class="card-body" style="padding: 1.5rem;">
        <!-- Tabs -->
        <div class="tabs" style="margin-bottom: 1.5rem;">
            <button type="button" class="tab-btn active" data-tab="users" onclick="switchTab('users')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                บุคลากร (User)
            </button>
            <button type="button" class="tab-btn" data-tab="students" onclick="switchTab('students')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px; margin-right: 6px;">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                </svg>
                นักศึกษา (Student)
            </button>
        </div>

        <!-- Filters -->
        <div class="filters" style="display: flex; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem; padding: 1rem; background: var(--color-gray-50); border-radius: 8px;">
            <div class="form-group" style="margin: 0; flex: 1; min-width: 150px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">ค้นหา</label>
                <input type="text" id="searchInput" class="form-control" placeholder="ชื่อ, อีเมล, login_uid..." onkeyup="handleSearch()">
            </div>
            <div class="form-group" style="margin: 0; min-width: 140px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">บทบาท</label>
                <select id="roleFilter" class="form-control" onchange="applyFilters()">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0; min-width: 180px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">หลักสูตร/โปรแกรม</label>
                <select id="programFilter" class="form-control" onchange="applyFilters()">
                    <option value="">ทั้งหมด</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0; min-width: 120px;">
                <label class="form-label" style="font-size: 0.75rem; margin-bottom: 0.25rem;">สถานะ</label>
                <select id="statusFilter" class="form-control" onchange="applyFilters()">
                    <option value="">ทั้งหมด</option>
                    <option value="active">ใช้งาน</option>
                    <option value="inactive">ไม่ใช้งาน</option>
                </select>
            </div>
            <div class="form-group" style="margin: 0; display: flex; align-items: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="resetFilters()">
                    รีเซ็ต
                </button>
            </div>
        </div>

        <!-- Bulk Actions -->
        <div class="bulk-actions" style="display: flex; gap: 0.5rem; margin-bottom: 1rem; padding: 0.75rem; background: var(--color-gray-50); border-radius: 6px; display: none;" id="bulkActions">
            <span style="font-size: 0.875rem; color: var(--color-gray-600); margin-right: auto;" id="selectedCount">เลือก 0 รายการ</span>
            <button type="button" class="btn btn-sm btn-success" onclick="bulkActivate()">เปิดใช้งาน</button>
            <button type="button" class="btn btn-sm" style="background: var(--color-gray-200);" onclick="bulkDeactivate()">ปิดใช้งาน</button>
        </div>

        <!-- Users Table -->
        <div id="usersTab" class="tab-content active">
            <div class="news-table-wrap">
                <table class="table" role="table" aria-label="รายการผู้ใช้">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <input type="checkbox" id="selectAllUsers" onchange="toggleSelectAll('users')">
                            </th>
                            <th scope="col" style="width: 60px;">ลำดับ</th>
                            <th scope="col">ชื่อ-นามสกุล</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col" style="width: 100px;">login_uid</th>
                            <th scope="col" style="width: 120px;">บทบาท</th>
                            <th scope="col" style="width: 100px;">หลักสูตร</th>
                            <th scope="col" style="width: 90px;">สถานะ</th>
                            <th scope="col" style="width: 100px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="usersTableBody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
            <div id="usersPagination" class="pagination" style="margin-top: 1rem;"></div>
        </div>

        <!-- Students Table -->
        <div id="studentsTab" class="tab-content" style="display: none;">
            <div class="news-table-wrap">
                <table class="table" role="table" aria-label="รายการนักศึกษา">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 40px;">
                                <input type="checkbox" id="selectAllStudents" onchange="toggleSelectAll('students')">
                            </th>
                            <th scope="col" style="width: 60px;">ลำดับ</th>
                            <th scope="col">ชื่อ-นามสกุล</th>
                            <th scope="col">รหัสนักศึกษา</th>
                            <th scope="col">อีเมล</th>
                            <th scope="col" style="width: 120px;">บทบาท</th>
                            <th scope="col" style="width: 100px;">หลักสูตร</th>
                            <th scope="col" style="width: 90px;">สถานะ</th>
                            <th scope="col" style="width: 100px;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="studentsTableBody">
                        <!-- Dynamic content -->
                    </tbody>
                </table>
            </div>
            <div id="studentsPagination" class="pagination" style="margin-top: 1rem;"></div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem;">แก้ไขผู้ใช้</h3>
            <button type="button" onclick="closeModal('editUserModal')" style="background: none; border: none; cursor: pointer; padding: 0.25rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                    <path d="M18 6L6 18M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editUserForm" onsubmit="saveUser(event)">
            <input type="hidden" id="editUserUid" name="uid">
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">ชื่อ <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" id="editUserGfName" name="gf_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">นามสกุล <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" id="editUserGlName" name="gl_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ชื่อแสดง</label>
                    <input type="text" id="editUserDisplayName" name="display_name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">อีเมล <span style="color: var(--color-danger);">*</span></label>
                    <input type="email" id="editUserEmail" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">login_uid</label>
                    <input type="text" id="editUserLoginUid" name="login_uid" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">บทบาท <span style="color: var(--color-danger);">*</span></label>
                    <select id="editUserRole" name="role" class="form-control" required>
                        <option value="user">User</option>
                        <option value="faculty_admin">Faculty Admin</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">หลักสูตร</label>
                    <select id="editUserProgramId" name="program_id" class="form-control">
                        <option value="">ไม่ระบุ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">สถานะ</label>
                    <select id="editUserStatus" name="status" class="form-control">
                        <option value="active">ใช้งาน</option>
                        <option value="inactive">ไม่ใช้งาน</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn" style="background: var(--color-gray-200);" onclick="closeModal('editUserModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div class="modal-content" style="background: white; border-radius: 12px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--color-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.25rem;">แก้ไขนักศึกษา</h3>
            <button type="button" onclick="closeModal('editStudentModal')" style="background: none; border: none; cursor: pointer; padding: 0.25rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 24px; height: 24px;">
                    <path d="M18 6L6 18M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <form id="editStudentForm" onsubmit="saveStudent(event)">
            <input type="hidden" id="editStudentId" name="id">
            <div class="modal-body" style="padding: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">ชื่อ <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" id="editStudentGfName" name="gf_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">นามสกุล <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" id="editStudentGlName" name="gl_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">ชื่อแสดง</label>
                    <input type="text" id="editStudentDisplayName" name="display_name" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">รหัสนักศึกษา <span style="color: var(--color-danger);">*</span></label>
                    <input type="text" id="editStudentStudentId" name="student_id" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">อีเมล <span style="color: var(--color-danger);">*</span></label>
                    <input type="email" id="editStudentEmail" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">บทบาท <span style="color: var(--color-danger);">*</span></label>
                    <select id="editStudentRole" name="role" class="form-control" required>
                        <option value="student">Student</option>
                        <option value="club">Club</option>
                        <option value="admin_student">Admin Student</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">หลักสูตร</label>
                    <select id="editStudentProgramId" name="program_id" class="form-control">
                        <option value="">ไม่ระบุ</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">สถานะ</label>
                    <select id="editStudentStatus" name="status" class="form-control">
                        <option value="active">ใช้งาน</option>
                        <option value="inactive">ไม่ใช้งาน</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer" style="padding: 1rem 1.5rem; border-top: 1px solid var(--color-gray-200); display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" class="btn" style="background: var(--color-gray-200);" onclick="closeModal('editStudentModal')">ยกเลิก</button>
                <button type="submit" class="btn btn-primary">บันทึก</button>
            </div>
        </form>
    </div>
</div>

<style>
    .tabs {
        display: flex;
        gap: 0.5rem;
        border-bottom: 2px solid var(--color-gray-200);
    }

    .tab-btn {
        padding: 0.75rem 1.25rem;
        border: none;
        background: none;
        cursor: pointer;
        font-size: 0.9rem;
        font-weight: 500;
        color: var(--color-gray-600);
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        display: flex;
        align-items: center;
    }

    .tab-btn.active {
        color: var(--color-primary);
        border-bottom-color: var(--color-primary);
    }

    .tab-btn:hover {
        color: var(--color-primary);
    }

    .form-group {
        margin-bottom: 1rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        font-weight: 500;
    }

    .form-control {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid var(--color-gray-300);
        border-radius: 6px;
        font-size: 0.875rem;
        background: white;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .btn {
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border: none;
        transition: all 0.2s;
    }

    .btn-sm {
        padding: 0.375rem 0.75rem;
        font-size: 0.8rem;
    }

    .btn-primary {
        background: var(--color-primary);
        color: white;
    }

    .btn-primary:hover {
        background: var(--color-primary-dark);
    }

    .btn-secondary {
        background: var(--color-gray-200);
        color: var(--color-gray-700);
    }

    .btn-secondary:hover {
        background: var(--color-gray-300);
    }

    .btn-success {
        background: var(--color-success);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .btn-danger {
        background: var(--color-danger);
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .btn-warning {
        background: var(--color-warning);
        color: #333;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .modal.active {
        display: flex !important;
    }

    .badge-success {
        background: var(--color-success);
        color: white;
    }

    .badge-warning {
        background: var(--color-warning);
        color: #333;
    }

    .badge-danger {
        background: var(--color-danger);
        color: white;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.25rem;
    }

    .pagination button {
        padding: 0.375rem 0.75rem;
        border: 1px solid var(--color-gray-300);
        background: white;
        cursor: pointer;
        border-radius: 4px;
    }

    .pagination button.active {
        background: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }

    .pagination button:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--color-gray-500);
    }

    .empty-state svg {
        width: 64px;
        height: 64px;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .action-btns {
        display: flex;
        gap: 0.25rem;
    }

    .action-btn {
        padding: 0.25rem 0.5rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.75rem;
    }

    .status-toggle {
        cursor: pointer;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.75rem;
        transition: all 0.2s;
    }
</style>

<script>
    const baseUrl = '<?= base_url() ?>';
    let currentTab = 'users';
    let currentPage = {
        users: 1,
        students: 1
    };
    let selectedItems = {
        users: [],
        students: []
    };
    let programs = [];

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadPrograms();
        loadUsers();
        loadStudents();
    });

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(content => content.style.display = 'none');
        document.querySelector(`[data-tab="${tab}"]`).classList.add('active');
        document.getElementById(tab + 'Tab').style.display = 'block';
        updateRoleFilter(tab);
    }

    function updateRoleFilter(tab) {
        const roleFilter = document.getElementById('roleFilter');
        roleFilter.innerHTML = '<option value="">ทั้งหมด</option>';
        if (tab === 'users') {
            roleFilter.innerHTML += `
            <option value="user">User</option>
            <option value="faculty_admin">Faculty Admin</option>
            <option value="super_admin">Super Admin</option>
        `;
        } else {
            roleFilter.innerHTML += `
            <option value="student">Student</option>
            <option value="club">Club</option>
            <option value="admin_student">Admin Student</option>
        `;
        }
    }

    function loadPrograms() {
        fetch(`${baseUrl}api/programs`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    programs = data.data || [];
                    updateProgramSelects();
                }
            })
            .catch(console.error);
    }

    function updateProgramSelects() {
        const options = '<option value="">ไม่ระบุ</option>' +
            programs.map(p => `<option value="${p.id}">${p.name_th || p.name}</option>`).join('');

        ['programFilter', 'editUserProgramId', 'editStudentProgramId'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.innerHTML = id === 'programFilter' ? '<option value="">ทั้งหมด</option>' + options.slice(28) : options;
        });
    }

    function loadUsers(page = 1) {
        currentPage.users = page;
        const params = new URLSearchParams({
            page: page,
            search: document.getElementById('searchInput').value,
            role: document.getElementById('roleFilter').value,
            program_id: document.getElementById('programFilter').value,
            status: document.getElementById('statusFilter').value
        });

        console.log('Loading users with params:', params.toString());

        fetch(`${baseUrl}admin/users/get-users?${params}`)
            .then(r => r.json())
            .then(data => {
                console.log('Users API response:', data);
                if (data.success) {
                    renderUsers(data.data);
                    renderPagination('users', data);
                } else {
                    console.error('API returned error:', data.message);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
            });
    }

    function loadStudents(page = 1) {
        currentPage.students = page;
        const params = new URLSearchParams({
            page: page,
            search: document.getElementById('searchInput').value,
            role: document.getElementById('roleFilter').value,
            program_id: document.getElementById('programFilter').value,
            status: document.getElementById('statusFilter').value
        });

        console.log('Loading students with params:', params.toString());

        fetch(`${baseUrl}admin/users/get-students?${params}`)
            .then(r => r.json())
            .then(data => {
                console.log('Students API response:', data);
                if (data.success) {
                    renderStudents(data.data);
                    renderPagination('students', data);
                } else {
                    console.error('API returned error:', data.message);
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
            });
    }

    function renderUsers(users) {
        const tbody = document.getElementById('usersTableBody');
        if (!users || users.length === 0) {
            tbody.innerHTML = `
            <tr><td colspan="9" class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87"/>
                </svg>
                <p>ไม่พบข้อมูลผู้ใช้</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = users.map((u, i) => `
        <tr>
            <td><input type="checkbox" value="${u.uid}" onchange="toggleSelect('users', '${u.uid}')"></td>
            <td style="font-variant-numeric: tabular-nums;">${i + 1 + (currentPage.users - 1) * 20}</td>
            <td><span style="font-weight: 500;">${u.display_name || u.gf_name + ' ' + u.gl_name}</span></td>
            <td><span style="font-size: 0.875rem;">${u.email}</span></td>
            <td><span style="font-size: 0.875rem; color: var(--color-gray-600);">${u.login_uid || '—'}</span></td>
            <td><span class="badge ${getRoleBadgeClass(u.role)}">${u.role}</span></td>
            <td><span style="font-size: 0.875rem;">${u.program_name || '—'}</span></td>
            <td>
                <span class="status-toggle ${u.status === 'active' ? 'badge-success' : ''}" 
                      onclick="toggleUserStatus('${u.uid}', '${u.status}')">
                    ${u.status === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-primary" onclick="editUser('${u.uid}')">แก้ไข</button>
                </div>
            </td>
        </tr>
    `).join('');
    }

    function renderStudents(students) {
        const tbody = document.getElementById('studentsTableBody');
        if (!students || students.length === 0) {
            tbody.innerHTML = `
            <tr><td colspan="9" class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                    <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                </svg>
                <p>ไม่พบข้อมูลนักศึกษา</p>
            </td></tr>`;
            return;
        }

        tbody.innerHTML = students.map((s, i) => `
        <tr>
            <td><input type="checkbox" value="${s.id}" onchange="toggleSelect('students', '${s.id}')"></td>
            <td style="font-variant-numeric: tabular-nums;">${i + 1 + (currentPage.students - 1) * 20}</td>
            <td><span style="font-weight: 500;">${s.display_name || s.gf_name + ' ' + s.gl_name}</span></td>
            <td><span style="font-size: 0.875rem;">${s.student_id}</span></td>
            <td><span style="font-size: 0.875rem;">${s.email}</span></td>
            <td><span class="badge ${getStudentRoleBadgeClass(s.role)}">${s.role}</span></td>
            <td><span style="font-size: 0.875rem;">${s.program_name || '—'}</span></td>
            <td>
                <span class="status-toggle ${s.status === 'active' ? 'badge-success' : ''}" 
                      onclick="toggleStudentStatus('${s.id}', '${s.status}')">
                    ${s.status === 'active' ? 'ใช้งาน' : 'ไม่ใช้งาน'}
                </span>
            </td>
            <td>
                <div class="action-btns">
                    <button class="action-btn btn-primary" onclick="editStudent('${s.id}')">แก้ไข</button>
                </div>
            </td>
        </tr>
    `).join('');
    }

    function getRoleBadgeClass(role) {
        const classes = {
            'super_admin': 'badge-danger',
            'faculty_admin': 'badge-warning',
            'user': ''
        };
        return classes[role] || '';
    }

    function getStudentRoleBadgeClass(role) {
        const classes = {
            'admin_student': 'badge-warning',
            'club': 'badge-success',
            'student': ''
        };
        return classes[role] || '';
    }

    function renderPagination(type, data) {
        if (!data) return;
        const container = document.getElementById(type + 'Pagination');
        const currentPage = data.page || 1;
        const totalPages = data.total_pages || 1;
        let html = '';

        if (totalPages > 1) {
            // Prev
            html += `<button ${currentPage === 1 ? 'disabled' : ''} onclick="load${type.charAt(0).toUpperCase() + type.slice(1)}(${currentPage - 1})">←</button>`;

            // Pages
            for (let i = 1; i <= totalPages; i++) {
                if (i === 1 || i === totalPages || (i >= currentPage - 2 && i <= currentPage + 2)) {
                    html += `<button class="${i === currentPage ? 'active' : ''}" onclick="load${type.charAt(0).toUpperCase() + type.slice(1)}(${i})">${i}</button>`;
                } else if (i === currentPage - 3 || i === currentPage + 3) {
                    html += `<span>...</span>`;
                }
            }

            // Next
            html += `<button ${currentPage === totalPages ? 'disabled' : ''} onclick="load${type.charAt(0).toUpperCase() + type.slice(1)}(${currentPage + 1})">→</button>`;
        }

        container.innerHTML = html;
    }

    function handleSearch() {
        clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(() => {
            loadUsers(1);
            loadStudents(1);
        }, 300);
    }

    function applyFilters() {
        loadUsers(1);
        loadStudents(1);
    }

    function resetFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('roleFilter').value = '';
        document.getElementById('programFilter').value = '';
        document.getElementById('statusFilter').value = '';
        loadUsers(1);
        loadStudents(1);
    }

    function toggleSelect(type, id) {
        const index = selectedItems[type].indexOf(id);
        if (index > -1) {
            selectedItems[type].splice(index, 1);
        } else {
            selectedItems[type].push(id);
        }
        updateBulkActions();
    }

    function toggleSelectAll(type) {
        const checkbox = document.getElementById('selectAll' + type.charAt(0).toUpperCase() + type.slice(1));
        const checkboxes = document.querySelectorAll(`#${type}Tab tbody input[type="checkbox"]`);

        if (checkbox.checked) {
            checkboxes.forEach(cb => {
                cb.checked = true;
                if (!selectedItems[type].includes(cb.value)) {
                    selectedItems[type].push(cb.value);
                }
            });
        } else {
            checkboxes.forEach(cb => cb.checked = false);
            selectedItems[type] = [];
        }
        updateBulkActions();
    }

    function updateBulkActions() {
        const total = selectedItems.users.length + selectedItems.students.length;
        const bulkActions = document.getElementById('bulkActions');
        const selectedCount = document.getElementById('selectedCount');

        if (total > 0) {
            bulkActions.style.display = 'flex';
            selectedCount.textContent = `เลือก ${total} รายการ`;
        } else {
            bulkActions.style.display = 'none';
        }
    }

    function openModal(id) {
        document.getElementById(id).classList.add('active');
    }

    function closeModal(id) {
        document.getElementById(id).classList.remove('active');
    }

    function editUser(uid) {
        fetch(`${baseUrl}admin/users/get-user-data/${uid}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const u = data.data;
                    document.getElementById('editUserUid').value = u.uid;
                    document.getElementById('editUserGfName').value = u.gf_name || '';
                    document.getElementById('editUserGlName').value = u.gl_name || '';
                    document.getElementById('editUserDisplayName').value = u.display_name || '';
                    document.getElementById('editUserEmail').value = u.email || '';
                    document.getElementById('editUserLoginUid').value = u.login_uid || '';
                    document.getElementById('editUserRole').value = u.role || 'user';
                    document.getElementById('editUserProgramId').value = u.program_id || '';
                    document.getElementById('editUserStatus').value = u.status || 'active';
                    openModal('editUserModal');
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            });
    }

    function editStudent(id) {
        fetch(`${baseUrl}admin/users/get-student-data/${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const s = data.data;
                    document.getElementById('editStudentId').value = s.id;
                    document.getElementById('editStudentGfName').value = s.gf_name || '';
                    document.getElementById('editStudentGlName').value = s.gl_name || '';
                    document.getElementById('editStudentDisplayName').value = s.display_name || '';
                    document.getElementById('editStudentStudentId').value = s.student_id || '';
                    document.getElementById('editStudentEmail').value = s.email || '';
                    document.getElementById('editStudentRole').value = s.role || 'student';
                    document.getElementById('editStudentProgramId').value = s.program_id || '';
                    document.getElementById('editStudentStatus').value = s.status || 'active';
                    openModal('editStudentModal');
                } else {
                    alert(data.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการโหลดข้อมูล');
            });
    }

    function saveUser(e) {
        e.preventDefault();
        const uid = document.getElementById('editUserUid').value;
        const formData = new FormData(document.getElementById('editUserForm'));
        const data = Object.fromEntries(formData);

        fetch(`${baseUrl}admin/users/ajax-update-user/${uid}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    closeModal('editUserModal');
                    loadUsers(currentPage.users);
                    alert('บันทึกสำเร็จ');
                } else {
                    alert(result.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการบันทึก');
            });
    }

    function saveStudent(e) {
        e.preventDefault();
        const id = document.getElementById('editStudentId').value;
        const formData = new FormData(document.getElementById('editStudentForm'));
        const data = Object.fromEntries(formData);

        fetch(`${baseUrl}admin/users/ajax-update-student/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    closeModal('editStudentModal');
                    loadStudents(currentPage.students);
                    alert('บันทึกสำเร็จ');
                } else {
                    alert(result.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(err => {
                console.error(err);
                alert('เกิดข้อผิดพลาดในการบันทึก');
            });
    }

    function toggleUserStatus(uid, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        if (!confirm(`ต้องการ${newStatus === 'active' ? 'เปิด' : 'ปิด'}ใช้งานผู้ใช้นี้?`)) return;

        fetch(`${baseUrl}admin/users/toggle-user-status/${uid}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    loadUsers(currentPage.users);
                } else {
                    alert(result.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(console.error);
    }

    function toggleStudentStatus(id, currentStatus) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        if (!confirm(`ต้องการ${newStatus === 'active' ? 'เปิด' : 'ปิด'}ใช้งานนักศึกษานี้?`)) return;

        fetch(`${baseUrl}admin/users/toggle-student-status/${id}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    loadStudents(currentPage.students);
                } else {
                    alert(result.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(console.error);
    }

    function bulkActivate() {
        bulkUpdate('activate');
    }

    function bulkDeactivate() {
        bulkUpdate('deactivate');
    }

    function bulkUpdate(action) {
        const data = {
            action: action,
            users: selectedItems.users,
            students: selectedItems.students
        };

        if (data.users.length === 0 && data.students.length === 0) {
            alert('กรุณาเลือกรายการก่อน');
            return;
        }

        fetch(`${baseUrl}admin/users/bulk-update`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            })
            .then(r => r.json())
            .then(result => {
                if (result.success) {
                    selectedItems = {
                        users: [],
                        students: []
                    };
                    document.getElementById('selectAllUsers').checked = false;
                    document.getElementById('selectAllStudents').checked = false;
                    updateBulkActions();
                    loadUsers(currentPage.users);
                    loadStudents(currentPage.students);
                    alert(`อัปเดตสำเร็จ ${result.updated || 0} รายการ`);
                } else {
                    alert(result.message || 'เกิดข้อผิดพลาด');
                }
            })
            .catch(console.error);
    }

    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', e => {
            if (e.target === modal) closeModal(modal.id);
        });
    });
</script>

<?= $this->endSection() ?>
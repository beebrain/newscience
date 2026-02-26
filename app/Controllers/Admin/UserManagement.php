<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\StudentUserModel;
use App\Models\ProgramModel;

/**
 * การจัดการผู้ใช้ — เฉพาะ Super_admin และ Faculty_admin เท่านั้นที่เห็นและเข้าได้
 * แสดงรายการผู้ใช้จากตาราง user และ student_user
 */
class UserManagement extends BaseController
{
    /** @var string */
    private const ROLE_SUPER_ADMIN = 'super_admin';
    private const ROLE_FACULTY_ADMIN = 'faculty_admin';
    private const ROLE_ADMIN = 'admin';
    private const ROLE_EDITOR = 'editor';

    /** @var array รายการ role ที่อนุญาตให้เข้าถึง */
    private const ALLOWED_ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_FACULTY_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_EDITOR
    ];

    protected $userModel;
    protected $studentUserModel;
    protected $programModel;
    protected $currentUser;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->studentUserModel = new StudentUserModel();
        $this->programModel = new ProgramModel();

        $session = session();
        $uid = $session->get('admin_id');
        $role = $session->get('admin_role');
        $programId = $session->get('admin_program_id');

        // ถ้าไม่มี program_id ใน session ให้ดึงจาก database
        if (!$programId && $uid) {
            $user = $this->userModel->find($uid);
            $programId = $user['program_id'] ?? null;
        }

        $this->currentUser = [
            'uid' => $uid,
            'role' => $role,
            'program_id' => $programId
        ];

        log_message('debug', 'UserManagement currentUser: ' . json_encode($this->currentUser));

        // Check if user is logged in and has proper role
        if (!$this->currentUser['uid'] || !in_array($this->currentUser['role'], self::ALLOWED_ROLES, true)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    /**
     * รายการผู้ใช้ทั้งหมด (Super_admin ดูทั้งหมด, Faculty_admin ดูเฉพาะ program ของตน)
     */
    public function index()
    {
        $data = [
            'page_title' => 'จัดการผู้ใช้',
            'current_user' => $this->currentUser,
            'programs' => $this->getAvailablePrograms()
        ];

        return view('admin/users/index_ajax', $data);
    }

    /**
     * AJAX: Get users data with filters
     */
    public function getUsers()
    {
        $role = $this->request->getGet('role') ?: null;
        $programId = $this->request->getGet('program_id');
        $programId = $programId !== '' && $programId !== null ? (int)$programId : null;
        $status = $this->request->getGet('status') ?: null;
        $search = $this->request->getGet('search') ?: null;
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Apply permission filters - ถ้าไม่ใช่ super_admin ต้องดูเฉพาะ program ตัวเอง
        if ($this->currentUser['role'] !== self::ROLE_SUPER_ADMIN) {
            $programId = $this->currentUser['program_id'] ?? null;
        }

        $users = $this->userModel->getUsersWithFilters($role, $programId, $status, $limit, $offset);
        $totalCount = $this->userModel->countUsersWithFilters($role, $programId, $status);

        // Apply search filter if provided
        if ($search) {
            $users = $this->userModel->searchUsers($search, $programId);
            $totalCount = count($users);
        }

        $formattedUsers = [];
        foreach ($users as $user) {
            $userProgramId = $user['program_id'] ?? null;
            $program = $userProgramId ? $this->programModel->find($userProgramId) : null;
            $isActive = (int) ($user['active'] ?? 0) === 1;
            $formattedUsers[] = [
                'uid' => (int)($user['uid'] ?? 0),
                'login_uid' => $user['login_uid'] ?? '',
                'email' => $user['email'] ?? '',
                'gf_name' => $user['gf_name'] ?? '',
                'gl_name' => $user['gl_name'] ?? '',
                'display_name' => $this->userModel->getFullNameThaiForDisplay($user),
                'role' => $user['role'] ?? 'user',
                'program_id' => (int)($user['program_id'] ?? 0),
                'program_name' => $program['name_th'] ?? $program['name'] ?? null,
                'status' => $isActive ? 'active' : 'inactive',
                'created_at' => $user['created_at'] ?? null,
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $formattedUsers,
            'total' => $totalCount,
            'page' => $page,
            'total_pages' => ceil($totalCount / $limit)
        ]);
    }

    /**
     * AJAX: Get students data with filters
     */
    public function getStudents()
    {
        $role = $this->request->getGet('role') ?: null;
        $programId = $this->request->getGet('program_id');
        $programId = $programId !== '' && $programId !== null ? (int)$programId : null;
        $status = $this->request->getGet('status') ?: null;
        $search = $this->request->getGet('search') ?: null;
        $page = (int)($this->request->getGet('page') ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Apply permission filters - ถ้าไม่ใช่ super_admin ต้องดูเฉพาะ program ตัวเอง
        if ($this->currentUser['role'] !== self::ROLE_SUPER_ADMIN) {
            $programId = $this->currentUser['program_id'] ?? null;
        }

        $students = $this->studentUserModel->getStudentsWithFilters($role, $programId, $status, $limit, $offset);
        $totalCount = $this->studentUserModel->countStudentsWithFilters($role, $programId, $status);

        // Apply search filter if provided
        if ($search) {
            $students = $this->studentUserModel->searchStudents($search, $programId);
            $totalCount = count($students);
        }

        $formattedStudents = [];
        foreach ($students as $student) {
            $studentProgramId = $student['program_id'] ?? null;
            $program = $studentProgramId ? $this->programModel->find($studentProgramId) : null;
            $isActive = (int) ($student['active'] ?? 0) === 1;
            $formattedStudents[] = [
                'id' => (int)($student['id'] ?? 0),
                'login_uid' => $student['login_uid'] ?? '',
                'student_id' => $student['student_id'] ?? '',
                'email' => $student['email'] ?? '',
                'gf_name' => $student['gf_name'] ?? '',
                'gl_name' => $student['gl_name'] ?? '',
                'display_name' => $this->studentUserModel->getFullName($student),
                'role' => $student['role'] ?? 'student',
                'program_id' => (int)($student['program_id'] ?? 0),
                'program_name' => $program['name_th'] ?? $program['name'] ?? null,
                'status' => $isActive ? 'active' : 'inactive',
                'created_at' => $student['created_at'] ?? null,
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $formattedStudents,
            'total' => $totalCount,
            'page' => $page,
            'total_pages' => ceil($totalCount / $limit)
        ]);
    }

    /**
     * AJAX: Get user data for edit modal
     */
    public function getUserData($uid)
    {
        $user = $this->userModel->find((int)$uid);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }

        // Check permission
        if (!$this->canManageUser($user)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการผู้ใช้นี้']);
        }

        $isActive = (int) ($user['active'] ?? 0) === 1;
        $userData = [
            'uid' => (int)$user['uid'],
            'login_uid' => $user['login_uid'] ?? '',
            'email' => $user['email'] ?? '',
            'title' => $user['title'] ?? '',
            'gf_name' => $user['gf_name'] ?? '',
            'gl_name' => $user['gl_name'] ?? '',
            'th_name' => $user['th_name'] ?? '',
            'thai_name' => $user['thai_name'] ?? '',
            'thai_lastname' => $user['thai_lastname'] ?? '',
            'role' => $user['role'] ?? 'user',
            'program_id' => (int)($user['program_id'] ?? 0),
            'status' => $isActive ? 'active' : 'inactive',
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $userData,
            'programs' => $this->getAvailablePrograms()
        ]);
    }

    /**
     * AJAX: Get student data for edit modal
     */
    public function getStudentData($id)
    {
        $student = $this->studentUserModel->find((int)$id);
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลนักศึกษา']);
        }

        // Check permission
        if (!$this->canManageStudent($student)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการนักศึกษานี้']);
        }

        $isActive = (int) ($student['active'] ?? 0) === 1;
        $studentData = [
            'id' => (int)$student['id'],
            'login_uid' => $student['login_uid'] ?? '',
            'email' => $student['email'] ?? '',
            'title' => $student['title'] ?? '',
            'gf_name' => $student['gf_name'] ?? '',
            'gl_name' => $student['gl_name'] ?? '',
            'th_name' => $student['th_name'] ?? '',
            'thai_lastname' => $student['thai_lastname'] ?? '',
            'role' => $student['role'] ?? 'student',
            'program_id' => (int)($student['program_id'] ?? 0),
            'status' => $isActive ? 'active' : 'inactive',
        ];

        return $this->response->setJSON([
            'success' => true,
            'data' => $studentData,
            'programs' => $this->getAvailablePrograms()
        ]);
    }

    /**
     * AJAX: Update user
     */
    public function ajaxUpdateUser($uid)
    {
        $user = $this->userModel->find((int)$uid);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }

        // Check permission
        if (!$this->canManageUser($user)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการผู้ใช้นี้']);
        }

        $requestedRole = (string) ($this->request->getPost('role') ?? '');

        $data = [
            'login_uid' => $this->request->getPost('login_uid'),
            'email' => $this->request->getPost('email'),
            'title' => $this->request->getPost('title'),
            'gf_name' => $this->request->getPost('gf_name'),
            'gl_name' => $this->request->getPost('gl_name'),
            'th_name' => $this->request->getPost('th_name'),
            'thai_name' => $this->request->getPost('thai_name'),
            'thai_lastname' => $this->request->getPost('thai_lastname'),
            'role' => $requestedRole,
            'program_id' => $this->request->getPost('program_id') ? (int)$this->request->getPost('program_id') : null,
            'status' => $this->request->getPost('status'),
        ];

        if ($requestedRole === '') {
            log_message('warning', 'UserManagement::ajaxUpdateUser missing role input', [
                'target_uid' => $uid,
                'requested_by' => $this->currentUser['uid'] ?? null,
                'post' => json_encode($this->request->getPost(null, FILTER_SANITIZE_SPECIAL_CHARS))
            ]);
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเลือกสิทธิ์ผู้ใช้']);
        }

        // Validate role assignment
        if (!$this->canAssignRole($data['role'], $data['program_id'])) {
            log_message('warning', 'UserManagement::ajaxUpdateUser role assignment denied', [
                'target_uid' => $uid,
                'requested_role' => $data['role'],
                'program_id' => $data['program_id'],
                'requested_by' => $this->currentUser['uid'] ?? null,
                'requester_role' => $this->currentUser['role'] ?? null,
            ]);
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถกำหนดสิทธิ์นี้ได้']);
        }

        if ($this->userModel->update($uid, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'อัปเดตข้อมูลผู้ใช้สำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'อัปเดตข้อมูลผู้ใช้ไม่สำเร็จ']);
    }

    /**
     * AJAX: Update student
     */
    public function ajaxUpdateStudent($id)
    {
        $student = $this->studentUserModel->find((int)$id);
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลนักศึกษา']);
        }

        // Check permission
        if (!$this->canManageStudent($student)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการนักศึกษานี้']);
        }

        $data = [
            'login_uid' => $this->request->getPost('login_uid'),
            'email' => $this->request->getPost('email'),
            'title' => $this->request->getPost('title'),
            'gf_name' => $this->request->getPost('gf_name'),
            'gl_name' => $this->request->getPost('gl_name'),
            'th_name' => $this->request->getPost('th_name'),
            'thai_lastname' => $this->request->getPost('thai_lastname'),
            'role' => $this->request->getPost('role'),
            'program_id' => $this->request->getPost('program_id') ? (int)$this->request->getPost('program_id') : null,
            'status' => $this->request->getPost('status'),
        ];

        // Validate role assignment
        if (!$this->canAssignStudentRole($data['role'] ?? '', $data['program_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถกำหนดสิทธิ์นี้ได้']);
        }

        if ($this->studentUserModel->update($id, $data)) {
            return $this->response->setJSON(['success' => true, 'message' => 'อัปเดตข้อมูลนักศึกษาสำเร็จ']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'อัปเดตข้อมูลนักศึกษาไม่สำเร็จ']);
    }

    /**
     * AJAX: Toggle user status
     */
    public function toggleUserStatus($uid)
    {
        $user = $this->userModel->find((int)$uid);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }

        // Check permission
        if (!$this->canManageUser($user)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการผู้ใช้นี้']);
        }

        if ($this->userModel->toggleStatus($uid)) {
            $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';
            return $this->response->setJSON(['success' => true, 'message' => "เปลี่ยนสถานะเป็น {$newStatus} สำเร็จ"]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เปลี่ยนสถานะไม่สำเร็จ']);
    }

    /**
     * AJAX: Toggle student status
     */
    public function toggleStudentStatus($id)
    {
        $student = $this->studentUserModel->find((int)$id);
        if (!$student) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลนักศึกษา']);
        }

        // Check permission
        if (!$this->canManageStudent($student)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการนักศึกษานี้']);
        }

        if ($this->studentUserModel->toggleStatus($id)) {
            $newStatus = $student['status'] === 'active' ? 'inactive' : 'active';
            return $this->response->setJSON(['success' => true, 'message' => "เปลี่ยนสถานะเป็น {$newStatus} สำเร็จ"]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'เปลี่ยนสถานะไม่สำเร็จ']);
    }

    /**
     * AJAX: Bulk operations
     */
    public function bulkUpdate()
    {
        $action = $this->request->getPost('action');
        $type = $this->request->getPost('type'); // 'user' or 'student'
        $ids = $this->request->getPost('ids') ?? [];

        if (empty($ids) || !in_array($type, ['user', 'student'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }

        switch ($action) {
            case 'activate':
                $status = 'active';
                $message = 'เปิดใช้งานสำเร็จ';
                break;
            case 'deactivate':
                $status = 'inactive';
                $message = 'ปิดใช้งานสำเร็จ';
                break;
            case 'role_user':
                $role = 'user';
                $message = 'เปลี่ยนสิทธิ์เป็น User สำเร็จ';
                break;
            case 'role_faculty_admin':
                $role = 'faculty_admin';
                $message = 'เปลี่ยนสิทธิ์เป็น Faculty Admin สำเร็จ';
                break;
            case 'role_student':
                $role = 'student';
                $message = 'เปลี่ยนสิทธิ์เป็น Student สำเร็จ';
                break;
            case 'role_admin_student':
                $role = 'admin_student';
                $message = 'เปลี่ยนสิทธิ์เป็น Admin Student สำเร็จ';
                break;
            default:
                return $this->response->setJSON(['success' => false, 'message' => 'Action ไม่ถูกต้อง']);
        }

        if ($type === 'user') {
            if (isset($status)) {
                $result = $this->userModel->bulkUpdateStatus($ids, $status);
            } else {
                $result = $this->userModel->bulkUpdateRoles($ids, $role);
            }
        } else {
            if (isset($status)) {
                $result = $this->studentUserModel->bulkUpdateStatus($ids, $status);
            } else {
                $result = $this->studentUserModel->bulkUpdateRoles($ids, $role);
            }
        }

        if ($result) {
            return $this->response->setJSON(['success' => true, 'message' => $message]);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'ดำเนินการไม่สำเร็จ']);
    }

    /**
     * Get available programs based on user role
     */
    private function getAvailablePrograms(): array
    {
        // Super admin can see all programs
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return $this->programModel->findAll();
        }

        // Admin, Editor, Faculty admin can only see their program
        if ($this->currentUser['program_id'] ?? null) {
            $program = $this->programModel->find($this->currentUser['program_id'] ?? null);
            return $program ? [$program] : [];
        }

        return [];
    }

    /**
     * Check if current user can manage the target user
     */
    private function canManageUser(array $user): bool
    {
        // Super admin can manage all users (including other super admins)
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        // Admin, Editor, Faculty admin can manage users in their program
        return $this->userModel->canManageProgram($this->currentUser, (int)($user['program_id'] ?? 0));
    }

    /**
     * Check if current user can manage the target student
     */
    private function canManageStudent(array $student): bool
    {
        // Super admin can manage all students
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        // Admin, Editor, Faculty admin can manage students in their program
        return $this->studentUserModel->canManageProgram($this->currentUser, (int)($student['program_id'] ?? 0));
    }

    /**
     * Check if current user can assign the specified role
     */
    private function canAssignRole(string $role, ?int $programId): bool
    {
        $role = trim($role);
        if ($role === '') {
            return false;
        }

        // Super admin can assign any role
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        // Admin, Editor, Faculty admin can only assign faculty_admin to their own program
        if ($role === self::ROLE_FACULTY_ADMIN) {
            return $programId == ($this->currentUser['program_id'] ?? null);
        }

        // Admin, Editor, Faculty admin can assign user role to their program
        if ($role === 'user') {
            return !$programId || $programId == ($this->currentUser['program_id'] ?? null);
        }

        return false;
    }

    /**
     * Check if current user can assign the specified student role
     */
    private function canAssignStudentRole(string $role, ?int $programId): bool
    {
        // Super admin can assign any role
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        // Admin, Editor, Faculty admin can assign admin_student only to their own program
        if ($role === 'admin_student') {
            return $programId == ($this->currentUser['program_id'] ?? null);
        }

        // Admin, Editor, Faculty admin can assign student/club role to their program
        if (in_array($role, ['student', 'club'])) {
            return !$programId || $programId == ($this->currentUser['program_id'] ?? null);
        }

        return false;
    }

    /**
     * AJAX: Get user system access permissions
     */
    public function getUserSystemAccess($uid)
    {
        $user = $this->userModel->find((int)$uid);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }

        // Check permission
        if (!$this->canManageUser($user)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการผู้ใช้นี้']);
        }

        // Get all systems
        $systemModel = new \App\Models\SystemModel();
        $systems = $systemModel->getAllActive();

        // Get user's current access
        $accessModel = new \App\Models\UserSystemAccessModel();
        $userAccess = $accessModel->getUserAccessMap((int)$uid);

        return $this->response->setJSON([
            'success' => true,
            'systems' => $systems,
            'user_access' => $userAccess,
            'is_super_admin' => ($user['role'] === 'super_admin')
        ]);
    }

    /**
     * AJAX: Update user system access permissions
     */
    public function updateUserSystemAccess($uid)
    {
        $user = $this->userModel->find((int)$uid);
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูลผู้ใช้']);
        }

        // Check permission
        if (!$this->canManageUser($user)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการผู้ใช้นี้']);
        }

        // Cannot modify super admin access (they have everything)
        if ($user['role'] === 'super_admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่สามารถแก้ไขสิทธิ์ Super Admin ได้']);
        }

        $accessData = $this->request->getPost('access');
        if (!is_array($accessData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ข้อมูลไม่ถูกต้อง']);
        }

        $grantedBy = $this->currentUser['uid'];
        $results = [];

        foreach ($accessData as $systemSlug => $level) {
            if ($level === null || $level === 'none' || $level === '') {
                // Revoke access
                $results[$systemSlug] = \App\Libraries\AccessControl::revokeAccess((int)$uid, $systemSlug);
            } else {
                // Grant/update access
                $results[$systemSlug] = \App\Libraries\AccessControl::grantAccess((int)$uid, $systemSlug, $level, $grantedBy);
            }
        }

        $allSuccess = !in_array(false, $results, true);

        return $this->response->setJSON([
            'success' => $allSuccess,
            'message' => $allSuccess ? 'อัปเดตสิทธิ์การเข้าถึงสำเร็จ' : 'บางรายการอัปเดตไม่สำเร็จ',
            'results' => $results
        ]);
    }
}

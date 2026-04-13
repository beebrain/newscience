<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\StudentUserModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

/**
 * ให้อาจารย์/ผู้ดูแลระบบในหลักสูตรกำหนดนักศึกษาตัวแทนสโมสร (role=club)
 */
class ClubRepresentatives extends BaseController
{
    private const ROLE_SUPER_ADMIN = 'super_admin';
    private const ROLE_FACULTY_ADMIN = 'faculty_admin';
    private const ROLE_ADMIN = 'admin';
    private const ROLE_EDITOR = 'editor';

    private const ALLOWED_ROLES = [
        self::ROLE_SUPER_ADMIN,
        self::ROLE_FACULTY_ADMIN,
        self::ROLE_ADMIN,
        self::ROLE_EDITOR,
    ];

    protected StudentUserModel $studentUserModel;
    protected ProgramModel $programModel;

    /** @var array{uid: int|null, role: string|null, program_id: int|null} */
    protected array $currentUser;

    public function __construct()
    {
        $this->studentUserModel = new StudentUserModel();
        $this->programModel     = new ProgramModel();

        $session   = session();
        $uid       = $session->get('admin_id');
        $role      = $session->get('admin_role');
        $programId = $session->get('admin_program_id');
        if (! $programId && $uid) {
            $userModel = new UserModel();
            $user      = $userModel->find((int) $uid);
            $programId = $user['program_id'] ?? null;
        }
        $this->currentUser = [
            'uid'        => $uid ? (int) $uid : null,
            'role'       => is_string($role) ? $role : null,
            'program_id' => $programId !== null && $programId !== '' ? (int) $programId : null,
        ];

        if (! $this->currentUser['uid'] || ! in_array($this->currentUser['role'], self::ALLOWED_ROLES, true)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $programs = [];
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            $programs = $this->programModel->orderBy('name_th', 'ASC')->orderBy('name_en', 'ASC')->findAll();
        }

        $filterProgramId = null;
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            $q = $this->request->getGet('program_id');
            if ($q !== null && $q !== '') {
                $filterProgramId = (int) $q;
            }
        } else {
            $filterProgramId = $this->currentUser['program_id'] ?: null;
        }

        $students = [];
        if ($filterProgramId !== null && $filterProgramId > 0) {
            $students = $this->studentUserModel
                ->where('program_id', $filterProgramId)
                ->orderBy('email', 'ASC')
                ->findAll();
        }

        return view('admin/club_representatives/index', [
            'page_title'        => 'ตัวแทนนักศึกษาสโมสร',
            'current_user'      => $this->currentUser,
            'programs'          => $programs,
            'filter_program_id' => $filterProgramId,
            'students'          => $students,
        ]);
    }

    public function setClubRole()
    {
        if (! $this->request->is('post')) {
            return redirect()->back()->with('error', 'คำขอไม่ถูกต้อง');
        }

        $studentId = (int) $this->request->getPost('student_user_id');
        $role      = trim((string) $this->request->getPost('role'));
        if (! in_array($role, ['student', 'club'], true)) {
            return redirect()->back()->with('error', 'บทบาทไม่ถูกต้อง');
        }

        $student = $this->studentUserModel->find($studentId);
        if (! $student) {
            return redirect()->back()->with('error', 'ไม่พบนักศึกษา');
        }

        if (! $this->studentUserModel->canManageProgram($this->currentUser, (int) ($student['program_id'] ?? 0))) {
            return redirect()->back()->with('error', 'ไม่มีสิทธิ์จัดการนักศึกษานี้');
        }

        if (! $this->canAssignStudentRole($role, $student['program_id'] ?? null)) {
            return redirect()->back()->with('error', 'ไม่สามารถกำหนดสิทธิ์นี้ได้');
        }

        if (($student['status'] ?? '') === 'pending') {
            return redirect()->back()->with('error', 'นักศึกษาสถานะ pending — รอเข้า Portal ก่อนจึงค่อยตั้งตัวแทนสโมสร');
        }

        if ($this->studentUserModel->update($studentId, ['role' => $role])) {
            return redirect()->back()->with('success', 'อัปเดตบทบาทนักศึกษาแล้ว');
        }

        return redirect()->back()->with('error', 'อัปเดตไม่สำเร็จ');
    }

    private function canAssignStudentRole(string $role, $programId): bool
    {
        if ($this->currentUser['role'] === self::ROLE_SUPER_ADMIN) {
            return true;
        }
        $pid = $programId !== null && $programId !== '' ? (int) $programId : 0;

        return in_array($role, ['student', 'club'], true)
            && ($pid === 0 || $pid === (int) ($this->currentUser['program_id'] ?? 0));
    }
}

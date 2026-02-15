<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentUserModel;

/**
 * Local-only: ข้าม Authen สำหรับทดสอบในเครื่อง (development เท่านั้น)
 * ใช้ได้เฉพาะเมื่อ ENVIRONMENT === 'development'
 * ใน production จะตอบ 404
 */
class Dev extends BaseController
{
    private function isLocal(): bool
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    }

    /**
     * เข้าเป็น Admin (user คนแรกในตาราง user)
     * GET /dev/login-as-admin
     */
    public function loginAsAdmin()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = new UserModel();
        $user = $userModel->first();
        if (!$user) {
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่พบ user ในระบบ (สร้าง user ก่อน)');
        }

        $role = $user['role'] ?? 'user';
        $allowedRoles = ['admin', 'editor', 'super_admin', 'faculty_admin'];
        $adminRole = in_array($role, $allowedRoles, true) ? $role : 'admin';

        session()->set([
            'admin_logged_in' => true,
            'admin_id' => $user['uid'],
            'admin_email' => $user['email'],
            'admin_name' => $userModel->getFullName($user),
            'admin_role' => $adminRole,
        ]);

        return redirect()->to(base_url('dashboard'))->with('success', '[Local] เข้าสู่ระบบเป็น Admin แล้ว');
    }

    /**
     * เข้าเป็น Student (student_user คนแรก)
     * GET /dev/login-as-student
     */
    public function loginAsStudent()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $student = $studentModel->first();
        if (!$student) {
            return redirect()->to(base_url('student/login'))
                ->with('error', 'ไม่พบ student_user ในระบบ (รัน migration และสร้างนักศึกษาทดสอบก่อน)');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id' => $student['id'],
            'student_email' => $student['email'],
            'student_name' => $studentModel->getFullName($student),
            'student_role' => $student['role'] ?? 'student',
        ]);

        return redirect()->to(base_url('student'))->with('success', '[Local] เข้าสู่ระบบเป็น Student แล้ว');
    }

    /**
     * เข้าเป็น Student Admin (นักศึกษาสโมสร role=club) แล้วไปหน้า Student Admin
     * GET /dev/login-as-student-admin
     */
    public function loginAsStudentAdmin()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $club = $studentModel->where('role', 'club')->where('status', 'active')->first();
        if (!$club) {
            return redirect()->to(base_url('student/login'))
                ->with('error', 'ไม่พบนักศึกษาสโมสร (role=club) ในระบบ — รัน php scripts/seed_student_users.php ก่อน');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id' => $club['id'],
            'student_email' => $club['email'],
            'student_name' => $studentModel->getFullName($club),
            'student_role' => 'club',
        ]);

        return redirect()->to(base_url('student-admin/barcode-events'))
            ->with('success', '[Local] เข้าสู่ระบบเป็น Student Admin แล้ว');
    }
}

<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Student Admin: อนุญาตเมื่อเป็น admin ระบบ (user) หรือ student_user role=club/admin_student
 * ใช้กับ route กลุ่ม /student-admin
 */
class StudentAdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1) Admin ระบบ (login ผ่าน user)
        if ($session->get('admin_logged_in')) {
            $role = $session->get('admin_role');
            $allowedAdminRoles = ['admin', 'editor', 'super_admin', 'faculty_admin'];
            if (in_array($role, $allowedAdminRoles, true)) {
                // Auto-login/associate student profile if not already logged in as a student
                if (!$session->get('student_logged_in')) {
                    $adminId = $session->get('admin_id');
                    $adminEmail = $session->get('admin_email');
                    if ($adminId && $adminEmail) {
                        $studentModel = new \App\Models\StudentUserModel();
                        $emailNorm = strtolower(trim($adminEmail));
                        $student = $studentModel->findByEmail($emailNorm);

                        if (!$student) {
                            $userModel = new \App\Models\UserModel();
                            $adminUser = $userModel->find($adminId);
                            if ($adminUser) {
                                $studentData = [
                                    'email'     => $emailNorm,
                                    'login_uid' => $adminUser['login_uid'] ?? '',
                                    'title'     => $adminUser['title'] ?? '',
                                    'tf_name'   => $adminUser['tf_name'] ?? '',
                                    'tl_name'   => $adminUser['tl_name'] ?? '',
                                    'gf_name'   => $adminUser['gf_name'] ?? '',
                                    'gl_name'   => $adminUser['gl_name'] ?? '',
                                    'role'      => 'student',
                                    'status'    => 'active',
                                ];
                                $newId = $studentModel->insert($studentData);
                                if ($newId) {
                                    $student = $studentModel->find($newId);
                                }
                            }
                        }

                        if ($student) {
                            $session->set([
                                'student_logged_in' => true,
                                'student_id'        => $student['id'],
                                'student_email'     => $student['email'],
                                'student_name'      => $studentModel->getFullName($student),
                                'student_role'      => $student['role'] ?? 'student',
                            ]);
                        }
                    }
                }
                return; // allow
            }
        }

        // 2) นักศึกษาที่มีสิทธิ์จัดการฝั่ง student-admin
        if ($session->get('student_logged_in') && in_array($session->get('student_role'), ['club', 'admin_student'], true)) {
            return; // allow
        }

        // Not allowed: redirect to appropriate login
        $session->set('student_admin_redirect_url', current_url());
        if ($session->get('admin_logged_in')) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'หน้านี้สำหรับผู้ดูแลระบบหรือนักศึกษาสโมสรเท่านั้น');
        }
        return redirect()->to(base_url('student/login'))
            ->with('error', 'กรุณาเข้าสู่ระบบ (นักศึกษาสโมสร/Student Admin) หรือใช้บัญชีผู้ดูแลระบบ');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

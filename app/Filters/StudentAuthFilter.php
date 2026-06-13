<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ตรวจว่าเข้าสู่ระบบ Student Portal แล้ว (student_logged_in)
 * ใช้กับ route ภายใน /student ยกเว้น login
 */
class StudentAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if ($session->get('admin_logged_in') && !$session->get('student_logged_in')) {
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

        if (!$session->get('student_logged_in') && !$session->get('admin_logged_in')) {
            $session->set('student_redirect_url', current_url());
            return redirect()->to(base_url('student/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบ');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

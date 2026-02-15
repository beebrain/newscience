<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Student Admin: อนุญาตเมื่อเป็น admin ระบบ (user) หรือ นักศึกษาสโมสร (student_user role=club)
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
                return; // allow
            }
        }

        // 2) นักศึกษาสโมสร (login ผ่าน student_user, role=club)
        if ($session->get('student_logged_in') && $session->get('student_role') === 'club') {
            return; // allow
        }

        // Not allowed: redirect to appropriate login
        $session->set('student_admin_redirect_url', current_url());
        if ($session->get('admin_logged_in')) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'หน้านี้สำหรับผู้ดูแลระบบหรือนักศึกษาสโมสรเท่านั้น');
        }
        return redirect()->to(base_url('student/login'))
            ->with('error', 'กรุณาเข้าสู่ระบบ (นักศึกษาสโมสร) หรือใช้บัญชีผู้ดูแลระบบ');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

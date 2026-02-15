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
        if (!session()->get('student_logged_in')) {
            session()->set('student_redirect_url', current_url());
            return redirect()->to(base_url('student/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบ');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

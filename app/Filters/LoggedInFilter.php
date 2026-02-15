<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ตรวจว่าเข้าสู่ระบบแล้ว (admin_logged_in) — ใช้กับหน้า Dashboard การจัดการ (ทุก role)
 */
class LoggedInFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in')) {
            session()->set('redirect_url', current_url());
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบ');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

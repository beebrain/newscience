<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AccessControl;

class EdocAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('admin_logged_in')) {
            $session->set('redirect_url', current_url());
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน E-Document');
        }

        $userId = $session->get('admin_id');

        // Use AccessControl library to check permission
        if (!AccessControl::hasAccess($userId, 'edoc')) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'คุณไม่มีสิทธิ์เข้าใช้ระบบ E-Document');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}

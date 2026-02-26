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

        $userId = (int) $session->get('admin_id');
        $path = $request->getUri()->getPath();
        $systemSlug = (strpos($path, 'edoc/admin') !== false) ? 'edoc_admin' : 'edoc';

        if (!AccessControl::hasAccess($userId, $systemSlug)) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', $systemSlug === 'edoc_admin' ? 'คุณไม่มีสิทธิ์จัดการ E-Document' : 'คุณไม่มีสิทธิ์เข้าใช้ระบบ E-Document');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}

<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AccessControl;

/**
 * E-Document: ทุกคนที่ล็อกอินแล้วเข้า E-Document (ดูเอกสาร) ได้
 * เฉพาะ path edoc/admin (การจัดการ) ต้องมีสิทธิ์ edoc_admin
 */
class EdocAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('admin_logged_in')) {
            $session->set('redirect_url', current_url());
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน E-Document');
        }

        $path = $request->getUri()->getPath();
        $isAdminPath = (strpos($path, 'edoc/admin') !== false);

        if ($isAdminPath) {
            $userId = (int) $session->get('admin_id');
            if (! AccessControl::hasAccess($userId, 'edoc_admin')) {
                return redirect()->to(base_url('edoc'))
                    ->with('error', 'คุณไม่มีสิทธิ์จัดการ E-Document เฉพาะ Admin ที่ได้รับสิทธิ์เท่านั้น');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

<?php

namespace App\Filters;

use App\Libraries\CertOrganizerAccess;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ต้องเข้าสู่ระบบแล้ว และมีสิทธิ์จัดกิจกรรมใบรับรอง (บุคลากรในคณะ / ผู้ดูแล)
 */
class CertOrganizerFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('admin_logged_in')) {
            session()->set('redirect_url', current_url());

            return redirect()->to(base_url('admin/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        if (! CertOrganizerAccess::currentMayOrganize()) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'คุณไม่มีสิทธิ์จัดการกิจกรรมใบรับรอง');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

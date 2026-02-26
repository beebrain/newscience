<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AccessControl;

class CertApproverFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();

        if (!$session->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $userUid = (int) $session->get('admin_id');
        if (AccessControl::hasAccess($userUid, 'cert_approve')) {
            return null;
        }

        $role = $session->get('admin_role');
        $allowedRoles = ['super_admin', 'admin', 'faculty_admin'];
        if (in_array($role, $allowedRoles, true)) {
            return null;
        }

        $signerModel = new \App\Models\CertSignerModel();
        $isSigner = $signerModel->where('user_uid', $userUid)->where('is_active', 1)->first();
        if ($isSigner) {
            return null;
        }

        $personnelProgramModel = new \App\Models\PersonnelProgramModel();
        $hasChair = $personnelProgramModel->personnelHasChairRole($userUid);
        if ($hasChair) {
            return null;
        }

        return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }
}

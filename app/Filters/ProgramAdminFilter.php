<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ProgramAdminFilter implements FilterInterface
{
    /**
     * Check if user has permission to manage program content
     */
    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();

        // Check if user is logged in
        if (!$session->has('admin_id') && !$session->has('admin_role')) {
            return redirect()->to(base_url('/admin/login'))->with('error', 'กรุณาเข้าสู่ระบบแอดมิน');
        }

        $userRole = $session->get('admin_role');

        // Debug logging
        log_message('debug', 'ProgramAdminFilter: userRole = ' . ($userRole ?? 'null'));
        log_message('debug', 'ProgramAdminFilter: admin_id = ' . ($session->get('admin_id') ?? 'null'));

        // Get user info
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($session->get('admin_id'));

        if (!$user) {
            return redirect()->to(base_url('/admin/login'))->with('error', 'ไม่พบข้อมูลผู้ใช้');
        }

        $isProgramAdmin = \App\Libraries\AccessControl::hasAccess((int) $user['uid'], 'program_admin');
        if (!$isProgramAdmin && in_array($userRole, ['super_admin', 'admin'], true)) {
            $isProgramAdmin = true;
        }
        if (!$isProgramAdmin && ($userRole === 'faculty_admin' || $userRole === 'faculty' || $userRole === 'admin')) {
            $personnelProgramModel = new \App\Models\PersonnelProgramModel();
            $isProgramAdmin = $personnelProgramModel->personnelHasChairRole($user['uid']);
        }
        if (!$isProgramAdmin) {
            return redirect()->to(base_url('/dashboard'))->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตร');
        }

        return null;
    }

    /**
     * Allow after filter to run
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }
}

<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('admin_logged_in')) {
            $url = current_url();
            log_message('debug', 'AdminAuthFilter: not logged in, redirect to login. intended_url=' . $url);
            // Store intended URL for redirect after login
            $session->set('redirect_url', $url);

            return redirect()->to(base_url('admin/login'))
                ->with('error', 'Please login to access admin area.');
        }

        // ถ้าเป็น user role (ไม่ใช่ admin) — ส่งไปหน้า Dashboard แทนหน้า Admin (ตาราง user: role = user|faculty_admin|super_admin, admin=1)
        $role = $session->get('admin_role');
        $allowedRoles = ['admin', 'editor', 'super_admin', 'faculty_admin'];
        if (!in_array($role, $allowedRoles, true)) {
            log_message('debug', 'AdminAuthFilter: user role, redirect to dashboard. role=' . ($role === null ? 'null' : $role));
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'หน้านี้สำหรับผู้ดูแลระบบเท่านั้น');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}

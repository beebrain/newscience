<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

/**
 * Executive Dashboard for dean / vice-dean.
 * Access restricted to admin or super_admin role.
 */
class ExecutiveDashboard extends BaseController
{
    /**
     * Dashboard view: stats and charts loaded via AJAX from Api\ExecutiveStats.
     */
    public function index()
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $role = session()->get('admin_role');
        if ($role !== 'admin' && $role !== 'super_admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'เฉพาะผู้บริหาร (คณบดี/รองคณบดี) เท่านั้นที่เข้าหน้านี้ได้');
        }

        $data = [
            'page_title' => 'Dashboard ผู้บริหาร',
        ];

        return view('admin/executive/dashboard', $data);
    }
}

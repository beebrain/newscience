<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * หน้า Dashboard — แยก layout ตาม role:
 *   - super_admin / admin / faculty_admin → admin_layout (sidebar + full management)
 *   - user / editor                      → user_layout  (topbar-only, card-based)
 */
class Dashboard extends BaseController
{
    public function index()
    {
        $userId = session()->get('admin_id');
        if (!$userId) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $userModel = new UserModel();
        $row = $userModel->find($userId);
        if (!$row) {
            return redirect()->to(base_url('admin/login'))->with('error', 'ไม่พบข้อมูลผู้ใช้');
        }

        $profile = [
            'uid'           => (int) ($row['uid'] ?? 0),
            'email'         => $row['email'] ?? '',
            'login_uid'     => $row['login_uid'] ?? '',
            'title'         => $row['title'] ?? $row['titleThai'] ?? '',
            'name_th'       => $userModel->getFullNameThaiForDisplay($row),
            'name_en'       => trim(($row['gf_name'] ?? '') . ' ' . ($row['gl_name'] ?? '')),
            'role'          => $row['role'] ?? 'user',
            'status'        => UserModel::statusFromActive($row['status'] ?? $row['active'] ?? 0),
            'created_at'    => $row['created_at'] ?? null,
            'updated_at'    => $row['updated_at'] ?? null,
        ];

        $canManageEvaluate = in_array($profile['role'], ['super_admin', 'faculty_admin'], true);
        $isSuperAdmin = in_array($profile['role'], ['super_admin', 'admin', 'faculty_admin'], true);

        $data = [
            'page_title'         => 'การจัดการ',
            'profile'            => $profile,
            'can_manage_evaluate' => $canManageEvaluate,
        ];

        if ($isSuperAdmin) {
            return view('admin/dashboard/index', $data);
        }

        return view('user/dashboard/index', $data);
    }
}

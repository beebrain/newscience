<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * หน้า Dashboard — แยก layout ตาม role:
 *   - super_admin / admin / faculty_admin → admin_layout (sidebar + full management)
 *   - user / editor                      → user_layout  (topbar-only, card-based)
 *
 * Super Admin สามารถสลับเป็นมุมมองผู้ใช้ทั่วไป (user_layout + การ์ด Portal) ผ่าน session
 * {@see self::SESSION_SUPER_ADMIN_MEMBER_PORTAL} และเส้นทาง portal-mode/*
 */
class Dashboard extends BaseController
{
    /** เมื่อเป็น true และ role = super_admin หน้า /dashboard ใช้มุมมองเดียวกับผู้ใช้ทั่วไป */
    public const SESSION_SUPER_ADMIN_MEMBER_PORTAL = 'super_admin_member_portal';

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
        $useAdminDashboard = in_array($profile['role'], ['super_admin', 'admin', 'faculty_admin'], true);

        if ($profile['role'] === 'super_admin' && session()->get(self::SESSION_SUPER_ADMIN_MEMBER_PORTAL)) {
            $useAdminDashboard = false;
        }

        $data = [
            'page_title'         => $useAdminDashboard ? 'การจัดการ' : 'Portal',
            'profile'            => $profile,
            'can_manage_evaluate' => $canManageEvaluate,
        ];

        if ($useAdminDashboard) {
            return view('admin/dashboard/index', $data);
        }

        return view('user/dashboard/index', $data);
    }

    /**
     * Super Admin — สลับมุมมอง Portal ให้เหมือนผู้ใช้ทั่วไป (หน้า /dashboard + chrome แบบ user_layout)
     */
    public function setMemberPortal()
    {
        $userId = (int) session()->get('admin_id');
        if ($userId <= 0) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $userModel = new UserModel();
        $row       = $userModel->find($userId);
        if (!$row || ($row['role'] ?? '') !== 'super_admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'เฉพาะ Super Admin เท่านั้นที่ใช้มุมมองนี้ได้');
        }

        session()->set(self::SESSION_SUPER_ADMIN_MEMBER_PORTAL, true);

        return redirect()->to(base_url('dashboard'));
    }

    /**
     * Super Admin — กลับมุมมองจัดการ (แดชบอร์ดแบบ admin_layout)
     */
    public function setManagePortal()
    {
        $userId = (int) session()->get('admin_id');
        if ($userId <= 0) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        session()->remove(self::SESSION_SUPER_ADMIN_MEMBER_PORTAL);

        return redirect()->to(base_url('dashboard'));
    }
}

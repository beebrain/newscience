<?php

namespace App\Controllers\User;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * หน้า Dashboard สำหรับผู้ใช้งาน (role = user ธรรมดา) — หน้าตาเหมือน Admin
 * แยก Controller ไว้ในโฟลเดอร์ User เพื่อการจัดการ
 * เมนู: เข้าสู่ Edoc, เข้าสู่หน้าการจัดการงานวิจัย (Research Record)
 * หน้าแรก: ข้อมูลพื้นฐานของผู้ใช้คนนั้น (ชื่อ นามสกุล อีเมล ฯลฯ)
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
            'status'        => \App\Models\UserModel::statusFromActive($row['status'] ?? $row['active'] ?? 0),
            'created_at'    => $row['created_at'] ?? null,
            'updated_at'    => $row['updated_at'] ?? null,
        ];

        $data = [
            'page_title' => 'การจัดการ',
            'profile'    => $profile,
        ];

        return view('user/dashboard/index', $data);
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * จัดการผู้ใช้ (รายการทั้งหมดจากตาราง user) — สำหรับ Admin เท่านั้น
 */
class Users extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $rows = $userModel->orderBy('uid', 'ASC')->findAll();

        $users = [];
        foreach ($rows as $row) {
            $users[] = [
                'uid'          => (int) ($row['uid'] ?? 0),
                'login_uid'    => $row['login_uid'] ?? '',
                'email'        => $row['email'] ?? '',
                'display_name' => $userModel->getFullNameThaiForDisplay($row),
                'role'         => $row['role'] ?? 'user',
                'status'       => \App\Models\UserModel::statusFromActive($row['active'] ?? 0),
                'created_at'   => $row['created_at'] ?? null,
            ];
        }

        $data = [
            'page_title' => 'จัดการผู้ใช้',
            'users'      => $users,
        ];

        return view('admin/users/index', $data);
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

/**
 * การจัดการผู้ใช้ — เฉพาะ Super_admin เท่านั้นที่เห็นและเข้าได้
 * แสดงรายการผู้ใช้จากตาราง user
 */
class UserManagement extends BaseController
{
    /** @var string */
    private const ROLE_SUPER_ADMIN = 'super_admin';

    public function __construct()
    {
        $session = session();
        $role = $session->get('admin_role');
        if ($role !== self::ROLE_SUPER_ADMIN) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
    }

    /**
     * รายการผู้ใช้ทั้งหมด (เฉพาะ Super_admin)
     */
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
                'status'       => $row['status'] ?? 'active',
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

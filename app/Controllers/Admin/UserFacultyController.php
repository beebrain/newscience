<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\OrganizationPersonnelSync;
use App\Models\UserModel;

class UserFacultyController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display list of users with faculty management
     */
    public function index()
    {
        $search = $this->request->getGet('search');
        $faculty = $this->request->getGet('faculty');

        $builder = $this->userModel->builder();

        if ($search) {
            $builder->groupStart()
                ->like('tf_name', $search)
                ->orLike('tl_name', $search)
                ->orLike('email', $search)
                ->orLike('nickname', $search)
                ->groupEnd();
        }

        if ($faculty) {
            $builder->where('faculty', $faculty);
        }

        $users = $builder->orderBy('tf_name', 'ASC')
            ->orderBy('tl_name', 'ASC')
            ->get()
            ->getResultArray();

        // Get unique faculties for filter dropdown
        $faculties = $this->userModel->distinct()
            ->select('faculty')
            ->where('faculty IS NOT NULL')
            ->where('faculty !=', '')
            ->findAll();

        $data = [
            'users' => $users,
            'faculties' => array_column($faculties, 'faculty'),
            'search' => $search,
            'selectedFaculty' => $faculty,
        ];

        return view('admin/user_faculty/index', $data);
    }

    /**
     * Update user faculty
     */
    public function updateFaculty()
    {
        $userId = $this->request->getPost('user_id');
        $faculty = $this->request->getPost('faculty');

        if (!$userId) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบรหัสผู้ใช้'
            ]);
        }

        $result = $this->userModel->update($userId, ['faculty' => $faculty ?: null]);

        if ($result) {
            OrganizationPersonnelSync::syncAfterUserFacultyChange((int) $userId);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'อัปเดตคณะเรียบร้อย'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'ไม่สามารถอัปเดตคณะได้'
        ]);
    }

    /**
     * Bulk update faculty for multiple users
     */
    public function bulkUpdate()
    {
        $userIds = $this->request->getPost('user_ids');
        $faculty = $this->request->getPost('faculty');

        if (empty($userIds) || !is_array($userIds)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'กรุณาเลือกผู้ใช้อย่างน้อย 1 คน'
            ]);
        }

        $updated = 0;
        foreach ($userIds as $userId) {
            if ($this->userModel->update($userId, ['faculty' => $faculty ?: null])) {
                OrganizationPersonnelSync::syncAfterUserFacultyChange((int) $userId);
                $updated++;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => "อัปเดตคณะให้ {$updated} คนเรียบร้อย"
        ]);
    }

    /**
     * Get user details via AJAX
     */
    public function getUser($userId)
    {
        $user = $this->userModel->find($userId);

        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบผู้ใช้'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'user' => $user
        ]);
    }
}

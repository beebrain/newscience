<?php

namespace App\Controllers\Evaluate;

use App\Controllers\BaseController;
use App\Models\Evaluate\EvaluationRefereeModel;
use App\Models\UserModel;

/**
 * CRUD ผู้ทรงคุณวุฒิ (evaluate_referees) สำหรับ Admin
 */
class AdminRefereeController extends BaseController
{
    protected $refereeModel;
    protected $userModel;

    public function __construct()
    {
        $this->refereeModel = new EvaluationRefereeModel();
        $this->userModel    = new UserModel();

        if (! session()->get('admin_logged_in')) {
            redirect()->to(base_url('admin/login'))->send();
            exit;
        }
    }

    private function hasManageRights(int $uid): bool
    {
        $user = $this->userModel->find($uid);
        return in_array($user['role'] ?? '', ['super_admin', 'faculty_admin'], true);
    }

    /**
     * แสดงรายการผู้ทรงคุณวุฒิทั้งหมด
     */
    public function index()
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return redirect()->to(base_url('evaluate/admin'))->with('error', 'ไม่มีสิทธิ์เข้าหน้านี้');
        }

        $search = $this->request->getGet('search') ?? '';
        $data['referees']   = $this->refereeModel->getAllPaginated(50, $search ?: null);
        $data['search']     = $search;
        $data['page_title'] = 'จัดการผู้ทรงคุณวุฒิ';

        return view('evaluate/admin_referees', $data);
    }

    /**
     * บันทึก (สร้าง/แก้ไข) ผู้ทรงคุณวุฒิ — AJAX POST
     */
    public function save()
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์'])->setStatusCode(403);
        }

        $id = (int) $this->request->getPost('id');

        $rules = [
            'email' => 'required|valid_email|max_length[255]',
            'name'  => 'required|max_length[255]',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => implode(', ', $this->validator->getErrors()),
            ])->setStatusCode(422);
        }

        $data = [
            'email'       => $this->request->getPost('email'),
            'name'        => $this->request->getPost('name'),
            'institution' => $this->request->getPost('institution') ?? '',
            'expertise'   => $this->request->getPost('expertise') ?? '',
            'phone'       => $this->request->getPost('phone') ?? '',
            'status'      => (int) ($this->request->getPost('status') ?? 1),
        ];

        $result = $this->refereeModel->saveReferee($data, $id > 0 ? $id : null);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $id > 0 ? 'แก้ไขข้อมูลผู้ทรงคุณวุฒิเรียบร้อย' : 'เพิ่มผู้ทรงคุณวุฒิเรียบร้อย',
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => 'ไม่สามารถบันทึกข้อมูลได้',
        ])->setStatusCode(500);
    }

    /**
     * ดึงข้อมูลผู้ทรงคุณวุฒิ 1 คน — AJAX GET
     */
    public function get(int $id)
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return $this->response->setJSON(['success' => false])->setStatusCode(403);
        }

        $referee = $this->refereeModel->find($id);
        if (! $referee) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล'])->setStatusCode(404);
        }

        return $this->response->setJSON(['success' => true, 'data' => $referee]);
    }

    /**
     * ลบผู้ทรงคุณวุฒิ (soft-delete) — AJAX POST
     */
    public function delete()
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์'])->setStatusCode(403);
        }

        $id = (int) $this->request->getPost('id');
        if ($id <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'ID ไม่ถูกต้อง'])->setStatusCode(400);
        }

        $result = $this->refereeModel->deleteReferee($id);

        return $this->response->setJSON([
            'success' => $result,
            'message' => $result ? 'ลบผู้ทรงคุณวุฒิเรียบร้อย' : 'ไม่สามารถลบได้',
        ]);
    }

    /**
     * Toggle สถานะ active/inactive — AJAX POST
     */
    public function toggleStatus()
    {
        $adminId = (int) session()->get('admin_id');
        if (! $this->hasManageRights($adminId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์'])->setStatusCode(403);
        }

        $id = (int) $this->request->getPost('id');
        $referee = $this->refereeModel->find($id);
        if (! $referee) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล'])->setStatusCode(404);
        }

        $newStatus = ((int) $referee['status'] === 1) ? 0 : 1;
        $result = $this->refereeModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON([
            'success' => (bool) $result,
            'newStatus' => $newStatus,
            'message' => $newStatus === 1 ? 'เปิดใช้งานเรียบร้อย' : 'ปิดใช้งานเรียบร้อย',
        ]);
    }
}

<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UrgentPopupModel;

class UrgentPopups extends BaseController
{
    protected $popupModel;

    public function __construct()
    {
        $this->popupModel = new UrgentPopupModel();
    }

    /**
     * รายการประกาศด่วน (สูงสุด 3 รายการ)
     */
    public function index()
    {
        $data = [
            'page_title' => 'ประกาศด่วน (ป๊อปอัปหน้าแรก)',
            'popups' => $this->popupModel->getAllForAdmin(),
            'max_items' => UrgentPopupModel::MAX_ACTIVE,
            'can_add' => $this->popupModel->canAdd(),
        ];

        return view('admin/urgent_popups/index', $data);
    }

    /**
     * ฟอร์มเพิ่มประกาศ
     */
    public function create()
    {
        if (!$this->popupModel->canAdd()) {
            return redirect()->to(base_url('admin/urgent-popups'))
                ->with('error', 'ประกาศด่วนมีได้สูงสุด ' . UrgentPopupModel::MAX_ACTIVE . ' รายการ กรุณาลบหรือปิดการแสดงผลก่อนเพิ่มใหม่');
        }

        $data = [
            'page_title' => 'เพิ่มประกาศด่วน',
            'popup' => null,
        ];

        return view('admin/urgent_popups/form', $data);
    }

    /**
     * บันทึกประกาศใหม่
     */
    public function store()
    {
        if (!$this->popupModel->canAdd()) {
            return redirect()->to(base_url('admin/urgent-popups'))
                ->with('error', 'ประกาศด่วนมีได้สูงสุด ' . UrgentPopupModel::MAX_ACTIVE . ' รายการ');
        }

        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getPopupDataFromRequest();
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $data['image'] = $this->uploadPopupImage($imageFile);
        }

        $this->popupModel->insert($data);

        return redirect()->to(base_url('admin/urgent-popups'))
            ->with('success', 'เพิ่มประกาศด่วนสำเร็จ');
    }

    /**
     * ฟอร์มแก้ไข
     */
    public function edit($id)
    {
        $popup = $this->popupModel->find($id);
        if (!$popup) {
            return redirect()->to(base_url('admin/urgent-popups'))->with('error', 'ไม่พบข้อมูล');
        }

        $data = [
            'page_title' => 'แก้ไขประกาศด่วน',
            'popup' => $popup,
        ];

        return view('admin/urgent_popups/form', $data);
    }

    /**
     * อัปเดตประกาศ
     */
    public function update($id)
    {
        $popup = $this->popupModel->find($id);
        if (!$popup) {
            return redirect()->to(base_url('admin/urgent-popups'))->with('error', 'ไม่พบข้อมูล');
        }

        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getPopupDataFromRequest();
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $this->deletePopupImage($popup['image'] ?? '');
            $data['image'] = $this->uploadPopupImage($imageFile);
        }

        $this->popupModel->update($id, $data);

        return redirect()->to(base_url('admin/urgent-popups'))
            ->with('success', 'แก้ไขประกาศด่วนสำเร็จ');
    }

    /**
     * ลบประกาศ
     */
    public function delete($id)
    {
        $popup = $this->popupModel->find($id);
        if (!$popup) {
            return redirect()->to(base_url('admin/urgent-popups'))->with('error', 'ไม่พบข้อมูล');
        }
        $this->deletePopupImage($popup['image'] ?? '');
        $this->popupModel->delete($id);
        return redirect()->to(base_url('admin/urgent-popups'))
            ->with('success', 'ลบประกาศด่วนสำเร็จ');
    }

    /**
     * สลับสถานะเปิด/ปิด (AJAX)
     */
    public function toggleActive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }
        $popup = $this->popupModel->find($id);
        if (!$popup) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }
        $newStatus = $popup['is_active'] ? 0 : 1;
        $this->popupModel->update($id, ['is_active' => $newStatus]);
        return $this->response->setJSON([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'เปิดแสดงแล้ว' : 'ปิดแสดงแล้ว',
        ]);
    }

    private function getPopupDataFromRequest(): array
    {
        return [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'link_url' => $this->request->getPost('link_url') ?: null,
            'link_text' => $this->request->getPost('link_text') ?: 'ดูรายละเอียด',
            'sort_order' => (int) $this->request->getPost('sort_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];
    }

    private function uploadPopupImage($file): string
    {
        $dir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'urgent_popups';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);
        return 'uploads/urgent_popups/' . $newName;
    }

    private function deletePopupImage($path): void
    {
        if (empty($path)) {
            return;
        }
        $base = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $full = $base . $path;
        if (file_exists($full)) {
            @unlink($full);
        }
    }
}

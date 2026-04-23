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
        helper(['image_manager', 'content_sanitizer']);
    }

    /**
     * รายการประกาศด่วน (เพิ่มได้ไม่จำกัด แต่หน้าแรกจะแสดงสูงสุด MAX_ACTIVE รายการ)
     */
    public function index()
    {
        $data = [
            'page_title' => 'ประกาศด่วน (ป๊อปอัปหน้าแรก)',
            'popups' => $this->popupModel->getAllForAdmin(),
            'max_items' => UrgentPopupModel::MAX_ACTIVE,
            'active_count' => $this->popupModel->countActive(),
            'can_add' => $this->popupModel->canAdd(),
        ];

        return view('admin/urgent_popups/index', $data);
    }

    /**
     * ฟอร์มเพิ่มประกาศ
     */
    public function create()
    {
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
        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Insert ก่อนเพื่อให้มี id สำหรับตั้งชื่อไฟล์
        $data = $this->getPopupDataFromRequest();
        $newId = $this->popupModel->insert($data, true);
        if (!$newId) {
            return redirect()->back()->withInput()->with('error', 'บันทึกไม่สำเร็จ');
        }

        $imageData = $this->handleImageUpload((int) $newId);
        if ($imageData !== null) {
            $this->popupModel->update($newId, $imageData);
        }

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
        $imageData = $this->handleImageUpload((int) $id, $popup['image'] ?? null);
        if ($imageData !== null) {
            $data = array_merge($data, $imageData);
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
        image_manager_delete('popup', $popup['image'] ?? null);
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
            'content' => sanitize_html_content($this->request->getPost('content')),
            'link_url' => $this->request->getPost('link_url') ?: null,
            'link_text' => $this->request->getPost('link_text') ?: 'ดูรายละเอียด',
            'sort_order' => (int) $this->request->getPost('sort_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];
    }

    /**
     * จัดการอัปโหลดรูป — รองรับทั้ง UploadedFile และ base64 จาก crop
     * ถ้ามีการอัปโหลดรูปใหม่ จะลบรูปเก่าอัตโนมัติ
     *
     * @return array|null {image, image_width, image_height} หรือ null ถ้าไม่มีรูปใหม่
     */
    private function handleImageUpload(int $popupId, ?string $oldImagePath = null): ?array
    {
        // base64 ก่อน (จาก crop client-side)
        $base64 = $this->request->getPost('image_base64');
        if (!empty($base64) && is_string($base64) && strpos($base64, 'base64,') !== false) {
            $result = image_manager_save_base64('popup', $popupId, $base64);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('popup', $oldImagePath);
                return [
                    'image'        => $result['path'],
                    'image_width'  => $result['width'],
                    'image_height' => $result['height'],
                ];
            }
        }

        // File ปกติ
        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $result = image_manager_save_file('popup', $popupId, $file);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('popup', $oldImagePath);
                return [
                    'image'        => $result['path'],
                    'image_width'  => $result['width'],
                    'image_height' => $result['height'],
                ];
            }
        }

        return null;
    }
}

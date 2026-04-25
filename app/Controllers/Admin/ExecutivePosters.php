<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ExecutivePosterModel;

class ExecutivePosters extends BaseController
{
    protected $posterModel;

    public function __construct()
    {
        $this->posterModel = new ExecutivePosterModel();
        helper(['image_manager', 'content_sanitizer']);
    }

    /**
     * รายการโปสเตอร์ผู้บริหาร
     */
    public function index()
    {
        $data = [
            'page_title' => 'โปสเตอร์ผู้บริหาร (สไลด์หน้า About)',
            'posters'    => $this->posterModel->getAllForAdmin(),
        ];

        return view('admin/executive_posters/index', $data);
    }

    /**
     * ฟอร์มเพิ่มโปสเตอร์
     */
    public function create()
    {
        $data = [
            'page_title' => 'เพิ่มโปสเตอร์ผู้บริหาร',
            'poster'     => null,
        ];

        return view('admin/executive_posters/form', $data);
    }

    /**
     * บันทึกโปสเตอร์ใหม่
     */
    public function store()
    {
        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getPosterDataFromRequest();
        $newId = $this->posterModel->insert($data, true);
        if (!$newId) {
            return redirect()->back()->withInput()->with('error', 'บันทึกไม่สำเร็จ');
        }

        $imageData = $this->handleImageUpload((int) $newId);
        if ($imageData !== null) {
            $this->posterModel->update($newId, $imageData);
        }

        return redirect()->to(base_url('admin/executive-posters'))
            ->with('success', 'เพิ่มโปสเตอร์สำเร็จ');
    }

    /**
     * ฟอร์มแก้ไข
     */
    public function edit($id)
    {
        $poster = $this->posterModel->find($id);
        if (!$poster) {
            return redirect()->to(base_url('admin/executive-posters'))->with('error', 'ไม่พบข้อมูล');
        }

        $data = [
            'page_title' => 'แก้ไขโปสเตอร์ผู้บริหาร',
            'poster'     => $poster,
        ];

        return view('admin/executive_posters/form', $data);
    }

    /**
     * อัปเดตโปสเตอร์
     */
    public function update($id)
    {
        $poster = $this->posterModel->find($id);
        if (!$poster) {
            return redirect()->to(base_url('admin/executive-posters'))->with('error', 'ไม่พบข้อมูล');
        }

        $rules = [
            'title' => 'required|max_length[255]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = $this->getPosterDataFromRequest();
        $imageData = $this->handleImageUpload((int) $id, $poster['image'] ?? null);
        if ($imageData !== null) {
            $data = array_merge($data, $imageData);
        }

        $this->posterModel->update($id, $data);

        return redirect()->to(base_url('admin/executive-posters'))
            ->with('success', 'แก้ไขโปสเตอร์สำเร็จ');
    }

    /**
     * ลบโปสเตอร์
     */
    public function delete($id)
    {
        $poster = $this->posterModel->find($id);
        if (!$poster) {
            return redirect()->to(base_url('admin/executive-posters'))->with('error', 'ไม่พบข้อมูล');
        }
        image_manager_delete('executive_poster', $poster['image'] ?? null);
        $this->posterModel->delete($id);
        return redirect()->to(base_url('admin/executive-posters'))
            ->with('success', 'ลบโปสเตอร์สำเร็จ');
    }

    /**
     * สลับสถานะเปิด/ปิด (AJAX)
     */
    public function toggleActive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }
        $poster = $this->posterModel->find($id);
        if (!$poster) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }
        $newStatus = $poster['is_active'] ? 0 : 1;
        $this->posterModel->update($id, ['is_active' => $newStatus]);
        return $this->response->setJSON([
            'success'   => true,
            'is_active' => $newStatus,
            'message'   => $newStatus ? 'เปิดแสดงแล้ว' : 'ปิดแสดงแล้ว',
        ]);
    }

    private function getPosterDataFromRequest(): array
    {
        return [
            'title'      => $this->request->getPost('title'),
            'caption'    => $this->request->getPost('caption') ?: null,
            'link_url'   => $this->request->getPost('link_url') ?: null,
            'sort_order' => (int) $this->request->getPost('sort_order') ?: 0,
            'is_active'  => $this->request->getPost('is_active') ? 1 : 0,
        ];
    }

    /**
     * จัดการอัปโหลดรูป — รองรับทั้ง UploadedFile และ base64 จาก crop
     *
     * @return array|null {image, image_width, image_height} หรือ null ถ้าไม่มีรูปใหม่
     */
    private function handleImageUpload(int $posterId, ?string $oldImagePath = null): ?array
    {
        $base64 = $this->request->getPost('image_base64');
        if (!empty($base64) && is_string($base64) && strpos($base64, 'base64,') !== false) {
            $result = image_manager_save_base64('executive_poster', $posterId, $base64);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('executive_poster', $oldImagePath);
                return [
                    'image'        => $result['path'],
                    'image_width'  => $result['width'],
                    'image_height' => $result['height'],
                ];
            }
        }

        $file = $this->request->getFile('image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $result = image_manager_save_file('executive_poster', $posterId, $file);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('executive_poster', $oldImagePath);
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

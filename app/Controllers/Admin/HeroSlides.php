<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\HeroSlideModel;

class HeroSlides extends BaseController
{
    protected $heroSlideModel;

    public function __construct()
    {
        $this->heroSlideModel = new HeroSlideModel();
    }

    /**
     * List all hero slides
     */
    public function index()
    {
        $data = [
            'page_title' => 'Manage Hero Slides',
            'slides' => $this->heroSlideModel->getAllSlides()
        ];

        return view('admin/hero_slides/index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Add Hero Slide'
        ];

        return view('admin/hero_slides/create', $data);
    }

    /**
     * Store new slide
     */
    public function store()
    {
        $rules = [
            'image' => 'uploaded[image]|is_image[image]|max_size[image,5120]'
        ];

        // If no file uploaded, check for existing image path
        $imageFile = $this->request->getFile('image');
        if (!$imageFile || !$imageFile->isValid()) {
            $rules = [];
        }

        if (!empty($rules) && !$this->validate($rules)) {
            return redirect()->back()
                            ->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $slideData = [
            'title' => $this->request->getPost('title'),
            'subtitle' => $this->request->getPost('subtitle'),
            'description' => $this->request->getPost('description'),
            'link' => $this->request->getPost('link'),
            'link_text' => $this->request->getPost('link_text') ?: 'ดูรายละเอียด',
            'show_buttons' => $this->request->getPost('show_buttons') ? 1 : 0,
            'sort_order' => (int)$this->request->getPost('sort_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];

        // Handle image upload
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $newName = $imageFile->getRandomName();
            $imageFile->move(rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hero', $newName);
            $slideData['image'] = 'uploads/hero/' . $newName;
        }

        if (empty($slideData['image'])) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'กรุณาอัปโหลดรูปภาพ');
        }

        $this->heroSlideModel->insert($slideData);

        return redirect()->to(base_url('admin/hero-slides'))
                        ->with('success', 'เพิ่ม Hero Slide สำเร็จ');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            return redirect()->to(base_url('admin/hero-slides'))
                            ->with('error', 'ไม่พบข้อมูล');
        }

        $data = [
            'page_title' => 'Edit Hero Slide',
            'slide' => $slide
        ];

        return view('admin/hero_slides/edit', $data);
    }

    /**
     * Update slide
     */
    public function update($id)
    {
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            return redirect()->to(base_url('admin/hero-slides'))
                            ->with('error', 'ไม่พบข้อมูล');
        }

        $slideData = [
            'title' => $this->request->getPost('title'),
            'subtitle' => $this->request->getPost('subtitle'),
            'description' => $this->request->getPost('description'),
            'link' => $this->request->getPost('link'),
            'link_text' => $this->request->getPost('link_text') ?: 'ดูรายละเอียด',
            'show_buttons' => $this->request->getPost('show_buttons') ? 1 : 0,
            'sort_order' => (int)$this->request->getPost('sort_order') ?: 0,
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
        ];

        // Handle image upload
        $imageFile = $this->request->getFile('image');
        if ($imageFile && $imageFile->isValid() && !$imageFile->hasMoved()) {
            $heroDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hero';
            $oldPath = $slide['image'] ? $heroDir . DIRECTORY_SEPARATOR . basename($slide['image']) : '';
            if ($oldPath && file_exists($oldPath)) {
                @unlink($oldPath);
            } elseif ($slide['image'] && file_exists(FCPATH . $slide['image'])) {
                @unlink(FCPATH . $slide['image']);
            }

            $newName = $imageFile->getRandomName();
            $imageFile->move($heroDir, $newName);
            $slideData['image'] = 'uploads/hero/' . $newName;
        }

        $this->heroSlideModel->update($id, $slideData);

        return redirect()->to(base_url('admin/hero-slides'))
                        ->with('success', 'แก้ไข Hero Slide สำเร็จ');
    }

    /**
     * Delete slide
     */
    public function delete($id)
    {
        $slide = $this->heroSlideModel->find($id);

        if (!$slide) {
            return redirect()->to(base_url('admin/hero-slides'))
                            ->with('error', 'ไม่พบข้อมูล');
        }

        // Delete image file (writable first, then public fallback)
        $heroDir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'hero';
        $fn = $slide['image'] ? basename($slide['image']) : '';
        if ($fn) {
            $path = $heroDir . DIRECTORY_SEPARATOR . $fn;
            if (file_exists($path)) {
                @unlink($path);
            } elseif (file_exists(FCPATH . $slide['image'])) {
                @unlink(FCPATH . $slide['image']);
            }
        }

        $this->heroSlideModel->delete($id);

        return redirect()->to(base_url('admin/hero-slides'))
                        ->with('success', 'ลบ Hero Slide สำเร็จ');
    }

    /**
     * Toggle active status (AJAX)
     */
    public function toggleActive($id)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $slide = $this->heroSlideModel->find($id);
        if (!$slide) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบข้อมูล']);
        }

        $newStatus = $slide['is_active'] ? 0 : 1;
        $this->heroSlideModel->update($id, ['is_active' => $newStatus]);

        return $this->response->setJSON([
            'success' => true,
            'is_active' => $newStatus,
            'message' => $newStatus ? 'เปิดใช้งานแล้ว' : 'ปิดใช้งานแล้ว'
        ]);
    }

    /**
     * Update sort order (AJAX)
     */
    public function updateOrder()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403);
        }

        $order = $this->request->getJSON(true)['order'] ?? [];
        
        foreach ($order as $index => $id) {
            $this->heroSlideModel->updateSortOrder($id, $index);
        }

        return $this->response->setJSON(['success' => true]);
    }
}

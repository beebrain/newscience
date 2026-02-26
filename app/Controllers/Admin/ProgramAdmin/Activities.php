<?php

namespace App\Controllers\Admin\ProgramAdmin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\ProgramActivityModel;
use App\Models\ProgramActivityImageModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;

class Activities extends BaseController
{
    protected $programModel;
    protected $activityModel;
    protected $imageModel;
    protected $personnelModel;
    protected $personnelProgramModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->activityModel = new ProgramActivityModel();
        $this->imageModel = new ProgramActivityImageModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
    }

    /**
     * List activities for a program
     */
    public function index($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program;
        }

        $activities = $this->activityModel->getByProgramId($programId);
        foreach ($activities as &$a) {
            $a['images'] = $this->imageModel->getByActivityId((int) $a['id']);
        }

        $data = [
            'page_title' => 'กิจกรรมภายในหลักสูตร - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'activities' => $activities,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/programs/activities', $data);
    }

    /**
     * Show create activity form
     */
    public function createActivity($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program;
        }

        $data = [
            'page_title' => 'เพิ่มกิจกรรม - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'activity' => null,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/programs/activity_editor', $data);
    }

    /**
     * Store new activity (POST)
     */
    public function storeActivity($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program;
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
            'description' => 'permit_empty',
            'activity_date' => 'permit_empty',
            'location' => 'max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $sortOrder = $this->activityModel->countByProgramId($programId) + 1;
        $data = [
            'program_id' => $programId,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'activity_date' => $this->request->getPost('activity_date') ?: null,
            'location' => $this->request->getPost('location'),
            'sort_order' => $sortOrder,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];

        try {
            $activityId = $this->activityModel->insert($data);
            return redirect()->to(base_url('program-admin/activity/' . $activityId . '/edit'))
                ->with('success', 'สร้างกิจกรรมเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Edit activity form
     */
    public function editActivity($activityId)
    {
        $activity = $this->activityModel->find($activityId);
        if (!$activity) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }

        $program = $this->getProgramWithAuth($activity['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        $activity['images'] = $this->imageModel->getByActivityId((int) $activityId);

        $data = [
            'page_title' => 'แก้ไขกิจกรรม - ' . ($activity['title'] ?? ''),
            'program' => $program,
            'activity' => $activity,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/programs/activity_editor', $data);
    }

    /**
     * Update activity (POST)
     */
    public function updateActivity($activityId)
    {
        $activity = $this->activityModel->find($activityId);
        if (!$activity) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }

        $program = $this->getProgramWithAuth($activity['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
            'description' => 'permit_empty',
            'activity_date' => 'permit_empty',
            'location' => 'max_length[255]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'activity_date' => $this->request->getPost('activity_date') ?: null,
            'location' => $this->request->getPost('location'),
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];

        try {
            $this->activityModel->update($activityId, $data);
            return redirect()->back()->with('success', 'บันทึกกิจกรรมเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete activity (POST)
     */
    public function deleteActivity($activityId)
    {
        $activity = $this->activityModel->find($activityId);
        if (!$activity) {
            return redirect()->back()->with('error', 'ไม่พบกิจกรรม');
        }

        $program = $this->getProgramWithAuth($activity['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        helper('program_upload');
        $programId = (int) $activity['program_id'];
        $images = $this->imageModel->getByActivityId($activityId);
        foreach ($images as $img) {
            $fullPath = upload_resolve_full_path($img['image_path']);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
            $thumbPath = dirname($fullPath) . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . basename($fullPath);
            if (is_file($thumbPath)) {
                @unlink($thumbPath);
            }
        }

        try {
            $this->activityModel->delete($activityId);
            return redirect()->to(base_url('program-admin/activities/' . $programId))
                ->with('success', 'ลบกิจกรรมเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Upload activity image (POST, JSON response)
     */
    public function uploadActivityImage($activityId)
    {
        $activity = $this->activityModel->find($activityId);
        if (!$activity) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบกิจกรรม'])->setStatusCode(404);
        }

        if (!$this->checkProgramAuth($activity['program_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์'])->setStatusCode(403);
        }

        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีไฟล์หรือไฟล์ไม่ถูกต้อง']);
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, $allowed, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'อนุญาตเฉพาะรูปภาพ (jpg, png, gif, webp)']);
        }

        helper('program_upload');
        $programId = (int) $activity['program_id'];
        $uploadPath = program_upload_path($programId, 'activities');
        $prefix = 'p' . $programId . '_';
        $filename = $prefix . program_unique_filename($file, 'img');
        $relativePath = program_upload_relative_path($programId, 'activities', $filename);

        try {
            $file->move($uploadPath, $filename);
            $fullPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
            program_create_image_thumbnail($fullPath);
            $sortOrder = $this->imageModel->getNextSortOrder($activityId);
            $imageId = $this->imageModel->addImage($activityId, $relativePath, null, $sortOrder);
            $serveUrl = base_url('serve/uploads/' . $relativePath);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'อัปโหลดเรียบร้อย',
                'image_id' => $imageId,
                'image_path' => $relativePath,
                'url' => $serveUrl,
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Delete activity image (POST, JSON response)
     */
    public function deleteActivityImage($imageId)
    {
        $image = $this->imageModel->find($imageId);
        if (!$image) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบรูปภาพ'])->setStatusCode(404);
        }

        $activity = $this->activityModel->find($image['activity_id']);
        if (!$activity || !$this->checkProgramAuth($activity['program_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์'])->setStatusCode(403);
        }

        helper('program_upload');
        $fullPath = upload_resolve_full_path($image['image_path']);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
        $thumbPath = dirname($fullPath) . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . basename($fullPath);
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }

        $this->imageModel->deleteImage($imageId);
        return $this->response->setJSON(['success' => true, 'message' => 'ลบรูปภาพเรียบร้อยแล้ว']);
    }

    protected function checkProgramAuth(int $programId): bool
    {
        $userRole = session()->get('admin_role');
        if ($userRole === 'super_admin' || $userRole === 'admin') {
            return true;
        }
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;
        if (!$isChair) {
            $program = $this->programModel->find($programId);
            if ($program && $program['chair_personnel_id']) {
                $isChair = $program['chair_personnel_id'] == $userId;
            }
        }
        return $isChair;
    }

    /**
     * @return array|\CodeIgniter\HTTP\RedirectResponse
     */
    protected function getProgramWithAuth(int $programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }
        if (!$this->checkProgramAuth($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }
        return $program;
    }
}

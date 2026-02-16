<?php

namespace App\Controllers\Admin\ProgramAdmin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Models\ProgramDownloadModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;

class Dashboard extends BaseController
{
    protected $programModel;
    protected $programPageModel;
    protected $programDownloadModel;
    protected $personnelModel;
    protected $personnelProgramModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->programPageModel = new ProgramPageModel();
        $this->programDownloadModel = new ProgramDownloadModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
    }

    /**
     * Dashboard - แสำหรับแสดงหลักสูตรที่ประธานจัดการ
     */
    public function index()
    {
        // Get programs that this user is chair of
        $userId = session()->get('admin_id');
        $user = $this->personnelModel->find($userId);

        if (!$user || $user['role'] !== 'faculty') {
            return redirect()->to(base_url('/dashboard'))->with('error', 'ไม่พบสิทธิ์เพียงการจัดการหลักสูตร');
        }

        // Get programs where user is chair
        $coordinatorIds = $this->personnelProgramModel->getAllCoordinators();
        $programIds = array_keys($coordinatorIds);

        $programs = [];
        if (!empty($programIds)) {
            $programs = $this->programModel->getWithCoordinator();
            $programs = array_filter($programs, function ($program) use ($programIds) {
                return in_array($program['id'], $programIds);
            });
        }

        // Get program pages for these programs
        $programPages = [];
        foreach ($programs as $program) {
            $page = $this->programPageModel->findByProgramId($program['id']);
            if ($page) {
                $program['page'] = $page;
            }
        }

        $data = [
            'page_title' => 'จัดการหลักสูตร (ประธานหลักสูตร)',
            'programs' => $programs,
            'program_pages' => $programPages,
            'total_programs' => count($programs),
            'published_pages' => count(array_filter($programPages, fn($p) => $p['is_published'])),
        ];

        return view('admin/programs/dashboard', $data);
    }

    /**
     * Edit program content
     */
    public function edit($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการหลักสูตรนี้');
        }

        $programPage = $this->programPageModel->findByProgramId($programId);
        $downloads = $this->programDownloadModel->getByProgramId($programId);

        // Get coordinator info
        $coordinator = null;
        if ($program['chair_personnel_id']) {
            $coordinator = $this->personnelModel->find($program['chair_personnel_id']);
        }

        // Get personnel for this program
        $personnelPrograms = $this->personnelProgramModel->getByProgramId($programId);
        $personnelIds = array_map(fn($pp) => $pp['personnel_id'], $personnelPrograms);
        $personnelList = [];
        if (!empty($personnelIds)) {
            $personnelList = $this->personnelModel->whereIn('id', $personnelIds)->findAll();
        }

        $data = [
            'page_title' => 'แก้ไข้อมเนื้อหา - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'program_page' => $programPage,
            'downloads' => $downloads,
            'coordinator' => $coordinator,
            'personnel_list' => $personnelList,
            'personnel_programs' => $personnelPrograms,
        ];

        return view('admin/programs/edit_content', $data);
    }

    /**
     * Update program content
     */
    public function update($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการหลักสูตรนี้');
        }

        $rules = [
            'name_th' => 'required|min_length[1]|max_length[255]',
            'name_en' => 'max_length[255]',
            'level' => 'required|in_list[bachelor,master,doctorate]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        // Update program
        $updateData = [
            'name_th' => $this->request->getPost('name_th'),
            'name_en' => $this->request->getPost('name_en'),
            'level' => $this->request->getPost('level'),
            'status' => $this->request->getPost('status'),
        ];

        try {
            $this->programModel->update($programId, $updateData);

            // Update slug to simple format
            $this->programModel->update($programId, ['slug' => 'program-' . $programId]);

            return redirect()->to(base_url('program-admin/'))->with('success', 'อัปเดตอข้อมูลหลักสูตรเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Update program page content
     */
    public function updatePage($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการหลักสูตรนี้');
        }

        $rules = [
            'philosophy' => 'max_length[5000]',
            'objectives' => 'max_length[5000]',
            'graduate_profile' => 'max_length[5000]',
            'curriculum_structure' => 'max_length[10000]',
            'study_plan' => 'max_length[10000]',
            'career_prospects' => 'max_length[5000]',
            'tuition_fees' => 'max_length[5000]',
            'admission_info' => 'max_length[5000]',
            'contact_info' => 'max_length[5000]',
            'intro_video_url' => 'max_length[500]',
            'theme_color' => 'max_length[7]',
            'meta_description' => 'max_length[500]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'philosophy' => $this->request->getPost('philosophy'),
            'objectives' => $this->request->getPost('objectives'),
            'graduate_profile' => $this->request->getPost('graduate_profile'),
            'curriculum_structure' => $this->request->getPost('curriculum_structure'),
            'study_plan' => $this->request->getPost('study_plan'),
            'career_prospects' => $this->request->getPost('career_prospects'),
            'tuition_fees' => $this->request->getPost('tuition_fees'),
            'admission_info' => $this->request->getPost('admission_info'),
            'contact_info' => $this->request->getPost('contact_info'),
            'intro_video_url' => $this->request->getPost('intro_video_url'),
            'theme_color' => $this->request->getPost('theme_color') ?: '#1e40af',
            'meta_description' => $this->request->getPost('meta_description'),
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];

        try {
            $this->programPageModel->updateOrCreate(['program_id' => $programId], $updateData);
            return redirect()->to(base_url('program-admin/edit/' . $programId))->with('success', 'อัปเดตอเนื้อหาเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Preview program page
     */
    public function preview($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการหลักสูตรนี้');
        }

        $programPage = $this->programPageModel->findByProgramId($programId);
        $programPage['program'] = $program;

        $data = [
            'page_title' => 'ตัวอย่างหน้าเว็บไซต์หลักสูตร - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'page' => $programPage,
        ];

        return view('admin/programs/preview', $data);
    }

    /**
     * Downloads management
     */
    public function downloads($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $program['chair_personnel_id'] == $userId;
        if (!$isChair) {
            $coordinatorId = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId);
            $isChair = $coordinatorId === $userId;
        }

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการดาวน์โหลด');
        }

        $downloads = $this->programDownloadModel->getByProgramId($programId);

        $data = [
            'page_title' => 'จัดการดาวน์โหลด - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'downloads' => $downloads,
        ];

        return view('admin/programs/downloads', $data);
    }

    /**
     * Upload download
     */
    public function uploadDownload($programId)
    {
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $program['chair_personnel_id'] == $userId;
        if (!$isChair) {
            $coordinatorId = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId);
            $isChair = $coordinatorId === $userId;
        }

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการดาวน์โหลด');
        }

        $validationRules = [
            'title' => 'required|max_length[255]',
            'file' => 'uploaded_file|ext_in[pdf,doc,docx,xlsx,ppt,pptx,zip,rar,jpg,jpeg,png,gif,mp4,mp3,txt]',
            'file_type' => 'required|max_length[50]',
        ];

        if (!$this->validate($validationRules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $file = $this->request->getFile('file');

        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with('error', 'ไฟล์ไม่ถูกต้องหรือเสามารไม่สนับ');
        }

        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx', 'xlsx', 'pptx', 'ppt', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'txt'];
        $fileType = strtolower(pathinfo($file->getClientExtension()));

        if (!in_array($fileType, $allowedTypes)) {
            return redirect()->back()->withInput()->with('error', 'ประเภทที่ไม่อนุญการอนุญา');
        }

        // Upload file
        $uploadPath = WRITEPATH . 'uploads/programs/' . $programId;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileName = $file->getRandomName();
        $filePath = $uploadPath . '/' . $fileName;

        try {
            $file->move($filePath);

            // Save to database
            $downloadData = [
                'program_id' => $programId,
                'title' => $this->request->getPost('title'),
                'file_path' => 'uploads/programs/' . $programId . '/' . $fileName,
                'file_type' => $fileType,
                'file_size' => $file->getSize(),
                'sort_order' => 0,
            ];

            $downloadId = $this->programDownloadModel->addDownload($programId, $downloadData);

            return redirect()->back()->with('success', 'อัปโหลดไฟล์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete download
     */
    public function deleteDownload($downloadId)
    {
        $download = $this->programDownloadModel->find($downloadId);
        if (!$download) {
            return redirect()->back()->with('error', 'ไม่พบข้อมไฟล');
        }

        $programId = $download['program_id'];

        // Check if user is chair of this program
        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;
        if (!$isChair) {
            $coordinatorId = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId);
            $isChair = $coordinatorId === $userId;
        }

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการดาวน์โหลด');
        }

        // Delete file from filesystem
        $filePath = WRITEPATH . $download['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        $this->programDownloadModel->deleteDownload($downloadId);

        return redirect()->back()->with('success', 'ลบไฟลเรียบร้อยแล้ว');
    }

    /**
     * Update download order
     */
    public function updateOrder($programId)
    {
        $downloadIds = $this->request->getPost('sort_order');

        if (is_array($downloadIds)) {
            foreach ($downloadIds as $index => $downloadId) {
                $this->programDownloadModel->update($downloadId, ['sort_order' => $index + 1]);
            }
        }

        return redirect()->back()->with('success', 'อัปเรียงลำดับลำดับ');
    }

    /**
     * Toggle publish status
     */
    public function togglePublish($programId)
    {
        $programPage = $this->programPageModel->findByProgramId($programId);
        if (!$programPage) {
            return redirect()->back()->with('error', 'ไม่พบหน้าเว็บไซต์หลักสูตร');
        }

        $userId = session()->get('admin_id');
        $isChair = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId) === $userId;
        if (!$isChair) {
            $coordinatorId = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId);
            $isChair = $coordinatorId === $userId;
        }

        if (!$isChair) {
            return redirect()->back()->with('error', 'คุณณมีสิทธิ์เพียงการจัดการหน้าเว็บไซต์หลักสูตร');
        }

        $isPublished = $programPage['is_published'] ?? 0;
        $newStatus = $isPublished ? 0 : 1;

        $this->programPageModel->update($programId, ['is_published' => $newStatus]);

        $status = $newStatus ? 'เผลิดแล้ว' : 'ซ่งการแสดง';

        return redirect()->back()->with('success', $status . 'หน้าเว็บไซต์หลักสูตรแล้ว');
    }
}

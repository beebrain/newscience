<?php

namespace App\Controllers\Admin\ProgramAdmin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Models\ProgramDownloadModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use App\Models\NewsImageModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $programModel;
    protected $programPageModel;
    protected $programDownloadModel;
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $newsModel;
    protected $newsTagModel;
    protected $newsImageModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->programPageModel = new ProgramPageModel();
        $this->programDownloadModel = new ProgramDownloadModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->newsModel = new NewsModel();
        $this->newsTagModel = new NewsTagModel();
        $this->newsImageModel = new NewsImageModel();
    }

    /**
     * ดึง user ปัจจุบันจาก session (ตาราง user)
     */
    protected function getCurrentUser(): ?array
    {
        $userId = session()->get('admin_id');
        if ($userId === null) {
            return null;
        }
        $userModel = new UserModel();
        return $userModel->find((int) $userId);
    }

    /**
     * รายการ program_id ที่ user มีสิทธิ์จัดการ (สำหรับ program-admin)
     * ใช้เฉพาะ Personnel + personnel_programs: หา personnel จาก user email แล้วดึงหลักสูตรจาก personnel_programs
     */
    protected function getUserProgramIds(array $user): array
    {
        $email = trim((string) ($user['email'] ?? ''));
        if ($email === '') {
            return [];
        }
        $personnel = $this->personnelModel->findByUserEmail($email);
        if (!$personnel || empty($personnel['id'])) {
            return [];
        }
        $rows = $this->personnelProgramModel->getByPersonnelId((int) $personnel['id']);
        $ids = array_map(fn($r) => (int) $r['program_id'], $rows);
        return array_values(array_unique($ids));
    }

    /**
     * Check if user can manage this program
     * super_admin: ได้ทุกหลักสูตร
     * admin/editor: ได้หลักสูตรจาก personnel_programs (ตาม personnel ที่เชื่อม user email)
     * อื่นๆ: หลักสูตรจาก personnel_programs + หลักสูตรที่ตนเป็นประธาน/ผู้ประสาน
     */
    protected function canManageProgram(int $programId): bool
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        $userRole = session()->get('admin_role');

        if ($userRole === 'super_admin') {
            return true;
        }

        $userProgramIds = $this->getUserProgramIds($user);

        if ($userRole === 'admin') {
            return empty($userProgramIds) || in_array((int) $programId, $userProgramIds, true);
        }

        if ($userRole === 'editor') {
            return empty($userProgramIds) || in_array((int) $programId, $userProgramIds, true);
        }

        if (in_array((int) $programId, $userProgramIds, true)) {
            return true;
        }

        // สิทธิ์จาก personnel: ประธานหลักสูตร / ผู้ประสาน
        $coordinatorId = $this->personnelProgramModel->getCoordinatorIdByProgramId($programId);
        if ($coordinatorId !== null && (int) $coordinatorId === (int) $user['uid']) {
            return true;
        }
        $program = $this->programModel->find($programId);
        if ($program && !empty($program['chair_personnel_id']) && (int) $program['chair_personnel_id'] === (int) $user['uid']) {
            return true;
        }

        return false;
    }

    /**
     * Dashboard - แสดงหลักสูตรตามสิทธิ์ (ใช้เฉพาะ personnel_programs ตาม personnel ที่เชื่อม user email)
     * super_admin: แสดงทุกหลักสูตร
     * admin/editor: หลักสูตรที่ user สังกัดจาก personnel_programs (Personnel)
     * อื่นๆ: หลักสูตรจาก personnel_programs + หลักสูตรที่ตนเป็นประธาน/ผู้ประสาน
     */
    public function index()
    {
        $user = $this->getCurrentUser();
        if (!$user) {
            return redirect()->to(base_url('/admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }
        $userRole = session()->get('admin_role');
        $userProgramIds = $this->getUserProgramIds($user);

        $allPrograms = $this->programModel->getWithCoordinator();

        if ($userRole === 'super_admin') {
            $programs = $allPrograms;
            $pageTitle = 'จัดการหลักสูตร (ผู้ดูแลระบบ)';
        } elseif ($userRole === 'admin') {
            if (!empty($userProgramIds)) {
                $programs = array_filter($allPrograms, fn($p) => in_array((int) $p['id'], $userProgramIds, true));
                $pageTitle = 'จัดการหลักสูตร';
            } else {
                $programs = $allPrograms;
                $pageTitle = 'จัดการหลักสูตร (ผู้ดูแลระบบ)';
            }
        } elseif ($userRole === 'editor') {
            if (!empty($userProgramIds)) {
                $programs = array_filter($allPrograms, fn($p) => in_array((int) $p['id'], $userProgramIds, true));
            } else {
                $programs = $allPrograms;
            }
            $pageTitle = 'จัดการหลักสูตร';
        } else {
            $allowedIds = array_values($userProgramIds);
            $coordinatorMap = $this->personnelProgramModel->getAllCoordinators();
            foreach ($coordinatorMap as $pid => $personnelId) {
                if ((int) $personnelId === (int) $user['uid']) {
                    $allowedIds[] = (int) $pid;
                }
            }
            foreach ($allPrograms as $p) {
                if (!empty($p['chair_personnel_id']) && (int) $p['chair_personnel_id'] === (int) $user['uid']) {
                    $allowedIds[] = (int) $p['id'];
                }
            }
            $allowedIds = array_unique($allowedIds);
            $programs = array_filter($allPrograms, fn($p) => in_array((int) $p['id'], $allowedIds, true));
            $pageTitle = 'จัดการหลักสูตร';
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
            'page_title' => $pageTitle,
            'programs' => $programs,
            'program_pages' => $programPages,
            'total_programs' => count($programs),
            'published_pages' => count(array_filter($programPages, fn($p) => $p['is_published'] ?? false)),
            'user_role' => $userRole,
            'layout' => 'admin/layouts/admin_layout',
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

        // Check authorization
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
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
            'page_title' => 'แก้ไขเนื้อหา - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'program_page' => $programPage,
            'downloads' => $downloads,
            'coordinator' => $coordinator,
            'personnel_list' => $personnelList,
            'personnel_programs' => $personnelPrograms,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/programs/edit_content', $data);
    }

    /**
     * Update program content
     */
    public function update($programId)
    {
        $isAjax = $this->request->isAJAX();
        $programId = (int) $programId;

        $program = $this->programModel->find($programId);
        if (!$program) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหลักสูตร'])->setStatusCode(404);
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        if (!$this->canManageProgram($programId)) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้'])->setStatusCode(403);
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }

        $rules = [
            'name_th' => 'required|min_length[1]|max_length[255]',
            'name_en' => 'max_length[255]',
            'level' => 'required|in_list[bachelor,master,doctorate]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name_th' => $this->request->getPost('name_th'),
            'name_en' => $this->request->getPost('name_en'),
            'level' => $this->request->getPost('level'),
            'status' => $this->request->getPost('status'),
        ];

        try {
            $this->programModel->update($programId, $updateData);

            if ($isAjax) return $this->response->setJSON(['success' => true, 'message' => 'อัปเดตข้อมูลหลักสูตรเรียบร้อยแล้ว']);
            return redirect()->to(base_url('program-admin/'))->with('success', 'อัปเดตข้อมูลหลักสูตรเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->setStatusCode(500);
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Update program page content
     */
    public function updatePage($programId)
    {
        $isAjax = $this->request->isAJAX();
        $programId = (int) $programId;

        $program = $this->programModel->find($programId);
        if (!$program) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหลักสูตร'])->setStatusCode(404);
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        if (!$this->canManageProgram($programId)) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้'])->setStatusCode(403);
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }

        $rules = [
            'philosophy' => 'max_length[5000]',
            'objectives' => 'max_length[5000]',
            'graduate_profile' => 'max_length[5000]',
            'elos_json' => 'max_length[65000]',
            'curriculum_json' => 'max_length[65000]',
            'curriculum_structure' => 'max_length[10000]',
            'study_plan' => 'max_length[10000]',
            'career_prospects' => 'max_length[5000]',
            'tuition_fees' => 'max_length[5000]',
            'admission_info' => 'max_length[5000]',
            'contact_info' => 'max_length[5000]',
            'intro_video_url' => 'max_length[500]',
            'meta_description' => 'max_length[500]',
        ];

        if (!$this->validate($rules)) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => implode(', ', $this->validator->getErrors())]);
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'philosophy' => $this->request->getPost('philosophy'),
            'objectives' => $this->request->getPost('objectives'),
            'graduate_profile' => $this->request->getPost('graduate_profile'),
            'elos_json' => $this->request->getPost('elos_json'),
            'curriculum_json' => $this->request->getPost('curriculum_json'),
            'curriculum_structure' => $this->request->getPost('curriculum_structure'),
            'study_plan' => $this->request->getPost('study_plan'),
            'career_prospects' => $this->request->getPost('career_prospects'),
            'tuition_fees' => $this->request->getPost('tuition_fees'),
            'admission_info' => $this->request->getPost('admission_info'),
            'contact_info' => $this->request->getPost('contact_info'),
            'intro_video_url' => $this->request->getPost('intro_video_url'),
            'meta_description' => $this->request->getPost('meta_description'),
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];

        if ($this->request->getPost('hero_image_remove')) {
            $updateData['hero_image'] = '';
        } else {
            $heroFile = $this->request->getFile('hero_image');
            if ($heroFile && $heroFile->isValid() && ! $heroFile->hasMoved()) {
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $ext = strtolower($heroFile->getExtension() ?: pathinfo($heroFile->getClientName(), PATHINFO_EXTENSION));
                if (in_array($ext, $allowed, true)) {
                    helper('program_upload');
                    $uploadPath = program_upload_path($programId, 'hero');
                    $filename = 'hero_' . program_unique_filename($heroFile, 'img');
                    $relativePath = program_upload_relative_path($programId, 'hero', $filename);
                    if ($heroFile->move($uploadPath, $filename)) {
                        $updateData['hero_image'] = $relativePath;
                    }
                }
            }
        }

        try {
            $this->programPageModel->updateOrCreate(['program_id' => $programId], $updateData);
            if ($isAjax) return $this->response->setJSON(['success' => true, 'message' => 'อัปเดตเนื้อหาเรียบร้อยแล้ว']);
            return redirect()->to(base_url('program-admin/edit/' . $programId))->with('success', 'อัปเดตเนื้อหาเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            if ($isAjax) return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->setStatusCode(500);
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * บันทึกเฉพาะ elos_json และ/หรือ curriculum_json ผ่าน Ajax (สำหรับ AUN-QA)
     * POST program-admin/update-page-json/{id}
     * Body: elos_json (optional), curriculum_json (optional), curriculum_structure (optional)
     */
    public function updatePageJson($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหลักสูตร'])->setStatusCode(404);
        }
        if (!$this->canManageProgram($programId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการหลักสูตรนี้'])->setStatusCode(403);
        }

        $updateData = [];
        if ($this->request->getPost('elos_json') !== null) {
            $raw = $this->request->getPost('elos_json');
            if (is_string($raw) && strlen($raw) <= 65000) {
                $updateData['elos_json'] = $raw;
            }
        }
        if ($this->request->getPost('curriculum_json') !== null) {
            $raw = $this->request->getPost('curriculum_json');
            if (is_string($raw) && strlen($raw) <= 65000) {
                $updateData['curriculum_json'] = $raw;
            }
        }
        if ($this->request->getPost('curriculum_structure') !== null) {
            $raw = $this->request->getPost('curriculum_structure');
            if (is_string($raw) && strlen($raw) <= 10000) {
                $updateData['curriculum_structure'] = $raw;
            }
        }

        if (empty($updateData)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีข้อมูลที่จะบันทึก']);
        }

        try {
            $this->programPageModel->updateOrCreate(['program_id' => $programId], $updateData);
            return $this->response->setJSON(['success' => true, 'message' => 'บันทึกเรียบร้อยแล้ว']);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    /**
     * Update website settings (text color, background color) for program page.
     * POST program-admin/update-website/{id}
     */
    public function updateWebsite($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }

        $themeColor = trim((string) $this->request->getPost('theme_color'));
        $textColor = trim((string) $this->request->getPost('text_color'));
        $bgColor = trim((string) $this->request->getPost('background_color'));

        if ($themeColor !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $themeColor)) {
            $themeColor = '#1e40af';
        }
        if ($textColor !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $textColor)) {
            $textColor = '';
        }
        if ($bgColor !== '' && !preg_match('/^#[0-9A-Fa-f]{6}$/', $bgColor)) {
            $bgColor = '';
        }

        $updateData = [
            'theme_color' => $themeColor ?: '#1e40af',
            'text_color' => $textColor ?: null,
            'background_color' => $bgColor ?: null,
        ];

        try {
            $this->programPageModel->updateOrCreate(['program_id' => $programId], $updateData);
            return redirect()->to(base_url('program-admin/edit/' . $programId) . '?tab=website')
                ->with('success', 'บันทึกการตั้งค่าเว็บไซต์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Upload hero image only (สำหรับ drag-drop + crop จากแท็บข้อมูลพื้นฐาน).
     * POST program-admin/upload-hero/{id}
     * Body: hero_image (file), หรือ hero_image_remove=1 เพื่อลบ
     */
    public function uploadHero($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบหลักสูตร'])->setStatusCode(404);
        }
        if (!$this->canManageProgram($programId)) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่มีสิทธิ์จัดการหลักสูตรนี้'])->setStatusCode(403);
        }

        if ($this->request->getPost('hero_image_remove')) {
            try {
                $this->programPageModel->updateOrCreate(['program_id' => $programId], ['hero_image' => '']);
                return $this->response->setJSON(['success' => true, 'message' => 'ลบรูปหน้าปกแล้ว', 'hero_url' => '']);
            } catch (\Exception $e) {
                return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
            }
        }

        $file = $this->request->getFile('hero_image');
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON(['success' => false, 'message' => 'กรุณาเลือกไฟล์รูปภาพ'])->setStatusCode(400);
        }
        $ext = strtolower($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allowed, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'รองรับเฉพาะ JPG, PNG, GIF, WEBP'])->setStatusCode(400);
        }

        helper('program_upload');
        $uploadPath = program_upload_path($programId, 'hero');
        $filename = 'hero_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
        $relativePath = program_upload_relative_path($programId, 'hero', $filename);

        if (!$file->move($uploadPath, $filename)) {
            return $this->response->setJSON(['success' => false, 'message' => 'บันทึกไฟล์ไม่สำเร็จ'])->setStatusCode(500);
        }

        try {
            $this->programPageModel->updateOrCreate(['program_id' => $programId], ['hero_image' => $relativePath]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()])->setStatusCode(500);
        }

        $heroUrl = base_url('serve/uploads/' . ltrim(str_replace('\\', '/', $relativePath), '/'));
        return $this->response->setJSON(['success' => true, 'message' => 'อัปโหลดรูปหน้าปกเรียบร้อย', 'hero_url' => $heroUrl]);
    }

    /**
     * Preview program page — เปิด Single Page จริง (/p/{id}/main)
     */
    public function preview($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }

        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }

        return redirect()->to(base_url('p/' . $programId . '/main'))->with('preview', true);
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

        // Check authorization
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการดาวน์โหลด');
        }

        $downloads = $this->programDownloadModel->getByProgramId($programId);

        $data = [
            'page_title' => 'จัดการดาวน์โหลด - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'downloads' => $downloads,
            'layout' => 'admin/layouts/admin_layout',
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
            return redirect()->back()->with('error', 'คุณมีสิทธิ์เพียงการดาวน์โหลด');
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
            return redirect()->back()->with('error', 'ไฟล์ไม่ถูกต้องหรือส่งมาไม่สมบูรณ์');
        }

        // Validate file type
        $allowedTypes = ['pdf', 'doc', 'docx', 'xlsx', 'pptx', 'ppt', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'txt'];
        $fileType = strtolower($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));

        if (!in_array($fileType, $allowedTypes)) {
            return redirect()->back()->withInput()->with('error', 'ประเภทที่ไม่อนุญาต');
        }

        helper('program_upload');
        $uploadPath = program_upload_path($programId, 'downloads');
        $prefix = 'p' . $programId . '_';
        $fileName = $prefix . program_unique_filename($file, 'doc');
        $relativePath = program_upload_relative_path($programId, 'downloads', $fileName);

        try {
            $file->move($uploadPath, $fileName);

            // Save to database (store path relative to uploads/ for serve URL)
            $downloadData = [
                'program_id' => $programId,
                'title' => $this->request->getPost('title'),
                'file_path' => $relativePath,
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
            return redirect()->back()->with('error', 'ไม่พบข้อมูลไฟล์');
        }

        $programId = $download['program_id'];

        // Check authorization
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการดาวน์โหลด');
        }

        helper('program_upload');
        $fullPath = upload_resolve_full_path($download['file_path']);
        if (file_exists($fullPath)) {
            @unlink($fullPath);
        }

        // Delete from database
        $this->programDownloadModel->deleteDownload($downloadId);

        return redirect()->back()->with('success', 'ลบไฟล์เรียบร้อยแล้ว');
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

        // Check authorization
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหน้าเว็บไซต์หลักสูตร');
        }

        $isPublished = $programPage['is_published'] ?? 0;
        $newStatus = $isPublished ? 0 : 1;

        $this->programPageModel->update($programPage['id'], ['is_published' => $newStatus]);

        $status = $newStatus ? 'เผยแพร่' : 'ซ่อนการแสดง';

        return redirect()->back()->with('success', $status . 'หน้าเว็บไซต์หลักสูตรแล้ว');
    }

    /**
     * Get news list for program (by tag program_{id}). JSON for tab.
     */
    public function programNews($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return $this->response->setJSON(['success' => false, 'data' => []])->setStatusCode(404);
        }
        if (!$this->canManageProgram($programId)) {
            return $this->response->setJSON(['success' => false, 'data' => []])->setStatusCode(403);
        }
        $tagSlug = 'program_' . $programId;
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        helper('program_upload');
        $list = $this->newsModel->getPublishedByTag($tagSlug, 50, 0);
        foreach ($list as &$n) {
            $n['created_at_formatted'] = $n['created_at'] ? date('d/m/Y', strtotime($n['created_at'])) : '';
            $n['url'] = base_url('news/' . $n['id']);
            if (!empty($n['featured_image'])) {
                $n['thumb_url'] = featured_image_serve_url($n['featured_image'], true);
            } else {
                $n['thumb_url'] = null;
            }
        }
        return $this->response->setJSON(['success' => true, 'data' => $list]);
    }

    /**
     * Create news for program (tag program_{id} set by default). Uploads to writable/uploads/news/ ใช้รหัสหลักสูตรในชื่อไฟล์ (p1_xxx) แก้ที่ admin/news/edit ก็เห็นรูป
     */
    public function createProgramNews($programId)
    {
        $programId = (int) $programId;
        $program = $this->programModel->find($programId);
        if (!$program) {
            return redirect()->back()->with('error', 'ไม่พบหลักสูตร');
        }
        if (!$this->canManageProgram($programId)) {
            return redirect()->back()->with('error', 'คุณไม่มีสิทธิ์จัดการหลักสูตรนี้');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[500]',
            'content' => 'required',
            'status' => 'required|in_list[draft,published]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        helper('program_upload');
        $title = $this->request->getPost('title');
        $slug = $this->newsModel->generateSlug($title);
        $newsData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $this->request->getPost('status'),
            'display_as_event' => (int) $this->request->getPost('display_as_event'),
            'author_id' => session()->get('admin_id'),
            'published_at' => $this->request->getPost('status') === 'published' ? date('Y-m-d H:i:s') : null,
        ];
        $newsId = $this->newsModel->insert($newsData);
        if (!$newsId) {
            return redirect()->back()->withInput()->with('error', 'บันทึกข่าวไม่สำเร็จ');
        }

        $programTag = $this->newsTagModel->findOrCreateForProgram($programId, $program['name_th'] ?? $program['name_en']);
        if ($programTag) {
            $this->newsTagModel->setTagsForNews((int) $newsId, [(int) $programTag['id']]);
        }

        $uploadPath = program_upload_path($programId, 'news');
        $prefix = 'p' . $programId . '_';
        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            $ext = strtolower($featuredImage->getExtension()) ?: 'jpg';
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $filename = featured_image_filename($featuredImage, $programId, null);
                $relativePath = program_upload_relative_path($programId, 'news', $filename);
                try {
                    $featuredImage->move($uploadPath, $filename);
                    $fullPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
                    program_create_image_thumbnail($fullPath);
                    $this->newsModel->update($newsId, ['featured_image' => $relativePath]);
                } catch (\Throwable $e) {
                    log_message('error', 'Program news featured image: ' . $e->getMessage());
                }
            }
        }

        $files = $this->request->getFiles();
        $sortOrder = 0;
        foreach (['attachments_images' => 'image', 'attachments_docs' => 'document'] as $inputName => $type) {
            if (empty($files[$inputName])) {
                continue;
            }
            $list = is_array($files[$inputName]) ? $files[$inputName] : [$files[$inputName]];
            foreach ($list as $file) {
                if (!$file->isValid() || $file->hasMoved()) {
                    continue;
                }
                $ext = strtolower($file->getExtension());
                $allowedImg = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                $allowedDoc = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
                $ok = ($type === 'image' && in_array($ext, $allowedImg, true)) || ($type === 'document' && in_array($ext, $allowedDoc, true));
                if (!$ok) {
                    continue;
                }
                $filename = $prefix . program_unique_filename($file, $type === 'document' ? 'doc' : 'img');
                $relativePath = program_upload_relative_path($programId, 'news', $filename);
                try {
                    $file->move($uploadPath, $filename);
                    if ($type === 'image') {
                        $fullPath = rtrim($uploadPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
                        program_create_image_thumbnail($fullPath);
                    }
                    $this->newsImageModel->addAttachment($newsId, $relativePath, $type, $type === 'document' ? $file->getClientName() : null, $sortOrder);
                    $sortOrder++;
                } catch (\Throwable $e) {
                    log_message('error', 'Program news attachment: ' . $e->getMessage());
                }
            }
        }

        return redirect()->to(base_url('program-admin/edit/' . $programId) . '?tab=news')
            ->with('success', 'เพิ่มข่าวหลักสูตรเรียบร้อยแล้ว');
    }
}

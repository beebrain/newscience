<?php

namespace App\Controllers\Admin\ProgramAdmin;

use App\Controllers\BaseController;
use App\Models\ProgramModel;
use App\Models\ProgramContentBlockModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;

class ContentBuilder extends BaseController
{
    protected $programModel;
    protected $contentBlockModel;
    protected $personnelModel;
    protected $personnelProgramModel;

    public function __construct()
    {
        $this->programModel = new ProgramModel();
        $this->contentBlockModel = new ProgramContentBlockModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
    }

    /**
     * Content Builder Dashboard
     */
    public function index($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program; // Redirect response
        }

        $blocks = $this->contentBlockModel->getByProgramId($programId);

        $data = [
            'page_title' => 'Content Builder - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'blocks' => $blocks,
            'total_blocks' => count($blocks),
            'published_blocks' => count(array_filter($blocks, fn($b) => $b['is_published'])),
            'active_blocks' => count(array_filter($blocks, fn($b) => $b['is_active'])),
        ];

        return view('admin/programs/content_builder', $data);
    }

    /**
     * Get blocks as JSON (for AJAX)
     */
    public function getBlocks($programId)
    {
        $this->checkProgramAuth($programId);

        $blocks = $this->contentBlockModel->getByProgramId($programId);
        return $this->response->setJSON($blocks);
    }

    /**
     * Create new block
     */
    public function createBlock($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program;
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
            'block_type' => 'required|in_list[html,css,js,wysiwyg,markdown]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $blockKey = $this->contentBlockModel->generateBlockKey($programId, $title);

        $data = [
            'program_id' => $programId,
            'block_key' => $blockKey,
            'block_type' => $this->request->getPost('block_type'),
            'title' => $title,
            'content' => '',
            'sort_order' => $this->contentBlockModel->countByProgramId($programId) + 1,
            'is_active' => 0,
            'is_published' => 0,
        ];

        try {
            $blockId = $this->contentBlockModel->insert($data);
            return redirect()->to(base_url('program-admin/content-builder/block/' . $blockId . '/edit'))
                ->with('success', 'สร้างบล็อกใหม่เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Edit block
     */
    public function editBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        $data = [
            'page_title' => 'Edit Block: ' . $block['title'],
            'program' => $program,
            'block' => $block,
        ];

        return view('admin/programs/block_editor', $data);
    }

    /**
     * Update block
     */
    public function updateBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        $rules = [
            'title' => 'required|min_length[1]|max_length[255]',
            'content' => 'permit_empty',
            'custom_css' => 'permit_empty',
            'custom_js' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'content' => $this->request->getPost('content'),
            'custom_css' => $this->request->getPost('custom_css'),
            'custom_js' => $this->request->getPost('custom_js'),
        ];

        try {
            $this->contentBlockModel->update($blockId, $data);
            return redirect()->back()->with('success', 'บันทึกบล็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Delete block
     */
    public function deleteBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        try {
            $this->contentBlockModel->delete($blockId);
            return redirect()->to(base_url('program-admin/content-builder/' . $block['program_id']))
                ->with('success', 'ลบบล็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Reorder blocks
     */
    public function reorderBlocks($programId)
    {
        $this->checkProgramAuth($programId);

        $blockIds = $this->request->getPost('block_ids');
        if (!is_array($blockIds)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid data']);
        }

        try {
            $this->contentBlockModel->updateSortOrder($programId, $blockIds);
            return $this->response->setJSON(['success' => true]);
        } catch (\Exception $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Toggle block active status
     */
    public function toggleBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        try {
            $this->contentBlockModel->toggleActive($blockId);
            $status = $block['is_active'] ? 'ปิดการใช้งาน' : 'เปิดการใช้งาน';
            return redirect()->back()->with('success', $status . 'บล็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Toggle block published status
     */
    public function togglePublishBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        try {
            $this->contentBlockModel->togglePublished($blockId);
            $status = $block['is_published'] ? 'ยกเลิกการเผยแพร่' : 'เผยแพร่';
            return redirect()->back()->with('success', $status . 'บล็อกเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate block
     */
    public function duplicateBlock($blockId)
    {
        $block = $this->contentBlockModel->find($blockId);
        if (!$block) {
            return redirect()->back()->with('error', 'ไม่พบบล็อก');
        }

        $program = $this->getProgramWithAuth($block['program_id']);
        if (!is_array($program)) {
            return $program;
        }

        try {
            $newBlockId = $this->contentBlockModel->duplicateBlock($blockId);
            if ($newBlockId) {
                return redirect()->to(base_url('program-admin/content-builder/' . $block['program_id']))
                    ->with('success', 'คัดลอกบล็อกเรียบร้อยแล้ว');
            }
            return redirect()->back()->with('error', 'ไม่สามารถคัดลอกบล็อกได้');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }

    /**
     * Live preview
     */
    public function livePreview($programId)
    {
        $program = $this->getProgramWithAuth($programId);
        if (!is_array($program)) {
            return $program;
        }

        // Get active blocks (for preview, show active blocks even if not published)
        $blocks = $this->contentBlockModel->getActiveByProgramId($programId);
        $css = $this->contentBlockModel->getCombinedCss($programId);
        $js = $this->contentBlockModel->getCombinedJs($programId);

        $data = [
            'page_title' => 'Live Preview - ' . ($program['name_th'] ?? $program['name_en']),
            'program' => $program,
            'blocks' => $blocks,
            'custom_css' => $css,
            'custom_js' => $js,
        ];

        return view('admin/programs/live_preview', $data);
    }

    /**
     * Check if user is chair of program
     */
    protected function checkProgramAuth(int $programId): bool
    {
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
     * Get program with auth check - returns array or redirect response
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

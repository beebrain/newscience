<?php

namespace App\Controllers;

use App\Models\SiteSettingModel;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Models\ProgramDownloadModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\NewsModel;

/**
 * Public program page controller — แยกจาก Pages เพื่อไม่กระทบ controller เดิม
 * URL: /program/{id}
 */
class ProgramController extends BaseController
{
    protected $siteSettingModel;
    protected $programModel;
    protected $programPageModel;
    protected $programDownloadModel;
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $newsModel;

    public function __construct()
    {
        $this->siteSettingModel      = new SiteSettingModel();
        $this->programModel          = new ProgramModel();
        $this->programPageModel      = new ProgramPageModel();
        $this->programDownloadModel  = new ProgramDownloadModel();
        $this->personnelModel        = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->newsModel             = new NewsModel();
    }

    /**
     * Common site data (settings + layout) — same pattern as Pages controller
     */
    protected function getCommonData(): array
    {
        $settings = $this->siteSettingModel->getAll();
        $layout   = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        return [
            'settings'  => $settings,
            'site_info' => $settings,
            'layout'    => $layout,
        ];
    }

    /**
     * แสดงหน้าหลักสูตร
     * GET /program/{id}
     */
    public function show($id = null): string
    {
        $id = (int) $id;

        // --- โหลดข้อมูลหลักสูตร ---
        $program = null;
        if ($id > 0) {
            $program = $this->programModel->find($id);
        }

        // ถ้าไม่เจอ หรือ ไม่ active → แสดงหน้า "ไม่พบหลักสูตร"
        if (!$program || ($program['status'] ?? '') !== 'active') {
            $data = array_merge($this->getCommonData(), [
                'page_title' => 'ไม่พบหลักสูตร',
                'program'    => null,
                'page'       => [],
                'news'       => [],
                'personnel'  => [],
                'downloads'  => [],
                'programDownloadModel' => $this->programDownloadModel,
            ]);
            return view('pages/program', $data);
        }

        // --- Page content (program_pages) ---
        $page = $this->programPageModel->findByProgramId($id) ?? [];

        // --- บุคลากรในหลักสูตร ---
        $personnel = $this->buildPersonnelList($id);

        // --- ไฟล์ดาวน์โหลด ---
        $downloads = $this->programDownloadModel->getByProgramId($id);

        // --- ข่าวที่เกี่ยวข้อง (tag: program_{id}) ---
        $news = $this->loadProgramNews($id);

        // --- รวมข้อมูลส่งไป view ---
        $siteName = $this->siteSettingModel->getAll()['site_name_th'] ?? '';

        $data = array_merge($this->getCommonData(), [
            'page_title'           => ($program['name_th'] ?? 'หลักสูตร') . ' | ' . $siteName,
            'meta_description'     => $page['meta_description'] ?? ('หลักสูตร' . ($program['name_th'] ?? '')),
            'active_page'          => 'academics',
            'program'              => $program,
            'page'                 => $page,
            'news'                 => $news,
            'personnel'            => $personnel,
            'downloads'            => $downloads,
            'programDownloadModel' => $this->programDownloadModel,
        ]);

        return view('pages/program', $data);
    }

    // ------------------------------------------------------------------
    //  Private helpers
    // ------------------------------------------------------------------

    /**
     * โหลดบุคลากรในหลักสูตร พร้อม name, image, position, role_in_curriculum
     */
    private function buildPersonnelList(int $programId): array
    {
        $ppRows = $this->personnelProgramModel->getByProgramId($programId);
        if (empty($ppRows)) {
            return [];
        }

        $personnelIds = array_map(fn($r) => (int) $r['personnel_id'], $ppRows);
        $roleMap = [];
        foreach ($ppRows as $row) {
            $roleMap[(int) $row['personnel_id']] = $row['role_in_curriculum'] ?? '';
        }

        // โหลด personnel พร้อม user join (ได้ name, image ที่ถูกต้อง)
        $rows = $this->personnelModel->getActiveByIdsWithUser($personnelIds);

        $list = [];
        foreach ($rows as $p) {
            $pid = (int) ($p['id'] ?? 0);
            $list[] = [
                'name'               => trim($p['name'] ?? ''),
                'image'              => trim($p['image'] ?? ''),
                'position'           => trim($p['position'] ?? ''),
                'role_in_curriculum' => $roleMap[$pid] ?? '',
            ];
        }

        // เรียงประธานหลักสูตรขึ้นก่อน
        usort($list, function ($a, $b) {
            $aChair = mb_strpos($a['role_in_curriculum'], 'ประธาน') !== false ? 0 : 1;
            $bChair = mb_strpos($b['role_in_curriculum'], 'ประธาน') !== false ? 0 : 1;
            return $aChair - $bChair;
        });

        return $list;
    }

    /**
     * โหลดข่าวที่เกี่ยวข้องกับหลักสูตร (tag slug = program_{id})
     */
    private function loadProgramNews(int $programId): array
    {
        try {
            $db = \Config\Database::connect();
            if ($db->tableExists('news_tags') && $db->tableExists('news_news_tags')) {
                return $this->newsModel->getPublishedByTag('program_' . $programId, 5);
            }
        } catch (\Throwable $e) {
            log_message('debug', 'ProgramController::loadProgramNews error: ' . $e->getMessage());
        }
        return [];
    }
}

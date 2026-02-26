<?php

namespace App\Controllers;

use App\Models\SiteSettingModel;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;

/**
 * Public program page controller
 * URL: /program/{id}
 * Data loading is handled client-side via /api/program/{id}
 */
class ProgramController extends BaseController
{
    protected $siteSettingModel;
    protected $programModel;
    protected $programPageModel;

    public function __construct()
    {
        $this->siteSettingModel = new SiteSettingModel();
        $this->programModel    = new ProgramModel();
        $this->programPageModel = new ProgramPageModel();
    }

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
     * Render program page shell; JS loads data via API
     * GET /program/{id}
     */
    public function show($id = null): string
    {
        $id = (int) $id;

        $program = null;
        if ($id > 0) {
            $program = $this->programModel->find($id);
        }

        if (!$program || ($program['status'] ?? '') !== 'active') {
            $data = array_merge($this->getCommonData(), [
                'page_title' => 'ไม่พบหลักสูตร',
                'program'    => null,
                'page'       => [],
            ]);
            return view('pages/program', $data);
        }

        $page = $this->programPageModel->findByProgramId($id) ?? [];
        $siteName = $this->siteSettingModel->getAll()['site_name_th'] ?? '';

        $data = array_merge($this->getCommonData(), [
            'page_title'       => ($program['name_th'] ?? 'หลักสูตร') . ' | ' . $siteName,
            'meta_description' => $page['meta_description'] ?? ('หลักสูตร' . ($program['name_th'] ?? '')),
            'active_page'      => 'academics',
            'program'          => $program,
            'page'             => $page,
        ]);

        return view('pages/program', $data);
    }
}

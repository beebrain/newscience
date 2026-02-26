<?php

namespace App\Controllers;

use App\Models\SiteSettingModel;

/**
 * AUN-QA Program Detail Page — AJAX-driven single-page module
 * URL: /program-detail?program=slug
 */
class ProgramDetailController extends BaseController
{
    protected $siteSettingModel;

    public function __construct()
    {
        $this->siteSettingModel = new SiteSettingModel();
    }

    /**
     * Serve the HTML shell — JS handles data loading via AJAX / mock JSON
     */
    public function index(): string
    {
        $settings = $this->siteSettingModel->getAll();
        $layout   = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        $data = [
            'settings'         => $settings,
            'site_info'        => $settings,
            'layout'           => $layout,
            'page_title'       => 'รายละเอียดหลักสูตร | ' . ($settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี'),
            'meta_description' => 'ข้อมูลหลักสูตรตามเกณฑ์ AUN-QA คณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page'      => 'academics',
        ];

        return view('pages/program_detail', $data);
    }
}

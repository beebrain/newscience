<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\ProgramModel;
use App\Models\SiteSettingModel;
use App\Models\PersonnelModel;

class Home extends BaseController
{
    public function index(): string
    {
        $newsModel = new NewsModel();
        $programModel = new ProgramModel();
        $settingsModel = new SiteSettingModel();
        $personnelModel = new PersonnelModel();
        
        // Get site settings
        $settings = $settingsModel->getAll();
        
        // Get latest news (limit 6)
        $news = $newsModel->getPublished(6);
        
        // Get programs by level
        $bachelorPrograms = $programModel->getBachelor();
        $masterPrograms = $programModel->getMaster();
        $doctoratePrograms = $programModel->getDoctorate();
        
        // Get dean
        $dean = $personnelModel->getDean();
        
        $data = [
            'page_title' => $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี',
            'meta_description' => $settings['about_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'home',
            'settings' => $settings,
            'news' => $news,
            'bachelor_programs' => $bachelorPrograms,
            'master_programs' => $masterPrograms,
            'doctorate_programs' => $doctoratePrograms,
            'dean' => $dean,
        ];
        
        return view('pages/home', $data);
    }
}

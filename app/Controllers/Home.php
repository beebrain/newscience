<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\ProgramModel;
use App\Models\SiteSettingModel;
use App\Models\PersonnelModel;
use App\Models\HeroSlideModel;

class Home extends BaseController
{
    public function index(): string
    {
        $newsModel = new NewsModel();
        $programModel = new ProgramModel();
        $settingsModel = new SiteSettingModel();
        $personnelModel = new PersonnelModel();
        $heroSlideModel = new HeroSlideModel();

        // Get site settings
        $settings = $settingsModel->getAll();

        // News will be loaded via AJAX - don't load here to improve initial page load
        // Keep empty arrays for backward compatibility
        $generalNews = [];
        $studentNews = [];
        $researchNews = [];

        // Get programs by level
        $bachelorPrograms = $programModel->getBachelor();
        $masterPrograms = $programModel->getMaster();
        $doctoratePrograms = $programModel->getDoctorate();

        // Get dean
        $dean = $personnelModel->getDean();

        // Get active hero slides
        try {
            $heroSlides = $heroSlideModel->getActiveSlides();
        } catch (\Exception $e) {
            $heroSlides = [];
        }

        // Format hero slides for view
        $formattedSlides = [];
        foreach ($heroSlides as $slide) {
            $formattedSlides[] = [
                'image' => base_url($slide['image']),
                'title' => $slide['title'] ?? '',
                'subtitle' => $slide['subtitle'] ?? '',
                'description' => $slide['description'] ?? '',
                'link' => $slide['link'] ?? '',
                'link_text' => !empty($slide['link_text']) ? $slide['link_text'] : 'ดูรายละเอียด',
                'show_buttons' => (bool)($slide['show_buttons'] ?? false),
            ];
        }

        // Determine layout based on request type
        $layout = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        $data = [
            'layout' => $layout,
            'page_title' => $settings['site_name_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี',
            'meta_description' => $settings['about_th'] ?? 'คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'home',
            'settings' => $settings,
            'news' => $generalNews, // Keep for backward compatibility
            'general_news' => $generalNews,
            'student_news' => $studentNews,
            'research_news' => $researchNews,
            'bachelor_programs' => $bachelorPrograms,
            'master_programs' => $masterPrograms,
            'doctorate_programs' => $doctoratePrograms,
            'dean' => $dean,
            'hero_slides' => $formattedSlides,
        ];

        return view('pages/home', $data);
    }
}


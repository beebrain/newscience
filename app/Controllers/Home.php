<?php

namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\ProgramModel;
use App\Models\SiteSettingModel;
use App\Models\PersonnelModel;
use App\Models\HeroSlideModel;
use App\Models\EventModel;
use App\Libraries\OrganizationRoles;

class Home extends BaseController
{
    public function index(): string
    {
        $newsModel = new NewsModel();
        $programModel = new ProgramModel();
        $settingsModel = new SiteSettingModel();
        $personnelModel = new PersonnelModel();
        $heroSlideModel = new HeroSlideModel();
        $eventModel = new EventModel();

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

        // ทีมผู้บริหาร: ดึงจาก personnel (คณบดี = tier 1, รองคณบดี = tier 2)
        $personnel = $personnelModel->getActiveWithDepartment();
        $dean = null;
        $viceDeans = [];
        foreach ($personnel as $p) {
            $tier = OrganizationRoles::getTier(['position' => $p['position'] ?? '', 'position_en' => $p['position_en'] ?? '']);
            if ($tier === 1) {
                $dean = $p;
                break;
            }
        }
        foreach ($personnel as $p) {
            $tier = OrganizationRoles::getTier(['position' => $p['position'] ?? '', 'position_en' => $p['position_en'] ?? '']);
            if ($tier === 2) {
                $viceDeans[] = $p;
            }
        }
        usort($viceDeans, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));

        // Get upcoming events from events table (for "กิจกรรมที่จะมาถึง" section)
        $upcomingEvents = [];
        try {
            $upcomingEvents = $eventModel->getUpcoming(4);
        } catch (\Exception $e) {
            $upcomingEvents = [];
        }

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
                'image' => $slide['image'] ? base_url('serve/uploads/hero/' . basename($slide['image'])) : '',
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
            'vice_deans' => $viceDeans,
            'hero_slides' => $formattedSlides,
            'upcoming_events' => $upcomingEvents,
        ];

        return view('pages/home', $data);
    }
}

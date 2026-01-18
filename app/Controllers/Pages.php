<?php

namespace App\Controllers;

use App\Models\SiteSettingModel;
use App\Models\NewsModel;
use App\Models\ProgramModel;
use App\Models\PersonnelModel;
use App\Models\DepartmentModel;

class Pages extends BaseController
{
    protected $siteSettingModel;
    protected $newsModel;
    protected $programModel;
    protected $personnelModel;
    protected $departmentModel;
    
    public function __construct()
    {
        $this->siteSettingModel = new SiteSettingModel();
        $this->newsModel = new NewsModel();
        $this->programModel = new ProgramModel();
        $this->personnelModel = new PersonnelModel();
        $this->departmentModel = new DepartmentModel();
    }
    
    /**
     * Get common site data for all pages
     */
    protected function getCommonData(): array
    {
        return [
            'site_info' => $this->siteSettingModel->getAll(),
        ];
    }
    
    public function about(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'เกี่ยวกับคณะ | ' . ($siteInfo['site_name_th'] ?? 'About'),
            'meta_description' => $siteInfo['vision_th'] ?? 'เรียนรู้เกี่ยวกับประวัติ ปรัชญา วิสัยทัศน์ และพันธกิจของคณะ',
            'active_page' => 'about',
            'vision' => $siteInfo['vision_th'] ?? '',
            'mission' => $siteInfo['mission_th'] ?? '',
            'philosophy' => $siteInfo['philosophy_th'] ?? '',
            'identity' => $siteInfo['identity_th'] ?? '',
            'history' => $siteInfo['history_th'] ?? '',
            'departments' => $this->departmentModel->getActive(),
        ]);
        
        return view('pages/about', $data);
    }
    
    public function academics(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        // Get programs by level
        $programs = $this->programModel->getWithDepartment();
        $bachelorPrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'bachelor');
        $masterPrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'master');
        $doctoratePrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'doctorate');
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'หลักสูตร | ' . ($siteInfo['site_name_th'] ?? 'Academics'),
            'meta_description' => 'หลักสูตรระดับปริญญาตรี ปริญญาโท และปริญญาเอก คณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'academics',
            'bachelor_programs' => $bachelorPrograms,
            'master_programs' => $masterPrograms,
            'doctorate_programs' => $doctoratePrograms,
            'total_programs' => count($programs),
            'departments' => $this->departmentModel->getActive(),
        ]);
        
        return view('pages/academics', $data);
    }
    
    public function research(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'งานวิจัย | ' . ($siteInfo['site_name_th'] ?? 'Research'),
            'meta_description' => 'งานวิจัยและนวัตกรรม คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'research',
        ]);
        
        return view('pages/research', $data);
    }
    
    public function campusLife(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'กิจกรรมนักศึกษา | ' . ($siteInfo['site_name_th'] ?? 'Campus Life'),
            'meta_description' => 'กิจกรรมนักศึกษาและชีวิตในมหาวิทยาลัย',
            'active_page' => 'campus-life',
        ]);
        
        return view('pages/campus_life', $data);
    }
    
    public function admission(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        // Get programs for admission page
        $programs = $this->programModel->getWithDepartment();
        $bachelorPrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'bachelor');
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'รับสมัครนักศึกษา | ' . ($siteInfo['site_name_th'] ?? 'Admission'),
            'meta_description' => 'เปิดรับสมัครนักศึกษาใหม่ คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'admission',
            'programs' => $bachelorPrograms,
        ]);
        
        return view('pages/admission', $data);
    }
    
    public function news(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        // Get all published news with pagination
        $perPage = 12;
        $newsModel = new NewsModel();
        $allNews = $newsModel->getPublished(100); // Get all news
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'ข่าวสาร | ' . ($siteInfo['site_name_th'] ?? 'News'),
            'meta_description' => 'ข่าวสารและประกาศล่าสุดจากคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'news',
            'news_items' => $allNews,
        ]);
        
        return view('pages/news', $data);
    }
    
    public function newsDetail($slug = null): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        if (!$slug) {
            return redirect()->to('/news');
        }
        
        $newsItem = $this->newsModel->where('slug', $slug)->first();
        
        if (!$newsItem) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Increment view count
        $this->newsModel->incrementViewCount($newsItem['id']);
        
        // Get related news
        $relatedNews = $this->newsModel->getPublished(4);
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => $newsItem['title'] . ' | ข่าวสาร',
            'meta_description' => $newsItem['excerpt'] ?? mb_substr($newsItem['title'], 0, 160),
            'active_page' => 'news',
            'news' => $newsItem,
            'related_news' => $relatedNews,
        ]);
        
        return view('pages/news_detail', $data);
    }
    
    public function events(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'กิจกรรม | ' . ($siteInfo['site_name_th'] ?? 'Events'),
            'meta_description' => 'กิจกรรมและโครงการต่างๆ ของคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'events',
        ]);
        
        return view('pages/events', $data);
    }
    
    public function contact(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'ติดต่อเรา | ' . ($siteInfo['site_name_th'] ?? 'Contact'),
            'meta_description' => 'ข้อมูลติดต่อ คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'contact',
            'phone' => $siteInfo['phone'] ?? '',
            'fax' => $siteInfo['fax'] ?? '',
            'email' => $siteInfo['email'] ?? '',
            'address_th' => $siteInfo['address_th'] ?? '',
            'address_en' => $siteInfo['address_en'] ?? '',
            'facebook' => $siteInfo['facebook'] ?? '',
            'website' => $siteInfo['website'] ?? '',
        ]);
        
        return view('pages/contact', $data);
    }
    
    public function personnel(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        
        // Get all personnel grouped by department
        $personnel = $this->personnelModel->getActive(100);
        $departments = $this->departmentModel->getActive();
        
        $data = array_merge($this->getCommonData(), [
            'page_title' => 'บุคลากร | ' . ($siteInfo['site_name_th'] ?? 'Personnel'),
            'meta_description' => 'บุคลากร คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'personnel',
            'personnel' => $personnel,
            'departments' => $departments,
        ]);
        
        return view('pages/personnel', $data);
    }
}

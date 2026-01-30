<?php

namespace App\Controllers;

use App\Libraries\FacultyPersonnelApi;
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
        $settings = $this->siteSettingModel->getAll();
        // Determine layout based on request type
        $layout = $this->request->isAJAX() ? 'layouts/ajax_layout' : 'layouts/main_layout';

        return [
            'settings' => $settings,
            'site_info' => $settings,
            'layout' => $layout,
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

    public function newsDetail($id = null): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        if (!$id || !is_numeric($id)) {
            return redirect()->to('/news');
        }

        $newsItem = $this->newsModel->find($id);

        if (!$newsItem) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Increment view count
        $this->newsModel->incrementViews($newsItem['id']);

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

        // Get all active personnel and departments
        $personnel = $this->personnelModel->getActive();
        $departments = $this->departmentModel->getActive();

        // Group personnel by department for "บุคลากรตามหลักสูตร" tab
        $personnelByDepartment = [];
        foreach ($departments as $dept) {
            $personnelByDepartment[$dept['id']] = [
                'department' => $dept,
                'personnel' => $this->personnelModel->getByDepartment($dept['id']),
            ];
        }
        // Personnel without department
        $noDept = array_filter($personnel, fn($p) => empty($p['department_id']));
        if (!empty($noDept)) {
            $personnelByDepartment['_none'] = [
                'department' => ['id' => null, 'name_th' => 'อื่นๆ', 'name_en' => 'Other'],
                'personnel' => $noDept,
            ];
        }

        // ดึงข้อมูลบุคลากรตามหลักสูตรจาก API (Research Record)
        $apiByCurriculum = FacultyPersonnelApi::fetchGroupedByCurriculum();

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'บุคลากร | ' . ($siteInfo['site_name_th'] ?? 'Personnel'),
            'meta_description' => 'บุคลากร คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'personnel',
            'personnel' => $personnel,
            'departments' => $departments,
            'personnel_by_department' => $personnelByDepartment,
            'api_personnel_by_curriculum' => $apiByCurriculum['groups'] ?? [],
            'api_faculty' => $apiByCurriculum['faculty'] ?? null,
            'api_personnel_total' => $apiByCurriculum['total'] ?? 0,
        ]);

        return view('pages/personnel', $data);
    }

    public function supportDocuments(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        // Define document categories with links from sci.uru.ac.th
        $documentCategories = [
            'general' => [
                'title' => 'งานบริหารทั่วไป',
                'icon' => 'folder',
                'documents' => [
                    ['name' => 'ขอใช้สถานที่-อุปกรณ์', 'url' => 'https://sci.uru.ac.th/docs/download/0001.doc', 'type' => 'doc'],
                    ['name' => 'ขอให้ออกหนังสือราชการ', 'url' => 'https://sci.uru.ac.th/docs/download/0004.doc', 'type' => 'doc'],
                    ['name' => 'ขออนุญาตไปราชการ (แบบ งค.๐๓)', 'url' => 'https://sci.uru.ac.th/doctopic/254', 'type' => 'link'],
                    ['name' => 'บันทึกขอใช้รถ 6 ล้อ', 'url' => 'https://sci.uru.ac.th/docs/download/0076.pdf', 'type' => 'pdf'],
                    ['name' => 'แบบขอใช้รถยนต์มหาวิทยาลัย', 'url' => 'https://sci.uru.ac.th/docs/download/0007.doc', 'type' => 'doc'],
                    ['name' => 'แบบขออนุญาตผู้ปกครองพานักศึกษาไปนอกสถานที่', 'url' => 'https://sci.uru.ac.th/docs/download/0009.doc', 'type' => 'doc'],
                    ['name' => 'แบบขออนุญาตพานักศึกษาไปนอกสถานที่', 'url' => 'https://sci.uru.ac.th/docs/download/0010.doc', 'type' => 'doc'],
                    ['name' => 'แบบขออนุญาตให้ผู้อื่นปฏิบัติหน้าที่เวรแทน', 'url' => 'https://sci.uru.ac.th/docs/download/0052.doc', 'type' => 'doc'],
                    ['name' => 'แบบใบลาพักผ่อน', 'url' => 'https://sci.uru.ac.th/docs/download/0012.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มบันทึกข้อความ', 'url' => 'https://sci.uru.ac.th/docs/download/0054.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มบันทึกขอไปราชการ/ขอใช้รถยนต์ส่วนตัว', 'url' => 'https://sci.uru.ac.th/docs/download/0068.docx', 'type' => 'docx'],
                    ['name' => 'แบบฟอร์มมอบหมายงานเพื่อลากิจ/ลาพักผ่อน/ไปราชการ', 'url' => 'https://sci.uru.ac.th/docs/download/0069.pdf', 'type' => 'pdf'],
                    ['name' => 'แบบฟอร์มหนังสือภายนอก', 'url' => 'https://sci.uru.ac.th/docs/download/0055.doc', 'type' => 'doc'],
                    ['name' => 'ใบมอบหมายงานเพื่อลากิจ', 'url' => 'https://sci.uru.ac.th/docs/download/0024.doc', 'type' => 'doc'],
                    ['name' => 'รายงานผลการไปราชการ', 'url' => 'https://sci.uru.ac.th/docs/download/0027.doc', 'type' => 'doc'],
                    ['name' => 'สัญญาจ้างเหมารถยนต์โดยสารพร้อมพนักงานขับรถ', 'url' => 'https://sci.uru.ac.th/docs/download/0077_1.docx', 'type' => 'docx'],
                ],
            ],
            'finance' => [
                'title' => 'งานการเงินและพัสดุ',
                'icon' => 'banknotes',
                'documents' => [
                    ['name' => 'บันทึกข้อความ ขอโอนเงินผ่านธนาคาร', 'url' => 'https://sci.uru.ac.th/docs/download/0050.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มขอเบิกใบเสร็จรับเงินเบ็ดเตล็ด', 'url' => 'https://sci.uru.ac.th/docs/download/0067.docx', 'type' => 'docx'],
                    ['name' => 'แบบฟอร์มใบส่งของ', 'url' => 'https://sci.uru.ac.th/docs/download/0083.docx', 'type' => 'docx'],
                    ['name' => 'แบบสำรวจ จัดซื้อ-จัดจ้าง', 'url' => 'https://sci.uru.ac.th/docs/download/0017.doc', 'type' => 'doc'],
                    ['name' => 'ใบขออนุมัติเบิกเงิน', 'url' => 'https://sci.uru.ac.th/docs/download/0020.doc', 'type' => 'doc'],
                    ['name' => 'ใบตรวจรับสินค้า', 'url' => 'https://sci.uru.ac.th/docs/download/0021.doc', 'type' => 'doc'],
                    ['name' => 'ใบเบิกค่าใช้จ่ายในการเดินทางไปราชการ', 'url' => 'https://sci.uru.ac.th/docs/download/0022.doc', 'type' => 'doc'],
                    ['name' => 'ใบเบิกค่าใช้จ่าย (บุคคลภายนอก)', 'url' => 'https://sci.uru.ac.th/docs/download/0022_1.doc', 'type' => 'doc'],
                    ['name' => 'ใบมอบฉันทะ', 'url' => 'https://sci.uru.ac.th/docs/download/0023.doc', 'type' => 'doc'],
                    ['name' => 'ใบสำคัญรับเงิน', 'url' => 'https://sci.uru.ac.th/docs/download/0025.doc', 'type' => 'doc'],
                    ['name' => 'ใบสำคัญรับเงินค่าอาหาร', 'url' => 'https://sci.uru.ac.th/docs/download/0103.docx', 'type' => 'docx'],
                    ['name' => 'ใบเสนอราคา', 'url' => 'https://sci.uru.ac.th/docs/download/0026.doc', 'type' => 'doc'],
                    ['name' => 'สัญญาค้ำประกันการยืมเงิน', 'url' => 'https://sci.uru.ac.th/docs/download/0028.pdf', 'type' => 'pdf'],
                ],
            ],
            'academic' => [
                'title' => 'งานวิชาการ',
                'icon' => 'academic-cap',
                'documents' => [
                    ['name' => 'ขอเบิกค่าสอนการเปิดสอนกรณีพิเศษ', 'url' => 'https://sci.uru.ac.th/docs/download/0002.doc', 'type' => 'doc'],
                    ['name' => 'แบบขอเปลี่ยน "ร" หรือ "I"', 'url' => 'https://sci.uru.ac.th/docs/download/0032.doc', 'type' => 'doc'],
                    ['name' => 'แบบขอเปลี่ยนแปลงแผนการเรียนหลักสูตร', 'url' => 'https://sci.uru.ac.th/docs/download/0074.docx', 'type' => 'docx'],
                    ['name' => 'แบบขออนุญาตสอนชดเชย/ให้ผู้อื่นสอนแทน', 'url' => 'https://sci.uru.ac.th/docs/download/0081.docx', 'type' => 'docx'],
                    ['name' => 'แบบขออนุญาตไปราชการ ช่วงเวลาสอบ', 'url' => 'https://sci.uru.ac.th/docs/download/0089.docx', 'type' => 'docx'],
                    ['name' => 'แบบขออนุญาตสอบนอกตาราง', 'url' => 'https://sci.uru.ac.th/docs/download/0090.docx', 'type' => 'docx'],
                    ['name' => 'แบบฟอร์มการขอเปลี่ยนแปลงข้อมูลตารางเรียน', 'url' => 'https://sci.uru.ac.th/doctopic/208', 'type' => 'link'],
                    ['name' => 'แบบฟอร์มแก้ไขผลการเรียน', 'url' => 'https://sci.uru.ac.th/docs/download/0075.docx', 'type' => 'docx'],
                    ['name' => 'แบบฟอร์มขอแก้ไขตารางสอน', 'url' => 'https://sci.uru.ac.th/doctopic/209', 'type' => 'link'],
                    ['name' => 'แบบฟอร์มแนวการสอน', 'url' => 'https://sci.uru.ac.th/docs/download/0043.doc', 'type' => 'doc'],
                    ['name' => 'แบบรายงานการประเมิน ส่งเกรด', 'url' => 'https://sci.uru.ac.th/docs/download/grade_2023_04_20.doc', 'type' => 'doc'],
                    ['name' => 'แบบเสนอเพื่อแต่งตั้งอาจารย์พิเศษ', 'url' => 'https://sci.uru.ac.th/docs/download/0066.docx', 'type' => 'docx'],
                    ['name' => 'ฟอร์มแบบเสนอ มคอ.3', 'url' => 'https://sci.uru.ac.th/docs/download/0044.doc', 'type' => 'doc'],
                ],
            ],
            'research' => [
                'title' => 'งานวิจัยและบริการวิชาการ',
                'icon' => 'beaker',
                'documents' => [
                    ['name' => 'แบบประเมินการติดตามการนำความรู้ไปใช้ประโยชน์', 'url' => 'https://sci.uru.ac.th/docs/download/0059.doc', 'type' => 'doc'],
                    ['name' => 'แบบประเมินโครงการบริการวิชาการ', 'url' => 'https://sci.uru.ac.th/docs/download/0062.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มขอจดลิขสิทธิ์', 'url' => 'https://sci.uru.ac.th/docs/download/0056.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มสรุปโครงการทั่วไป', 'url' => 'https://sci.uru.ac.th/docs/download/0060.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มสรุปโครงการบริการวิชาการ', 'url' => 'https://sci.uru.ac.th/docs/download/0061.doc', 'type' => 'doc'],
                    ['name' => 'แบบรายงานผลการดำเนินโครงการ', 'url' => 'https://sci.uru.ac.th/docs/download/0030.doc', 'type' => 'doc'],
                    ['name' => 'แบบเสนอขอรับทุนสาขาวิทยาศาสตร์ฯ', 'url' => 'https://sci.uru.ac.th/docs/download/0049.doc', 'type' => 'doc'],
                    ['name' => 'แบบเสนอโครงการวิจัย', 'url' => 'https://sci.uru.ac.th/docs/download/0018.doc', 'type' => 'doc'],
                    ['name' => 'แบบเสนอภาระงานวิจัย', 'url' => 'https://sci.uru.ac.th/docs/download/0053.doc', 'type' => 'doc'],
                    ['name' => 'หนังสือมอบอำนาจดำเนินการจดลิขสิทธิ์แทน', 'url' => 'https://sci.uru.ac.th/docs/download/0057.doc', 'type' => 'doc'],
                    ['name' => 'แบบรายงานการนำผลงานวิจัยไปสู่การใช้ประโยชน์', 'url' => 'https://sci.uru.ac.th/docs/download/0038.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มขอรับรางวัลการเขียนบทความ (การนำเสนอ)', 'url' => 'https://sci.uru.ac.th/docs/download/0045.doc', 'type' => 'doc'],
                    ['name' => 'แบบฟอร์มขอรับรางวัลการเขียนบทความ (วารสาร)', 'url' => 'https://sci.uru.ac.th/docs/download/0046.doc', 'type' => 'doc'],
                ],
            ],
        ];

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'เอกสารสายสนับสนุน | ' . ($siteInfo['site_name_th'] ?? 'Support Documents'),
            'meta_description' => 'เอกสารแบบฟอร์มดาวน์โหลดสำหรับบุคลากรสายสนับสนุน คณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'support-documents',
            'document_categories' => $documentCategories,
        ]);

        return view('pages/support_documents', $data);
    }
    public function officialDocuments(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'คำสั่ง/ประกาศ/ระเบียบ | ' . ($siteInfo['site_name_th'] ?? 'Official Documents'),
            'meta_description' => 'ศูนย์รวมคำสั่ง ประกาศ และระเบียบต่างๆ ของคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'official-documents',
        ]);

        return view('pages/official_documents', $data);
    }

    public function promotionCriteria(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'เกณฑ์การประเมินบุคคล | ' . ($siteInfo['site_name_th'] ?? 'Promotion Criteria'),
            'meta_description' => 'หลักเกณฑ์และวิธีการประเมินค่างานเพื่อกำหนดระดับตำแหน่งที่สูงขึ้น สำหรับพนักงานมหาวิทยาลัยสายสนับสนุน',
            'active_page' => 'promotion-criteria',
        ]);

        return view('pages/promotion_criteria', $data);
    }
}

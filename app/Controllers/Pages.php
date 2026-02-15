<?php

namespace App\Controllers;

use App\Libraries\FacultyPersonnelApi;
use App\Models\SiteSettingModel;
use App\Models\NewsModel;
use App\Models\NewsImageModel;
use App\Models\NewsTagModel;
use App\Models\ProgramModel;
use App\Models\PersonnelModel;
use App\Models\PersonnelProgramModel;
use App\Models\OrganizationUnitModel;
use App\Models\EventModel;

class Pages extends BaseController
{
    protected $siteSettingModel;
    protected $newsModel;
    protected $newsTagModel;
    protected $programModel;
    protected $personnelModel;
    protected $personnelProgramModel;
    protected $organizationUnitModel;
    protected $eventModel;

    public function __construct()
    {
        $this->siteSettingModel = new SiteSettingModel();
        $this->newsModel = new NewsModel();
        $this->newsTagModel = new NewsTagModel();
        $this->programModel = new ProgramModel();
        $this->personnelModel = new PersonnelModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->organizationUnitModel = new OrganizationUnitModel();
        $this->eventModel = new EventModel();
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
            'departments' => $this->organizationUnitModel->getOrdered(),
        ]);

        return view('pages/about', $data);
    }

    public function academics(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        // Get programs by level (with department)
        $programs = $this->programModel->getWithDepartment();

        // โหลดประธานหลักสูตร: ใช้ programs.chair_personnel_id ก่อน แล้ว fallback เป็น coordinator_id / personnel_programs
        $coordinatorIds = array_values(array_unique(array_filter(array_map(function ($p) {
            $id = (int)($p['chair_personnel_id'] ?? $p['coordinator_id'] ?? 0);
            return $id > 0 ? $id : null;
        }, $programs))));

        $chairByProgramId = [];
        foreach ($programs as $prog) {
            $pid = (int)($prog['id'] ?? 0);
            if ($pid <= 0) continue;
            $cid = (int)($prog['chair_personnel_id'] ?? 0);
            if ($cid > 0) {
                $chairByProgramId[$pid] = $cid;
                continue;
            }
            $cid = (int)($prog['coordinator_id'] ?? 0);
            if ($cid > 0) {
                $chairByProgramId[$pid] = $cid;
                $coordinatorIds[] = $cid;
                continue;
            }
            if ($this->personnelProgramModel->db->tableExists('personnel_programs')) {
                $ppRows = $this->personnelProgramModel->getByProgramId($pid);
                foreach ($ppRows as $row) {
                    if (mb_strpos(trim($row['role_in_curriculum'] ?? ''), 'ประธาน') !== false) {
                        $chairByProgramId[$pid] = (int)($row['personnel_id'] ?? 0);
                        $coordinatorIds[] = $chairByProgramId[$pid];
                        break;
                    }
                }
            }
        }
        $coordinatorIds = array_values(array_unique(array_filter($coordinatorIds, fn($id) => $id > 0)));

        $coordinatorsById = [];
        if ($coordinatorIds !== []) {
            $personnel = $this->personnelModel->where('status', 'active')->whereIn('id', $coordinatorIds)->findAll();
            foreach ($personnel as $p) {
                $coordinatorsById[(int)($p['id'] ?? 0)] = $p;
            }
        }
        foreach ($programs as &$p) {
            $chairId = $chairByProgramId[(int)($p['id'] ?? 0)] ?? (int)($p['chair_personnel_id'] ?? $p['coordinator_id'] ?? 0);
            $p['coordinator'] = $chairId > 0 ? ($coordinatorsById[$chairId] ?? null) : null;
        }
        unset($p);

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
            'departments' => $this->organizationUnitModel->getOrdered(),
        ]);

        return view('pages/academics', $data);
    }

    public function research(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();

        // Get research news with pagination (same as news method)
        $perPage = 12;
        $newsModel = new NewsModel();

        // Get news with research or research_grant tags
        $researchNews = $newsModel->getPublishedByTag('research', 100);

        // Also get research_grant news and merge
        $researchGrantNews = $newsModel->getPublishedByTag('research_grant', 100);

        // Merge and remove duplicates
        $allResearchNews = array_merge($researchNews, $researchGrantNews);
        $uniqueNews = [];
        $seenIds = [];

        foreach ($allResearchNews as $news) {
            $id = $news['id'] ?? null;
            if ($id && !isset($seenIds[$id])) {
                $seenIds[$id] = true;
                $uniqueNews[] = $news;
            }
        }

        // Sort by published_at descending
        usort($uniqueNews, function ($a, $b) {
            $dateA = $a['published_at'] ?? $a['created_at'] ?? '';
            $dateB = $b['published_at'] ?? $b['created_at'] ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'ข่าววิจัย | ' . ($siteInfo['site_name_th'] ?? 'Research'),
            'meta_description' => 'ข่าววิจัยและนวัตกรรมล่าสุดจากคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'research',
            'news_items' => $uniqueNews,
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

        // Get programs by level for admission page
        $programs = $this->programModel->getWithDepartment();
        $bachelorPrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'bachelor');
        $masterPrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'master');
        $doctoratePrograms = array_filter($programs, fn($p) => ($p['level'] ?? 'bachelor') === 'doctorate');

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'รับสมัครนักศึกษา | ' . ($siteInfo['site_name_th'] ?? 'Admission'),
            'meta_description' => 'เปิดรับสมัครนักศึกษาใหม่ คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'admission',
            'programs' => $bachelorPrograms,
            'master_programs' => $masterPrograms,
            'doctorate_programs' => $doctoratePrograms,
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

        // Get tags for this news
        $db = \Config\Database::connect();
        $newsTags = [];
        if ($db->tableExists('news_tags') && $db->tableExists('news_news_tags')) {
            $newsTags = $this->newsTagModel->getTagsByNewsId((int) $newsItem['id']);
        }

        // รูปภาพประกอบ (จาก news_images)
        $newsImageModel = new NewsImageModel();
        $newsImages = $db->tableExists('news_images') ? $newsImageModel->getImagesByNewsId((int) $newsItem['id']) : [];
        $newsDocuments = $db->tableExists('news_images') ? $newsImageModel->getDocumentsByNewsId((int) $newsItem['id']) : [];

        $data = array_merge($this->getCommonData(), [
            'page_title' => $newsItem['title'] . ' | ข่าวสาร',
            'meta_description' => $newsItem['excerpt'] ?? mb_substr($newsItem['title'], 0, 160),
            'active_page' => 'news',
            'news' => $newsItem,
            'news_tags' => $newsTags,
            'news_images' => $newsImages,
            'news_documents' => $newsDocuments,
            'related_news' => $relatedNews,
        ]);

        return view('pages/news_detail', $data);
    }

    public function events(): string
    {
        $siteInfo = $this->siteSettingModel->getAll();
        $events = $this->eventModel->getUpcoming(50);

        $data = array_merge($this->getCommonData(), [
            'page_title' => 'กิจกรรม | ' . ($siteInfo['site_name_th'] ?? 'Events'),
            'meta_description' => 'กิจกรรมและโครงการต่างๆ ของคณะวิทยาศาสตร์และเทคโนโลยี',
            'active_page' => 'events',
            'events' => $events,
        ]);

        return view('pages/events', $data);
    }

    /**
     * Single event detail page
     */
    public function eventDetail($id): string
    {
        $event = $this->eventModel->find($id);
        if (!$event || $event['status'] !== 'published') {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $siteInfo = $this->siteSettingModel->getAll();
        $data = array_merge($this->getCommonData(), [
            'page_title' => esc($event['title']) . ' | ' . ($siteInfo['site_name_th'] ?? 'Events'),
            'meta_description' => $event['excerpt'] ?: mb_substr(strip_tags($event['content'] ?? ''), 0, 160),
            'active_page' => 'events',
            'event' => $event,
        ]);

        return view('pages/event_detail', $data);
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

    /**
     * Position tier for "โดยรวม" view: คณบดี → รองคณบดี → ผู้ช่วยคณบดี → อาจารย์/บุคลากรในสังกัด
     * รองรับทั้ง "รองคณบดี" และ "รอง คณบดี", "ผู้ช่วยคณบดี" และ "ผู้ช่วย คณบดี"
     */
    private static function personnelPositionTier(string $position): int
    {
        $p = $position ?: '';
        // ผู้อำนวยการ (เช่น ผู้อำนวยการสำนักงานคณบดี) ให้เป็น tier 4 ก่อน ไม่งั้นจะไปติด tier 1 เพราะมีคำว่า "คณบดี"
        if (mb_strpos($p, 'ผู้อำนวยการ') !== false) return 4;
        $hasDean = mb_strpos($p, 'คณบดี') !== false;
        $hasVice = mb_strpos($p, 'รอง') !== false;
        $hasAssistant = mb_strpos($p, 'ผู้ช่วย') !== false;

        if ($hasDean && $hasVice && !$hasAssistant) return 2;  // รองคณบดี (รอง + คณบดี)
        if ($hasDean && $hasAssistant) return 3;                 // ผู้ช่วยคณบดี (ผู้ช่วย + คณบดี)
        if ($hasDean) return 1;                                 // คณบดี เท่านั้น
        return 4;                                                // อาจารย์, ประธานหลักสูตร ฯลฯ
    }

    /** Tier from English position (when Thai position is empty) */
    private static function personnelPositionTierEn(string $position): int
    {
        $p = strtolower($position ?: '');
        if (strpos($p, 'director') !== false) return 4;  // ผู้อำนวยการ
        if (strpos($p, 'associate dean') !== false || strpos($p, 'vice dean') !== false) return 2;
        if (strpos($p, 'assistant dean') !== false) return 3;
        if (strpos($p, 'dean') !== false) return 1;
        return 4;
    }

    /**
     * Sort personnel by position tier then by sort_order
     */
    private static function sortPersonnelByPositionTier(array $list): array
    {
        usort($list, function ($a, $b) {
            $tierA = self::personnelPositionTier($a['position'] ?? '');
            $tierB = self::personnelPositionTier($b['position'] ?? '');
            if ($tierA !== $tierB) return $tierA - $tierB;
            return ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0));
        });
        return $list;
    }

    /**
     * Group personnel by position tier for "โดยรวม": คณบดี | รองคณบดี | ผู้ช่วยคณบดี | อาจารย์และบุคลากร
     */
    private static function groupPersonnelByPositionTier(array $personnel): array
    {
        $groups = [
            1 => ['label_th' => 'คณบดี', 'label_en' => 'Dean', 'personnel' => []],
            2 => ['label_th' => 'รองคณบดี', 'label_en' => 'Associate Dean', 'personnel' => []],
            3 => ['label_th' => 'ผู้ช่วยคณบดี', 'label_en' => 'Assistant Dean', 'personnel' => []],
            4 => ['label_th' => 'อาจารย์และบุคลากรในสังกัด', 'label_en' => 'Faculty & Staff', 'personnel' => []],
        ];
        foreach ($personnel as $p) {
            $posTh = $p['position'] ?? '';
            $posEn = $p['position_en'] ?? '';
            $tier = self::personnelPositionTier($posTh);
            if ($tier === 4 && $posTh === '' && $posEn !== '') {
                $tier = self::personnelPositionTierEn($posEn);
            }
            $groups[$tier]['personnel'][] = $p;
        }
        return $groups;
    }

    public function personnel(): string
    {
        $common = $this->getCommonData();
        $siteInfo = $common['settings'] ?? $this->siteSettingModel->getAll();

        // Get all active personnel with department name (1 query)
        $personnel = $this->personnelModel->getActiveWithDepartment();
        $personnel = $this->enrichPersonnelWithProgramRoles($personnel);

        $programs = $this->programModel->getActive();
        $personnelById = [];
        foreach ($personnel as $p) {
            $personnelById[(int)($p['id'] ?? 0)] = $p;
        }

        // บุคลากรแยกตามหลักสูตร: ประธานหลักสูตรด้านบนสุด (เหมือนคณบดี) แล้วตามด้วยอาจารย์ที่ถูก tag ในหลักสูตร
        // ลำดับบนหน้านี้ = ประธานหลักสูตรสูงสุด ไม่ใช้ลำดับคณบดี/รอง
        // ประธาน: ใช้ programs.chair_personnel_id ก่อน → personnel_programs role ประธาน → position ประธานหลักสูตร
        $personnelByProgram = [];
        $hasChairColumn = $this->programModel->db->fieldExists('chair_personnel_id', 'programs');
        foreach ($programs as $program) {
            $programId = (int) $program['id'];
            $ppRows = $this->personnelProgramModel->getByProgramId($programId);
            $chair = null;

            // 1) ประธานจาก programs.chair_personnel_id
            if ($hasChairColumn) {
                $chairId = (int) ($program['chair_personnel_id'] ?? 0);
                if ($chairId > 0) {
                    $chair = $personnelById[$chairId] ?? null;
                }
            }
            // 2) จาก personnel_programs: role ประธาน
            if ($chair === null) {
                foreach ($ppRows as $row) {
                    $pid = (int)($row['personnel_id'] ?? 0);
                    $role = trim($row['role_in_curriculum'] ?? '');
                    if (mb_strpos($role, 'ประธาน') !== false) {
                        $chair = $personnelById[$pid] ?? null;
                        if ($chair !== null) break;
                    }
                }
            }
            // 3) จากตำแหน่ง personnel ประธานหลักสูตร ในหลักสูตรนี้
            if ($chair === null) {
                foreach ($ppRows as $row) {
                    $pid = (int)($row['personnel_id'] ?? 0);
                    $person = $personnelById[$pid] ?? null;
                    if ($person && mb_strpos($person['position'] ?? '', 'ประธานหลักสูตร') !== false) {
                        $chair = $person;
                        break;
                    }
                }
            }

            // รายชื่อบุคลากรในหลักสูตร (ที่ถูก tag) เรียงตาม sort_order ของ personnel_programs — ไม่รวมประธาน
            $personnelList = [];
            $chairId = $chair !== null ? (int)($chair['id'] ?? 0) : 0;
            foreach ($ppRows as $row) {
                $pid = (int)($row['personnel_id'] ?? 0);
                if ($pid === $chairId) continue;
                $person = $personnelById[$pid] ?? null;
                if ($person !== null) {
                    $personnelList[] = $person;
                }
            }

            $personnelByProgram[] = [
                'program' => $program,
                'chair' => $chair,
                'personnel' => $personnelList,
            ];
        }

        // หัวหน้าหน่วยงาน: โดยตำแหน่ง หรือ organization_unit_id — เรียงหัวหน้าหน่วยขึ้นก่อน (เหมือนประธานหลักสูตร)
        $hasOrgUnitId = $this->personnelModel->db->fieldExists('organization_unit_id', 'personnel');
        $researchPersonnel = array_values(array_filter($personnel, function ($p) use ($hasOrgUnitId) {
            if ($hasOrgUnitId && (int)($p['organization_unit_id'] ?? 0) === 3) {
                return true;
            }
            return mb_strpos($p['position'] ?? '', 'หัวหน้าหน่วยจัดการงานวิจัย') !== false;
        }));
        $researchHeadsFirst = array_values(array_filter($researchPersonnel, function ($p) {
            return mb_strpos($p['position'] ?? '', 'หัวหน้าหน่วยจัดการงานวิจัย') !== false;
        }));
        $researchRest = array_values(array_filter($researchPersonnel, function ($p) {
            return mb_strpos($p['position'] ?? '', 'หัวหน้าหน่วยจัดการงานวิจัย') === false;
        }));
        usort($researchHeadsFirst, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        usort($researchRest, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        $headResearch = array_merge($researchHeadsFirst, $researchRest);

        $officePersonnel = array_values(array_filter($personnel, function ($p) use ($hasOrgUnitId) {
            if ($hasOrgUnitId && (int)($p['organization_unit_id'] ?? 0) === 2) {
                return true;
            }
            $pos = $p['position'] ?? '';
            return mb_strpos($pos, 'หัวหน้าสำนักงาน') !== false || mb_strpos($pos, 'เจ้าหน้าที่') !== false;
        }));
        $officeHeadsFirst = array_values(array_filter($officePersonnel, function ($p) {
            return mb_strpos($p['position'] ?? '', 'หัวหน้าสำนักงาน') !== false;
        }));
        $officeRest = array_values(array_filter($officePersonnel, function ($p) {
            return mb_strpos($p['position'] ?? '', 'หัวหน้าสำนักงาน') === false;
        }));
        usort($officeHeadsFirst, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        usort($officeRest, fn($a, $b) => ((int)($a['sort_order'] ?? 0)) - ((int)($b['sort_order'] ?? 0)));
        $headOffice = array_merge($officeHeadsFirst, $officeRest);

        // หน้า Personnel แสดง 3 หน่วยงาน (icon): หลักสูตร, หน่วยจัดการงานวิจัย, สำนักงานคณบดี — ชื่อจาก organization_units ถ้ามี
        $bachelorPrograms = [];
        $graduatePrograms = [];
        foreach ($personnelByProgram as $block) {
            $level = $block['program']['level'] ?? 'bachelor';
            if ($level === 'master' || $level === 'doctorate') {
                $graduatePrograms[] = $block;
            } else {
                $bachelorPrograms[] = $block;
            }
        }
        $allPrograms = array_merge($bachelorPrograms, $graduatePrograms);

        $orgUnits = $this->organizationUnitModel->getOrdered();
        $unitByCode = [];
        foreach ($orgUnits as $u) {
            $unitByCode[$u['code'] ?? ''] = $u;
        }
        $researchUnit = $unitByCode['research'] ?? ['id' => 3, 'name_th' => 'หัวหน้าหน่วยการจัดการงานวิจัย', 'name_en' => 'Research Management Unit', 'code' => 'research'];
        $officeUnit = $unitByCode['office'] ?? ['id' => 2, 'name_th' => 'สำนักงานคณบดี', 'name_en' => "Dean's Office", 'code' => 'office'];

        $organizationSections = [
            [
                'unit' => ['id' => 1, 'name_th' => 'หลักสูตร', 'name_en' => 'Programs', 'code' => 'curriculum'],
                'programs' => $allPrograms,
                'personnel' => [],
            ],
            [
                'unit' => ['id' => (int)$researchUnit['id'], 'name_th' => $researchUnit['name_th'] ?? 'หน่วยจัดการงานวิจัย', 'name_en' => $researchUnit['name_en'] ?? 'Research Management Unit', 'code' => 'research'],
                'programs' => [],
                'personnel' => $headResearch,
            ],
            [
                'unit' => ['id' => (int)$officeUnit['id'], 'name_th' => $officeUnit['name_th'] ?? 'สำนักงานคณบดี', 'name_en' => $officeUnit['name_en'] ?? "Dean's Office", 'code' => 'office'],
                'programs' => [],
                'personnel' => $headOffice,
            ],
        ];

        $data = array_merge($common, [
            'page_title' => 'บุคลากร | ' . ($siteInfo['site_name_th'] ?? 'Personnel'),
            'meta_description' => 'บุคลากร คณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'personnel',
            'organization_sections' => $organizationSections,
            'head_research_personnel' => $headResearch,
            'head_office_personnel' => $headOffice,
        ]);

        return view('pages/personnel', $data);
    }

    /**
     * เติม programs_list_tags (หลักสูตร + บทบาท) ให้แต่ละคน จาก personnel_programs (batch load)
     */
    private function enrichPersonnelWithProgramRoles(array $personnel): array
    {
        if (!$this->personnelModel->db->tableExists('personnel_programs')) {
            foreach ($personnel as &$p) {
                $p['programs_list_tags'] = [];
            }
            unset($p);
            return $personnel;
        }
        $personnelIds = array_values(array_filter(array_map(fn($p) => (int)($p['id'] ?? 0), $personnel), fn($id) => $id > 0));
        $ppRows = $personnelIds !== [] ? $this->personnelProgramModel->getByPersonnelIds($personnelIds) : [];
        $programIds = array_values(array_unique(array_filter(array_map(fn($r) => (int)($r['program_id'] ?? 0), $ppRows), fn($id) => $id > 0)));
        $programsMap = [];
        if ($programIds !== []) {
            foreach ($this->programModel->whereIn('id', $programIds)->findAll() as $pr) {
                $programsMap[(int)$pr['id']] = $pr;
            }
        }
        $ppByPersonnel = [];
        foreach ($ppRows as $row) {
            $pid = (int)$row['personnel_id'];
            if (!isset($ppByPersonnel[$pid])) {
                $ppByPersonnel[$pid] = [];
            }
            $ppByPersonnel[$pid][] = $row;
        }
        foreach ($personnel as &$p) {
            $pid = (int)($p['id'] ?? 0);
            $ppList = $ppByPersonnel[$pid] ?? [];
            $tags = [];
            foreach ($ppList as $pp) {
                $pr = $programsMap[(int)($pp['program_id'] ?? 0)] ?? null;
                if ($pr) {
                    $tags[] = [
                        'name' => $pr['name_th'] ?? $pr['name_en'] ?? '',
                        'role' => $pp['role_in_curriculum'] ?? '',
                    ];
                }
            }
            $p['programs_list_tags'] = $tags;
        }
        unset($p);
        return $personnel;
    }

    /**
     * หน้าผู้บริหาร – โครงสร้างองค์กร (โหลดข้อมูลผ่าน Ajax ค่อยๆ จาก GET /api/executives)
     * ส่งเฉพาะเปลือกหน้า + placeholder โหลด; ข้อมูลจริง inject ด้วย JS
     */
    public function executives(): string
    {
        $common = $this->getCommonData();
        $siteInfo = $common['settings'] ?? [];

        $data = array_merge($common, [
            'page_title' => 'ผู้บริหาร | ' . ($siteInfo['site_name_th'] ?? 'Executives'),
            'meta_description' => 'ผู้บริหารคณะวิทยาศาสตร์และเทคโนโลยี มหาวิทยาลัยราชภัฏอุตรดิตถ์',
            'active_page' => 'executives',
        ]);

        return view('pages/executives', $data);
    }

    /**
     * สร้างรายการประธานหลักสูตรจาก coordinator ของแต่ละหลักสูตร (programs.chair_personnel_id)
     *
     * @return array List of ['program_name' => string, 'person' => personnel row]
     */
    private function buildProgramChairItemsFromCoordinators(): array
    {
        $programs = $this->programModel->getWithDepartment();
        $chairIds = [];
        foreach ($programs as $p) {
            $cid = (int) ($p['chair_personnel_id'] ?? 0);
            if ($cid > 0) {
                $chairIds[$cid] = true;
            }
            if ($cid <= 0 && $this->programModel->db->fieldExists('coordinator_id', 'programs')) {
                $cid = (int) ($p['coordinator_id'] ?? 0);
                if ($cid > 0) {
                    $chairIds[$cid] = true;
                }
            }
        }
        if ($this->personnelProgramModel->db->tableExists('personnel_programs')) {
            $ppModel = $this->personnelProgramModel;
            foreach ($programs as $p) {
                $pid = (int) ($p['id'] ?? 0);
                if ((int)($p['chair_personnel_id'] ?? 0) > 0) continue;
                $row = $ppModel->where('program_id', $pid)->like('role_in_curriculum', 'ประธาน')->first();
                if ($row && !empty($row['personnel_id'])) {
                    $chairIds[(int) $row['personnel_id']] = true;
                }
            }
        }
        $chairIds = array_keys($chairIds);
        $personnelMap = [];
        if (!empty($chairIds)) {
            $list = $this->personnelModel->getActiveByIdsWithUser($chairIds);
            foreach ($list as $p) {
                $personnelMap[(int) ($p['id'] ?? 0)] = $p;
            }
        }

        $items = [];
        foreach ($programs as $program) {
            $programId = (int) ($program['id'] ?? 0);
            $chairId = (int) ($program['chair_personnel_id'] ?? 0);
            if ($chairId <= 0 && $this->programModel->db->fieldExists('coordinator_id', 'programs')) {
                $chairId = (int) ($program['coordinator_id'] ?? 0);
            }
            if ($chairId <= 0 && $this->personnelProgramModel->db->tableExists('personnel_programs')) {
                $row = $this->personnelProgramModel->where('program_id', $programId)->like('role_in_curriculum', 'ประธาน')->first();
                $chairId = $row ? (int) ($row['personnel_id'] ?? 0) : 0;
            }
            if ($chairId <= 0) continue;
            $person = $personnelMap[$chairId] ?? null;
            if (!$person) continue;
            $programName = trim($program['name_th'] ?? $program['name_en'] ?? '');
            if ($programName !== '') {
                $items[] = [
                    'program_name' => $programName,
                    'person' => $person,
                ];
            }
        }
        return $items;
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

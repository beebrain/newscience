<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\PersonnelModel;
use App\Models\OrganizationUnitModel;
use App\Models\ProgramModel;
use App\Models\ProgramPageModel;
use App\Models\ProgramActivityModel;
use App\Models\PersonnelProgramModel;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use App\Models\EventModel;
use App\Models\StudentUserModel;
use App\Models\CertificateModel;
use App\Models\CertEventModel;
use App\Models\BarcodeEventModel;
use App\Models\BarcodeModel;
use App\Models\Edoc\EdoctitleModel;
use App\Models\PageViewModel;
use App\Libraries\HttpTransport;
use CodeIgniter\API\ResponseTrait;
use Config\ResearchApi;

/**
 * Executive Dashboard Statistics API
 * For dean / vice-dean: overview, personnel, programs, news, edoc, certificates.
 * Requires admin or super_admin role.
 */
class ExecutiveStats extends BaseController
{
    use ResponseTrait;

    protected $db;
    protected $personnelModel;
    protected $organizationUnitModel;
    protected $programModel;
    protected $programPageModel;
    protected $programActivityModel;
    protected $personnelProgramModel;
    protected $newsModel;
    protected $newsTagModel;
    protected $eventModel;
    protected $studentUserModel;
    protected $certificateModel;
    protected $certEventModel;
    protected $barcodeEventModel;
    protected $barcodeModel;
    protected $edoctitleModel;
    protected $pageViewModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->personnelModel = new PersonnelModel();
        $this->organizationUnitModel = new OrganizationUnitModel();
        $this->programModel = new ProgramModel();
        $this->programPageModel = new ProgramPageModel();
        $this->programActivityModel = new ProgramActivityModel();
        $this->personnelProgramModel = new PersonnelProgramModel();
        $this->newsModel = new NewsModel();
        $this->newsTagModel = new NewsTagModel();
        $this->eventModel = new EventModel();
        $this->studentUserModel = new StudentUserModel();
        $this->certificateModel = new CertificateModel();
        $this->certEventModel = new CertEventModel();
        $this->barcodeEventModel = new BarcodeEventModel();
        $this->barcodeModel = new BarcodeModel();
        if ($this->db->tableExists('edoctitle')) {
            $this->edoctitleModel = new EdoctitleModel();
        } else {
            $this->edoctitleModel = null;
        }
        $this->pageViewModel = new PageViewModel();
    }

    /**
     * Ensure caller is admin or super_admin
     */
    protected function ensureExecutiveAccess(): ?\CodeIgniter\HTTP\Response
    {
        if (!session()->get('admin_logged_in')) {
            return $this->respond(['error' => 'Unauthorized', 'success' => false], 403);
        }
        $role = session()->get('admin_role');
        if ($role !== 'admin' && $role !== 'super_admin') {
            return $this->respond(['error' => 'Forbidden: executive dashboard only', 'success' => false], 403);
        }
        return null;
    }

    /**
     * Apply date filter to builder by period (all, year, quarter, month)
     * @param \CodeIgniter\Database\BaseBuilder $builder
     * @param string $period
     * @param string $dateColumn e.g. published_at, issued_date, regisdate, created_at
     */
    protected function applyPeriodFilter($builder, string $period, string $dateColumn = 'published_at'): void
    {
        if ($period === 'all') {
            return;
        }
        $today = date('Y-m-d');
        if ($period === 'year') {
            $builder->where($dateColumn . ' >=', date('Y-01-01'));
        } elseif ($period === 'quarter') {
            $month = (int) date('n');
            $quarterStart = date('Y') . '-' . str_pad((floor(($month - 1) / 3) * 3) + 1, 2, '0', STR_PAD_LEFT) . '-01';
            $builder->where($dateColumn . ' >=', $quarterStart);
        } elseif ($period === 'month') {
            $builder->where($dateColumn . ' >=', date('Y-m-01'));
        }
    }

    /**
     * GET overview: counts for 6 cards (personnel, programs, students, news, edoc, certificates)
     */
    public function overview()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $personnelCount = 0;
        if ($this->db->tableExists('personnel')) {
            $personnelCount = $this->personnelModel->where('status', 'active')->countAllResults();
        }

        $programsTotal = 0;
        $programsByLevel = ['bachelor' => 0, 'master' => 0, 'doctorate' => 0];
        if ($this->db->tableExists('programs')) {
            $programsTotal = $this->programModel->where('status', 'active')->countAllResults();
            $programsByLevel['bachelor'] = $this->programModel->where('status', 'active')->where('level', 'bachelor')->countAllResults();
            $programsByLevel['master'] = $this->programModel->where('status', 'active')->where('level', 'master')->countAllResults();
            $programsByLevel['doctorate'] = $this->programModel->where('status', 'active')->where('level', 'doctorate')->countAllResults();
        }

        $studentsCount = 0;
        if ($this->db->tableExists('student_user')) {
            $studentsCount = $this->studentUserModel->where('status', 'active')->countAllResults();
        }

        $newsCount = 0;
        if ($this->db->tableExists('news')) {
            $newsCount = $this->newsModel->where('status', 'published')->countAllResults();
        }

        $edocCount = 0;
        if ($this->db->tableExists('edoctitle')) {
            $edocCount = $this->edoctitleModel->countAllResults();
        }

        $certCount = 0;
        if ($this->db->tableExists('certificates')) {
            $certCount = $this->certificateModel->countAllResults();
        }

        $researchTotal = 0;
        $cached = cache()->get('exec_research_stats');
        if (is_array($cached) && isset($cached['total_publications'])) {
            $researchTotal = (int) $cached['total_publications'];
        }

        $pageViewsTotal = 0;
        if ($this->db->tableExists('page_views')) {
            $pageViewsTotal = (int) $this->pageViewModel->countAllResults();
        }

        $totalUsersAdmin = 0;
        if ($this->db->tableExists('user')) {
            $totalUsersAdmin = (int) $this->db->table('user')->countAllResults();
        }
        $totalUsers = $totalUsersAdmin + $studentsCount;

        $academicServicesTotal = 0;
        if ($this->db->tableExists('academic_services')) {
            $academicServicesTotal = (int) $this->db->table('academic_services')->countAllResults();
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'personnel' => $personnelCount,
                'programs' => $programsTotal,
                'programs_by_level' => $programsByLevel,
                'students' => $studentsCount,
                'news' => $newsCount,
                'edoc' => $edocCount,
                'certificates' => $certCount,
                'research' => $researchTotal,
                'page_views' => $pageViewsTotal,
                'total_users' => $totalUsers,
                'academic_services' => $academicServicesTotal,
            ],
        ]);
    }

    /**
     * GET academic-services: summary stats for academic service (บริการวิชาการ)
     * Total by year, by service_type, distinct participants count
     */
    public function academicServices()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        if (! $this->db->tableExists('academic_services')) {
            return $this->respond([
                'success' => true,
                'data' => [
                    'total' => 0,
                    'by_year' => [],
                    'by_service_type' => [],
                    'distinct_participants' => 0,
                ],
            ]);
        }

        $total = (int) $this->db->table('academic_services')->countAllResults();

        $byYear = [];
        $rows = $this->db->table('academic_services')
            ->select('academic_year, COUNT(*) as count')
            ->where('academic_year IS NOT NULL')
            ->where('academic_year !=', '')
            ->groupBy('academic_year')
            ->orderBy('academic_year', 'DESC')
            ->get()
            ->getResultArray();
        foreach ($rows as $row) {
            $byYear[] = ['year' => $row['academic_year'], 'count' => (int) $row['count']];
        }

        $byServiceType = [];
        $rows = $this->db->table('academic_services')
            ->select('service_type, COUNT(*) as count')
            ->where('service_type IS NOT NULL')
            ->where('service_type !=', '')
            ->groupBy('service_type')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();
        foreach ($rows as $row) {
            $byServiceType[] = ['service_type' => $row['service_type'], 'count' => (int) $row['count']];
        }

        $distinctParticipants = 0;
        if ($this->db->tableExists('academic_service_participants')) {
            $row = $this->db->query('SELECT COUNT(DISTINCT user_uid) AS c FROM academic_service_participants WHERE user_uid IS NOT NULL')->getRow();
            $distinctParticipants = (int) ($row->c ?? 0);
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'total' => $total,
                'by_year' => $byYear,
                'by_service_type' => $byServiceType,
                'distinct_participants' => $distinctParticipants,
            ],
        ]);
    }

    /**
     * GET personnel: by department (organization_unit), by position, per program
     */
    public function personnel()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $byDepartment = [];
        if ($this->db->tableExists('personnel') && $this->db->tableExists('organization_units') && $this->db->fieldExists('organization_unit_id', 'personnel')) {
            $rows = $this->db->table('personnel p')
                ->select('ou.name_th as name, COUNT(*) as count')
                ->join('organization_units ou', 'ou.id = p.organization_unit_id', 'left')
                ->where('p.status', 'active')
                ->groupBy('p.organization_unit_id')
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $byDepartment[] = ['name' => $row['name'] ?: 'ไม่ระบุ', 'count' => (int) $row['count']];
            }
        }

        $byPosition = [];
        if ($this->db->tableExists('personnel')) {
            $rows = $this->db->table('personnel')
                ->select('position as name, COUNT(*) as count')
                ->where('status', 'active')
                ->groupBy('position')
                ->orderBy('count', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $byPosition[] = ['name' => $row['name'] ?: 'ไม่ระบุ', 'count' => (int) $row['count']];
            }
        }

        $perProgram = [];
        if ($this->db->tableExists('personnel_programs') && $this->db->tableExists('programs')) {
            $rows = $this->db->table('personnel_programs pp')
                ->select('pr.name_th as name, COUNT(DISTINCT pp.personnel_id) as count')
                ->join('programs pr', 'pr.id = pp.program_id', 'inner')
                ->groupBy('pp.program_id')
                ->orderBy('count', 'DESC')
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $perProgram[] = ['name' => $row['name'] ?: '-', 'count' => (int) $row['count']];
            }
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'by_department' => $byDepartment,
                'by_position' => $byPosition,
                'per_program' => $perProgram,
            ],
        ]);
    }

    /**
     * GET programs: by level, published pages count, activities per program
     */
    public function programs()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $byLevel = [];
        if ($this->db->tableExists('programs')) {
            $rows = $this->db->table('programs')
                ->select('level as name, COUNT(*) as count')
                ->where('status', 'active')
                ->groupBy('level')
                ->get()
                ->getResultArray();
            $labels = ['bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'doctorate' => 'ปริญญาเอก'];
            foreach ($rows as $row) {
                $byLevel[] = ['name' => $labels[$row['name']] ?? $row['name'], 'count' => (int) $row['count']];
            }
        }

        $publishedTotal = 0;
        $pagesTotal = 0;
        if ($this->db->tableExists('program_pages')) {
            $pagesTotal = $this->programPageModel->countAllResults();
            $publishedTotal = $this->programPageModel->where('is_published', 1)->countAllResults();
        }

        $activitiesPerProgram = [];
        if ($this->db->tableExists('program_activities') && $this->db->tableExists('programs')) {
            $rows = $this->db->table('program_activities pa')
                ->select('p.name_th as name, COUNT(*) as count')
                ->join('programs p', 'p.id = pa.program_id', 'inner')
                ->groupBy('pa.program_id')
                ->orderBy('count', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $activitiesPerProgram[] = ['name' => $row['name'] ?: '-', 'count' => (int) $row['count']];
            }
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'by_level' => $byLevel,
                'program_pages_total' => $pagesTotal,
                'program_pages_published' => $publishedTotal,
                'activities_per_program' => $activitiesPerProgram,
            ],
        ]);
    }

    /**
     * GET news: by tag, by month, top by view_count, upcoming events
     */
    public function news()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $period = $this->request->getGet('period') ?? 'all';

        $byTag = [];
        if ($this->db->tableExists('news_tags') && $this->db->tableExists('news_news_tags')) {
            $builder = $this->db->table('news n')
                ->select('t.name, COUNT(*) as count')
                ->join('news_news_tags nnt', 'nnt.news_id = n.id', 'inner')
                ->join('news_tags t', 't.id = nnt.news_tag_id', 'inner')
                ->where('n.status', 'published');
            $this->applyPeriodFilter($builder, $period, 'n.published_at');
            $rows = $builder->groupBy('t.id')->orderBy('count', 'DESC')->get()->getResultArray();
            foreach ($rows as $row) {
                $byTag[] = ['name' => $row['name'] ?? '-', 'count' => (int) $row['count']];
            }
        }

        $byMonth = [];
        if ($this->db->tableExists('news')) {
            $builder = $this->db->table('news')
                ->select('DATE_FORMAT(published_at, "%Y-%m") as month, COUNT(*) as count')
                ->where('status', 'published')
                ->where('published_at IS NOT NULL');
            $this->applyPeriodFilter($builder, $period, 'published_at');
            $rows = $builder->groupBy('month')->orderBy('month', 'ASC')->get()->getResultArray();
            foreach ($rows as $row) {
                $byMonth[] = ['month' => $row['month'], 'count' => (int) $row['count']];
            }
        }

        $topViewed = [];
        if ($this->db->tableExists('news')) {
            $builder = $this->newsModel->where('status', 'published')->orderBy('view_count', 'DESC')->limit(10);
            $rows = $builder->findAll();
            foreach ($rows as $row) {
                $topViewed[] = [
                    'id' => (int) $row['id'],
                    'title' => $row['title'] ?? '',
                    'view_count' => (int) ($row['view_count'] ?? 0),
                ];
            }
        }

        $upcomingEvents = [];
        if ($this->db->tableExists('events')) {
            $rows = $this->eventModel->where('status', 'published')
                ->where('event_date >=', date('Y-m-d'))
                ->orderBy('event_date', 'ASC')
                ->limit(10)
                ->findAll();
            foreach ($rows as $row) {
                $upcomingEvents[] = [
                    'id' => (int) $row['id'],
                    'title' => $row['title'] ?? '',
                    'event_date' => $row['event_date'] ?? '',
                ];
            }
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'by_tag' => $byTag,
                'by_month' => $byMonth,
                'top_viewed' => $topViewed,
                'upcoming_events' => $upcomingEvents,
            ],
            'period' => $period,
        ]);
    }

    /**
     * GET edoc: doc type distribution, monthly trend, top owners, paper summary
     */
    public function edoc()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        if (!$this->db->tableExists('edoctitle') || $this->edoctitleModel === null) {
            return $this->respond([
                'success' => true,
                'data' => [
                    'by_type' => [],
                    'by_month' => [],
                    'top_owners' => [],
                    'paper_summary' => ['total_docs' => 0, 'total_pages' => 0, 'total_paper' => 0],
                ],
            ]);
        }

        $byType = $this->edoctitleModel->getDocumentCountByType();
        $out = [];
        foreach ($byType as $row) {
            $out[] = ['name' => $row['doctype'] ?? 'ไม่ระบุ', 'count' => (int) ($row['count'] ?? 0)];
        }

        $byMonth = $this->edoctitleModel->getDocumentsPerMonth();

        $topOwners = $this->edoctitleModel->getTopDocumentOwners(10);
        $ownersOut = [];
        foreach ($topOwners as $row) {
            $ownersOut[] = ['name' => $row['owner'] ?? 'ไม่ระบุ', 'count' => (int) ($row['count'] ?? 0)];
        }

        $totalDocs = $this->edoctitleModel->countAllResults();
        $totalPages = $this->db->table('edoctitle')->selectSum('pages')->get()->getRowArray()['pages'] ?? 0;
        $totalPaper = $this->edoctitleModel->getsummaryPaper();

        return $this->respond([
            'success' => true,
            'data' => [
                'by_type' => $out,
                'by_month' => $byMonth,
                'top_owners' => $ownersOut,
                'paper_summary' => [
                    'total_docs' => (int) $totalDocs,
                    'total_pages' => (int) $totalPages,
                    'total_paper' => (int) $totalPaper,
                ],
            ],
        ]);
    }

    /**
     * GET certificates: by status, by month, cert events with stats, barcode events with counts
     */
    public function certificates()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $period = $this->request->getGet('period') ?? 'all';

        $byStatus = [];
        if ($this->db->tableExists('certificates')) {
            $rows = $this->db->table('certificates')
                ->select('is_revoked, COUNT(*) as count')
                ->groupBy('is_revoked')
                ->get()
                ->getResultArray();
            foreach ($rows as $row) {
                $label = !empty($row['is_revoked']) ? 'ยกเลิก' : 'ใช้งาน';
                $byStatus[] = ['name' => $label, 'count' => (int) $row['count']];
            }
        }

        $byMonth = [];
        if ($this->db->tableExists('certificates') && $this->db->fieldExists('issued_date', 'certificates')) {
            $builder = $this->db->table('certificates')
                ->select('DATE_FORMAT(issued_date, "%Y-%m") as month, COUNT(*) as count')
                ->where('issued_date IS NOT NULL');
            $this->applyPeriodFilter($builder, $period, 'issued_date');
            $rows = $builder->groupBy('month')->orderBy('month', 'ASC')->get()->getResultArray();
            foreach ($rows as $row) {
                $byMonth[] = ['month' => $row['month'], 'count' => (int) $row['count']];
            }
        }

        $certEvents = [];
        if ($this->db->tableExists('cert_events')) {
            $events = $this->certEventModel->getAllWithStats(null, 20);
            foreach ($events as $e) {
                $certEvents[] = [
                    'id' => (int) $e['id'],
                    'title' => $e['title'] ?? '',
                    'event_date' => $e['event_date'] ?? '',
                    'total_recipients' => (int) ($e['total_recipients'] ?? 0),
                    'issued_count' => (int) ($e['issued_count'] ?? 0),
                    'pending_count' => (int) ($e['pending_count'] ?? 0),
                ];
            }
        }

        $barcodeEvents = [];
        if ($this->db->tableExists('barcode_events')) {
            $events = $this->barcodeEventModel->getAllOrdered();
            foreach (array_slice($events, 0, 20) as $e) {
                $withCounts = $this->barcodeEventModel->getWithCounts((int) $e['id']);
                if ($withCounts) {
                    $barcodeEvents[] = [
                        'id' => (int) $withCounts['id'],
                        'title' => $withCounts['title'] ?? '',
                        'event_date' => $withCounts['event_date'] ?? '',
                        'barcode_total' => (int) ($withCounts['barcode_total'] ?? 0),
                        'barcode_assigned' => (int) ($withCounts['barcode_assigned'] ?? 0),
                        'barcode_claimed' => (int) ($withCounts['barcode_claimed'] ?? 0),
                    ];
                }
            }
        }

        $barcodeClaimRate = null;
        if ($this->db->tableExists('barcodes')) {
            $total = $this->barcodeModel->countAllResults();
            $claimed = $total > 0 ? $this->barcodeModel->where('claimed_at IS NOT NULL')->countAllResults() : 0;
            $barcodeClaimRate = ['total' => $total, 'claimed' => $claimed, 'rate' => $total > 0 ? round($claimed / $total * 100, 1) : 0];
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'by_status' => $byStatus,
                'by_month' => $byMonth,
                'cert_events' => $certEvents,
                'barcode_events' => $barcodeEvents,
                'barcode_claim_rate' => $barcodeClaimRate,
            ],
            'period' => $period,
        ]);
    }

    /**
     * GET pageviews: page_views stats, news view_count sum, document_views count, user counts. Supports period.
     */
    public function pageviews()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $period = $this->request->getGet('period') ?? 'all';

        $totalViews = 0;
        $uniqueVisitors = 0;
        $byPage = [];
        $byDay = [];
        $userBreakdown = ['admin' => 0, 'student' => 0, 'guest' => 0];

        if ($this->db->tableExists('page_views')) {
            $builder = $this->db->table('page_views');
            $this->applyPeriodFilter($builder, $period, 'created_at');
            $totalViews = (int) (clone $builder)->countAllResults(false);

            $builderDistinct = $this->db->table('page_views')->select('COUNT(DISTINCT session_id) as cnt');
            $this->applyPeriodFilter($builderDistinct, $period, 'created_at');
            $row = $builderDistinct->get()->getRow();
            $uniqueVisitors = (int) ($row->cnt ?? 0);

            $builderPage = $this->db->table('page_views')->select('url, COUNT(*) as count')->groupBy('url')->orderBy('count', 'DESC')->limit(20);
            $this->applyPeriodFilter($builderPage, $period, 'created_at');
            $rows = $builderPage->get()->getResultArray();
            foreach ($rows as $row) {
                $byPage[] = ['url' => $row['url'] ?? '', 'count' => (int) $row['count']];
            }

            $builderDay = $this->db->table('page_views')
                ->select('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT session_id) as unique_visitors')
                ->groupBy('date')
                ->orderBy('date', 'ASC');
            $this->applyPeriodFilter($builderDay, $period, 'created_at');
            $dayRows = $builderDay->get()->getResultArray();
            foreach ($dayRows as $row) {
                $byDay[] = [
                    'date' => $row['date'],
                    'views' => (int) $row['views'],
                    'unique_visitors' => (int) ($row['unique_visitors'] ?? 0),
                ];
            }

            $builderType = $this->db->table('page_views')->select('user_type, COUNT(*) as count')->groupBy('user_type');
            $this->applyPeriodFilter($builderType, $period, 'created_at');
            $typeRows = $builderType->get()->getResultArray();
            foreach ($typeRows as $row) {
                $t = $row['user_type'] ?? 'guest';
                if (isset($userBreakdown[$t])) {
                    $userBreakdown[$t] = (int) $row['count'];
                } else {
                    $userBreakdown['guest'] += (int) $row['count'];
                }
            }
        }

        $newsViewsTotal = 0;
        if ($this->db->tableExists('news')) {
            $newsViewsTotal = (int) $this->db->table('news')->selectSum('view_count')->get()->getRow()->view_count;
        }

        $edocViewsTotal = 0;
        if ($this->db->tableExists('document_views')) {
            $edocViewsTotal = (int) $this->db->table('document_views')->countAllResults();
        }

        $totalUsersAdmin = 0;
        if ($this->db->tableExists('user')) {
            $totalUsersAdmin = (int) $this->db->table('user')->countAllResults();
        }

        $totalStudents = 0;
        if ($this->db->tableExists('student_user')) {
            $totalStudents = (int) $this->studentUserModel->where('status', 'active')->countAllResults();
        }

        return $this->respond([
            'success' => true,
            'data' => [
                'total_views' => $totalViews,
                'unique_visitors' => $uniqueVisitors,
                'by_page' => $byPage,
                'by_day' => $byDay,
                'user_breakdown' => $userBreakdown,
                'news_views_total' => $newsViewsTotal,
                'edoc_views_total' => $edocViewsTotal,
                'total_users_admin' => $totalUsersAdmin,
                'total_students' => $totalStudents,
            ],
            'period' => $period,
        ]);
    }

    /**
     * GET research: publications from Research Record API (per personnel email), cached 6h.
     * Query param refresh=1 to invalidate cache.
     */
    public function research()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $refresh = $this->request->getGet('refresh');
        $cacheKey = 'exec_research_stats';
        if ($refresh === '1' || $refresh === 1) {
            cache()->delete($cacheKey);
        }

        $cached = cache()->get($cacheKey);
        if ($cached !== null && is_array($cached)) {
            return $this->respond([
                'success' => true,
                'data' => $cached,
                'cached' => true,
            ]);
        }

        $researchApi = config(ResearchApi::class);
        if (! $researchApi->isConfigured()) {
            return $this->respond([
                'success' => true,
                'data' => [
                    'total_publications' => 0,
                    'unique_researchers' => 0,
                    'by_type' => [],
                    'by_year' => [],
                    'by_personnel' => [],
                    'by_program' => [],
                ],
            ]);
        }

        $personnelWithEmail = [];
        if ($this->db->tableExists('personnel')) {
            $rows = $this->personnelModel
                ->where('status', 'active')
                ->where('user_email !=', '')
                ->where('user_email IS NOT NULL')
                ->findAll();
            foreach ($rows as $row) {
                $personnelWithEmail[] = [
                    'id' => (int) $row['id'],
                    'user_email' => trim((string) $row['user_email']),
                    'name' => $row['name'] ?? $row['name_en'] ?? $row['user_email'],
                    'program_id' => isset($row['program_id']) ? (int) $row['program_id'] : null,
                ];
            }
        }

        $allPublications = [];
        $countByEmail = [];

        foreach ($personnelWithEmail as $p) {
            $email = $p['user_email'];
            if ($email === '') {
                continue;
            }
            $url = $researchApi->baseUrl . '/api/public/publications-by-email?' . http_build_query(['email' => $email]);
            try {
                $response = HttpTransport::get($url, ['timeout' => 8], [
                    'headers' => [
                        'X-API-KEY' => $researchApi->apiKey,
                        'Accept' => 'application/json',
                    ],
                ]);
                $body = $response->getBody();
                $data = json_decode($body, true);
                if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300 && is_array($data) && ! empty($data['success']) && ! empty($data['publications'])) {
                    $pubs = $data['publications'];
                    $countByEmail[$email] = ['name' => $p['name'], 'count' => count($pubs), 'program_id' => $p['program_id']];
                    foreach ($pubs as $pub) {
                        $pub['_email'] = $email;
                        $pub['_program_id'] = $p['program_id'];
                        $allPublications[] = $pub;
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', 'ExecutiveStats::research API call failed for ' . $email . ': ' . $e->getMessage());
            }
        }

        $byType = [];
        $byYear = [];
        $byProgramId = [];

        foreach ($allPublications as $pub) {
            $type = isset($pub['publication_type']) && (string) $pub['publication_type'] !== '' ? (string) $pub['publication_type'] : 'อื่นๆ';
            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $year = isset($pub['publication_year']) ? (string) $pub['publication_year'] : '';
            if ($year !== '') {
                $byYear[$year] = ($byYear[$year] ?? 0) + 1;
            }
            $pid = $pub['_program_id'] ?? null;
            if ($pid !== null) {
                $byProgramId[$pid] = ($byProgramId[$pid] ?? 0) + 1;
            }
        }

        ksort($byYear, SORT_STRING);
        $byYearArr = [];
        foreach ($byYear as $y => $c) {
            $byYearArr[] = ['year' => $y, 'count' => $c];
        }
        $byTypeArr = [];
        foreach ($byType as $name => $c) {
            $byTypeArr[] = ['name' => $name, 'count' => $c];
        }

        $byPersonnelArr = [];
        foreach ($countByEmail as $email => $info) {
            $byPersonnelArr[] = [
                'name' => $info['name'],
                'email' => $email,
                'count' => (int) $info['count'],
            ];
        }
        usort($byPersonnelArr, static function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        $byPersonnelArr = array_slice($byPersonnelArr, 0, 10);

        $programNames = [];
        if ($this->db->tableExists('programs') && ! empty($byProgramId)) {
            $ids = array_keys($byProgramId);
            $programs = $this->programModel->whereIn('id', $ids)->findAll();
            foreach ($programs as $pr) {
                $programNames[(int) $pr['id']] = $pr['name_th'] ?? $pr['name_en'] ?? 'โปรแกรม #' . $pr['id'];
            }
        }
        $byProgramArr = [];
        foreach ($byProgramId as $pid => $c) {
            $byProgramArr[] = [
                'name' => $programNames[$pid] ?? 'โปรแกรม #' . $pid,
                'count' => (int) $c,
            ];
        }
        usort($byProgramArr, static function ($a, $b) {
            return $b['count'] <=> $a['count'];
        });

        $result = [
            'total_publications' => count($allPublications),
            'unique_researchers' => count($countByEmail),
            'by_type' => $byTypeArr,
            'by_year' => $byYearArr,
            'by_personnel' => $byPersonnelArr,
            'by_program' => $byProgramArr,
        ];
        cache()->save($cacheKey, $result, 21600); // 6 hours

        return $this->respond([
            'success' => true,
            'data' => $result,
            'cached' => false,
        ]);
    }

    /**
     * GET program-summary: per-program breakdown (personnel, students, activities, downloads, facilities, page, news, research).
     */
    public function programSummary()
    {
        if ($r = $this->ensureExecutiveAccess()) {
            return $r;
        }

        $programs = $this->programModel->where('status', 'active')->orderBy('sort_order', 'ASC')->findAll();
        $researchByProgram = [];
        $cached = cache()->get('exec_research_stats');
        if (is_array($cached) && ! empty($cached['by_program'])) {
            foreach ($cached['by_program'] as $item) {
                $researchByProgram[$item['name']] = (int) ($item['count'] ?? 0);
            }
        }

        $levelLabels = ['bachelor' => 'ปริญญาตรี', 'master' => 'ปริญญาโท', 'doctorate' => 'ปริญญาเอก'];
        $rows = [];
        foreach ($programs as $p) {
            $id = (int) $p['id'];
            $name = $p['name_th'] ?? $p['name_en'] ?? 'โปรแกรม #' . $id;
            $level = $levelLabels[$p['level'] ?? ''] ?? ($p['level'] ?? '-');

            $personnelCount = 0;
            if ($this->db->tableExists('personnel_programs')) {
                $personnelCount = (int) $this->db->table('personnel_programs')->where('program_id', $id)->countAllResults();
            }

            $studentCount = 0;
            if ($this->db->tableExists('student_user')) {
                $studentCount = (int) $this->studentUserModel->where('program_id', $id)->where('status', 'active')->countAllResults();
            }

            $activityCount = 0;
            if ($this->db->tableExists('program_activities')) {
                $activityCount = (int) $this->db->table('program_activities')->where('program_id', $id)->countAllResults();
            }

            $downloadCount = 0;
            if ($this->db->tableExists('program_downloads')) {
                $downloadCount = (int) $this->db->table('program_downloads')->where('program_id', $id)->countAllResults();
            }

            $facilityCount = 0;
            if ($this->db->tableExists('program_facilities')) {
                $facilityCount = (int) $this->db->table('program_facilities')->where('program_id', $id)->countAllResults();
            }

            $pagePublished = false;
            if ($this->db->tableExists('program_pages')) {
                $page = $this->programPageModel->where('program_id', $id)->first();
                $pagePublished = ! empty($page['is_published']);
            }

            $newsCount = 0;
            if ($this->db->tableExists('news_tags') && $this->db->tableExists('news_news_tags')) {
                $tag = $this->db->table('news_tags')->where('slug', 'program_' . $id)->get()->getRowArray();
                if ($tag) {
                    $newsCount = (int) $this->db->table('news_news_tags')->where('news_tag_id', $tag['id'])->countAllResults();
                }
            }

            $researchCount = $researchByProgram[$name] ?? 0;

            $rows[] = [
                'program_id' => $id,
                'program_name' => $name,
                'program_level' => $level,
                'personnel_count' => $personnelCount,
                'student_count' => $studentCount,
                'activity_count' => $activityCount,
                'download_count' => $downloadCount,
                'facility_count' => $facilityCount,
                'page_published' => $pagePublished,
                'news_count' => $newsCount,
                'research_count' => $researchCount,
            ];
        }

        return $this->respond([
            'success' => true,
            'data' => ['programs' => $rows],
        ]);
    }
}

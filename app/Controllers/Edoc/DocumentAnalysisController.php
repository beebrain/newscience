<?php

namespace App\Controllers\Edoc;

use App\Models\Edoc\EdoctitleModel;
use CodeIgniter\API\ResponseTrait;

/**
 * Document Analysis Controller with Role-Based Access Control
 *
 * This controller manages document analytics with differentiated access levels.
 * Administrators can view comprehensive repository metrics across all documents,
 * while standard users see analytics confined to documents they're tagged in.
 */
class DocumentAnalysisController extends EdocBaseController
{
    use ResponseTrait;

    protected $edoctitleModel;
    protected $db;

    /**
     * Initialize controller dependencies and validate session
     */
    public function __construct()
    {
        $this->edoctitleModel = new EdoctitleModel();
        $this->db = \Config\Database::connect();
    }

    /**
     * Primary dashboard view with role-specific analytics
     *
     * @return string Rendered view
     */
    public function index()
    {
        $userId = $this->edocUser['uid'];

        $data = [
            'isAdmin' => $this->isEdocAdmin,
            'userId' => $userId,
            'title' => 'Document Analytics Dashboard',
            'user' => $this->getUserDetails($userId),
            'edocUser' => $this->edocUser,
            'isEdocAdmin' => $this->isEdocAdmin,
        ];

        return view('edoc/analysis/document_dashboard', $data);
    }

    /**
     * API endpoint for document summary metrics with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getSummaryMetrics()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $userId = $this->edocUser['uid'];

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        $totalDocuments = $builder->countAllResults();

        $builderPages = clone $builder;
        $totalPages = $builderPages->selectSum('pages')->get()->getRowArray()['pages'] ?? 0;

        $builderPaper = clone $builder;
        $totalPaper = $builderPaper->select('SUM(pages * copynum) as total_paper')
            ->get()
            ->getRowArray()['total_paper'] ?? 0;

        $builderTypes = clone $builder;
        $documentTypes = $builderTypes->select('doctype')->distinct()->countAllResults();

        $builderOwners = clone $builder;
        $uniqueOwners = $builderOwners->select('owner')->distinct()->countAllResults();

        $builderRecent = clone $builder;
        $recentActivity = $builderRecent->select('DATE(regisdate) as date, COUNT(*) as count')
            ->groupBy('DATE(regisdate)')
            ->orderBy('date', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $avgPages = $totalDocuments > 0 ? $totalPages / $totalDocuments : 0;

        $data = [
            'access_level' => $this->isEdocAdmin ? 'administrator' : 'standard_user',
            'metrics' => [
                'total_documents' => $totalDocuments,
                'total_pages' => $totalPages,
                'total_paper_usage' => $totalPaper,
                'document_types' => $documentTypes,
                'unique_owners' => $uniqueOwners,
                'average_pages' => round($avgPages, 2)
            ],
            'recent_activity' => $recentActivity,
            'period' => $period
        ];

        return $this->respond($data);
    }

    /**
     * API endpoint for document type distribution with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getDocTypeDistribution()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $userId = $this->edocUser['uid'];

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        $data = $builder->select('doctype, COUNT(*) as count, SUM(pages) as total_pages')
            ->groupBy('doctype')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        return $this->respond($data);
    }

    /**
     * API endpoint for monthly trend analysis with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getMonthlyTrend()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $userId = $this->edocUser['uid'];

        $dateFormat = $this->getDateFormatByPeriod($period);

        $sql = "SELECT $dateFormat as period, COUNT(*) as count, SUM(pages) as total_pages 
                FROM edoctitle e 
                WHERE 1=1";

        $periodFilter = $this->getPeriodWhereClause($period);
        if ($periodFilter) {
            $sql .= " AND " . ltrim($periodFilter, "WHERE ");
        }

        if (!$this->isEdocAdmin) {
            $sql .= " AND FIND_IN_SET('$userId', e.participant) > 0";
        }

        $sql .= " GROUP BY period ORDER BY ";

        if ($period == 'month') {
            $sql .= "STR_TO_DATE(period, '%d %b')";
        } else if ($period == 'quarter' || $period == 'year') {
            $sql .= "STR_TO_DATE(period, '%M')";
        } else {
            $sql .= "period";
        }

        $query = $this->db->query($sql);
        $data = $query->getResultArray();

        return $this->respond($data);
    }

    /**
     * API endpoint for top document owners with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getTopOwners()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $limit = (int)($this->request->getGet('limit') ?? 10);
        $userId = $this->edocUser['uid'];

        if ($limit < 1 || $limit > 50) {
            $limit = 10;
        }

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        $data = $builder->select('owner, COUNT(*) as count, SUM(pages) as total_pages')
            ->groupBy('owner')
            ->orderBy('count', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();

        foreach ($data as &$item) {
            if (!empty($item['owner'])) {
                $ownerDetails = $this->findUserByName($item['owner']);
                if ($ownerDetails) {
                    $item['owner_details'] = $ownerDetails;
                }
            }
        }

        return $this->respond($data);
    }

    /**
     * API endpoint for page distribution analysis with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getPageDistribution()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $userId = $this->edocUser['uid'];

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        $builder->select("
            CASE 
                WHEN pages <= 5 THEN '1-5 pages'
                WHEN pages > 5 AND pages <= 10 THEN '6-10 pages'
                WHEN pages > 10 AND pages <= 20 THEN '11-20 pages'
                WHEN pages > 20 AND pages <= 50 THEN '21-50 pages'
                ELSE 'More than 50 pages'
            END as page_range,
            COUNT(*) as count,
            SUM(pages) as total_pages
        ");

        $builder->groupBy('page_range');
        $builder->orderBy("FIELD(page_range, '1-5 pages', '6-10 pages', '11-20 pages', '21-50 pages', 'More than 50 pages')");

        $data = $builder->get()->getResultArray();

        return $this->respond($data);
    }

    /**
     * API endpoint for advanced document analytics with role-based filtering
     *
     * @return \CodeIgniter\HTTP\Response
     */
    public function getAdvancedAnalytics()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $metric = $this->request->getGet('metric') ?? 'doc_count';
        $dimension = $this->request->getGet('dimension') ?? 'doctype';
        $userId = $this->edocUser['uid'];

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        if ($dimension === 'doctype') {
            $builder->select("
                doctype as dimension, 
                COUNT(*) as doc_count, 
                AVG(pages) as avg_pages, 
                SUM(pages * copynum) as total_paper,
                MAX(pages) as max_pages,
                MIN(pages) as min_pages
            ");
            $builder->groupBy('doctype');
            $builder->orderBy($metric, 'DESC');
        } else if ($dimension === 'owner') {
            $builder->select("
                owner as dimension, 
                COUNT(*) as doc_count, 
                AVG(pages) as avg_pages, 
                SUM(pages * copynum) as total_paper,
                MAX(pages) as max_pages,
                MIN(pages) as min_pages
            ");
            $builder->groupBy('owner');
            $builder->orderBy($metric, 'DESC');
        } else if ($dimension === 'time') {
            $dateFormat = $this->getDateFormatByPeriod($period);

            $builder->select("
                $dateFormat as dimension,
                COUNT(*) as doc_count, 
                AVG(pages) as avg_pages, 
                SUM(pages * copynum) as total_paper,
                MAX(pages) as max_pages,
                MIN(pages) as min_pages
            ");
            $builder->groupBy('dimension');

            if ($period === 'month') {
                $builder->orderBy("STR_TO_DATE(dimension, '%d %b')", 'ASC');
            } else if ($period === 'quarter' || $period === 'year') {
                $builder->orderBy("STR_TO_DATE(dimension, '%M')", 'ASC');
            } else {
                $builder->orderBy('dimension', 'ASC');
            }
        } else if ($dimension === 'participant') {
            if ($this->isEdocAdmin) {
                return $this->getParticipantAnalytics($period, $metric);
            } else {
                return $this->respond([
                    'error' => 'Administrator privileges required for participant analytics',
                    'access_denied' => true
                ], 403);
            }
        }

        $data = $builder->get()->getResultArray();

        foreach ($data as &$item) {
            $item['avg_pages'] = round($item['avg_pages'], 2);
            $item['total_paper'] = (int)$item['total_paper'];
            $item['doc_count'] = (int)$item['doc_count'];
        }

        return $this->respond($data);
    }

    /**
     * Generate analytics specifically for document participants
     * Only accessible by administrators
     */
    private function getParticipantAnalytics($period = 'all', $metric = 'doc_count')
    {
        $sql = "SELECT 
                    TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.participant, ',', numbers.n), ',', -1)) as dimension,
                    COUNT(*) as doc_count, 
                    AVG(t.pages) as avg_pages, 
                    SUM(t.pages * t.copynum) as total_paper,
                    MAX(t.pages) as max_pages,
                    MIN(t.pages) as min_pages
                FROM
                    (SELECT participant, pages, copynum FROM edoctitle WHERE participant IS NOT NULL AND participant <> ''";

        $periodClause = $this->getPeriodWhereClause($period);
        if ($periodClause) {
            $sql .= " " . $periodClause;
        }

        $sql .= ") t 
                CROSS JOIN
                    (SELECT 1 as n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10) numbers
                WHERE
                    n <= 1 + (LENGTH(t.participant) - LENGTH(REPLACE(t.participant, ',', '')))
                    AND TRIM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.participant, ',', numbers.n), ',', -1)) <> ''
                GROUP BY dimension";

        if ($metric === 'avg_pages') {
            $sql .= " ORDER BY avg_pages DESC";
        } else if ($metric === 'total_paper') {
            $sql .= " ORDER BY total_paper DESC";
        } else {
            $sql .= " ORDER BY doc_count DESC";
        }

        $query = $this->db->query($sql);
        $result = $query->getResultArray();

        foreach ($result as &$item) {
            $item['avg_pages'] = round($item['avg_pages'], 2);
            $item['total_paper'] = (int)$item['total_paper'];
            $item['doc_count'] = (int)$item['doc_count'];

            $userDetails = $this->getUserDetails($item['dimension']);
            if ($userDetails) {
                $item['user_details'] = $userDetails;
            }
        }

        return $this->respond($result);
    }

    /**
     * Generate comprehensive PDF report of document analytics
     */
    public function exportAnalysisReport()
    {
        $period = $this->request->getGet('period') ?? 'all';
        $userId = $this->edocUser['uid'];

        $user = $this->getUserDetails($userId);

        $builder = $this->db->table('edoctitle e');
        $this->applyPeriodFilter($builder, $period);

        if (!$this->isEdocAdmin) {
            $builder->where("FIND_IN_SET('$userId', e.participant) > 0");
        }

        $totalDocuments = $builder->countAllResults();

        $builderPages = clone $builder;
        $totalPages = $builderPages->selectSum('pages')->get()->getRowArray()['pages'] ?? 0;

        $builderPaper = clone $builder;
        $totalPaper = $builderPaper->selectSum('pages * copynum', 'total_paper')->get()->getRowArray()['total_paper'] ?? 0;

        $builderTypes = clone $builder;
        $documentTypes = $builderTypes->select('doctype, COUNT(*) as count')
            ->groupBy('doctype')
            ->orderBy('count', 'DESC')
            ->get()
            ->getResultArray();

        $builderOwners = clone $builder;
        $topOwners = $builderOwners->select('owner, COUNT(*) as count')
            ->groupBy('owner')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();

        $sqlTrend = "SELECT DATE_FORMAT(regisdate, '%Y-%m') as month, COUNT(*) as count 
                     FROM edoctitle e WHERE 1=1";

        $periodFilter = $this->getPeriodWhereClause($period);
        if ($periodFilter) {
            $sqlTrend .= " AND " . ltrim($periodFilter, "WHERE ");
        }

        if (!$this->isEdocAdmin) {
            $sqlTrend .= " AND FIND_IN_SET('$userId', e.participant) > 0";
        }

        $sqlTrend .= " GROUP BY month ORDER BY month ASC";
        $monthlyTrend = $this->db->query($sqlTrend)->getResultArray();

        $data = [
            'title' => 'Document Analysis Report',
            'period' => $this->getPeriodLabel($period),
            'generated_at' => date('Y-m-d H:i:s'),
            'user' => $user,
            'access_level' => $this->isEdocAdmin ? 'Administrator' : 'Standard User',
            'metrics' => [
                'total_documents' => $totalDocuments,
                'total_pages' => $totalPages,
                'total_paper_usage' => $totalPaper,
                'document_types_count' => count($documentTypes)
            ],
            'document_types' => $documentTypes,
            'top_owners' => $topOwners,
            'monthly_trend' => $monthlyTrend
        ];

        $dompdf = new \Dompdf\Dompdf();
        $html = view('edoc/analysis/export_pdf', $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'document_analysis_report_' . ($this->isEdocAdmin ? 'admin_' : 'user_') . date('Y-m-d') . '.pdf';
        return $dompdf->stream($filename);
    }

    /**
     * Retrieve detailed user information
     */
    private function getUserDetails($userId)
    {
        $user = $this->db->table('user')
            ->where('uid', $userId)
            ->get()
            ->getRowArray();

        if ($user) {
            return [
                'id' => $user['uid'],
                'name' => trim(($user['thai_name'] ?? '') . ' ' . ($user['thai_lastname'] ?? '')),
                'email' => $user['email'],
                'title' => $user['title'] ?? ''
            ];
        }

        return null;
    }

    /**
     * Find a user by their name
     */
    private function findUserByName($name)
    {
        $users = $this->db->table('user')
            ->select('uid, thai_name, thai_lastname, email, title')
            ->like('CONCAT(thai_name, " ", thai_lastname)', $name)
            ->get()
            ->getResultArray();

        if (!empty($users)) {
            $user = $users[0];
            return [
                'id' => $user['uid'],
                'name' => trim($user['thai_name'] . ' ' . $user['thai_lastname']),
                'email' => $user['email'],
                'title' => $user['title'] ?? ''
            ];
        }

        return null;
    }

    /**
     * Apply time period filter to a query builder
     */
    private function applyPeriodFilter($builder, $period = 'all')
    {
        if ($period === 'year') {
            $builder->where('YEAR(regisdate)', date('Y'));
        } else if ($period === 'quarter') {
            $builder->where('regisdate >=', date('Y-m-d', strtotime('-3 months')));
        } else if ($period === 'month') {
            $builder->where('regisdate >=', date('Y-m-d', strtotime('-1 month')));
        } else if ($period === 'week') {
            $builder->where('regisdate >=', date('Y-m-d', strtotime('-1 week')));
        }

        return $builder;
    }

    /**
     * Get WHERE clause for a time period filter
     */
    private function getPeriodWhereClause($period = 'all')
    {
        if ($period === 'year') {
            return "WHERE YEAR(regisdate) = " . date('Y');
        } else if ($period === 'quarter') {
            return "WHERE regisdate >= '" . date('Y-m-d', strtotime('-3 months')) . "'";
        } else if ($period === 'month') {
            return "WHERE regisdate >= '" . date('Y-m-d', strtotime('-1 month')) . "'";
        } else if ($period === 'week') {
            return "WHERE regisdate >= '" . date('Y-m-d', strtotime('-1 week')) . "'";
        }

        return "";
    }

    /**
     * Get appropriate date format SQL for different time periods
     */
    private function getDateFormatByPeriod($period)
    {
        if ($period === 'all' || $period === 'year') {
            return "DATE_FORMAT(regisdate, '%Y-%m')";
        } else if ($period === 'quarter') {
            return "DATE_FORMAT(regisdate, '%M')";
        } else if ($period === 'month') {
            return "DATE_FORMAT(regisdate, '%d %b')";
        } else {
            return "DATE_FORMAT(regisdate, '%Y-%m-%d')";
        }
    }

    /**
     * Get human-readable label for a time period
     */
    private function getPeriodLabel($period)
    {
        switch ($period) {
            case 'year':
                return 'Current Year (' . date('Y') . ')';
            case 'quarter':
                return 'Last 3 Months';
            case 'month':
                return 'Last 30 Days';
            case 'week':
                return 'Last 7 Days';
            default:
                return 'All Time';
        }
    }
}

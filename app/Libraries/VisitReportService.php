<?php

namespace App\Libraries;

class VisitReportService
{
    private $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    public function getDefaultFilters(array $input = []): array
    {
        $end = $this->validDate($input['end_date'] ?? null) ?: date('Y-m-d');
        $start = $this->validDate($input['start_date'] ?? null) ?: date('Y-m-d', strtotime($end . ' -29 days'));
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }

        $dimension = $input['dimension'] ?? 'content_type';
        if (! array_key_exists($dimension, $this->getDimensionOptions())) {
            $dimension = 'content_type';
        }

        return [
            'start_date' => $start,
            'end_date' => $end,
            'dimension' => $dimension,
        ];
    }

    public function getDimensionOptions(): array
    {
        return [
            'content_type' => 'ประเภทเนื้อหา',
            'content_title_snapshot' => 'เนื้อหาที่เข้าชม',
            'device_type' => 'ประเภทอุปกรณ์',
            'browser_family' => 'เบราว์เซอร์',
            'os_family' => 'ระบบปฏิบัติการ',
            'traffic_source' => 'แหล่งที่มา',
            'referrer_host' => 'เว็บไซต์อ้างอิง',
        ];
    }

    public function buildReport(array $filters): array
    {
        if (! $this->db->tableExists('page_views')) {
            return $this->emptyReport($filters);
        }

        $summary = $this->summary($filters);
        $trend = $this->trendByDay($filters);
        $dimension = $this->dimensionBreakdown($filters, $filters['dimension']);

        return [
            'filters' => $filters,
            'dimension_options' => $this->getDimensionOptions(),
            'dimension_label' => $this->getDimensionOptions()[$filters['dimension']] ?? $filters['dimension'],
            'summary' => $summary,
            'trend' => $trend,
            'dimension' => $dimension,
            'top_pages' => $this->topPages($filters),
            'content_breakdown' => $this->dimensionBreakdown($filters, 'content_type', 20),
            'device_breakdown' => $this->dimensionBreakdown($filters, 'device_type', 20),
            'browser_breakdown' => $this->dimensionBreakdown($filters, 'browser_family', 20),
            'source_breakdown' => $this->dimensionBreakdown($filters, 'traffic_source', 20),
        ];
    }

    public function dimensionBreakdown(array $filters, string $field, int $limit = 30): array
    {
        if (! $this->db->tableExists('page_views')) {
            return ['labels' => [], 'data' => [], 'rows' => []];
        }

        $selectField = $this->db->fieldExists($field, 'page_views') ? $field : 'route';
        $builder = $this->db->table('page_views')
            ->select($selectField . ' AS label, COUNT(*) AS views, COUNT(DISTINCT session_id) AS unique_visitors')
            ->groupBy($selectField)
            ->orderBy('views', 'DESC')
            ->limit($limit);
        $this->applyDateFilter($builder, $filters);

        $rows = [];
        foreach ($builder->get()->getResultArray() as $row) {
            $label = trim((string) ($row['label'] ?? ''));
            if ($label === '') {
                $label = '(ไม่ระบุ)';
            }
            $rows[] = [
                'label' => $this->labelFor($field, $label),
                'views' => (int) $row['views'],
                'unique_visitors' => (int) ($row['unique_visitors'] ?? 0),
            ];
        }

        return [
            'labels' => array_column($rows, 'label'),
            'data' => array_column($rows, 'views'),
            'rows' => $rows,
        ];
    }

    public function topPages(array $filters, int $limit = 20): array
    {
        if (! $this->db->tableExists('page_views')) {
            return [];
        }

        $hasTitle = $this->db->fieldExists('content_title_snapshot', 'page_views');
        $hasType = $this->db->fieldExists('content_type', 'page_views');
        $hasId = $this->db->fieldExists('content_id', 'page_views');

        $select = [
            'route',
            'MIN(url) AS url',
            'COUNT(*) AS views',
            'COUNT(DISTINCT session_id) AS unique_visitors',
        ];
        if ($hasTitle) {
            $select[] = 'content_title_snapshot';
        }
        if ($hasType) {
            $select[] = 'content_type';
        }
        if ($hasId) {
            $select[] = 'content_id';
        }

        $builder = $this->db->table('page_views')
            ->select(implode(', ', $select))
            ->groupBy('route')
            ->orderBy('views', 'DESC')
            ->limit($limit);
        if ($hasTitle) {
            $builder->groupBy('content_title_snapshot');
        }
        if ($hasType) {
            $builder->groupBy('content_type');
        }
        if ($hasId) {
            $builder->groupBy('content_id');
        }
        $this->applyDateFilter($builder, $filters);

        $rows = [];
        foreach ($builder->get()->getResultArray() as $row) {
            $title = $hasTitle ? ($row['content_title_snapshot'] ?? '') : '';
            $rows[] = [
                'title' => $title !== '' ? $title : ($row['route'] ?? $row['url'] ?? '-'),
                'route' => $row['route'] ?? '',
                'content_type' => $this->labelFor('content_type', (string) ($row['content_type'] ?? 'other')),
                'content_id' => isset($row['content_id']) ? (int) $row['content_id'] : null,
                'views' => (int) $row['views'],
                'unique_visitors' => (int) ($row['unique_visitors'] ?? 0),
            ];
        }

        return $rows;
    }

    private function summary(array $filters): array
    {
        $builder = $this->db->table('page_views');
        $this->applyDateFilter($builder, $filters);
        $totalViews = (int) (clone $builder)->countAllResults(false);

        $uniqueBuilder = $this->db->table('page_views')->select('COUNT(DISTINCT session_id) AS cnt');
        $this->applyDateFilter($uniqueBuilder, $filters);
        $uniqueVisitors = (int) (($uniqueBuilder->get()->getRow()->cnt ?? 0));

        $days = max(1, ((int) floor((strtotime($filters['end_date']) - strtotime($filters['start_date'])) / 86400)) + 1);

        return [
            'total_views' => $totalViews,
            'unique_visitors' => $uniqueVisitors,
            'average_per_day' => round($totalViews / $days, 1),
            'days' => $days,
        ];
    }

    private function trendByDay(array $filters): array
    {
        $builder = $this->db->table('page_views')
            ->select('DATE(created_at) AS date, COUNT(*) AS views, COUNT(DISTINCT session_id) AS unique_visitors')
            ->groupBy('date')
            ->orderBy('date', 'ASC');
        $this->applyDateFilter($builder, $filters);

        $rows = [];
        foreach ($builder->get()->getResultArray() as $row) {
            $rows[] = [
                'date' => $row['date'],
                'views' => (int) $row['views'],
                'unique_visitors' => (int) ($row['unique_visitors'] ?? 0),
            ];
        }

        return [
            'labels' => array_column($rows, 'date'),
            'views' => array_column($rows, 'views'),
            'unique_visitors' => array_column($rows, 'unique_visitors'),
            'rows' => $rows,
        ];
    }

    private function applyDateFilter($builder, array $filters): void
    {
        $builder->where('created_at >=', $filters['start_date'] . ' 00:00:00');
        $builder->where('created_at <=', $filters['end_date'] . ' 23:59:59');
    }

    private function emptyReport(array $filters): array
    {
        return [
            'filters' => $filters,
            'dimension_options' => $this->getDimensionOptions(),
            'dimension_label' => $this->getDimensionOptions()[$filters['dimension']] ?? $filters['dimension'],
            'summary' => ['total_views' => 0, 'unique_visitors' => 0, 'average_per_day' => 0, 'days' => 0],
            'trend' => ['labels' => [], 'views' => [], 'unique_visitors' => [], 'rows' => []],
            'dimension' => ['labels' => [], 'data' => [], 'rows' => []],
            'top_pages' => [],
            'content_breakdown' => ['labels' => [], 'data' => [], 'rows' => []],
            'device_breakdown' => ['labels' => [], 'data' => [], 'rows' => []],
            'browser_breakdown' => ['labels' => [], 'data' => [], 'rows' => []],
            'source_breakdown' => ['labels' => [], 'data' => [], 'rows' => []],
        ];
    }

    private function validDate($value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }
        return $value;
    }

    private function labelFor(string $field, string $value): string
    {
        $maps = [
            'content_type' => [
                'home' => 'หน้าแรก',
                'static' => 'หน้า Static',
                'news_list' => 'รายการข่าว',
                'news' => 'ข่าว',
                'event_list' => 'รายการกิจกรรม',
                'event' => 'กิจกรรม',
                'program' => 'หลักสูตร',
                'document' => 'เอกสาร',
                'other' => 'อื่นๆ',
            ],
            'device_type' => [
                'desktop' => 'Desktop',
                'mobile' => 'Mobile',
                'tablet' => 'Tablet',
                'bot' => 'Bot/Crawler',
            ],
            'traffic_source' => [
                'direct' => 'Direct',
                'internal' => 'Internal',
                'search' => 'Search',
                'social' => 'Social',
                'referral' => 'Referral',
            ],
        ];

        return $maps[$field][$value] ?? $value;
    }
}

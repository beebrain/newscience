<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\VisitReportService;

class VisitReports extends BaseController
{
    private VisitReportService $reportService;

    public function __construct()
    {
        $this->reportService = new VisitReportService();
    }

    public function index()
    {
        $filters = $this->reportService->getDefaultFilters($this->request->getGet());
        $report = $this->reportService->buildReport($filters);

        return view('admin/visit_reports/index', [
            'page_title' => 'รายงานผู้เข้าชมเว็บไซต์',
            'report' => $report,
        ]);
    }

    public function data()
    {
        $filters = $this->reportService->getDefaultFilters($this->request->getGet());
        return $this->response->setJSON([
            'success' => true,
            'data' => $this->reportService->buildReport($filters),
        ]);
    }

    public function export()
    {
        $filters = $this->reportService->getDefaultFilters($this->request->getGet());
        $report = $this->reportService->buildReport($filters);

        $filename = 'visit-report-' . $filters['start_date'] . '-to-' . $filters['end_date'] . '.csv';
        $out = fopen('php://temp', 'r+');

        fputcsv($out, ['รายงานผู้เข้าชมเว็บไซต์']);
        fputcsv($out, ['ช่วงวันที่', $filters['start_date'] . ' ถึง ' . $filters['end_date']]);
        fputcsv($out, []);
        fputcsv($out, ['สรุป', 'จำนวน']);
        fputcsv($out, ['จำนวนเข้าชมรวม', $report['summary']['total_views']]);
        fputcsv($out, ['ผู้เข้าชมไม่ซ้ำ', $report['summary']['unique_visitors']]);
        fputcsv($out, ['ค่าเฉลี่ยต่อวัน', $report['summary']['average_per_day']]);

        fputcsv($out, []);
        fputcsv($out, ['แนวโน้มรายวัน']);
        fputcsv($out, ['วันที่', 'จำนวนเข้าชม', 'ผู้เข้าชมไม่ซ้ำ']);
        foreach ($report['trend']['rows'] as $row) {
            fputcsv($out, [$row['date'], $row['views'], $row['unique_visitors']]);
        }

        fputcsv($out, []);
        fputcsv($out, ['สรุปตาม' . $report['dimension_label']]);
        fputcsv($out, [$report['dimension_label'], 'จำนวนเข้าชม', 'ผู้เข้าชมไม่ซ้ำ']);
        foreach ($report['dimension']['rows'] as $row) {
            fputcsv($out, [$row['label'], $row['views'], $row['unique_visitors']]);
        }

        fputcsv($out, []);
        fputcsv($out, ['หน้า/เนื้อหาที่เข้าชมสูงสุด']);
        fputcsv($out, ['ชื่อหน้า/เนื้อหา', 'Route', 'ประเภท', 'จำนวนเข้าชม', 'ผู้เข้าชมไม่ซ้ำ']);
        foreach ($report['top_pages'] as $row) {
            fputcsv($out, [$row['title'], $row['route'], $row['content_type'], $row['views'], $row['unique_visitors']]);
        }

        rewind($out);
        $body = "\xEF\xBB\xBF" . stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($body);
    }
}

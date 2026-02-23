<?php

namespace App\Libraries;

use Smalot\PdfParser\Parser;

/**
 * PDF Text Extractor - ดึงข้อความจาก PDF Template
 */
class PdfTextExtractor
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * ดึงข้อความทั้งหมดจาก PDF file
     */
    public function extractText(string $pdfPath): string
    {
        if (!file_exists($pdfPath)) {
            return '';
        }

        try {
            $pdf = $this->parser->parseFile($pdfPath);
            return $pdf->getText();
        } catch (\Exception $e) {
            log_message('error', 'PDF Text Extraction failed: ' . $e->getMessage());
            return '';
        }
    }

    /**
     * ดึงข้อความและตำแหน่ง (coordinates) จาก PDF
     */
    public function extractTextWithDetails(string $pdfPath): array
    {
        if (!file_exists($pdfPath)) {
            return [];
        }

        try {
            $pdf = $this->parser->parseFile($pdfPath);
            $pages = $pdf->getPages();
            $details = [];

            foreach ($pages as $pageNum => $page) {
                $text = $page->getText();
                $details[] = [
                    'page' => $pageNum + 1,
                    'text' => $text,
                ];
            }

            return $details;
        } catch (\Exception $e) {
            log_message('error', 'PDF Details Extraction failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ค้นหาคำหรือ pattern ในข้อความ PDF
     */
    public function findTextPatterns(string $text, array $patterns): array
    {
        $found = [];

        foreach ($patterns as $pattern) {
            if (stripos($text, $pattern) !== false) {
                $found[] = [
                    'pattern' => $pattern,
                    'found' => true,
                    'context' => $this->getContext($text, $pattern, 50),
                ];
            }
        }

        return $found;
    }

    /**
     * แนะนำ field names จากข้อความใน PDF
     */
    public function suggestFields(string $text): array
    {
        $suggestions = [];

        // Common Thai patterns for certificate fields
        $patterns = [
            ['name' => 'student_name', 'keywords' => ['ชื่อ', 'นามสกุล', 'ชื่อ-สกุล', 'Name', 'Student']],
            ['name' => 'student_id', 'keywords' => ['รหัสนักศึกษา', 'Student ID', 'รหัสประจำตัว']],
            ['name' => 'program_name', 'keywords' => ['หลักสูตร', 'สาขาวิชา', 'Program', 'Major']],
            ['name' => 'faculty_name', 'keywords' => ['คณะ', 'Faculty']],
            ['name' => 'date', 'keywords' => ['วันที่', 'Date', 'ออกให้ ณ วันที่']],
            ['name' => 'grade', 'keywords' => ['ผลการเรียน', 'GPA', 'เกรด', 'Grade']],
            ['name' => 'degree', 'keywords' => ['ปริญญา', 'Degree', 'Bachelor', 'Master', 'Doctor']],
        ];

        foreach ($patterns as $pattern) {
            foreach ($pattern['keywords'] as $keyword) {
                if (stripos($text, $keyword) !== false) {
                    $suggestions[$pattern['name']] = [
                        'label' => $keyword,
                        'confidence' => 'high',
                    ];
                    break;
                }
            }
        }

        return $suggestions;
    }

    /**
     * สร้าง field mapping เริ่มต้นจาก PDF
     */
    public function generateFieldMapping(string $pdfPath): array
    {
        $text = $this->extractText($pdfPath);
        $suggestions = $this->suggestFields($text);

        $defaultMapping = [
            'student_name' => ['x' => 100, 'y' => 200, 'font_size' => 16, 'suggested' => isset($suggestions['student_name'])],
            'student_id' => ['x' => 100, 'y' => 230, 'font_size' => 14, 'suggested' => isset($suggestions['student_id'])],
            'program_name' => ['x' => 100, 'y' => 260, 'font_size' => 14, 'suggested' => isset($suggestions['program_name'])],
            'date' => ['x' => 100, 'y' => 290, 'font_size' => 12, 'suggested' => isset($suggestions['date'])],
        ];

        // Add detected suggestions
        foreach ($suggestions as $field => $info) {
            if (!isset($defaultMapping[$field])) {
                $defaultMapping[$field] = [
                    'x' => 100,
                    'y' => 320 + (count($defaultMapping) * 30),
                    'font_size' => 14,
                    'suggested' => true,
                ];
            }
        }

        return $defaultMapping;
    }

    /**
     * ดึงบริบทรอบๆ คำที่พบ
     */
    protected function getContext(string $text, string $pattern, int $length): string
    {
        $pos = stripos($text, $pattern);
        if ($pos === false) {
            return '';
        }

        $start = max(0, $pos - $length);
        $end = min(strlen($text), $pos + strlen($pattern) + $length);

        return substr($text, $start, $end - $start);
    }
}

<?php

namespace App\Commands;

use App\Libraries\ResearchRecordCvSyncMerge;
use App\Models\CvSectionModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ทดสอบหน้า publication + ข้อมูลใน DB (local/CI)
 *
 * php spark cv:test-publication-page
 */
class TestCvPublicationPage extends BaseCommand
{
    protected $group       = 'CV';
    protected $name        = 'cv:test-publication-page';
    protected $description = 'ทดสอบหน้า CV publication (view, route, section ใน DB)';
    protected $usage       = 'cv:test-publication-page';

    public function run(array $params): int
    {
        CLI::write('=== CV Publication Page (local) ===', 'cyan');
        CLI::newLine();

        $fail = 0;

        $viewFile = APPPATH . 'Views/user/profile/cv_publication_entry.php';
        $jsFile   = FCPATH . 'assets/js/cv-publication-entry-page.js';
        foreach ([$viewFile => 'view', $jsFile => 'js'] as $path => $label) {
            if (is_file($path)) {
                CLI::write('[PASS] ' . $label . ': ' . $path, 'green');
            } else {
                CLI::write('[FAIL] missing ' . $label . ': ' . $path, 'red');
                $fail++;
            }
        }

        $viewSrc = (string) file_get_contents($viewFile);
        foreach (['cv-pub-form', 'cv-pub-ai-panel', 'cv-publication-entry-page.js', 'cv_publication_page', 'เพิ่มผลงานตีพิมพ์'] as $needle) {
            if (str_contains($viewSrc, $needle)) {
                CLI::write('[PASS] view template: ' . $needle, 'green');
            } else {
                CLI::write('[FAIL] view template missing: ' . $needle, 'red');
                $fail++;
            }
        }
        CLI::write('[INFO] render ใน PHPUnit: ./vendor/bin/phpunit tests/unit/CvPublicationEntryPageTest.php', 'white');

        $base = rtrim((string) config(\Config\App::class)->baseURL, '/');
        $url  = $base . '/dashboard/profile/cv/publication?section_id=1';
        CLI::write('URL ตัวอย่าง (ต้องล็อกอิน): ' . $url, 'yellow');

        try {
            $model = new CvSectionModel();
            $found = null;
            foreach ($model->select('id, personnel_id, type, title')->orderBy('id', 'DESC')->findAll(100) as $row) {
                if (ResearchRecordCvSyncMerge::isPublicationCvSection($row)) {
                    $found = $row;
                    break;
                }
            }
            if ($found !== null) {
                $sid = (int) ($found['id'] ?? 0);
                CLI::write('[PASS] publication section id=' . $sid . ' title=' . ($found['title'] ?? ''), 'green');
                CLI::write('     เปิด: ' . $base . '/dashboard/profile/cv/publication?section_id=' . $sid, 'white');
                CLI::write('     AI:   ' . $base . '/dashboard/profile/cv/publication?section_id=' . $sid . '&ai=1', 'white');
            } else {
                CLI::write('[WARN] ไม่พบหัวข้อ publication ใน DB', 'yellow');
            }
        } catch (\Throwable $e) {
            CLI::write('[WARN] DB: ' . $e->getMessage(), 'yellow');
        }

        CLI::newLine();
        if ($fail > 0) {
            CLI::write('Summary: FAILED (' . $fail . ' checks)', 'red');

            return 1;
        }
        CLI::write('Summary: OK — เปิดเบราว์เซอร์หลังล็อกอิน ตาม URL ด้านบน', 'green');

        return 0;
    }
}

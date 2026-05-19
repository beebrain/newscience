<?php

namespace App\Commands;

use App\Libraries\AiPublicationParser;
use App\Libraries\CvAiFileStorage;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\AiCv;

/**
 * ทดสอบ workflow CV AI (อัปโหลด → /cv-ai/file → n8n) — สำหรับ agent/CI
 *
 * ใช้: php spark cv:test-ai-workflow [--live-n8n] [--sample-url=URL]
 */
class TestCvAiWorkflow extends BaseCommand
{
    protected $group       = 'CV';
    protected $name        = 'cv:test-ai-workflow';
    protected $description = 'ทดสอบ workflow CV AI (config, storage, parser, optional live n8n)';
    protected $usage       = 'cv:test-ai-workflow [--live-n8n] [--sample-url=https://…]';
    protected $options     = [
        '--live-n8n'   => 'เรียก n8n จริงด้วย URL ตัวอย่าง',
        '--sample-url' => 'URL สำหรับทดสอบ n8n (default: W3C dummy PDF)',
    ];

    private int $pass = 0;

    private int $fail = 0;

    private int $warn = 0;

    public function run(array $params): int
    {
        $liveN8n   = CLI::getOption('live-n8n') !== null;
        $sampleUrl = CLI::getOption('sample-url') ?: 'https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf';

        CLI::write('=== CV AI Workflow Test ===', 'cyan');
        CLI::newLine();

        $cfg = config(AiCv::class);
        $this->step('config: AiCv::isReady()', $cfg->isReady(), $cfg->isReady() ? 'OK' : 'ตั้ง AI_CV_N8N_URL ใน .env');
        $this->step('config: n8n URL', $cfg->n8nUrl !== '', $cfg->n8nUrl !== '' ? $cfg->n8nUrl : '(ว่าง)');
        $pubBase = $cfg->filePublicBaseUrl !== '' ? $cfg->filePublicBaseUrl : (string) config(\Config\App::class)->baseURL;
        if (str_contains($pubBase, 'localhost') || str_contains($pubBase, '127.0.0.1')) {
            $this->step('config: file public URL for n8n', false, 'ตั้ง AI_CV_FILE_PUBLIC_BASE_URL เป็นโดเมนจริง', true);
        } else {
            $this->step('config: file public base', $pubBase !== '', $pubBase);
        }

        $fixture = ROOTPATH . 'tests/fixtures/cv_ai_n8n_response_sample.json';
        $this->step('fixture exists', is_file($fixture), $fixture);
        if (is_file($fixture)) {
            $decoded = json_decode((string) file_get_contents($fixture), true);
            $out     = is_array($decoded) ? ($decoded['output'] ?? null) : null;
            $norm    = is_array($out) ? AiPublicationParser::normalizePublicationFromRrLikeArray($out) : ['success' => false];
            $this->step(
                'parser: normalize fixture',
                (bool) ($norm['success'] ?? false),
                ($norm['publication']['title'] ?? '') . ' | doi=' . ($norm['publication']['doi'] ?? '')
            );
        }

        $uploadDir = CvAiFileStorage::uploadDir();
        $this->step('storage: writable', is_dir($uploadDir) && is_writable($uploadDir), $uploadDir);

        $validName = bin2hex(random_bytes(16)) . '.pdf';
        $validPath = $uploadDir . $validName;
        file_put_contents($validPath, "%PDF-1.4\n%%EOF\n");
        $this->step('storage: valid name', CvAiFileStorage::isValidStoredName($validName));
        $dlUrl = CvAiFileStorage::publicDownloadUrl($validName);
        $this->step(
            'storage: download URL',
            str_contains($dlUrl, '/uploads/cv_ai/') || str_contains($dlUrl, '/serve/uploads/cv_ai/'),
            $dlUrl
        );

        if (function_exists('curl_init')) {
            $ch = curl_init($dlUrl);
            curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15, CURLOPT_FOLLOWLOCATION => true]);
            curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $this->step('http: GET file endpoint', $code >= 200 && $code < 400, 'HTTP ' . $code, $code < 200 || $code >= 400);
        } else {
            $this->step('http: curl', false, 'ไม่มี ext-curl', true);
        }
        @unlink($validPath);

        if ($liveN8n) {
            if (! $cfg->isReady()) {
                $this->step('n8n: live', false, 'ไม่ได้ตั้งค่า AI');
            } else {
                CLI::write('Calling n8n with: ' . $sampleUrl, 'yellow');
                $r = AiPublicationParser::parseFromUrl($sampleUrl);
                $this->step(
                    'n8n: parseFromUrl',
                    (bool) ($r['success'] ?? false),
                    ($r['success'] ?? false)
                        ? (($r['publication']['title'] ?? '') . ' | ' . ($r['publication']['doi'] ?? ''))
                        : (($r['message'] ?? '') . ' [' . ($r['error'] ?? '') . ']')
                );
            }
        } else {
            $this->step('n8n: live', true, 'ข้าม — ใช้ --live-n8n เพื่อทดสอบจริง', true);
        }

        CLI::newLine();
        CLI::write(sprintf('Summary: %d passed, %d failed, %d warnings', $this->pass, $this->fail, $this->warn), $this->fail > 0 ? 'red' : 'green');

        return $this->fail > 0 ? 1 : 0;
    }

    private function step(string $name, bool $ok, string $detail = '', bool $isWarn = false): void
    {
        if ($isWarn) {
            $this->warn++;
            CLI::write('[WARN] ' . $name, 'yellow');
        } elseif ($ok) {
            $this->pass++;
            CLI::write('[PASS] ' . $name, 'green');
        } else {
            $this->fail++;
            CLI::write('[FAIL] ' . $name, 'red');
        }
        if ($detail !== '') {
            CLI::write('       ' . $detail, 'light_gray');
        }
    }
}

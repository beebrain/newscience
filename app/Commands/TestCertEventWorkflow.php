<?php

namespace App\Commands;

use App\Libraries\CertPdfGenerator;
use App\Libraries\CertRecipientStudentResolver;
use App\Models\CertEventRecipientModel;
use App\Services\CertificateEmailService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Certificate as CertificateConfig;
use Config\Email as EmailConfig;
use Tests\Support\Models\MapStudentUserModel;

/**
 * ทดสอบ workflow E-Certificate (resolver, routes, PDF, DB ถ้ามี)
 *
 * ใช้: php spark cert:test-event-workflow
 */
class TestCertEventWorkflow extends BaseCommand
{
    protected $group       = 'Certificate';
    protected $name        = 'cert:test-event-workflow';
    protected $description = 'ทดสอบ workflow E-Certificate (config, student link, PDF, DB optional)';
    protected $usage       = 'cert:test-event-workflow';

    private int $pass = 0;

    private int $fail = 0;

    private int $warn = 0;

    public function run(array $params): int
    {
        CLI::write('=== E-Certificate Event Workflow Test ===', 'cyan');
        CLI::newLine();

        $this->testStudentResolver();
        $this->testRoutesRegistered();
        $this->testConfigPaths();
        $this->testPdfGenerationWithFixture();
        $this->testDatabaseOptional();

        CLI::newLine();
        CLI::write("PASS: {$this->pass}  FAIL: {$this->fail}  WARN: {$this->warn}", $this->fail > 0 ? 'red' : 'green');

        return $this->fail > 0 ? 1 : 0;
    }

    private function testStudentResolver(): void
    {
        $model = new MapStudentUserModel();
        $model->rows = [
            ['id' => 42, 'email' => 'cert.test@example.com', 'login_uid' => '99001', 'status' => 'active', 'tf_name' => 'Cert', 'tl_name' => 'Test'],
        ];
        $resolver = new CertRecipientStudentResolver($model);

        $this->step('resolver: by email', $resolver->resolve(null, 'Cert.Test@Example.com', null) === 42);
        $this->step('resolver: by login_uid', $resolver->resolve(null, '', '99001') === 42);

        $payload = $resolver->buildRecipientPayloadFromStudent(1, $model->rows[0]);
        $this->step('resolver: build payload', is_array($payload) && ($payload['student_id'] ?? 0) === 42);
    }

    private function testRoutesRegistered(): void
    {
        $routesPath = APPPATH . 'Config/Routes.php';
        $content    = is_file($routesPath) ? (string) file_get_contents($routesPath) : '';
        $this->step('routes: students-search', str_contains($content, 'studentsSearch'));
        $this->step('routes: add-students-bulk', str_contains($content, 'addStudentsBulk'));
        $this->step('routes: student portal certificates', str_contains($content, 'Student\\Certificate::index'));
    }

    private function testConfigPaths(): void
    {
        $cfg = config(CertificateConfig::class);
        $this->step('config: certificate output path writable', is_dir($cfg->certificateOutputPath) || @mkdir($cfg->certificateOutputPath, 0775, true));
        $this->step('config: event background path writable', is_dir($cfg->eventBackgroundUploadPath) || @mkdir($cfg->eventBackgroundUploadPath, 0775, true));

        $email = config(EmailConfig::class);
        $from  = trim($email->fromEmail);
        if ($from === '') {
            $this->step('config: fromEmail (mail.fromEmail)', false, 'ยังไม่ตั้ง — ออกใบได้แต่ส่งอีเมลอาจล้มเหลว', true);
        } else {
            $this->step('config: fromEmail (mail.fromEmail)', true, $from);
        }

        $host = trim($email->SMTPHost);
        if ($host === '') {
            $this->step('config: SMTPHost (mail.smtpHost)', false, 'ยังไม่ตั้ง mail.smtpHost ใน .env', true);
        } else {
            $crypto = trim($email->SMTPCrypto);
            $detail = $host . ':' . $email->SMTPPort . ($crypto !== '' ? ' (' . $crypto . ')' : '');
            $this->step('config: SMTPHost (mail.smtpHost)', true, $detail);
        }

        $this->step('config: protocol smtp', $email->protocol === 'smtp', $email->protocol);
    }

    private function testPdfGenerationWithFixture(): void
    {
        $fixtureDir = ROOTPATH . 'writable/uploads/cert_system/temp/test_fixtures';
        if (! is_dir($fixtureDir)) {
            @mkdir($fixtureDir, 0775, true);
        }
        $pngPath = $fixtureDir . '/cert_workflow_test_bg.png';
        if (! is_file($pngPath) && function_exists('imagecreatetruecolor')) {
            $img = imagecreatetruecolor(800, 1131);
            if ($img !== false) {
                $white = imagecolorallocate($img, 255, 255, 255);
                imagefill($img, 0, 0, $white);
                imagepng($img, $pngPath);
                imagedestroy($img);
            }
        }

        if (! is_file($pngPath)) {
            $this->step('pdf: fixture background', false, 'ข้าม — ไม่มี GD/png fixture', true);

            return;
        }

        $event = [
            'background_kind' => 'image',
            'background_file' => 'writable/uploads/cert_system/temp/test_fixtures/cert_workflow_test_bg.png',
            'layout_json'     => json_encode([
                'orientation'   => 'portrait',
                'field_mapping' => [
                    'student_name' => ['x' => 90, 'y' => 145, 'box_w' => 110, 'box_h' => 22, 'font_size' => 22],
                ],
            ], JSON_UNESCAPED_UNICODE),
        ];

        $template = [
            'field_mapping' => '{}',
            'signature_x'   => 150,
            'signature_y'   => 200,
            'qr_x'          => 18,
            'qr_y'          => 262,
            'qr_size'       => 22,
        ];

        $generator = new CertPdfGenerator();
        try {
            $pdfPath = $generator->generate(
                ['request_number' => 'CERT-TEST-' . date('Y'), 'purpose' => 'Workflow Test'],
                $template,
                ['tf_name' => 'ทดสอบ', 'tl_name' => 'ระบบ', 'login_uid' => '99001'],
                bin2hex(random_bytes(16)),
                null,
                $event
            );
        } catch (\Throwable $e) {
            $this->step('pdf: generate with image background', false, $e->getMessage());

            return;
        }

        $mailService = new CertificateEmailService();
        $abs         = $pdfPath ? $mailService->resolvePdfAbsolutePath($pdfPath) : null;
        $ok          = $pdfPath !== null && $abs !== null && is_file($abs);
        $this->step('pdf: generate with image background', $ok, $ok ? basename($abs) : ($pdfPath ? 'path not resolved' : 'generate returned null'));

        if ($ok && $abs) {
            $hash = $generator->hashFile($abs);
            $this->step('pdf: hash file', $hash !== '');
        }
    }

    private function testDatabaseOptional(): void
    {
        try {
            $db = \Config\Database::connect();
            $db->connID ?? $db->initialize();
            $tablesOk = $db->tableExists('cert_events')
                && $db->tableExists('cert_event_recipients')
                && $db->tableExists('student_user');
            $this->step('db: cert tables exist', $tablesOk);

            if ($tablesOk) {
                $model = new CertEventRecipientModel();
                $this->step('db: existsForEventStudent guard', ! $model->existsForEventStudent(0, 1));
            }
        } catch (\Throwable $e) {
            $this->step('db: connection', false, $e->getMessage(), true);
        }
    }

    private function step(string $label, bool $ok, string $detail = '', bool $warning = false): void
    {
        if ($ok) {
            $this->pass++;
            CLI::write('  ✓ ' . $label . ($detail !== '' ? ' — ' . $detail : ''), 'green');
        } elseif ($warning) {
            $this->warn++;
            CLI::write('  ⚠ ' . $label . ($detail !== '' ? ' — ' . $detail : ''), 'yellow');
        } else {
            $this->fail++;
            CLI::write('  ✗ ' . $label . ($detail !== '' ? ' — ' . $detail : ''), 'red');
        }
    }
}

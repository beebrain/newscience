<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ตรวจว่า PHP อ่านไฟล์ CV AI จาก writable/uploads/cv_ai ได้ (รันบน server IIS)
 *
 * php spark cv:probe-ai-file 6050a989121639dde899d02191d57b66.pdf
 */
class ProbeCvAiFile extends BaseCommand
{
    protected $group       = 'CV';
    protected $name        = 'cv:probe-ai-file';
    protected $description = 'ตรวจ path/สิทธิ์อ่านไฟล์ CV AI บนเซิร์ฟเวอร์';
    protected $usage       = 'cv:probe-ai-file <stored-filename.pdf>';
    protected $arguments   = [
        'filename' => 'ชื่อไฟล์ เช่น 6050a989121639dde899d02191d57b66.pdf',
    ];

    public function run(array $params): int
    {
        $filename = $params[0] ?? CLI::getOption('filename') ?? '';
        if ($filename === '') {
            CLI::error('ระบุชื่อไฟล์ เช่น php spark cv:probe-ai-file 6050a989....pdf');

            return 1;
        }
        $filename = basename($filename);
        $w        = rtrim(WRITEPATH, DIRECTORY_SEPARATOR);
        $paths    = [
            'writable/uploads/cv_ai' => $w . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR . $filename,
            'writable/cv_ai_uploads' => $w . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR . $filename,
        ];

        CLI::write('WRITEPATH: ' . WRITEPATH, 'cyan');
        CLI::write('FCPATH:    ' . FCPATH, 'cyan');
        CLI::newLine();

        $found = null;
        foreach ($paths as $label => $path) {
            $real = realpath($path);
            $ok   = $real !== false && is_file($real) && is_readable($real);
            CLI::write($label, 'yellow');
            CLI::write('  path:      ' . $path);
            CLI::write('  realpath:  ' . ($real !== false ? $real : '(ไม่พบ)'));
            CLI::write('  is_file:   ' . (is_file($path) ? 'yes' : 'no'));
            CLI::write('  readable:  ' . (is_readable($path) ? 'yes' : 'no'), $ok ? 'green' : 'red');
            if ($ok) {
                $found = $real;
            }
            CLI::newLine();
        }

        if ($found !== null) {
            CLI::write('OK — PHP อ่านไฟล์ได้', 'green');
            CLI::write('ทดสอบ URL: ' . rtrim((string) config(\Config\App::class)->baseURL, '/') . '/serve/uploads/cv_ai/' . rawurlencode($filename));

            return 0;
        }

        CLI::error('ไม่พบไฟล์หรือ PHP อ่านไม่ได้ — ตรวจสิทธิ์ IIS (IIS_IUSRS) บน writable\\uploads\\cv_ai');

        return 1;
    }
}

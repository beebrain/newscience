<?php

namespace App\Commands;

use App\Libraries\CvAiFileStorage;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * ตรวจว่า PHP อ่านไฟล์ CV AI ได้ (รันบน server IIS)
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
        CLI::write('FCPATH:    ' . FCPATH, 'cyan');
        CLI::write('WRITEPATH: ' . WRITEPATH, 'cyan');
        CLI::newLine();

        $paths = [
            'writable/uploads/cv_ai' => CvAiFileStorage::uploadDir() . $filename,
            'writable/cv_ai_uploads' => rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR . $filename,
            'public/uploads/cv_ai'   => CvAiFileStorage::publicUploadDir() . $filename,
        ];

        $found = service('cvAiFile')->resolveAbsolutePath($filename);
        foreach ($paths as $label => $path) {
            $real = realpath($path);
            $ok   = $real !== false && is_file($real) && is_readable($real);
            CLI::write($label, 'yellow');
            CLI::write('  path:     ' . $path);
            CLI::write('  readable: ' . ($ok ? 'yes' : 'no'), $ok ? 'green' : 'red');
            CLI::newLine();
        }

        if ($found !== null) {
            CLI::write('OK — resolveReadablePath: ' . $found, 'green');
            CLI::write('ทดสอบ URL: ' . CvAiFileStorage::publicDownloadUrl($filename));

            return 0;
        }

        CLI::error('ไม่พบไฟล์ — ตรวจสิทธิ์ IIS บน writable\\uploads\\cv_ai');

        return 1;
    }
}

<?php

namespace App\Controllers\Dev;

use App\Controllers\BaseController;
use App\Services\FileUploadService;
use Config\Certificate as CertificateConfig;

/**
 * Upload Test Controller - ทดสอบระบบอัปโหลดไฟล์
 * 
 * URL: /dev/upload-test
 */
class UploadTest extends BaseController
{
    protected FileUploadService $uploadService;
    protected CertificateConfig $config;

    public function __construct()
    {
        $this->uploadService = new FileUploadService();
        $this->config = config(CertificateConfig::class);
    }

    /**
     * หน้าทดสอบอัปโหลดทั้งหมด
     */
    public function index()
    {
        $tests = [
            'folder_structure' => $this->testFolderStructure(),
            'temp_cleanup' => $this->testTempCleanup(),
            'config_paths' => $this->testConfigPaths(),
        ];

        return view('dev/upload_test', [
            'page_title' => 'ทดสอบระบบอัปโหลดไฟล์',
            'tests' => $tests,
        ]);
    }

    /**
     * AJAX: ทดสอบอัปโหลด PDF Template
     */
    public function testPdfUpload()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $file = $this->request->getFile('test_file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบไฟล์หรือไฟล์ไม่ถูกต้อง'
            ]);
        }

        // Validate
        if (!$this->uploadService->validatePdfTemplate($file)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->uploadService->getErrors()
            ]);
        }

        // Save as temp
        $tempName = $this->uploadService->saveTempTemplate($file);
        
        if (!$tempName) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถบันทึกไฟล์ได้',
                'errors' => $this->uploadService->getErrors()
            ]);
        }

        $tempPath = $this->uploadService->getTempPath('templates') . $tempName;
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'อัปโหลดสำเร็จ',
            'temp_file' => $tempName,
            'file_size' => $this->formatBytes(filesize($tempPath)),
            'mime_type' => mime_content_type($tempPath),
        ]);
    }

    /**
     * AJAX: ทดสอบอัปโหลด CSV Import
     */
    public function testCsvUpload()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $file = $this->request->getFile('test_file');
        
        if (!$file || !$file->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่พบไฟล์หรือไฟล์ไม่ถูกต้อง'
            ]);
        }

        // Validate
        if (!$this->uploadService->validateCsvImport($file)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $this->uploadService->getErrors()
            ]);
        }

        // Save as temp
        $tempPath = $this->uploadService->saveTempImport($file);
        
        if (!$tempPath) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ไม่สามารถบันทึกไฟล์ได้',
                'errors' => $this->uploadService->getErrors()
            ]);
        }

        // อ่าน preview ของ CSV
        $preview = [];
        if (($handle = fopen($tempPath, 'r')) !== false) {
            $headers = fgetcsv($handle);
            $rowCount = 0;
            while (($row = fgetcsv($handle)) !== false && $rowCount < 5) {
                $preview[] = array_combine($headers, $row);
                $rowCount++;
            }
            fclose($handle);
        }

        // ลบไฟล์ temp หลังทดสอบ
        @unlink($tempPath);
        
        return $this->response->setJSON([
            'success' => true,
            'message' => 'อัปโหลดและอ่าน CSV สำเร็จ',
            'headers' => $headers ?? [],
            'preview' => $preview,
            'total_rows' => count($preview),
        ]);
    }

    /**
     * AJAX: ทดสอบการล้างไฟล์ temp
     */
    public function cleanupTemp()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $stats = $this->uploadService->cleanupTempFiles(0); // ล้างทั้งหมด

        return $this->response->setJSON([
            'success' => true,
            'message' => "ล้างไฟล์ temp เสร็จสิ้น",
            'deleted' => $stats['deleted'],
            'failed' => $stats['failed'],
        ]);
    }

    /**
     * AJAX: ดูข้อมูลโฟลเดอร์
     */
    public function getFolderInfo()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $folder = $this->request->getGet('folder');
        $paths = [
            'templates' => $this->uploadService->getTemplatePath(),
            'certificates' => $this->config->certificateOutputPath . date('Y') . '/',
            'temp_templates' => $this->uploadService->getTempPath('templates'),
            'temp_import' => $this->uploadService->getTempPath('import'),
            'signatures' => $this->uploadService->getSignaturePath(),
        ];

        if (!isset($paths[$folder])) {
            return $this->response->setJSON(['success' => false, 'message' => 'ไม่พบโฟลเดอร์']);
        }

        $path = $paths[$folder];
        $files = [];
        
        if (is_dir($path)) {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = [
                        'name' => $file->getFilename(),
                        'size' => $this->formatBytes($file->getSize()),
                        'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    ];
                }
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'path' => $path,
            'exists' => is_dir($path),
            'writable' => is_writable($path),
            'file_count' => count($files),
            'files' => array_slice($files, 0, 50), // จำกัด 50 ไฟล์
        ]);
    }

    /**
     * ทดสอบโครงสร้างโฟลเดอร์
     */
    protected function testFolderStructure(): array
    {
        $tests = [
            ['name' => 'Templates Base', 'path' => $this->config->templateUploadPath],
            ['name' => 'Templates 2024', 'path' => $this->config->templateUploadPath . '2024/'],
            ['name' => 'Templates 2025', 'path' => $this->config->templateUploadPath . '2025/'],
            ['name' => 'Certificates Base', 'path' => $this->config->certificateOutputPath],
            ['name' => 'Certificates 2024', 'path' => $this->config->certificateOutputPath . '2024/'],
            ['name' => 'Temp Templates', 'path' => $this->config->tempTemplatePath],
            ['name' => 'Temp Import', 'path' => $this->config->tempImportPath],
            ['name' => 'Signatures', 'path' => $this->config->signaturePath],
        ];

        $results = [];
        foreach ($tests as $test) {
            $exists = is_dir($test['path']);
            $writable = $exists && is_writable($test['path']);
            
            $results[] = [
                'name' => $test['name'],
                'path' => $test['path'],
                'exists' => $exists,
                'writable' => $writable,
                'status' => $exists && $writable ? 'success' : ($exists ? 'warning' : 'error'),
            ];
        }

        return $results;
    }

    /**
     * ทดสอบการล้างไฟล์ temp
     */
    protected function testTempCleanup(): array
    {
        // สร้างไฟล์ temp จำลอง
        $tempTemplatesDir = $this->uploadService->getTempPath('templates');
        $tempImportDir = $this->uploadService->getTempPath('import');

        $testFiles = [];
        
        // สร้างไฟล์เก่า (มากกว่า 24 ชั่วโมง)
        $oldFile1 = $tempTemplatesDir . 'test_old_' . time() . '.pdf';
        file_put_contents($oldFile1, 'test');
        touch($oldFile1, time() - 25 * 3600); // ตั้งเวลาย้อนหลัง 25 ชั่วโมง
        $testFiles[] = $oldFile1;

        // สร้างไฟล์ใหม่ (น้อยกว่า 24 ชั่วโมง)
        $newFile1 = $tempTemplatesDir . 'test_new_' . time() . '.pdf';
        file_put_contents($newFile1, 'test');
        $testFiles[] = $newFile1;

        $oldFile2 = $tempImportDir . 'test_old_' . time() . '.csv';
        file_put_contents($oldFile2, 'name,email\nTest,test@test.com');
        touch($oldFile2, time() - 25 * 3600);
        $testFiles[] = $oldFile2;

        // ทดสอบ cleanup
        $stats = $this->uploadService->cleanupTempFiles(24);

        // ตรวจสอบผลลัพธ์
        $oldFile1Exists = file_exists($oldFile1);
        $newFile1Exists = file_exists($newFile1);
        $oldFile2Exists = file_exists($oldFile2);

        // ลบไฟล์ที่เหลือ
        foreach ($testFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }

        return [
            'cleanup_stats' => $stats,
            'test_results' => [
                'old_template_deleted' => !$oldFile1Exists,
                'new_template_kept' => $newFile1Exists,
                'old_import_deleted' => !$oldFile2Exists,
            ],
            'all_passed' => !$oldFile1Exists && $newFile1Exists && !$oldFile2Exists,
        ];
    }

    /**
     * ทดสอบ config paths
     */
    protected function testConfigPaths(): array
    {
        return [
            'templateUploadPath' => [
                'value' => $this->config->templateUploadPath,
                'contains_cert_system' => str_contains($this->config->templateUploadPath, 'cert_system'),
            ],
            'certificateOutputPath' => [
                'value' => $this->config->certificateOutputPath,
                'contains_cert_system' => str_contains($this->config->certificateOutputPath, 'cert_system'),
            ],
            'tempTemplatePath' => [
                'value' => $this->config->tempTemplatePath,
                'contains_cert_system' => str_contains($this->config->tempTemplatePath, 'cert_system'),
            ],
            'tempImportPath' => [
                'value' => $this->config->tempImportPath,
                'contains_cert_system' => str_contains($this->config->tempImportPath, 'cert_system'),
            ],
            'signaturePath' => [
                'value' => $this->config->signaturePath,
                'contains_cert_system' => str_contains($this->config->signaturePath, 'cert_system'),
            ],
            'maxTemplateSize' => [
                'value' => $this->formatBytes($this->config->maxTemplateSize),
                'bytes' => $this->config->maxTemplateSize,
            ],
            'tempFileMaxAgeHours' => $this->config->tempFileMaxAgeHours,
        ];
    }

    /**
     * แปลง bytes เป็นข้อความ readable
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $unitIndex = 0;
        
        while ($bytes >= 1024 && $unitIndex < count($units) - 1) {
            $bytes /= 1024;
            $unitIndex++;
        }
        
        return round($bytes, 2) . ' ' . $units[$unitIndex];
    }
}

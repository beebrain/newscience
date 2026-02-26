<?php

namespace App\Services;

use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Certificate as CertificateConfig;

/**
 * File Upload Service - จัดการการอัปโหลดไฟล์สำหรับระบบ Certificate
 * 
 * แยก folder ตาม feature:
 * - cert_system/templates/     : ไฟล์ PDF template
 * - cert_system/certificates/  : ใบรับรองที่ออก
 * - cert_system/temp/templates/: temp สำหรับ preview template
 * - cert_system/temp/import/  : temp สำหรับ import CSV
 * - cert_system/signatures/    : รูปลายเซ็น
 */
class FileUploadService
{
    protected CertificateConfig $config;
    protected array $errors = [];
    
    // ขนาดสูงสุดสำหรับแต่ละประเภท (bytes)
    protected array $maxSizes = [
        'pdf_template' => 8 * 1024 * 1024,      // 8 MB
        'certificate'  => 5 * 1024 * 1024,    // 5 MB
        'csv_import'   => 2 * 1024 * 1024,    // 2 MB
        'signature'    => 1 * 1024 * 1024,    // 1 MB
    ];
    
    // MIME types ที่อนุญาต
    protected array $allowedMimeTypes = [
        'pdf_template' => ['application/pdf'],
        'certificate'  => ['application/pdf'],
        'csv_import'   => [
            'text/csv',
            'text/plain',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
        'signature'    => ['image/png', 'image/jpeg'],
    ];
    
    // Extensions ที่อนุญาต
    protected array $allowedExtensions = [
        'pdf_template' => ['pdf'],
        'certificate'  => ['pdf'],
        'csv_import'   => ['csv', 'txt', 'xls', 'xlsx'],
        'signature'    => ['png', 'jpg', 'jpeg'],
    ];

    public function __construct()
    {
        $this->config = config(CertificateConfig::class);
    }

    /**
     * ตรวจสอบและ validate ไฟล์ PDF Template
     */
    public function validatePdfTemplate(UploadedFile $file): bool
    {
        return $this->validate($file, 'pdf_template');
    }

    /**
     * ตรวจสอบและ validate ไฟล์ CSV Import
     */
    public function validateCsvImport(UploadedFile $file): bool
    {
        return $this->validate($file, 'csv_import');
    }

    /**
     * ตรวจสอบและ validate ไฟล์ลายเซ็น
     */
    public function validateSignature(UploadedFile $file): bool
    {
        return $this->validate($file, 'signature');
    }

    /**
     * Validation หลัก
     */
    protected function validate(UploadedFile $file, string $type): bool
    {
        $this->errors = [];

        // 1. ตรวจสอบว่าไฟล์ upload สำเร็จ
        if (!$file->isValid()) {
            $this->errors[] = 'ไฟล์อัปโหลดไม่สำเร็จ: ' . $this->getUploadErrorMessage($file->getError());
            return false;
        }

        // 2. ตรวจสอบขนาดไฟล์
        $maxSize = $this->maxSizes[$type] ?? 0;
        if ($file->getSize() > $maxSize) {
            $this->errors[] = sprintf(
                'ไฟล์ใหญ่เกินไป (สูงสุด %s)',
                $this->formatBytes($maxSize)
            );
            return false;
        }

        // 3. ตรวจสอบ extension
        $ext = strtolower($file->getExtension());
        $allowedExts = $this->allowedExtensions[$type] ?? [];
        if (!in_array($ext, $allowedExts)) {
            $this->errors[] = sprintf(
                'ประเภทไฟล์ไม่รองรับ (รองรับ: %s)',
                implode(', ', $allowedExts)
            );
            return false;
        }

        // 4. ตรวจสอบ MIME type (ถ้ามีใน whitelist)
        $mime = $file->getMimeType();
        $allowedMimes = $this->allowedMimeTypes[$type] ?? [];
        if (!empty($allowedMimes) && !in_array($mime, $allowedMimes)) {
            $this->errors[] = 'ประเภทไฟล์ไม่ถูกต้อง';
            return false;
        }

        // 5. ตรวจสอบว่าเป็นไฟล์จริง (ไม่ใช่ fake extension)
        if ($type === 'pdf_template' || $type === 'certificate') {
            if (!$this->isValidPdf($file)) {
                $this->errors[] = 'ไฟล์ PDF ไม่ถูกต้องหรือเสียหาย';
                return false;
            }
        }

        return true;
    }

    /**
     * บันทึกไฟล์ PDF Template
     */
    public function savePdfTemplate(UploadedFile $file, ?string $customName = null): ?string
    {
        if (!$this->validatePdfTemplate($file)) {
            return null;
        }

        $targetDir = $this->getTemplatePath();
        $this->ensureDirectory($targetDir);

        $filename = $customName ?? $this->generateUniqueName($file, 'template');
        
        try {
            $file->move($targetDir, $filename, true);
            return 'uploads/cert_system/templates/' . $filename;
        } catch (\Exception $e) {
            $this->errors[] = 'ไม่สามารถบันทึกไฟล์ได้: ' . $e->getMessage();
            return null;
        }
    }

    /**
     * บันทึกไฟล์ชั่วคราวสำหรับ preview template
     */
    public function saveTempTemplate(UploadedFile $file): ?string
    {
        if (!$this->validatePdfTemplate($file)) {
            return null;
        }

        $targetDir = $this->getTempPath('templates');
        $this->ensureDirectory($targetDir);

        $filename = 'preview_' . time() . '_' . bin2hex(random_bytes(8)) . '.pdf';
        
        try {
            $file->move($targetDir, $filename, true);
            return $filename;
        } catch (\Exception $e) {
            $this->errors[] = 'ไม่สามารถบันทึกไฟล์ได้: ' . $e->getMessage();
            return null;
        }
    }

    /**
     * บันทึกไฟล์ชั่วคราวสำหรับ import
     */
    public function saveTempImport(UploadedFile $file): ?string
    {
        if (!$this->validateCsvImport($file)) {
            return null;
        }

        $targetDir = $this->getTempPath('import');
        $this->ensureDirectory($targetDir);

        $filename = 'import_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $file->getExtension();
        
        try {
            $file->move($targetDir, $filename, true);
            return $targetDir . $filename;
        } catch (\Exception $e) {
            $this->errors[] = 'ไม่สามารถบันทึกไฟล์ได้: ' . $e->getMessage();
            return null;
        }
    }

    /**
     * ย้ายไฟล์ temp ไปยังโฟลเดอร์จริง
     */
    public function moveTempFile(string $tempFilename, string $type): ?string
    {
        $tempDir = $this->getTempPath($type);
        $tempPath = $tempDir . $tempFilename;

        if (!file_exists($tempPath)) {
            $this->errors[] = 'ไม่พบไฟล์ชั่วคราว';
            return null;
        }

        $targetDir = $this->getTemplatePath();
        $this->ensureDirectory($targetDir);

        $newName = date('Ymd_His') . '_' . bin2hex(random_bytes(8)) . '.pdf';
        $targetPath = $targetDir . $newName;

        if (copy($tempPath, $targetPath)) {
            unlink($tempPath);
            return 'uploads/cert_system/templates/' . $newName;
        }

        $this->errors[] = 'ไม่สามารถย้ายไฟล์ได้';
        return null;
    }

    /**
     * ลบไฟล์ temp โดยตรง
     */
    public function deleteTempFile(string $tempFilename, string $type): bool
    {
        $tempPath = $this->getTempPath($type) . $tempFilename;
        if (file_exists($tempPath)) {
            return unlink($tempPath);
        }
        return false;
    }

    /**
     * ล้างไฟล์ temp ที่เก่าเกินกำหนด
     */
    public function cleanupTempFiles(int $maxAgeHours = 24): array
    {
        $stats = ['deleted' => 0, 'failed' => 0];
        $tempTypes = ['templates', 'import'];

        foreach ($tempTypes as $type) {
            $tempDir = $this->getTempPath($type);
            if (!is_dir($tempDir)) continue;

            $files = glob($tempDir . '*');
            $cutoffTime = time() - ($maxAgeHours * 3600);

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoffTime) {
                    if (unlink($file)) {
                        $stats['deleted']++;
                    } else {
                        $stats['failed']++;
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * สร้างโฟลเดอร์สำหรับใบรับรองตามปี
     */
    public function getCertificatePath(): string
    {
        $year = date('Y');
        $path = $this->config->certificateOutputPath . $year . '/';
        $this->ensureDirectory($path);
        return $path;
    }

    /**
     * รับ path สำหรับเก็บ template
     */
    public function getTemplatePath(): string
    {
        $year = date('Y');
        $path = str_replace(
            'writable/uploads/cert_templates/',
            'writable/uploads/cert_system/templates/' . $year . '/',
            $this->config->templateUploadPath
        );
        $this->ensureDirectory($path);
        return $path;
    }

    /**
     * รับ path สำหรับ temp files
     */
    public function getTempPath(string $type = 'templates'): string
    {
        $basePath = str_replace(
            'writable/uploads/',
            'writable/uploads/cert_system/temp/',
            WRITEPATH
        );
        $path = $basePath . $type . '/';
        $this->ensureDirectory($path);
        return $path;
    }

    /**
     * รับ path สำหรับ signatures
     */
    public function getSignaturePath(): string
    {
        $path = FCPATH . 'uploads/cert_system/signatures/';
        $this->ensureDirectory($path);
        return $path;
    }

    /**
     * รับข้อผิดพลาด
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * รับข้อผิดพลาดแรก
     */
    public function getFirstError(): ?string
    {
        return $this->errors[0] ?? null;
    }

    /**
     * สร้างโฟลเดอร์ถ้ายังไม่มี
     */
    protected function ensureDirectory(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0775, true);
        }
    }

    /**
     * สร้างชื่อไฟล์ unique
     */
    protected function generateUniqueName(UploadedFile $file, string $prefix): string
    {
        return date('Ymd_His') . '_' . $prefix . '_' . bin2hex(random_bytes(8)) . '.' . $file->getExtension();
    }

    /**
     * ตรวจสอบว่าเป็น PDF ที่ถูกต้อง
     */
    protected function isValidPdf(UploadedFile $file): bool
    {
        $tempPath = $file->getTempName();
        
        // อ่าน header ของไฟล์
        $handle = fopen($tempPath, 'rb');
        if (!$handle) return false;
        
        $header = fread($handle, 8);
        fclose($handle);
        
        // PDF files start with %PDF-
        return strpos($header, '%PDF-') === 0;
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

    /**
     * แปลง error code เป็นข้อความ
     */
    protected function getUploadErrorMessage(int $code): string
    {
        return match ($code) {
            UPLOAD_ERR_INI_SIZE   => 'ไฟล์ใหญ่เกินกำหนดใน php.ini',
            UPLOAD_ERR_FORM_SIZE  => 'ไฟล์ใหญ่เกินกำหนดใน form',
            UPLOAD_ERR_PARTIAL    => 'อัปโหลดไม่สมบูรณ์',
            UPLOAD_ERR_NO_FILE    => 'ไม่มีไฟล์',
            UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ temp',
            UPLOAD_ERR_CANT_WRITE => 'เขียนไฟล์ไม่ได้',
            UPLOAD_ERR_EXTENSION  => 'ถูกจำกัดโดย extension',
            default               => 'เกิดข้อผิดพลาดที่ไม่ทราบสาเหตุ',
        };
    }
}

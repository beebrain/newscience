<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\PdfTextExtractor;
use App\Services\FileUploadService;

/**
 * CertTemplatePreview Controller - จัดการ Preview และดึงข้อมูลจาก PDF
 * 
 * ใช้ FileUploadService สำหรับจัดการ temp files ในโฟลเดอร์ที่แยกตาม feature
 * - cert_system/temp/templates/ : สำหรับ preview template
 */
class CertTemplatePreview extends BaseController
{
    protected FileUploadService $uploadService;

    public function __construct()
    {
        $this->uploadService = new FileUploadService();
    }

    /**
     * AJAX: อัปโหลด PDF ชั่วคราวและดึงข้อมูล
     */
    public function uploadPreview()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $file = $this->request->getFile('pdf_file');

        // ใช้ FileUploadService สำหรับ validation และบันทึก
        $tempName = $this->uploadService->saveTempTemplate($file);

        if (!$tempName) {
            return $this->response->setJSON([
                'error' => $this->uploadService->getFirstError() ?: 'อัปโหลดไฟล์ไม่สำเร็จ'
            ]);
        }

        $tempPath = $this->uploadService->getTempPath('templates') . $tempName;

        // ดึงข้อความจาก PDF
        $extractor = new PdfTextExtractor();
        $text = $extractor->extractText($tempPath);
        $suggestions = $extractor->suggestFields($text);
        $fieldMapping = $extractor->generateFieldMapping($tempPath);

        return $this->response->setJSON([
            'success' => true,
            'filename' => $file->getClientName(),
            'text_preview' => substr($text, 0, 2000),
            'suggestions' => $suggestions,
            'field_mapping' => $fieldMapping,
            'temp_file' => $tempName,
        ]);
    }

    /**
     * AJAX: ดึงข้อความจาก PDF ที่อัปโหลดแล้ว
     */
    public function extractText()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid request']);
        }

        $tempFile = $this->request->getPost('temp_file');
        $tempPath = $this->uploadService->getTempPath('templates') . $tempFile;

        if (!file_exists($tempPath)) {
            return $this->response->setJSON(['error' => 'File not found']);
        }

        $extractor = new PdfTextExtractor();
        $text = $extractor->extractText($tempPath);

        return $this->response->setJSON([
            'success' => true,
            'text' => $text,
        ]);
    }

    /**
     * AJAX: ลบไฟล์ชั่วคราว
     */
    public function clearTemp()
    {
        $tempFile = $this->request->getPost('temp_file');

        if ($tempFile) {
            $this->uploadService->deleteTempFile($tempFile, 'templates');
        }

        return $this->response->setJSON(['success' => true]);
    }
}

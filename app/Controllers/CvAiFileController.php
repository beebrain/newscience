<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\CvAiFileStorage;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ส่งไฟล์ CV AI ให้ n8n / ภายนอก (แบบ Edoc public/view-file — route + controller อ่านจาก writable)
 */
class CvAiFileController extends BaseController
{
    /**
     * GET cv-ai/public/file/{storedName}
     * ไม่ต้องล็อกอิน — ชื่อไฟล์สุ่ม 32 hex ทำหน้าที่เป็น secret (เทียบ token ของ Edoc)
     */
    public function publicFile(string $storedName): ResponseInterface
    {
        $storedName = basename($storedName);
        if (! CvAiFileStorage::isValidStoredName($storedName)) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $filePath = CvAiFileStorage::resolveReadablePath($storedName);
        if ($filePath === null) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $mimeType = mime_content_type($filePath);
        if ($mimeType === false || $mimeType === 'application/octet-stream') {
            $mimeType = CvAiFileStorage::mimeForFilename($storedName);
        }

        return $this->response
            ->setHeader('Content-Type', $mimeType)
            ->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $storedName) . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody((string) file_get_contents($filePath));
    }

    /** @deprecated ใช้ publicFile — alias สำหรับ route เก่า */
    public function download(string $storedName): ResponseInterface
    {
        return $this->publicFile($storedName);
    }
}

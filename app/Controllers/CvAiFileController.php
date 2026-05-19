<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Libraries\CvAiFileStorage;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ส่งไฟล์ CV AI ให้ n8n / ภายนอก (อ่านจาก writable หรือ public — ไม่ต้องพึ่ง static file บน IIS)
 */
class CvAiFileController extends BaseController
{
    /**
     * GET /cv-ai/download/{storedName}
     * GET /cv-ai/file/{storedName} (alias)
     */
    public function download(string $storedName): ResponseInterface
    {
        $storedName = basename($storedName);
        if (! CvAiFileStorage::isValidStoredName($storedName)) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $path = CvAiFileStorage::pathForStoredName($storedName);
        if ($path === null) {
            return $this->response->setStatusCode(404)->setBody('Not found');
        }

        $body = file_get_contents($path);
        if ($body === false) {
            return $this->response->setStatusCode(500)->setBody('Cannot read file');
        }

        return $this->response
            ->setHeader('Content-Type', CvAiFileStorage::mimeForFilename($storedName))
            ->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $storedName) . '"')
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setHeader('Content-Length', (string) strlen($body))
            ->setBody($body);
    }
}

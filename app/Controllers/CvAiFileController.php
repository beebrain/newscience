<?php

declare(strict_types=1);

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * ส่งไฟล์ CV AI ให้ n8n / ภายนอก — รับชื่อไฟล์แล้ว return เป็นไฟล์
 */
class CvAiFileController extends BaseController
{
    /**
     * GET cv-ai/file?f={storedName}
     * แนะนำบน IIS — ไม่มี .pdf ใน path segment
     */
    public function serve(): ResponseInterface
    {
        $name = trim((string) ($this->request->getGet('f') ?? $this->request->getGet('name') ?? ''));

        return $this->respondWithFile($name);
    }

    /**
     * GET cv-ai/public/file/{storedName}
     */
    public function publicFile(string $storedName): ResponseInterface
    {
        return $this->respondWithFile($storedName);
    }

    /** @deprecated alias */
    public function download(string $storedName): ResponseInterface
    {
        return $this->publicFile($storedName);
    }

    private function respondWithFile(string $storedName): ResponseInterface
    {
        $file = service('cvAiFile')->resolveForDownload($storedName);
        if ($file === null) {
            return $this->response->setStatusCode(404)->setBody('File not found');
        }

        $safeName = str_replace('"', '\\"', $file['filename']);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', $file['mime'])
            ->setHeader('Content-Disposition', 'inline; filename="' . $safeName . '"')
            ->setHeader('Content-Length', (string) $file['size'])
            ->setHeader('Cache-Control', 'private, max-age=3600')
            ->setBody((string) file_get_contents($file['path']));
    }
}

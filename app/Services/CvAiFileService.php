<?php

declare(strict_types=1);

namespace App\Services;

use App\Libraries\CvAiFileStorage;
use Config\AiCv;

/**
 * อ่านและส่งไฟล์ CV AI จาก writable (สำหรับ n8n / URL สาธารณะ)
 */
final class CvAiFileService
{
    /**
     * @return array{path: string, filename: string, mime: string, size: int}|null
     */
    public function resolveForDownload(string $storedName): ?array
    {
        $filename = basename($storedName);
        if (! CvAiFileStorage::isValidStoredName($filename)) {
            return null;
        }

        $path = $this->resolveAbsolutePath($filename);
        if ($path === null) {
            return null;
        }

        $mime = mime_content_type($path);
        if ($mime === false || $mime === 'application/octet-stream') {
            $mime = CvAiFileStorage::mimeForFilename($filename);
        }

        return [
            'path'     => $path,
            'filename' => $filename,
            'mime'     => $mime,
            'size'     => (int) filesize($path),
        ];
    }

    public function resolveAbsolutePath(string $storedName): ?string
    {
        $filename = basename($storedName);
        if (! CvAiFileStorage::isValidStoredName($filename)) {
            return null;
        }

        foreach ($this->candidatePaths($filename) as $candidate) {
            if (file_exists($candidate) && is_file($candidate)) {
                return realpath($candidate) ?: $candidate;
            }
        }

        return null;
    }

    /**
     * URL สาธารณะ — รูปแบบเดียวกับ Edoc viewPDF (base_url + index.php/…)
     * ตัวอย่าง: https://sci.uru.ac.th/index.php/cv-ai/file/{storedName}
     */
    public function publicDownloadUrl(string $storedName): string
    {
        $encoded = rawurlencode(basename($storedName));
        $cfg     = config(AiCv::class);

        if ($cfg->filePublicBaseUrl !== '') {
            $base = rtrim($cfg->filePublicBaseUrl, '/');

            return $base . '/index.php/cv-ai/file/' . $encoded;
        }

        return base_url('index.php/cv-ai/file/' . $encoded);
    }

    /**
     * @return list<string>
     */
    private function candidatePaths(string $filename): array
    {
        $bases = [
            CvAiFileStorage::uploadDir(),
            rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR,
            CvAiFileStorage::publicUploadDir(),
        ];

        if (defined('ROOTPATH')) {
            $bases[] = rtrim(ROOTPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'writable' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
        }

        $paths = [];
        foreach ($bases as $base) {
            $paths[] = $base . $filename;
        }

        return $paths;
    }

}

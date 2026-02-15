<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serve uploaded files from the secure upload folder.
 *
 * Secure folder: writable/uploads/ (WRITEPATH . 'uploads/')
 * - อยู่นอก document root (public/) จึงไม่มีใครเข้าถึงไฟล์โดยตรงจาก URL
 * - ไฟล์ถูกส่งผ่าน controller นี้เท่านั้น (URL: /serve/uploads/<type>/<filename>)
 *
 * Fallback: ถ้าไฟล์ยังไม่มีใน writable (ข้อมูลเก่า) จะอ่านจาก public/uploads/
 */
class Serve extends BaseController
{
    private const ALLOWED_TYPES = ['news', 'events', 'hero', 'staff', 'programs'];

    /**
     * Serve a file by type and filename.
     * $type must be news|events|hero|staff; $filename is basename only (no path traversal).
     */
    public function file(string $type, string $filename): ResponseInterface
    {
        $type = strtolower($type);
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return $this->response->setStatusCode(404);
        }

        $filename = basename($filename);
        if ($filename === '' || strpos($filename, '..') !== false) {
            return $this->response->setStatusCode(404);
        }

        $base = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $writableBase = rtrim(WRITEPATH, DIRECTORY_SEPARATOR);
        $dir = $type . DIRECTORY_SEPARATOR;
        $writablePath = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;
        $publicPath = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;

        $path = null;
        if (is_file($writablePath)) {
            $path = $writablePath;
        } elseif (is_file($publicPath)) {
            $path = $publicPath;
        }

        if ($path === null) {
            return $this->response->setStatusCode(404);
        }

        $mime = $this->mimeFromFilename($filename);
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $filename) . '"');
        return $this->response->setBody(file_get_contents($path));
    }

    /**
     * Serve thumbnail for listing/cards. Looks in thumbs/ first, then falls back to full image.
     * URL: /serve/thumb/<type>/<filename>  e.g. serve/thumb/news/356.jpg
     * โฟลเดอร์ต้องชื่อ "thumbs" (ไม่ใช่ thumps): writable/uploads/<type>/thumbs/
     */
    public function thumb(string $type, string $filename): ResponseInterface
    {
        $type = strtolower($type);
        if (!in_array($type, self::ALLOWED_TYPES, true)) {
            return $this->response->setStatusCode(404);
        }

        $filename = basename($filename);
        if ($filename === '' || strpos($filename, '..') !== false) {
            return $this->response->setStatusCode(404);
        }

        $base = rtrim(FCPATH, DIRECTORY_SEPARATOR);
        $writableBase = rtrim(WRITEPATH, DIRECTORY_SEPARATOR);
        $dir = $type . DIRECTORY_SEPARATOR;
        $thumbs = 'thumbs' . DIRECTORY_SEPARATOR; // ชื่อโฟลเดอร์ต้องเป็น thumbs
        $writableThumb = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $thumbs . $filename;
        $publicThumb = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $thumbs . $filename;
        $writablePath = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;
        $publicPath = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;

        $path = null;
        if (is_file($writableThumb)) {
            $path = $writableThumb;
        } elseif (is_file($publicThumb)) {
            $path = $publicThumb;
        } elseif (is_file($writablePath)) {
            $path = $writablePath;
        } elseif (is_file($publicPath)) {
            $path = $publicPath;
        }
        if ($path === null && $type === 'news') {
            $newsImagesPath = $base . DIRECTORY_SEPARATOR . 'newsimages' . DIRECTORY_SEPARATOR . $filename;
            if (is_file($newsImagesPath)) {
                $path = $newsImagesPath;
            }
        }

        if ($path === null) {
            return $this->response->setStatusCode(404);
        }

        $mime = $this->mimeFromFilename($filename);
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $filename) . '"');
        return $this->response->setBody(file_get_contents($path));
    }

    private function mimeFromFilename(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }
}

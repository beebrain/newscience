<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;

/**
 * Serve uploaded files from the secure upload folder.
 *
 * มาตรฐาน: ไฟล์อัปโหลดทั้งหมดเก็บที่ writable/uploads/ เท่านั้น (ไม่ใช้ public/)
 * - อยู่นอก document root (public/) จึงไม่มีใครเข้าถึงไฟล์โดยตรงจาก URL
 * - ไฟล์ถูกส่งผ่าน controller นี้เท่านั้น (URL: /serve/uploads/...)
 *
 * Fallback: ถ้าไฟล์ยังไม่มีใน writable (ข้อมูลเก่า) จะอ่านจาก public/uploads/
 */
class Serve extends BaseController
{
    private const ALLOWED_TYPES = ['news', 'events', 'hero', 'staff', 'programs', 'personnel'];

    /**
     * Serve a file by type and filename.
     * $type must be news|events|hero|staff|personnel; personnel อ่านจากโฟลเดอร์ staff เหมือนกัน
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
        $dir = ($type === 'personnel') ? 'staff' . DIRECTORY_SEPARATOR : $type . DIRECTORY_SEPARATOR;
        $writablePath = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;
        $publicPath = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dir . $filename;

        $path = null;
        if (is_file($writablePath)) {
            $path = $writablePath;
        } elseif (is_file($publicPath)) {
            $path = $publicPath;
        }
        if ($path === null && $type === 'personnel') {
            $dirPersonnel = 'personnel' . DIRECTORY_SEPARATOR;
            $writablePathP = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $filename;
            $publicPathP = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $filename;
            if (is_file($writablePathP)) {
                $path = $writablePathP;
            } elseif (is_file($publicPathP)) {
                $path = $publicPathP;
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
        $dir = ($type === 'personnel') ? 'staff' . DIRECTORY_SEPARATOR : $type . DIRECTORY_SEPARATOR;
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
        if ($path === null && $type === 'personnel') {
            $dirPersonnel = 'personnel' . DIRECTORY_SEPARATOR;
            $thumbsP = 'thumbs' . DIRECTORY_SEPARATOR;
            $writableThumbP = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $thumbsP . $filename;
            $writablePathP = $writableBase . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $filename;
            if (is_file($writableThumbP)) {
                $path = $writableThumbP;
            } elseif (is_file($writablePathP)) {
                $path = $writablePathP;
            } else {
                $publicThumbP = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $thumbsP . $filename;
                $publicPathP = $base . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $dirPersonnel . $filename;
                if (is_file($publicThumbP)) {
                    $path = $publicThumbP;
                } elseif (is_file($publicPathP)) {
                    $path = $publicPathP;
                }
            }
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

    /**
     * Serve file by full relative path under uploads/ (e.g. programs/1/hero/filename.jpg).
     * URL: /serve/uploads/programs/1/hero/filename.jpg
     * Note: CI4 splits route params by slash, so we get path from URI instead of $path argument.
     */
    public function fileByPath(string $path = ''): ResponseInterface
    {
        // ดึง path เต็มจาก URI เพราะ route pass ที่มี / จะถูกแยกเป็นหลาย parameter
        $uriPath = $this->request->getUri()->getPath();
        $prefix = 'serve/uploads';
        $pos = strpos($uriPath, $prefix);
        if ($pos !== false) {
            $path = substr($uriPath, $pos + strlen($prefix));
            $path = trim(str_replace('\\', '/', $path), '/');
        }
        $path = str_replace(['\\', '..'], ['/', ''], $path);
        if ($path === '' || strpos($path, '..') !== false) {
            return $this->response->setStatusCode(404);
        }
        $writableBase = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        $sep = DIRECTORY_SEPARATOR;
        $fullPath = $writableBase . str_replace('/', $sep, $path);
        if (!is_file($fullPath)) {
            $publicPath = rtrim(FCPATH, $sep) . $sep . 'uploads' . $sep . str_replace('/', $sep, $path);
            if (is_file($publicPath)) {
                $fullPath = $publicPath;
            } else {
                $found = false;
                // รูปแบบใหม่: ใช้โฟลเดอร์เดียว news/, activities/, downloads/ — ลอง path ใหม่ก่อน
                if (preg_match('#^programs/(\d+)/(news|activities|downloads)/(.+)$#', $path, $m)) {
                    $newPath = $m[2] . '/' . $m[3];
                    $fullPathNew = $writableBase . str_replace('/', $sep, $newPath);
                    if (is_file($fullPathNew)) {
                        $fullPath = $fullPathNew;
                        $found = true;
                    }
                }
                if (!$found && preg_match('#^programs/(\d+)/news/(.+)$#', $path, $m)) {
                    $altPath = 'news/program-' . $m[1] . '/' . $m[2];
                    $fullPathAlt = $writableBase . str_replace('/', $sep, $altPath);
                    if (is_file($fullPathAlt)) {
                        $fullPath = $fullPathAlt;
                        $found = true;
                    }
                }
                if (!$found && preg_match('#^news/program-(\d+)/(.+)$#', $path, $m)) {
                    $altPath = 'programs/' . $m[1] . '/news/' . $m[2];
                    $fullPathAlt = $writableBase . str_replace('/', $sep, $altPath);
                    if (is_file($fullPathAlt)) {
                        $fullPath = $fullPathAlt;
                        $found = true;
                    }
                }
                if (!$found && strpos($path, 'personnel/') === 0) {
                    $altPath = 'staff/' . substr($path, 10);
                    $fullPathAlt = $writableBase . str_replace('/', $sep, $altPath);
                    if (is_file($fullPathAlt)) {
                        $fullPath = $fullPathAlt;
                        $found = true;
                    }
                }
                if (!$found) {
                    // Fallback: ถ้า path เป็น .../thumbs/xxx ลองใช้รูปเต็ม (สำหรับข้อมูลเก่าที่ยังไม่มี thumb)
                    if (preg_match('#^(.+)/thumbs/([^/]+)$#', $path, $m)) {
                        $fullPathAlt = $writableBase . str_replace('/', $sep, $m[1] . '/' . $m[2]);
                        if (is_file($fullPathAlt)) {
                            $fullPath = $fullPathAlt;
                            $found = true;
                        } else {
                            $publicAlt = rtrim(FCPATH, $sep) . $sep . 'uploads' . $sep . str_replace('/', $sep, $m[1] . '/' . $m[2]);
                            if (is_file($publicAlt)) {
                                $fullPath = $publicAlt;
                                $found = true;
                            }
                        }
                    }
                }
                if (!$found) {
                    return $this->response->setStatusCode(404);
                }
            }
        }
        $filename = basename($fullPath);
        $mime = $this->mimeFromFilename($filename);
        $this->response->setHeader('Content-Type', $mime);
        $this->response->setHeader('Content-Disposition', 'inline; filename="' . str_replace('"', '\\"', $filename) . '"');
        return $this->response->setBody(file_get_contents($fullPath));
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
            'pdf' => 'application/pdf',
        ];
        return $map[$ext] ?? 'application/octet-stream';
    }
}

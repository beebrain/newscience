<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\AiCv;

/**
 * เก็บไฟล์ชั่วคราวสำหรับส่ง URL ให้ n8n วิเคราะห์ (แบบ Research Record)
 *
 * พยายามเก็บที่ public/uploads/cv_ai/ (IIS เสิร์ฟตรง) — ถ้าเขียนไม่ได้ใช้ writable/uploads/cv_ai/ + /serve/uploads/cv_ai/
 */
final class CvAiFileStorage
{
    private const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

    private const MAX_BYTES = 10_485_760; // 10MB

    /** โฟลเดอร์ที่ใช้เขียนล่าสุด (public หรือ writable) */
    private static ?string $activeUploadDir = null;

    public static function publicUploadDir(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
    }

    public static function writableUploadDir(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
    }

    /** @deprecated ใช้ uploadDir() */
    public static function legacyWritableUploadDirV1(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR;
    }

    /**
     * โฟลเดอร์สำหรับอัปโหลด — เลือก public ถ้าเขียนได้ ไม่งั้น writable
     */
    public static function uploadDir(): string
    {
        if (self::$activeUploadDir !== null && is_dir(self::$activeUploadDir) && is_writable(self::$activeUploadDir)) {
            return self::$activeUploadDir;
        }

        foreach ([self::publicUploadDir(), self::writableUploadDir()] as $dir) {
            if (self::ensureDirectory($dir)) {
                self::$activeUploadDir = $dir;

                return $dir;
            }
        }

        return self::writableUploadDir();
    }

    public static function isPublicUploadDir(string $dir): bool
    {
        $pub = realpath(self::publicUploadDir());
        $got = realpath(rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);

        return $pub !== false && $got !== false && $got === $pub;
    }

    private static function ensureDirectory(string $dir): bool
    {
        if (is_dir($dir)) {
            return is_writable($dir);
        }

        if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            return false;
        }

        @file_put_contents($dir . 'index.html', '');

        return is_dir($dir) && is_writable($dir);
    }

    /**
     * @return array{success:bool,stored_name?:string,download_url?:string,original_name?:string,file_size?:int,message?:string}
     */
    public static function storeUploadedFile($file): array
    {
        if ($file === null || ! $file->isValid()) {
            return ['success' => false, 'message' => 'อัปโหลดไฟล์ไม่สำเร็จ'];
        }

        if ($file->getSize() > self::MAX_BYTES) {
            return ['success' => false, 'message' => 'ไฟล์ใหญ่เกิน 10MB'];
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, self::ALLOWED_EXT, true)) {
            return ['success' => false, 'message' => 'รองรับเฉพาะ PDF, DOC, DOCX, JPG, PNG, GIF, TXT'];
        }

        $dir = self::uploadDir();
        if (! is_writable($dir)) {
            return [
                'success' => false,
                'message' => 'เซิร์ฟเวอร์สร้างโฟลเดอร์อัปโหลดไม่ได้ — ตรวจสิทธิ์เขียนที่ public\\uploads\\cv_ai หรือ writable\\uploads\\cv_ai',
            ];
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        if (! $file->move($dir, $storedName)) {
            return ['success' => false, 'message' => 'บันทึกไฟล์ไม่สำเร็จ'];
        }

        // ถ้าเขียนได้แค่ writable แต่ public เขียนได้ — คัดลอกให้ IIS เสิร์ฟตรง (ไม่บังคับ)
        if (! self::isPublicUploadDir($dir)) {
            self::tryMirrorToPublic($dir . $storedName, $storedName);
        }

        return [
            'success'       => true,
            'stored_name'   => $storedName,
            'download_url'  => self::publicDownloadUrl($storedName),
            'original_name' => $file->getClientName(),
            'file_size'     => (int) $file->getSize(),
        ];
    }

    /** คัดลอกไป public ถ้าทำได้ (n8n ใช้ URL /uploads/cv_ai/) */
    private static function tryMirrorToPublic(string $sourcePath, string $storedName): void
    {
        if (! is_file($sourcePath) || ! self::ensureDirectory(self::publicUploadDir())) {
            return;
        }
        $dest = self::publicUploadDir() . $storedName;
        if (! is_file($dest)) {
            @copy($sourcePath, $dest);
        }
    }

    public static function isValidStoredName(string $storedName): bool
    {
        return (bool) preg_match('/^[a-f0-9]{32}\.(pdf|doc|docx|jpg|jpeg|png|gif|txt)$/i', $storedName);
    }

    public static function pathForStoredName(string $storedName): ?string
    {
        if (! self::isValidStoredName($storedName)) {
            return null;
        }
        foreach (self::candidatePaths($storedName) as $path) {
            $resolved = realpath($path);
            if ($resolved !== false && is_file($resolved) && is_readable($resolved)) {
                return $resolved;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public static function candidatePaths(string $storedName): array
    {
        $filename = basename($storedName);

        return [
            self::publicUploadDir() . $filename,
            self::writableUploadDir() . $filename,
            self::legacyWritableUploadDirV1() . $filename,
        ];
    }

    /**
     * URL สาธารณะให้ n8n — ใช้ /uploads/cv_ai/ ถ้าไฟล์อยู่ public ไม่งั้น /serve/uploads/cv_ai/
     */
    public static function publicDownloadUrl(string $storedName): string
    {
        $cfg  = config(AiCv::class);
        $app  = config(\Config\App::class);
        $base = rtrim($cfg->filePublicBaseUrl !== '' ? $cfg->filePublicBaseUrl : (string) ($app->baseURL ?? ''), '/');
        $name = rawurlencode($storedName);
        $pub  = self::publicUploadDir() . basename($storedName);
        if (is_file($pub) && is_readable($pub)) {
            return $base . '/uploads/cv_ai/' . $name;
        }

        return $base . '/serve/uploads/cv_ai/' . $name;
    }

    public static function rememberUploadForUser(int $userId, string $storedName): void
    {
        $key  = 'cv_ai_uploads_' . $userId;
        $list = session()->get($key);
        if (! is_array($list)) {
            $list = [];
        }
        $list[$storedName] = time();
        session()->set($key, $list);
    }

    public static function userOwnsUpload(int $userId, string $storedName): bool
    {
        $key  = 'cv_ai_uploads_' . $userId;
        $list = session()->get($key);

        return is_array($list) && isset($list[$storedName]);
    }
}

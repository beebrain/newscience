<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\AiCv;

/**
 * เก็บไฟล์ชั่วคราวสำหรับส่ง URL ให้ n8n วิเคราะห์ (แบบ Research Record)
 */
final class CvAiFileStorage
{
    private const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

    private const MAX_BYTES = 10_485_760; // 10MB

    public static function uploadDir(): string
    {
        $dir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir;
    }

    /** โฟลเดอร์เก่า (ก่อนย้ายไป uploads/cv_ai) */
    private static function legacyUploadDir(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR;
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

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->move(self::uploadDir(), $storedName);

        return [
            'success'       => true,
            'stored_name'   => $storedName,
            'download_url'  => self::publicDownloadUrl($storedName),
            'original_name' => $file->getClientName(),
            'file_size'     => (int) $file->getSize(),
        ];
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
        $path = self::uploadDir() . $storedName;
        if (is_file($path)) {
            return $path;
        }
        $legacy = self::legacyUploadDir() . $storedName;

        return is_file($legacy) ? $legacy : null;
    }

    /**
     * URL สาธารณะให้ n8n ดึงไฟล์ — ใช้ /serve/uploads/cv_ai/ (รองรับ IIS บน production)
     */
    public static function publicDownloadUrl(string $storedName): string
    {
        $cfg  = config(AiCv::class);
        $app  = config(\Config\App::class);
        $base = rtrim($cfg->filePublicBaseUrl !== '' ? $cfg->filePublicBaseUrl : (string) ($app->baseURL ?? ''), '/');

        return $base . '/serve/uploads/cv_ai/' . rawurlencode($storedName);
    }

    /** เส้นทางเดิม (backward compat) */
    public static function legacyPublicDownloadUrl(string $storedName): string
    {
        $cfg  = config(AiCv::class);
        $app  = config(\Config\App::class);
        $base = rtrim($cfg->filePublicBaseUrl !== '' ? $cfg->filePublicBaseUrl : (string) ($app->baseURL ?? ''), '/');

        return $base . '/cv-ai/file/' . rawurlencode($storedName);
    }

    /**
     * บันทึกใน session ว่า user นี้อัปโหลดไฟล์นี้ (ใช้ตอนเรียก preview)
     */
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

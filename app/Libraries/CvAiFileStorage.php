<?php

declare(strict_types=1);

namespace App\Libraries;

use Config\AiCv;

/**
 * เก็บไฟล์ชั่วคราวสำหรับ n8n (แบบ Edoc — writable เท่านั้น + route cv-ai/public/file)
 */
final class CvAiFileStorage
{
    private const ALLOWED_EXT = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'txt'];

    private const MAX_BYTES = 10_485_760; // 10MB

    /** writable/uploads/cv_ai/ (เทียบ Edoc → writable/edoc_documents/) */
    public static function uploadDir(): string
    {
        $dir = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
            @file_put_contents($dir . 'index.html', '');
        }

        return $dir;
    }

    private static function legacyWritableUploadDirV1(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'cv_ai_uploads' . DIRECTORY_SEPARATOR;
    }

    /** @deprecated ไฟล์เก่าที่ mirror ไป public */
    public static function publicUploadDir(): string
    {
        return rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'cv_ai' . DIRECTORY_SEPARATOR;
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
        if (! is_dir($dir) || ! is_writable($dir)) {
            return [
                'success' => false,
                'message' => 'เซิร์ฟเวอร์สร้างโฟลเดอร์อัปโหลดไม่ได้ — ตรวจสิทธิ์เขียนที่ writable\\uploads\\cv_ai',
            ];
        }

        $storedName = bin2hex(random_bytes(16)) . '.' . $ext;
        if (! $file->move($dir, $storedName)) {
            return ['success' => false, 'message' => 'บันทึกไฟล์ไม่สำเร็จ'];
        }

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

    /**
     * หา path ไฟล์จริง (เทียบ Edoc publicViewFile — ลองหลาย base path)
     */
    public static function resolveReadablePath(string $storedName): ?string
    {
        return service('cvAiFile')->resolveAbsolutePath($storedName);
    }

    public static function pathForStoredName(string $storedName): ?string
    {
        return self::resolveReadablePath($storedName);
    }

    /** URL สาธารณะให้ n8n — CvAiFileController + CvAiFileService */
    public static function publicDownloadUrl(string $storedName): string
    {
        return service('cvAiFile')->publicDownloadUrl($storedName);
    }

    public static function mimeForFilename(string $filename): string
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $map = [
            'pdf'  => 'application/pdf',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'txt'  => 'text/plain; charset=UTF-8',
        ];

        return $map[$ext] ?? 'application/octet-stream';
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

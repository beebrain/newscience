<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFileInterface;

/**
 * อัปโหลด/ลบรูปบุคลากรใต้ writable/uploads/staff (และสร้าง thumbnail)
 */
class StaffImageUpload
{
    public static function staffUploadPath(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
    }

    /**
     * บันทึกไฟล์ที่อัปโหลด คืน path แบบ staff/filename หรือ null
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $file
     */
    public static function handleUpload(?UploadedFileInterface $file): ?string
    {
        log_message('info', 'StaffImageUpload::handleUpload');

        if (!$file || !$file->isValid() || $file->getError() === UPLOAD_ERR_NO_FILE) {
            log_message('info', 'StaffImageUpload: no valid file (' . ($file ? (string) $file->getError() : 'null') . ')');

            return null;
        }
        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $validTypes, true)) {
            log_message('error', 'StaffImageUpload: invalid mime ' . $file->getMimeType());

            return null;
        }
        if ($file->getSize() > 20 * 1024 * 1024) {
            log_message('error', 'StaffImageUpload: file too large');

            return null;
        }
        $dir = self::staffUploadPath();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);

        $fullPath = $dir . DIRECTORY_SEPARATOR . $newName;
        $maxBytes = 1 * 1024 * 1024;
        if (is_file($fullPath) && filesize($fullPath) > $maxBytes) {
            helper('image');
            if (resize_image_to_max_bytes($fullPath, $maxBytes)) {
                log_message('info', 'StaffImageUpload: resized under 1 MB: ' . $newName);
            }
        }

        helper('image');
        if (create_staff_thumbnail($fullPath)) {
            log_message('info', 'StaffImageUpload: thumbnail created: ' . $newName);
        }

        $relativePath = 'staff/' . $newName;
        log_message('info', 'StaffImageUpload: saved ' . $relativePath);

        return $relativePath;
    }

    /** ลบไฟล์จาก path แบบ staff/filename รวม thumbnail และสำเนาใต้ public/uploads/staff ถ้ามี */
    public static function deleteStaffImageFile(string $relativePath): void
    {
        if ($relativePath === '' || strpos($relativePath, 'staff/') !== 0) {
            return;
        }
        $fn = basename($relativePath);
        $dir = self::staffUploadPath();
        $path = $dir . DIRECTORY_SEPARATOR . $fn;
        $thumbPath = $dir . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $fn;
        if (is_file($path)) {
            @unlink($path);
        }
        if (is_file($thumbPath)) {
            @unlink($thumbPath);
        }
        $publicDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'staff';
        $publicPath = $publicDir . DIRECTORY_SEPARATOR . $fn;
        if (is_file($publicPath)) {
            @unlink($publicPath);
        }
        $publicThumb = $publicDir . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $fn;
        if (is_file($publicThumb)) {
            @unlink($publicThumb);
        }
    }
}

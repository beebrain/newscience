<?php

/**
 * Upload Helper — เก็บไฟล์อัปโหลดทั้งหมดใน writable/uploads/ เท่านั้น
 * Load with: helper('program_upload');
 *
 * โครงสร้างโฟลเดอร์ (แบ่งตาม Feature ไม่มี subfolder ตามหลักสูตร):
 *   writable/uploads/
 *   ├── news/                      ← ข่าวทั้งหมด ชื่อไฟล์ใช้ prefix p1_, p2_ = หลักสูตร
 *   │   └── thumbs/
 *   ├── activities/                ← รูปกิจกรรมทั้งหมด ชื่อไฟล์ใช้ prefix p1_, p2_ = หลักสูตร
 *   │   └── thumbs/
 *   ├── downloads/                 ← ไฟล์เอกสารดาวน์โหลดทั้งหมด ชื่อไฟล์ใช้ prefix p1_, p2_ = หลักสูตร
 *   ├── events/
 *   ├── hero/
 *   └── staff/
 *
 * — ไม่ใช้ public/uploads/ เพื่อความปลอดภัย ต้องส่งผ่าน Serve controller เท่านั้น
 * — รูปภาพ: สร้าง Thumbnail หลังอัปโหลด (ใช้ program_create_image_thumbnail)
 */

if (!function_exists('upload_base_path')) {
    /**
     * Path ฐานสำหรับอัปโหลดทั้งหมด (writable เท่านั้น)
     *
     * @return string Absolute path พร้อม trailing slash
     */
    function upload_base_path(): string
    {
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    }
}

if (!function_exists('upload_path')) {
    /**
     * Path สำหรับ feature และ sub folder (สร้างโฟลเดอร์ถ้ายังไม่มี)
     * ใช้เมื่อต้องการจัดเก็บตาม feature/sub เช่น news, programs/1/news
     *
     * @param string $feature เช่น 'news', 'events', 'programs'
     * @param string|null $sub เช่น 'thumbs', หรือเลขหลักสูตร (optional)
     * @return string Absolute path พร้อม trailing slash
     */
    function upload_path(string $feature, ?string $sub = null): string
    {
        $base = upload_base_path() . str_replace(['\\', '..'], ['', ''], $feature) . DIRECTORY_SEPARATOR;
        if ($sub !== null && $sub !== '') {
            $base .= str_replace(['\\', '..'], ['', ''], $sub) . DIRECTORY_SEPARATOR;
        }
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        return $base;
    }
}

if (!function_exists('program_upload_path')) {
    /**
     * Get writable upload path for a program feature. Creates directory if missing.
     * news, activities, downloads เก็บรวมในโฟลเดอร์ตาม feature — ใช้รหัสหลักสูตรในชื่อไฟล์ (p1_xxx)
     *
     * @param int $programId Program ID
     * @param string $feature One of: 'news', 'downloads', 'activities'
     * @return string Absolute path (with trailing slash)
     */
    function program_upload_path(int $programId, string $feature): string
    {
        if (in_array($feature, ['news', 'activities', 'downloads'], true)) {
            return upload_path($feature);
        }
        $pid = (int) $programId;
        $base = upload_base_path() . 'programs' . DIRECTORY_SEPARATOR . $pid . DIRECTORY_SEPARATOR . $feature . DIRECTORY_SEPARATOR;
        if (!is_dir($base)) {
            mkdir($base, 0755, true);
        }
        return $base;
    }
}

if (!function_exists('program_unique_filename')) {
    /**
     * Generate a unique filename for an uploaded file to avoid collisions.
     * Format: Ymd_His_8hex.ext
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param string $prefix Optional prefix (e.g. 'img', 'doc')
     * @return string Filename only (no path)
     */
    function program_unique_filename(\CodeIgniter\HTTP\Files\UploadedFile $file, string $prefix = ''): string
    {
        $ext = strtolower($file->getExtension());
        if ($ext === '') {
            $ext = pathinfo($file->getClientName(), PATHINFO_EXTENSION);
            $ext = $ext !== '' ? strtolower($ext) : 'bin';
        }
        $part = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        if ($prefix !== '') {
            $part = $prefix . '_' . $part;
        }
        return $part . '.' . $ext;
    }
}

if (!function_exists('featured_image_filename')) {
    /**
     * ชื่อไฟล์สำหรับภาพ Featured เท่านั้น รูปแบบ Feature_p1_xxx.jpg หรือ Feature_364_xxx.jpg
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile $file
     * @param int|null $programId หลักสูตร (ได้ Feature_p1_xxx.jpg)
     * @param int|null $newsId รหัสข่าว จาก admin (ได้ Feature_364_xxx.jpg)
     * @return string ชื่อไฟล์เท่านั้น
     */
    function featured_image_filename(\CodeIgniter\HTTP\Files\UploadedFile $file, ?int $programId = null, ?int $newsId = null): string
    {
        $ext = strtolower($file->getExtension());
        if ($ext === '') {
            $ext = pathinfo($file->getClientName(), PATHINFO_EXTENSION);
            $ext = $ext !== '' ? strtolower($ext) : 'jpg';
        }
        $part = date('Ymd_His') . '_' . bin2hex(random_bytes(4));
        if ($programId !== null) {
            $prefix = 'Feature_p' . (int) $programId . '_';
        } elseif ($newsId !== null) {
            $prefix = 'Feature_' . (int) $newsId . '_';
        } else {
            $prefix = 'Feature_';
        }
        return $prefix . $part . '.' . $ext;
    }
}

if (!function_exists('program_upload_relative_path')) {
    /**
     * Relative path from uploads/ for storing in DB (ใช้กับ serve/uploads/...).
     * news, activities, downloads: {feature}/filename (ชื่อไฟล์ควรมี prefix p{id}_ จาก caller)
     *
     * @param int $programId
     * @param string $feature 'news' | 'activities' | 'downloads'
     * @param string $filename
     * @return string Path สัมพันธ์ เช่น activities/p1_img_xxx.jpg, downloads/p1_doc_xxx.pdf
     */
    function program_upload_relative_path(int $programId, string $feature, string $filename): string
    {
        if (in_array($feature, ['news', 'activities', 'downloads'], true)) {
            return $feature . '/' . $filename;
        }
        return 'programs/' . (int) $programId . '/' . $feature . '/' . $filename;
    }
}

if (!function_exists('program_create_image_thumbnail')) {
    /**
     * สร้าง thumbnail สำหรับรูปที่อัปโหลด (news/, programs/1/activities)
     * เก็บที่โฟลเดอร์ thumbs ข้างๆ ไฟล์ต้นฉบับ (เช่น news/thumbs/)
     *
     * @param string $fullPath path เต็มของไฟล์รูป
     * @return bool สำเร็จหรือไม่
     */
    function program_create_image_thumbnail(string $fullPath): bool
    {
        if (!is_file($fullPath)) {
            return false;
        }
        helper('image');
        return function_exists('create_upload_thumbnail') && create_upload_thumbnail($fullPath);
    }
}

if (!function_exists('program_thumb_relative_path')) {
    /**
     * คืน relative path ของ thumbnail จาก path รูปต้นฉบับ (สำหรับใช้กับ serve/uploads/)
     * เช่น news/p1_img_xxx.jpg → news/thumbs/p1_img_xxx.jpg
     *
     * @param string $imageRelativePath path สัมพันธ์ของรูป
     * @return string path สัมพันธ์ของ thumbnail
     */
    function program_thumb_relative_path(string $imageRelativePath): string
    {
        $dir = dirname($imageRelativePath);
        $base = basename($imageRelativePath);
        if ($dir === '.' || $dir === '') {
            $dir = 'news';
        }
        return $dir . '/thumbs/' . $base;
    }
}

if (!function_exists('upload_resolve_full_path')) {
    /**
     * แปลง relative path (จาก DB) เป็น full path บนดิสก์ สำหรับลบไฟล์
     *
     * @param string $relativePath เช่น activities/p1_img_xxx.jpg, downloads/p1_doc.pdf
     * @return string path เต็มใน writable/uploads/
     */
    function upload_resolve_full_path(string $relativePath): string
    {
        $path = str_replace(['\\', '..'], ['/', ''], $relativePath);
        return rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    }
}

if (!function_exists('featured_image_serve_url')) {
    /**
     * คืน URL สำหรับแสดงรูป featured_image (ข่าว/หลักสูตร)
     * ข่าวทั้งหมดเก็บใน news/ (path ขึ้นต้น news/) แก้ที่ admin ก็เห็นรูป
     *
     * @param string $imagePath ค่า featured_image จาก DB (path สัมพันธ์ หรือ URL เต็ม)
     * @param bool $useThumb true = thumbnail สำหรับรายการ, false = รูปเต็ม
     * @return string URL สำหรับใส่ใน src
     */
    function featured_image_serve_url(string $imagePath, bool $useThumb = false): string
    {
        $imagePath = trim($imagePath);
        if ($imagePath === '') {
            return '';
        }
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        if (strpos($imagePath, 'newsimages/') === 0) {
            return base_url($imagePath);
        }
        if (strpos($imagePath, 'news/') === 0) {
            $basename = basename($imagePath);
            return $useThumb
                ? base_url('serve/thumb/news/' . $basename)
                : base_url('serve/uploads/' . $imagePath);
        }
        if (strpos($imagePath, 'programs/') === 0 || strpos($imagePath, 'news/program-') === 0) {
            $path = $useThumb ? program_thumb_relative_path($imagePath) : $imagePath;
            return base_url('serve/uploads/' . $path);
        }
        $basename = basename($imagePath);
        $newsimagesPath = defined('FCPATH') ? rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'newsimages' . DIRECTORY_SEPARATOR . $imagePath : '';
        if ($newsimagesPath !== '' && is_file($newsimagesPath)) {
            return base_url('newsimages/' . $imagePath);
        }
        return $useThumb
            ? base_url('serve/thumb/news/' . $basename)
            : base_url('serve/uploads/news/' . $basename);
    }
}

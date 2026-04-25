<?php

/**
 * Image Manager Helper — ระบบจัดการรูปภาพแบบ unified สำหรับ popup / news / event
 * Load with: helper('image_manager');
 *
 * Entity whitelist (เพิ่มใหม่ได้ในคงที่ด้านล่าง):
 *   popup → writable/uploads/popups/      (DB: urgent_popups.image)
 *   news  → writable/uploads/news/        (DB: news.featured_image)
 *   event → writable/uploads/events/      (DB: events.featured_image)
 *
 * พฤติกรรมหลัก:
 * - ชื่อไฟล์มาตรฐาน: {entity}_{id}_{Ymd_His}_{hex}.{ext}  (id = 0 ถ้ายังไม่มี)
 * - upload อ่าน width/height อัตโนมัติ พร้อมสร้าง thumbnail (ถ้ารูป)
 * - รองรับทั้ง UploadedFile และ base64 (จาก crop client-side)
 * - normalize path ใน DB ให้ serve ได้ถูกต้องแม้รูปแบบเก่า
 *   (popup: uploads/urgent_popups/... , event: filename เฉยๆ)
 */

if (!defined('IMAGE_MANAGER_ENTITIES')) {
    define('IMAGE_MANAGER_ENTITIES', [
        'popup' => [
            'folder'  => 'popups',          // โฟลเดอร์ใหม่
            'legacy'  => 'urgent_popups',   // โฟลเดอร์เก่า (backward-compat)
            'prefix'  => 'popup',
            'thumbs'  => true,
        ],
        'news'  => [
            'folder'  => 'news',
            'legacy'  => null,
            'prefix'  => 'news',
            'thumbs'  => true,
        ],
        'event' => [
            'folder'  => 'events',
            'legacy'  => null,
            'prefix'  => 'event',
            'thumbs'  => true,
        ],
        'executive_poster' => [
            'folder'  => 'executive_posters',
            'legacy'  => null,
            'prefix'  => 'exec_poster',
            'thumbs'  => true,
        ],
    ]);
}

if (!function_exists('image_manager_config')) {
    /**
     * คืน config ของ entity หรือ null ถ้าไม่รู้จัก
     *
     * @return array{folder:string, legacy:?string, prefix:string, thumbs:bool}|null
     */
    function image_manager_config(string $entity): ?array
    {
        $entity = strtolower($entity);
        return IMAGE_MANAGER_ENTITIES[$entity] ?? null;
    }
}

if (!function_exists('image_manager_unique_filename')) {
    /**
     * สร้างชื่อไฟล์ตามมาตรฐาน: {entity}_{id}_{Ymd_His}_{hex}.{ext}
     *
     * @param string $entity popup|news|event
     * @param int    $id     id ของ record (ใช้ 0 ถ้ายังไม่มี)
     * @param string $ext    นามสกุลไฟล์ (ไม่มีจุดนำ)
     */
    function image_manager_unique_filename(string $entity, int $id, string $ext): string
    {
        $cfg = image_manager_config($entity);
        $prefix = $cfg['prefix'] ?? preg_replace('/[^a-z0-9]/i', '', $entity);
        $ext = strtolower(ltrim($ext, '.'));
        if ($ext === '' || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            $ext = 'jpg';
        }
        return sprintf(
            '%s_%d_%s_%s.%s',
            $prefix,
            max(0, $id),
            date('Ymd_His'),
            bin2hex(random_bytes(4)),
            $ext
        );
    }
}

if (!function_exists('image_manager_folder_path')) {
    /**
     * คืน absolute path ของโฟลเดอร์ entity (สร้างถ้าไม่มี)
     *
     * @param string $entity popup|news|event
     * @param bool   $thumbs true = subfolder thumbs/
     */
    function image_manager_folder_path(string $entity, bool $thumbs = false): ?string
    {
        $cfg = image_manager_config($entity);
        if ($cfg === null) return null;
        $base = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR
              . 'uploads' . DIRECTORY_SEPARATOR . $cfg['folder'] . DIRECTORY_SEPARATOR;
        if ($thumbs) $base .= 'thumbs' . DIRECTORY_SEPARATOR;
        if (!is_dir($base)) @mkdir($base, 0755, true);
        return $base;
    }
}

if (!function_exists('image_manager_normalize_path')) {
    /**
     * Normalize relative path จาก DB ให้อยู่ในรูป "{folder}/{filename}"
     * รองรับค่าเดิมที่ไม่ sotistent เช่น:
     *   'uploads/urgent_popups/xxx.jpg' → 'popups/xxx.jpg' (แต่คง legacy ไว้ให้ fallback ตอน serve)
     *   'xxx.jpg' (event เก่า)          → 'events/xxx.jpg'
     *   'news/foo.jpg'                   → 'news/foo.jpg' (ใช้งานได้อยู่แล้ว)
     *
     * @return string|null คืน path normalized หรือ null ถ้าว่าง
     */
    function image_manager_normalize_path(string $entity, ?string $path): ?string
    {
        if ($path === null) return null;
        $p = trim(str_replace('\\', '/', $path));
        if ($p === '') return null;

        // URL เต็ม → ปล่อยผ่าน
        if (preg_match('#^https?://#i', $p)) return $p;

        // ตัด prefix 'uploads/' ถ้ามี
        if (strpos($p, 'uploads/') === 0) {
            $p = substr($p, strlen('uploads/'));
        }

        $cfg = image_manager_config($entity);
        if ($cfg === null) return $p;

        $folder = $cfg['folder'];
        $legacy = $cfg['legacy'];

        // ถ้าเป็น legacy path (urgent_popups/...) — ปล่อยไว้ตามเดิม ไม่ rewrite
        // Serve จะ fallback หาในโฟลเดอร์ใหม่ให้เอง
        if ($legacy !== null && strpos($p, $legacy . '/') === 0) {
            return $p;
        }

        // ขึ้นต้นด้วย folder ใหม่แล้ว → ใช้ได้เลย
        if (strpos($p, $folder . '/') === 0) return $p;

        // แค่ชื่อไฟล์ (ไม่มี slash) → เติม folder
        if (strpos($p, '/') === false) return $folder . '/' . $p;

        return $p;
    }
}

if (!function_exists('image_manager_serve_url')) {
    /**
     * คืน URL สำหรับแสดงรูป (รองรับทั้งรูปแบบเก่า/ใหม่)
     *
     * @param string $entity popup|news|event
     * @param string|null $path ค่าจาก DB (ชื่อฟิลด์ image หรือ featured_image)
     * @param bool $thumb true = ใช้ thumbnail ถ้ามี
     */
    function image_manager_serve_url(string $entity, ?string $path, bool $thumb = false): string
    {
        $p = image_manager_normalize_path($entity, $path);
        if ($p === null) return '';
        if (preg_match('#^https?://#i', $p)) return $p;

        if (!$thumb) {
            return base_url('serve/uploads/' . $p);
        }

        // Thumbnail: แทรก /thumbs/ ก่อนชื่อไฟล์
        $dir = dirname($p);
        $base = basename($p);
        if ($dir === '.' || $dir === '') {
            return base_url('serve/uploads/' . $base);
        }
        return base_url('serve/uploads/' . $dir . '/thumbs/' . $base);
    }
}

if (!function_exists('image_manager_read_dimensions')) {
    /**
     * อ่าน width/height จากไฟล์รูป
     *
     * @return array{width:int,height:int}|null null ถ้าอ่านไม่ได้
     */
    function image_manager_read_dimensions(string $absolutePath): ?array
    {
        if (!is_file($absolutePath)) return null;
        $info = @getimagesize($absolutePath);
        if (!is_array($info) || empty($info[0]) || empty($info[1])) return null;
        return ['width' => (int) $info[0], 'height' => (int) $info[1]];
    }
}

if (!function_exists('image_manager_save_base64')) {
    /**
     * บันทึกรูปจาก base64 (data URI หรือ raw base64) ลงโฟลเดอร์ entity
     *
     * @return array{path:string, width:?int, height:?int, thumb_path:?string}|null
     */
    function image_manager_save_base64(string $entity, int $id, string $base64): ?array
    {
        $cfg = image_manager_config($entity);
        if ($cfg === null) return null;

        $raw = $base64;
        // ดึง ext จาก data URI ถ้ามี
        $ext = 'jpg';
        if (preg_match('#^data:image/([a-z0-9+]+);base64,#i', $raw, $m)) {
            $mime = strtolower($m[1]);
            $extMap = ['jpeg' => 'jpg', 'jpg' => 'jpg', 'png' => 'png', 'webp' => 'webp', 'gif' => 'gif'];
            $ext = $extMap[$mime] ?? 'jpg';
            $raw = substr($raw, strpos($raw, 'base64,') + 7);
        }

        $bin = base64_decode($raw, true);
        if ($bin === false || strlen($bin) === 0) return null;

        $folder = image_manager_folder_path($entity, false);
        if ($folder === null || !is_writable($folder)) return null;

        $filename = image_manager_unique_filename($entity, $id, $ext);
        $fullPath = $folder . $filename;
        if (file_put_contents($fullPath, $bin) === false) return null;

        return image_manager_finalize_upload($entity, $cfg, $folder, $filename, $fullPath);
    }
}

if (!function_exists('image_manager_save_file')) {
    /**
     * บันทึกรูปจาก UploadedFile ลงโฟลเดอร์ entity
     *
     * @return array{path:string, width:?int, height:?int, thumb_path:?string}|null
     */
    function image_manager_save_file(string $entity, int $id, \CodeIgniter\HTTP\Files\UploadedFile $file): ?array
    {
        $cfg = image_manager_config($entity);
        if ($cfg === null) return null;
        if (!$file->isValid() || $file->hasMoved()) return null;

        $ext = strtolower($file->getExtension());
        if ($ext === '') {
            $ext = strtolower(pathinfo($file->getClientName(), PATHINFO_EXTENSION));
        }
        $filename = image_manager_unique_filename($entity, $id, $ext ?: 'jpg');

        $folder = image_manager_folder_path($entity, false);
        if ($folder === null || !is_writable($folder)) return null;
        $file->move(rtrim($folder, DIRECTORY_SEPARATOR), $filename);

        $fullPath = $folder . $filename;
        return image_manager_finalize_upload($entity, $cfg, $folder, $filename, $fullPath);
    }
}

if (!function_exists('image_manager_finalize_upload')) {
    /**
     * ภายใน — อ่าน dimensions + สร้าง thumbnail (ถ้า config เปิดไว้)
     * คืน metadata array สำหรับเก็บลง DB
     *
     * @internal
     */
    function image_manager_finalize_upload(string $entity, array $cfg, string $folder, string $filename, string $fullPath): array
    {
        $dims = image_manager_read_dimensions($fullPath);

        $thumbPath = null;
        if (!empty($cfg['thumbs']) && is_file($fullPath)) {
            helper('image');
            if (function_exists('create_upload_thumbnail')) {
                $ok = @create_upload_thumbnail($fullPath);
                if ($ok) {
                    $thumbFull = $folder . 'thumbs' . DIRECTORY_SEPARATOR . $filename;
                    if (is_file($thumbFull)) {
                        $thumbPath = $cfg['folder'] . '/thumbs/' . $filename;
                    }
                }
            }
        }

        return [
            'path'       => $cfg['folder'] . '/' . $filename,
            'width'      => $dims['width']  ?? null,
            'height'     => $dims['height'] ?? null,
            'thumb_path' => $thumbPath,
        ];
    }
}

if (!function_exists('image_manager_delete')) {
    /**
     * ลบรูปและ thumbnail (รองรับทั้ง writable/ และ public/ — กรณีรูปเก่า)
     * ใช้ได้ทั้ง path ใหม่ (popups/...) และเก่า (urgent_popups/...)
     */
    function image_manager_delete(string $entity, ?string $path): void
    {
        $p = image_manager_normalize_path($entity, $path);
        if ($p === null) return;
        if (preg_match('#^https?://#i', $p)) return;

        $candidates = [
            rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $p),
            rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $p),
        ];
        foreach ($candidates as $c) {
            if (is_file($c)) @unlink($c);
        }

        // ลบ thumbnail ด้วย
        $dir = dirname($p);
        $base = basename($p);
        if ($dir !== '.' && $dir !== '') {
            $thumbRel = $dir . '/thumbs/' . $base;
            $thumbCandidates = [
                rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbRel),
                rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $thumbRel),
            ];
            foreach ($thumbCandidates as $tc) {
                if (is_file($tc)) @unlink($tc);
            }
        }
    }
}

if (!function_exists('image_manager_aspect_css')) {
    /**
     * สร้างค่า CSS variable สำหรับ .smart-media-frame (aspect-ratio จาก width/height)
     * ถ้าไม่มี dimensions → คืน '' (CSS จะ fallback เป็น auto)
     *
     * @param int|null $w
     * @param int|null $h
     */
    function image_manager_aspect_css(?int $w, ?int $h): string
    {
        $w = (int) $w;
        $h = (int) $h;
        if ($w > 0 && $h > 0) {
            return '--media-aspect: ' . $w . '/' . $h . ';';
        }
        return '';
    }
}

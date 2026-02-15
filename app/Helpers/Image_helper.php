<?php

/**
 * Image helper: สร้าง thumbnail และลดขนาดรูปตาม Config Thumbnails
 * ใช้เมื่ออัปโหลด/ปรับปรุงรูปบุคลากร (staff) หรือรูปข่าว (news)
 */

if (!function_exists('create_staff_thumbnail')) {
    /**
     * สร้าง thumbnail สำหรับรูปบุคลากร (staff)
     * เก็บที่โฟลเดอร์ thumbs ข้างๆ ไฟล์ต้นฉบับ (uploads/staff/thumbs/)
     *
     * @param string $fullPath path เต็มของไฟล์รูป เช่น writable/uploads/staff/xxx.jpg
     * @return bool สำเร็จหรือไม่
     */
    function create_staff_thumbnail(string $fullPath): bool
    {
        return create_upload_thumbnail($fullPath);
    }
}

if (!function_exists('create_news_thumbnail')) {
    /**
     * สร้าง thumbnail สำหรับรูปข่าว (news)
     * เก็บที่ uploads/news/thumbs/
     *
     * @param string $fullPath path เต็มของไฟล์รูป เช่น writable/uploads/news/123.jpg
     * @return bool สำเร็จหรือไม่
     */
    function create_news_thumbnail(string $fullPath): bool
    {
        return create_upload_thumbnail($fullPath);
    }
}

if (!function_exists('create_upload_thumbnail')) {
    /**
     * สร้าง thumbnail จากไฟล์รูปต้นฉบับ ใช้ Config Thumbnails (max 800px, ไม่เกิน 1MB)
     * เก็บที่โฟลเดอร์ thumbs ในระดับเดียวกับไฟล์ต้นฉบับ
     *
     * @param string $fullPath path เต็มของไฟล์รูป
     * @return bool สำเร็จหรือไม่
     */
    function create_upload_thumbnail(string $fullPath): bool
    {
        if (!is_file($fullPath) || !function_exists('imagecreatetruecolor')) {
            return false;
        }
        $dir   = dirname($fullPath);
        $base  = basename($fullPath);
        $thumbDir = $dir . DIRECTORY_SEPARATOR . 'thumbs';
        if (!is_dir($thumbDir)) {
            @mkdir($thumbDir, 0755, true);
        }
        $thumbPath = $thumbDir . DIRECTORY_SEPARATOR . $base;

        $config = config('Thumbnails');
        $maxW   = $config ? $config->maxWidth : 800;
        $maxH   = $config ? $config->maxHeight : 800;
        $maxB   = $config ? $config->maxBytes : 1048576;
        $jpegQ  = $config ? $config->jpegQuality : 75;
        $pngQ   = $config ? $config->pngCompression : 6;

        $result = _image_create_thumbnail($fullPath, $thumbPath, $maxW, $maxH, $maxB, $jpegQ, $pngQ);
        return $result === true;
    }
}

if (!function_exists('resize_image_to_max_bytes')) {
    /**
     * ลดขนาด/คุณภาพรูปใน place ให้ไม่เกิน maxBytes (ใช้กับรูปต้นฉบับ เช่น profile > 1MB)
     *
     * @param string $fullPath path เต็มของไฟล์รูป
     * @param int $maxBytes ขนาดสูงสุด (bytes)
     * @return bool สำเร็จหรือไม่
     */
    function resize_image_to_max_bytes(string $fullPath, int $maxBytes = 1048576): bool
    {
        if (!is_file($fullPath) || filesize($fullPath) <= $maxBytes || !function_exists('imagecreatetruecolor')) {
            return false;
        }
        $config = config('Thumbnails');
        $maxW   = $config ? $config->maxWidth : 800;
        $maxH   = $config ? $config->maxHeight : 800;
        $jpegQ  = $config ? $config->jpegQuality : 75;
        $pngQ   = $config ? $config->pngCompression : 6;
        $result = _image_create_thumbnail($fullPath, $fullPath, $maxW, $maxH, $maxBytes, $jpegQ, $pngQ);
        return $result === true;
    }
}

if (!function_exists('_image_create_thumbnail')) {
    /**
     * สร้าง thumbnail (ลดขนาดและคุณภาพ ไม่เกิน maxBytes)
     *
     * @param string $srcPath ไฟล์ต้นทาง
     * @param string $outPath ไฟล์ปลายทาง (อาจเป็น path เดียวกับต้นทาง สำหรับ resize in place)
     * @param int $maxW ความกว้างสูงสุด
     * @param int $maxH ความสูงสูงสุด
     * @param int $maxBytes ขนาดไฟล์สูงสุด
     * @param int $jpegQuality คุณภาพ JPEG
     * @param int $pngQuality การบีบอัด PNG
     * @return true|string true ถ้าสำเร็จ หรือ string ข้อความ error
     */
    function _image_create_thumbnail(
        string $srcPath,
        string $outPath,
        int $maxW,
        int $maxH,
        int $maxBytes,
        int $jpegQuality = 75,
        int $pngQuality = 6
    ) {
        $img = _image_load($srcPath);
        if (!$img) {
            return 'Could not load image.';
        }

        $w = imagesx($img);
        $h = imagesy($img);
        if ($w <= 0 || $h <= 0) {
            imagedestroy($img);
            return 'Invalid dimensions.';
        }

        $ext = strtolower(pathinfo($outPath, PATHINFO_EXTENSION));
        if ($ext === 'jpg') {
            $ext = 'jpeg';
        }

        $scale  = min($maxW / $w, $maxH / $h, 1.0);
        $newW   = (int) round($w * $scale);
        $newH   = (int) round($h * $scale);
        $newW   = max(1, $newW);
        $newH   = max(1, $newH);

        /** @var \GdImage|resource|false $thumb */
        $thumb = imagecreatetruecolor($newW, $newH);
        if (!$thumb) {
            imagedestroy($img);
            return 'Could not create thumbnail.';
        }

        if (!imagecopyresampled($thumb, $img, 0, 0, 0, 0, $newW, $newH, $w, $h)) {
            imagedestroy($img);
            imagedestroy($thumb);
            return 'Resample failed.';
        }
        imagedestroy($img);

        $q    = $jpegQuality;
        $pngQ = $pngQuality;
        $maxIter = 15;
        $iter = 0;

        while ($iter < $maxIter) {
            $iter++;
            $ok = false;
            if ($ext === 'jpeg' || $ext === 'jpg') {
                $ok = imagejpeg($thumb, $outPath, $q);
            } elseif ($ext === 'png') {
                $ok = imagepng($thumb, $outPath, $pngQ);
            } elseif ($ext === 'gif') {
                $ok = imagegif($thumb, $outPath);
            } elseif ($ext === 'webp' && function_exists('imagewebp')) {
                $ok = imagewebp($thumb, $outPath, $q);
            } else {
                $ok = imagejpeg($thumb, $outPath, $q);
            }

            if (!$ok) {
                imagedestroy($thumb);
                return 'Could not write file.';
            }

            $outSize = @filesize($outPath);
            if ($outSize !== false && $outSize <= $maxBytes) {
                imagedestroy($thumb);
                return true;
            }

            if ($ext === 'jpeg' || $ext === 'jpg' || ($ext === 'webp' && function_exists('imagewebp'))) {
                $q -= 8;
                if ($q < 25) {
                    $q = 25;
                    if ($newW > 80 && $newH > 80) {
                        $nextW = (int) ($newW * 0.8);
                        $nextH = (int) ($newH * 0.8);
                        /** @var \GdImage|resource|false $tmp */
                        $tmp   = imagecreatetruecolor($nextW, $nextH);
                        if ($tmp) {
                            imagecopyresampled($tmp, $thumb, 0, 0, 0, 0, $nextW, $nextH, $newW, $newH);
                            imagedestroy($thumb);
                            $thumb = $tmp;
                            $newW  = $nextW;
                            $newH  = $nextH;
                            $q     = $jpegQuality;
                        }
                    }
                }
            } elseif ($ext === 'png') {
                $pngQ = min(9, $pngQ + 1);
                if ($pngQ >= 9 && $newW > 80 && $newH > 80) {
                    $nextW = (int) ($newW * 0.8);
                    $nextH = (int) ($newH * 0.8);
                    /** @var \GdImage|resource|false $tmp */
                    $tmp   = imagecreatetruecolor($nextW, $nextH);
                    if ($tmp) {
                        imagecopyresampled($tmp, $thumb, 0, 0, 0, 0, $nextW, $nextH, $newW, $newH);
                        imagedestroy($thumb);
                        $thumb = $tmp;
                        $newW  = $nextW;
                        $newH  = $nextH;
                        $pngQ  = $pngQuality;
                    }
                }
            } else {
                imagedestroy($thumb);
                return true;
            }
        }

        imagedestroy($thumb);
        return true;
    }
}

if (!function_exists('_image_load')) {
    /**
     * โหลดรูปเป็น GD resource
     *
     * @param string $path path ไฟล์
     * @return \GdImage|resource|false GD image resource หรือ false
     */
    function _image_load(string $path)
    {
        $info = @getimagesize($path);
        if (!$info) {
            return false;
        }
        switch ($info[2]) {
            case IMAGETYPE_JPEG:
                return @imagecreatefromjpeg($path);
            case IMAGETYPE_PNG:
                return @imagecreatefrompng($path);
            case IMAGETYPE_GIF:
                return @imagecreatefromgif($path);
            case IMAGETYPE_WEBP:
                return function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false;
            default:
                return false;
        }
    }
}

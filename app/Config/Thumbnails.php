<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * การตั้งค่า Thumbnail สำหรับรูปที่อัปโหลดในโปรเจกต์
 * ใช้โดย Image_helper (create_staff_thumbnail, create_news_thumbnail)
 */
class Thumbnails extends BaseConfig
{
    /** ความกว้างสูงสุดของ thumbnail (พิกเซล) */
    public int $maxWidth = 800;

    /** ความสูงสูงสุดของ thumbnail (พิกเซล) */
    public int $maxHeight = 800;

    /** ขนาดไฟล์ thumbnail สูงสุด (bytes) ถ้าเกินจะลดคุณภาพ/ขนาดจนต่ำกว่านี้ */
    public int $maxBytes = 1048576;  // 1 MB

    /** คุณภาพ JPEG สำหรับ thumbnail (0–100) */
    public int $jpegQuality = 75;

    /** ระดับการบีบอัด PNG (0–9) */
    public int $pngCompression = 6;
}

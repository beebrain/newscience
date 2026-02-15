<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * API สำหรับอัปโหลดไฟล์แล้วถอดรหัสบาร์โค้ด (ส่งไฟล์ไป API แล้ว API แกะ barcode เป็น JSON).
 * ตั้งค่าใน .env: BARCODE_EXTRACT_API_URL, BARCODE_EXTRACT_API_KEY (ถ้ามี), BARCODE_EXTRACT_API_ENABLED
 *
 * คาดว่า API รับ POST multipart/form-data มีฟิลด์ไฟล์ (เช่น "file") และส่งกลับ JSON:
 *   { "barcodes": [ "code1", "code2", ... ] }
 * หรือ { "data": [ { "code": "..." }, ... ] }
 */
class BarcodeExtractApi extends BaseConfig
{
    /** URL เต็มของ API (รวม path) — ส่งไฟล์ที่อัปโหลดไปที่นี้ แล้ว API จะแกะ barcode ออกเป็น JSON */
    public string $url = 'https://sweetmeal-loamless-wendy.ngrok-free.dev/webhook/barcode';

    /** API key หรือ token สำหรับส่งใน header (ไม่บังคับ) */
    public string $apiKey = '';

    /** ชื่อฟิลด์ไฟล์ที่ API รับ (เช่น file, document) */
    public string $fileFieldName = 'file';

    /** เปิดใช้การเรียก API */
    public bool $enabled = true;

    public function __construct()
    {
        parent::__construct();
        $defaultUrl = 'https://sweetmeal-loamless-wendy.ngrok-free.dev/webhook/barcode';
        $this->url     = rtrim(env('BARCODE_EXTRACT_API_URL', $defaultUrl), '/');
        $this->apiKey  = (string) env('BARCODE_EXTRACT_API_KEY', '');
        $this->enabled = filter_var(env('BARCODE_EXTRACT_API_ENABLED') ?? true, FILTER_VALIDATE_BOOLEAN);
        if ($this->url === '/') {
            $this->url = '';
            $this->enabled = false;
        }
    }

    public function isConfigured(): bool
    {
        return $this->enabled && $this->url !== '';
    }
}

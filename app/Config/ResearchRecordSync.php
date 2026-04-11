<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Sync CV / publications ระหว่าง newScience กับ Research Record
 *
 * Phase 0 — ปลายทาง publications ใน newScience:
 * - **cv_entries (ตัวเลือก B)** — map ผลงานจาก RR เป็นรายการใต้หัวข้อ research/articles
 *   (ไม่เพิ่มตาราง publications แยก — ลดขอบเขต schema)
 * - ตัวเลือก A: ตาราง publications ใน NS (ยังไม่ใช้)
 * - ตัวเลือก C: snapshot / link only (ยังไม่ใช้)
 */
class ResearchRecordSync extends BaseConfig
{
    /** @var string cv_entries | publications_table | link_only */
    public string $publicationsDestination = 'cv_entries';

    /** HMAC สำหรับ query sync (email|exp) — ตั้งค่าเดียวกับฝั่ง Research Record (.env RESEARCH_SYNC_HMAC_SECRET) */
    public string $hmacSecret = '';

    /** อายุพารามิเตอร์ exp (วินาที) */
    public int $hmacTtlSeconds = 300;

    public function __construct()
    {
        parent::__construct();
        $this->hmacSecret = (string) env('RESEARCH_SYNC_HMAC_SECRET', '');
        $ttl                = env('RESEARCH_SYNC_HMAC_TTL');
        $this->hmacTtlSeconds = $ttl !== null && $ttl !== '' ? (int) $ttl : 300;
    }

    public function hmacEnabled(): bool
    {
        return $this->hmacSecret !== '';
    }
}

<?php

namespace App\Libraries;

use Config\ResearchRecordSync;

/**
 * ลงนาม HMAC สำหรับเรียก Research Record sync API — ต้องใช้ secret เดียวกับฝั่ง RR
 */
class ResearchSyncHmac
{
    public static function sign(string $email, int $exp): string
    {
        $cfg = config(ResearchRecordSync::class);
        $secret = $cfg->hmacSecret;
        if ($secret === '') {
            return '';
        }
        $payload = CvProfile::normalizeEmail($email) . '|' . $exp;

        return hash_hmac('sha256', $payload, $secret);
    }
}

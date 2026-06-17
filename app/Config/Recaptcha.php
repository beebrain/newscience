<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Google reCAPTCHA v3 settings.
 *
 * ใส่ค่า key จริงผ่าน .env (ไม่ commit secret):
 *   recaptcha.siteKey   = '6Lxxxx...'   (public — ฝังใน HTML ได้)
 *   recaptcha.secretKey = '6Lxxxx...'   (ลับ — ใช้ฝั่ง server ตรวจ token)
 *   recaptcha.minScore  = 0.5           (0.0–1.0 ยิ่งสูงยิ่งเข้ม)
 *
 * ถ้า secretKey ว่าง = ปิด reCAPTCHA อัตโนมัติ (honeypot/timing ยังทำงานอยู่)
 */
class Recaptcha extends BaseConfig
{
    /** Site key (public) — ใช้ฝั่งหน้าเว็บ */
    public string $siteKey = '';

    /** Secret key (private) — ใช้ฝั่ง server ตรวจสอบ token กับ Google */
    public string $secretKey = '';

    /** คะแนนขั้นต่ำที่ยอมรับ (reCAPTCHA v3 คืนค่า 0.0–1.0) */
    public float $minScore = 0.5;
}

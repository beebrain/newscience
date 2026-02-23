<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Research Record SSO — ล็อกอินผ่าน research.academic.uru.ac.th (URU Portal ผ่าน Research Record)
 * ใช้ shared secret เดียวกับ Edoc ได้ (ตั้ง edocsso.sharedSecret แล้วใช้านั้นที่นี่ หรือตั้ง researchrecordsso.sharedSecret)
 *
 * ตั้งค่าใน .env (ถ้ามี):
 *   researchrecordsso.baseUrl = "https://research.academic.uru.ac.th"
 *   researchrecordsso.exchangeCodeUrl = "https://research.academic.uru.ac.th/api/sso/exchange-code"
 *   researchrecordsso.sharedSecret = "same_as_edoc" หรือไม่ตั้งจะใช้ edocsso.sharedSecret
 *   researchrecordsso.enabled = "true"
 */
class ResearchRecordSso extends BaseConfig
{
    /** Base URL ของ Research Record (ไม่มี slash ท้าย) */
    public string $baseUrl = 'https://research.academic.uru.ac.th';

    /** URL ของ Research Record API สำหรับแลก one-time code เป็น user info (POST) */
    public string $exchangeCodeUrl = 'https://research.academic.uru.ac.th/api/sso/exchange-code';

    /** Shared secret (ใช้ค่าเดียวกับ Edoc และ Research Record) */
    public string $sharedSecret = 'SHARED_SECRET_PLACEHOLDER';

    /** เปิดใช้ SSO ผ่าน Research Record หรือไม่ */
    public bool $enabled = false;

    /** URL ออกจากระบบของ Research Record (ว่าง = ไม่ redirect ไป logout ที่ Research Record) */
    public string $logoutUrl = '';

    /** ชื่อ query parameter ที่ส่ง URL กลับหลัง logout (เช่น return_url หรือ redirect) */
    public string $logoutReturnParam = 'return_url';

    /** Path ของ SSO entry (ว่าง = /auth/sso-entry, ตั้งค่าถ้า server ต้องใช้ index.php เช่น /index.php/auth/sso-entry) */
    public string $ssoEntryPath = '/index.php/auth/sso-entry';

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = env('researchrecordsso.baseUrl', $this->baseUrl) ?: $this->baseUrl;
        $this->exchangeCodeUrl = env('researchrecordsso.exchangeCodeUrl', $this->exchangeCodeUrl) ?: $this->exchangeCodeUrl;
        $this->sharedSecret = env('researchrecordsso.sharedSecret', $this->sharedSecret) ?: env('edocsso.sharedSecret', $this->sharedSecret);
        $enabled = env('researchrecordsso.enabled', 'true');
        $this->enabled = ($enabled === 'true' || $enabled === '1' || $enabled === true);
        $this->logoutUrl = rtrim(env('researchrecordsso.logoutUrl', $this->logoutUrl) ?? '', '/ ');
        $param = env('researchrecordsso.logoutReturnParam', $this->logoutReturnParam);
        if ($param !== '') {
            $this->logoutReturnParam = $param;
        }
        $entryPath = env('researchrecordsso.ssoEntryPath', $this->ssoEntryPath);
        if ($entryPath !== '') {
            $this->ssoEntryPath = '/' . ltrim($entryPath, '/');
        }
    }
}

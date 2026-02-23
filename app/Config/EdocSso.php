<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Edoc SSO config — ล็อกอินผ่าน edoc.sci.uru.ac.th (URU Portal ผ่าน Edoc)
 * ใช้เมื่อไม่สามารถขอ OAuth client ใหม่จาก URU Portal ได้
 *
 * ตั้งค่าใน .env (ถ้ามี):
 *   edocsso.baseUrl = "https://edoc.sci.uru.ac.th" หรือ "https://edoc.sci.uru.ac.th/public" ถ้า Edoc เข้าแล้วได้ 404
 *   edocsso.returnUrl = "" (ว่าง = ใช้ base_url + /admin/oauth-callback)
 *   edocsso.exchangeCodeUrl = "https://edoc.sci.uru.ac.th/api/sso/exchange-code" (ใส่ /public ถ้า baseUrl มี /public)
 *   edocsso.sharedSecret = "your_shared_secret"
 *   edocsso.enabled = "true"
 *   เมื่อผู้ใช้กดออกจากระบบบน Edoc ต้อง redirect กลับมาที่ newScience:
 *   ให้ตั้งค่าใน Edoc ว่า หลัง logout redirect ไปที่: base_url() + /admin/edoc-logout-return
 *   เช่น https://sci.uru.ac.th/admin/edoc-logout-return (หรือ admin/login) เพื่อให้ผู้ใช้เด้งกลับมาหน้า login newScience
 */
class EdocSso extends BaseConfig
{
    /** Base URL ของ Edoc (ไม่มี slash ท้าย) */
    public string $baseUrl = 'https://edoc.sci.uru.ac.th';

    /** URL callback ของ newScience ที่ Edoc จะ redirect กลับมาพร้อม ?code=xxx */
    public string $returnUrl = '';

    /** URL ของ Edoc API สำหรับแลก one-time code เป็น user info (POST) */
    public string $exchangeCodeUrl = 'https://edoc.sci.uru.ac.th/api/sso/exchange-code';

    /** Shared secret ระหว่าง newScience กับ Edoc (ใช้ sign/ตรวจ request) — ใช้ค่าเดียวกับ Edoc และ Research Record */
    public string $sharedSecret = 'SHARED_SECRET_PLACEHOLDER';

    /** เปิดใช้ SSO ผ่าน Edoc หรือไม่ */
    public bool $enabled = false;

    /** URL ออกจากระบบของ Edoc เต็ม (ถ้าตั้งจะใช้แทน baseUrl + logoutPath) */
    public string $logoutUrl = '';

    /** Path ของ Edoc signout (ใช้เมื่อ logoutUrl ว่าง) — ต้องรับ query param ตาม logoutReturnParam แล้ว redirect กลับมาที่ newScience */
    public string $logoutPath = '/index.php/auth/logout';

    /** ชื่อ query parameter ที่ส่ง URL กลับมาหลัง signout (return_url หรือ redirect — ต้องเป็น URL หน้า newScience) */
    public string $logoutReturnParam = 'return_url';

    /** URL ที่ Edoc จะ redirect กลับมาหลัง signout (ต้องเป็นหน้า newScience — ว่าง = ใช้ admin/login?logout=1). ใช้เมื่อส่ง user ไป logout ที่ Edoc พร้อม return_url */
    public string $logoutReturnToUrl = '';

    /** Path ของ SSO entry (ว่าง = /auth/sso-entry, ตั้งค่าถ้า server ต้องใช้ index.php เช่น /index.php/auth/sso-entry) */
    public string $ssoEntryPath = '/index.php/auth/sso-entry';

    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = env('edocsso.baseUrl', $this->baseUrl) ?: $this->baseUrl;
        $this->returnUrl = env('edocsso.returnUrl', $this->returnUrl) ?: $this->returnUrl;
        $this->exchangeCodeUrl = env('edocsso.exchangeCodeUrl', $this->exchangeCodeUrl) ?: $this->exchangeCodeUrl;
        $this->sharedSecret = env('edocsso.sharedSecret', $this->sharedSecret) ?: $this->sharedSecret;
        $enabled = env('edocsso.enabled', 'true');
        $this->enabled = ($enabled === 'true' || $enabled === '1' || $enabled === true);
        if ($this->returnUrl === '' && function_exists('base_url')) {
            $this->returnUrl = rtrim(base_url(), '/') . '/admin/oauth-callback';
        }
        $logoutFromEnv = rtrim(env('edocsso.logoutUrl', '') ?? '', '/ ');
        $this->logoutUrl = $logoutFromEnv !== '' ? $logoutFromEnv : '';
        $path = env('edocsso.logoutPath', $this->logoutPath) ?? '';
        if ($path !== '') {
            $this->logoutPath = '/' . ltrim($path, '/');
        }
        // ถ้าไม่ได้ตั้ง logoutUrl ใน .env แต่เปิดใช้ Edoc — ใช้ baseUrl + logoutPath เพื่อให้ Portal ถูก signout แล้วเด้งกลับ newScience
        if ($this->logoutUrl === '' && $this->enabled && $this->baseUrl !== '') {
            $this->logoutUrl = rtrim($this->baseUrl, '/') . $this->logoutPath;
        }
        $param = env('edocsso.logoutReturnParam', $this->logoutReturnParam);
        if ($param !== '') {
            $this->logoutReturnParam = $param;
        }
        $returnTo = env('edocsso.logoutReturnToUrl', $this->logoutReturnToUrl) ?? '';
        $this->logoutReturnToUrl = $returnTo !== '' ? rtrim($returnTo, '?&') : '';
        // ค่า default: หลัง logout ที่ Edoc ให้ redirect มาที่ endpoint นี้
        if ($this->logoutReturnToUrl === '' && function_exists('base_url')) {
            $this->logoutReturnToUrl = rtrim(base_url(), '/') . '/admin/edoc-logout-return';
        }
    }
}

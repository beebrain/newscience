<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * URU Portal OAuth 2.0 Config
 * ล็อกอินผ่าน URU Portal (uruportal.uru.ac.th) ด้วย Authorization Code Flow
 *
 * ตั้งค่าใน .env:
 *   uruoauth.clientId       = sci
 *   uruoauth.clientSecret   = secret
 *   uruoauth.callbackUrl    = https://sci.uru.ac.th/index.php/oauth
 *   uruoauth.enabled        = true
 */
class UruPortalOAuth extends BaseConfig
{
    /** OAuth client_id ที่ได้รับจาก URU Portal */
    public string $clientId = 'SCI_PLACEHOLDER';

    /** OAuth client_secret */
    public string $clientSecret = 'SECRET_PLACEHOLDER';

    /** Callback URL ที่ URU Portal จะ redirect กลับมาพร้อม ?code=xxx */
    public string $callbackUrl = 'https://sci.uru.ac.th/index.php/oauth';

    /** URL สำหรับ redirect ผู้ใช้ไปล็อกอินที่ URU Portal */
    public string $loginUrl = 'https://uruportal.uru.ac.th/oauth_login';

    /** URL สำหรับแลก code เป็น access_token (POST) */
    public string $tokenUrl = 'https://uruportal.uru.ac.th/oauth/token';

    /** URL สำหรับดึงข้อมูลผู้ใช้ปัจจุบัน (GET พร้อม Bearer token) */
    public string $userInfoUrl = 'https://uruportal.uru.ac.th/me';

    /** เปิดใช้งาน OAuth หรือไม่ */
    public bool $enabled = true;

    /** Timeout (วินาที) สำหรับ HTTP request ไป URU Portal */
    public int $httpTimeout = 15;

    /** Domain email ของ URU (ใช้ตรวจสอบว่าเป็น user ของ URU) */
    public string $emailDomain = '@live.uru.ac.th';

    /** Prefix ของ student login_uid (นักศึกษาจะมี u นำหน้า เช่น u6512345) */
    public string $studentPrefix = 'u';

    public function __construct()
    {
        parent::__construct();

        $this->clientId       = env('uruoauth.clientId', $this->clientId) ?: $this->clientId;
        $this->clientSecret   = env('uruoauth.clientSecret', $this->clientSecret) ?: $this->clientSecret;
        $this->callbackUrl    = env('uruoauth.callbackUrl', $this->callbackUrl) ?: $this->callbackUrl;
        $this->loginUrl       = env('uruoauth.loginUrl', $this->loginUrl) ?: $this->loginUrl;
        $this->tokenUrl       = env('uruoauth.tokenUrl', $this->tokenUrl) ?: $this->tokenUrl;
        $this->userInfoUrl    = env('uruoauth.userInfoUrl', $this->userInfoUrl) ?: $this->userInfoUrl;

        $enabled = env('uruoauth.enabled', 'true');
        $this->enabled = ($enabled === 'true' || $enabled === '1' || $enabled === true);

        // ถ้าเป็น development ให้ใช้ callback URL ของ localhost แทน
        if (ENVIRONMENT === 'development') {
            $localCallback = env('uruoauth.callbackUrlLocal', '');
            if ($localCallback !== '') {
                $this->callbackUrl = $localCallback;
            }
        }
    }

    /**
     * สร้าง Authorization URL สำหรับ redirect ผู้ใช้ไปล็อกอินที่ URU Portal
     */
    public function buildAuthUrl(string $state = ''): string
    {
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'redirect_uri'  => $this->callbackUrl,
        ];
        if ($state !== '') {
            $params['state'] = $state;
        }
        return $this->loginUrl . '?' . http_build_query($params);
    }
}

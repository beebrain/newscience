<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * URU / ID Portal OAuth 2.0 Config — Authorization Code Flow
 *
 * clientId / clientSecret ใช้ชุดเดียว: uruoauth.clientId, uruoauth.clientSecret
 * เลือกชุด path/endpoint ด้วย uruoauth.provider = uruportal | idportal
 * uruoauth.httpVerifySsl = true|false — ตรวจ SSL กับ Portal (ค่าเริ่มต้น true)
 */
class UruPortalOAuth extends BaseConfig
{
    /** uruportal | idportal */
    public string $provider = 'uruportal';

    /** OAuth client_id */
    public string $clientId = 'SCI_PLACEHOLDER';

    /** OAuth client_secret */
    public string $clientSecret = 'SECRET_PLACEHOLDER';

    /** Callback URL (?code=...) — ต้องตรงกับที่ลงทะเบียน */
    public string $callbackUrl = 'https://sci.uru.ac.th/index.php/oauth';

    /** Authorization endpoint (GET) */
    public string $loginUrl = 'https://uruportal.uru.ac.th/oauth_login';

    /** Token endpoint (POST) */
    public string $tokenUrl = 'https://uruportal.uru.ac.th/oauth/token';

    /** ข้อมูลผู้ใช้ (GET + Bearer) — เช่น /me หรือ /info */
    public string $userInfoUrl = 'https://uruportal.uru.ac.th/me';

    /** ตรวจสอบสถานะล็อกอิน (GET + Bearer) — มีเฉพาะบาง provider เช่น idportal /check */
    public ?string $checkUrl = null;

    public bool $enabled = true;

    public int $httpTimeout = 15;

    public ?int $userInfoTimeout = 60;

    public string $emailDomain = '@live.uru.ac.th';

    public string $studentPrefix = 'u';

    /**
     * ตรวจสอบใบรับรอง SSL เมื่อเรียก HTTPS ไป Portal (ควร true บน production)
     * ถ้า dev บน Windows/XAMPP เจอปัญหา CA ให้ตั้ง uruoauth.httpVerifySsl = false ชั่วคราว
     */
    public bool $httpVerifySsl = true;

    public function __construct()
    {
        parent::__construct();

        $raw = strtolower((string) env('uruoauth.provider', 'uruportal'));
        $this->provider = in_array($raw, ['uruportal', 'idportal'], true) ? $raw : 'uruportal';

        $endpoints = self::defaultEndpoints($this->provider);

        $this->clientId     = $this->readGlobalCredential('clientId', $this->clientId);
        $this->clientSecret = $this->readGlobalCredential('clientSecret', $this->clientSecret);
        $this->callbackUrl  = $this->readString('callbackUrl', 'uruoauth.callbackUrl', $this->callbackUrl);
        $this->loginUrl     = $this->readString('loginUrl', 'uruoauth.loginUrl', $endpoints['loginUrl']);
        $this->tokenUrl     = $this->readString('tokenUrl', 'uruoauth.tokenUrl', $endpoints['tokenUrl']);
        $this->userInfoUrl  = $this->readString('userInfoUrl', 'uruoauth.userInfoUrl', $endpoints['userInfoUrl']);
        $this->checkUrl     = $this->readOptionalUrl('checkUrl', 'uruoauth.checkUrl', $endpoints['checkUrl']);

        $uiTimeout = env('uruoauth.userInfoTimeout', '');
        if ($uiTimeout !== '' && $uiTimeout !== null) {
            $this->userInfoTimeout = (int) $uiTimeout;
        }

        $enabled       = env('uruoauth.enabled', 'true');
        $this->enabled = ($enabled === 'true' || $enabled === '1' || $enabled === true);

        $verifySsl = env('uruoauth.httpVerifySsl', '');
        if ($verifySsl === '' || $verifySsl === null) {
            $this->httpVerifySsl = true;
        } else {
            $this->httpVerifySsl = ($verifySsl === 'true' || $verifySsl === '1' || $verifySsl === true);
        }

        if (ENVIRONMENT === 'development') {
            $local = $this->readString('callbackUrlLocal', 'uruoauth.callbackUrlLocal', '');
            if ($local !== '') {
                $this->callbackUrl = $local;
            }
        }
    }

    /**
     * @return array{loginUrl: string, tokenUrl: string, userInfoUrl: string, checkUrl: ?string}
     */
    private static function defaultEndpoints(string $provider): array
    {
        if ($provider === 'idportal') {
            return [
                'loginUrl'    => 'https://idportal.uru.ac.th/oauth2/authenticate',
                'tokenUrl'    => 'https://idportal.uru.ac.th/oauth2/access_token',
                'userInfoUrl' => 'https://idportal.uru.ac.th/info',
                'checkUrl'    => 'https://idportal.uru.ac.th/check',
            ];
        }

        return [
            'loginUrl'    => 'https://uruportal.uru.ac.th/oauth_login',
            'tokenUrl'    => 'https://uruportal.uru.ac.th/oauth/token',
            'userInfoUrl' => 'https://uruportal.uru.ac.th/me',
            'checkUrl'    => null,
        ];
    }

    private function readString(string $key, ?string $legacyKey, string $default): string
    {
        $specific = env('uruoauth.' . $this->provider . '.' . $key);
        if ($specific !== null && $specific !== false && (string) $specific !== '') {
            return (string) $specific;
        }
        if ($legacyKey !== null) {
            $leg = env($legacyKey);
            if ($leg !== null && $leg !== false && (string) $leg !== '') {
                return (string) $leg;
            }
        }

        return $default;
    }

    /** อ่านเฉพาะ uruoauth.{key} — ไม่แยกตาม provider */
    private function readGlobalCredential(string $key, string $default): string
    {
        $v = env('uruoauth.' . $key);
        if ($v !== null && $v !== false && (string) $v !== '') {
            return (string) $v;
        }

        return $default;
    }

    private function readOptionalUrl(string $key, ?string $legacyKey, ?string $default): ?string
    {
        $specific = env('uruoauth.' . $this->provider . '.' . $key);
        if ($specific !== null && $specific !== false && (string) $specific !== '') {
            return (string) $specific;
        }
        if ($legacyKey !== null) {
            $leg = env($legacyKey);
            if ($leg !== null && $leg !== false && (string) $leg !== '') {
                return (string) $leg;
            }
        }

        return $default;
    }

    /**
     * สร้าง Authorization URL สำหรับ redirect ผู้ใช้ไปล็อกอินที่ Portal
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

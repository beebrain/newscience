<?php

namespace App\Libraries;

use Config\UruPortalOAuth;

/**
 * URU Portal OAuth 2.0 Service
 * จัดการ Authorization Code Flow:
 *   1. redirect ผู้ใช้ไป URU Portal (buildAuthUrl)
 *   2. รับ code callback แล้วแลกเป็น access_token (exchangeCodeForToken)
 *   3. ดึงข้อมูลผู้ใช้ (fetchUserInfo)
 *   4. Refresh token เมื่อ token หมดอายุ (refreshAccessToken)
 */
class UruPortalOAuthService
{
    protected UruPortalOAuth $config;
    private const LOG_PREFIX = 'UruPortalOAuth: ';
    protected array $lastError = [];

    public function __construct(?UruPortalOAuth $config = null)
    {
        $this->config = $config ?? config(UruPortalOAuth::class);
    }

    public function getLastError(): array
    {
        return $this->lastError;
    }

    private function setLastError(string $stage, string $message, array $context = []): void
    {
        $this->lastError = array_merge([
            'stage' => $stage,
            'message' => $message,
        ], $context);
    }

    private function clearLastError(): void
    {
        $this->lastError = [];
    }

    // -------------------------------------------------------------------------
    // Step 2: Exchange authorization code → access_token + refresh_token
    // -------------------------------------------------------------------------

    /**
     * แลก authorization code เป็น token set
     * POST /oauth/token
     *
     * @return array|null ['access_token', 'refresh_token', 'expires_in', 'token_type'] หรือ null ถ้าล้มเหลว
     */
    public function exchangeCodeForToken(string $code): ?array
    {
        $this->clearLastError();

        $payload = [
            'grant_type'    => 'authorization_code',
            'client_id'     => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'redirect_uri'  => $this->config->callbackUrl,
            'code'          => $code,
        ];

        log_message('info', self::LOG_PREFIX . 'exchangeCodeForToken POST url=' . $this->config->tokenUrl . ' code_len=' . strlen($code));

        $response = $this->httpPost($this->config->tokenUrl, $payload, 'form');
        if ($response === null) {
            log_message('error', self::LOG_PREFIX . 'exchangeCodeForToken HTTP request failed');
            $this->setLastError('token_exchange', 'HTTP request failed', [
                'url' => $this->config->tokenUrl,
                'code_len' => strlen($code),
            ] + $this->lastError);
            return null;
        }

        [$status, $body] = $response;
        $safeBody = $this->redactSensitiveHttpBody($body);
        log_message('info', self::LOG_PREFIX . 'exchangeCodeForToken response status=' . $status . ' body=' . $this->truncateForLog($safeBody));

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'exchangeCodeForToken non-200 status=' . $status . ' body=' . $this->truncateForLog($safeBody));
            $this->setLastError('token_exchange', 'Portal token endpoint returned non-200 status', [
                'url' => $this->config->tokenUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($safeBody),
            ]);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['access_token'])) {
            log_message('error', self::LOG_PREFIX . 'exchangeCodeForToken missing access_token in response body=' . $this->truncateForLog($safeBody));
            $this->setLastError('token_exchange', 'Token response missing access_token or invalid JSON', [
                'url' => $this->config->tokenUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($safeBody),
            ]);
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'exchangeCodeForToken success token_type=' . ($data['token_type'] ?? '') . ' expires_in=' . ($data['expires_in'] ?? ''));
        return $data;
    }

    // -------------------------------------------------------------------------
    // Step 3: Get current user info
    // -------------------------------------------------------------------------

    /**
     * ดึงข้อมูลผู้ใช้ปัจจุบันจาก /me ด้วย Bearer token
     * GET /me
     *
     * @return array|null user info หรือ null ถ้าล้มเหลว
     */
    public function fetchUserInfo(string $accessToken): ?array
    {
        $this->clearLastError();
        log_message('info', self::LOG_PREFIX . 'fetchUserInfo GET url=' . $this->config->userInfoUrl);

        $response = $this->httpGet($this->config->userInfoUrl, $accessToken);
        if ($response === null) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo HTTP request failed');
            $this->setLastError('userinfo', 'HTTP request failed', [
                'url' => $this->config->userInfoUrl,
                'timeout' => (int) ($this->config->userInfoTimeout ?? $this->config->httpTimeout),
            ] + $this->lastError);
            return null;
        }

        [$status, $body] = $response;
        $topKeys = $this->jsonTopLevelKeys($body);
        log_message('info', self::LOG_PREFIX . 'fetchUserInfo response status=' . $status . ' body_bytes=' . strlen($body) . ' keys=' . $topKeys);

        if ($status !== 200) {
            $safe = $this->redactUserInfoLikeBody($body);
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo non-200 status=' . $status . ' body=' . $this->truncateForLog($safe));
            $this->setLastError('userinfo', 'Portal user info endpoint returned non-200 status', [
                'url' => $this->config->userInfoUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($safe),
            ]);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo response not JSON body=' . $this->truncateForLog($this->redactUserInfoLikeBody($body)));
            $this->setLastError('userinfo', 'User info response is not valid JSON', [
                'url' => $this->config->userInfoUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($this->redactUserInfoLikeBody($body)),
            ]);
            return null;
        }

        $data = $this->normalizePortalUserInfo($data);

        if (empty($data['email'])) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo missing email in response keys=' . implode(',', array_keys($data)));
            $this->setLastError('userinfo', 'User info response missing email', [
                'url' => $this->config->userInfoUrl,
                'status' => $status,
                'response_keys' => array_keys($data),
            ]);
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'fetchUserInfo success email=' . ($data['email'] ?? '') . ' login_uid=' . ($data['login_uid'] ?? $data['username'] ?? ''));
        return $data;
    }

    /**
     * แปลงรูปแบบ idportal (/info: adInfo, accountInfo, personInfo) ให้เข้ากับฟิลด์แบบ uruportal (/me)
     * — email, login_uid/username ที่ OAuthController และ Models ใช้
     */
    private function normalizePortalUserInfo(array $data): array
    {
        $email = trim((string) ($data['email'] ?? ''));
        if ($email !== '') {
            $data['email'] = strtolower($email);

            return $data;
        }

        $ad = $data['adInfo'] ?? null;
        if (is_array($ad)) {
            $fromMail = trim((string) ($ad['mail'] ?? ''));
            if ($fromMail !== '') {
                $data['email'] = strtolower($fromMail);
            } else {
                $upn = trim((string) ($ad['userPrincipalName'] ?? ''));
                if ($upn !== '' && str_contains($upn, '@')) {
                    $data['email'] = strtolower($upn);
                }
            }

            $sam = trim((string) ($ad['sAMAccountName'] ?? ''));
            if ($sam !== '') {
                if (empty(trim((string) ($data['login_uid'] ?? '')))) {
                    $data['login_uid'] = $sam;
                }
                if (empty(trim((string) ($data['username'] ?? '')))) {
                    $data['username'] = $sam;
                }
            }

            if (empty(trim((string) ($data['gf_name'] ?? ''))) && isset($ad['givenName'])) {
                $data['gf_name'] = trim((string) $ad['givenName']);
            }
            if (empty(trim((string) ($data['gl_name'] ?? ''))) && isset($ad['sn'])) {
                $data['gl_name'] = trim((string) $ad['sn']);
            }
        }

        $acc = $data['accountInfo'] ?? null;
        if (is_array($acc) && empty(trim((string) ($data['email'] ?? '')))) {
            $m = trim((string) ($acc['mail'] ?? $acc['email'] ?? $acc['userPrincipalName'] ?? ''));
            if ($m !== '') {
                $data['email'] = strtolower($m);
            }
        }

        $person = $data['personInfo'] ?? null;
        if (is_array($person)) {
            if (empty(trim((string) ($data['gf_name'] ?? '')))) {
                $data['gf_name'] = trim((string) ($person['engFirstName'] ?? $person['firstname_en'] ?? $person['givenName_en'] ?? ''));
            }
            if (empty(trim((string) ($data['gl_name'] ?? '')))) {
                $data['gl_name'] = trim((string) ($person['engLastName'] ?? $person['lastname_en'] ?? $person['surname_en'] ?? ''));
            }
            if (empty(trim((string) ($data['tf_name'] ?? '')))) {
                $data['tf_name'] = trim((string) ($person['thaiFirstName'] ?? $person['firstname_th'] ?? $person['givenName_th'] ?? ''));
            }
            if (empty(trim((string) ($data['tl_name'] ?? '')))) {
                $data['tl_name'] = trim((string) ($person['thaiLastName'] ?? $person['lastname_th'] ?? $person['surname_th'] ?? ''));
            }
        }

        return $data;
    }

    /**
     * ตรวจสอบสถานะล็อกอินกับ Portal (เมื่อมี checkUrl เช่น idportal /check)
     *
     * @return array|null JSON decode สำเร็จและ status 200 หรือ null
     */
    public function fetchCheckStatus(string $accessToken): ?array
    {
        $url = $this->config->checkUrl ?? '';
        if ($url === '') {
            return null;
        }

        $this->clearLastError();
        log_message('info', self::LOG_PREFIX . 'fetchCheckStatus GET url=' . $url);

        $response = $this->httpGet($url, $accessToken);
        if ($response === null) {
            $this->setLastError('check', 'HTTP request failed', ['url' => $url] + $this->lastError);
            return null;
        }

        [$status, $body] = $response;
        if ($status !== 200) {
            $this->setLastError('check', 'Check endpoint returned non-200', [
                'url' => $url,
                'status' => $status,
                'body_preview' => $this->truncateForLog($this->redactSensitiveHttpBody($body)),
            ]);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            $this->setLastError('check', 'Check response is not valid JSON', ['url' => $url]);
            return null;
        }

        return $data;
    }

    // -------------------------------------------------------------------------
    // Step 4: Refresh access token
    // -------------------------------------------------------------------------

    /**
     * ต่ออายุ access_token ด้วย refresh_token
     * POST /oauth/token
     *
     * @return array|null token set ใหม่ หรือ null ถ้าล้มเหลว
     */
    public function refreshAccessToken(string $refreshToken): ?array
    {
        $this->clearLastError();

        $payload = [
            'grant_type'    => 'refresh_token',
            'client_id'     => $this->config->clientId,
            'client_secret' => $this->config->clientSecret,
            'refresh_token' => $refreshToken,
        ];

        log_message('info', self::LOG_PREFIX . 'refreshAccessToken POST url=' . $this->config->tokenUrl);

        $response = $this->httpPost($this->config->tokenUrl, $payload, 'form');
        if ($response === null) {
            log_message('error', self::LOG_PREFIX . 'refreshAccessToken HTTP request failed');
            $this->setLastError('refresh_token', 'HTTP request failed', [
                'url' => $this->config->tokenUrl,
            ] + $this->lastError);
            return null;
        }

        [$status, $body] = $response;
        $safeBody = $this->redactSensitiveHttpBody($body);
        log_message('info', self::LOG_PREFIX . 'refreshAccessToken response status=' . $status . ' body=' . $this->truncateForLog($safeBody));

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'refreshAccessToken non-200 status=' . $status . ' body=' . $this->truncateForLog($safeBody));
            $this->setLastError('refresh_token', 'Refresh token endpoint returned non-200 status', [
                'url' => $this->config->tokenUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($safeBody),
            ]);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['access_token'])) {
            log_message('error', self::LOG_PREFIX . 'refreshAccessToken missing access_token body=' . $this->truncateForLog($safeBody));
            $this->setLastError('refresh_token', 'Refresh token response missing access_token or invalid JSON', [
                'url' => $this->config->tokenUrl,
                'status' => $status,
                'body_preview' => $this->truncateForLog($safeBody),
            ]);
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'refreshAccessToken success expires_in=' . ($data['expires_in'] ?? ''));
        return $data;
    }

    // -------------------------------------------------------------------------
    // Helpers: ตรวจสอบประเภทผู้ใช้
    // -------------------------------------------------------------------------

    /**
     * ตรวจสอบว่า login_uid เป็นนักศึกษาหรือไม่ (ขึ้นต้นด้วย u ตามด้วยตัวเลข)
     */
    public function isStudentUid(string $loginUid): bool
    {
        return (bool) preg_match('/^u\d+$/i', $loginUid);
    }

    /**
     * ตรวจสอบว่า email เป็นของ URU (@live.uru.ac.th)
     */
    public function isUruEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), strtolower($this->config->emailDomain));
    }

    /**
     * ตรวจสอบว่า user เป็นนักศึกษาจาก userInfo
     * — นักศึกษา: login_uid ขึ้นต้นด้วย u + ตัวเลข
     * — บุคลากร: ใช้ชื่อแทน (ไม่มีรูปแบบ u+ตัวเลข)
     */
    public function isStudent(array $userInfo): bool
    {
        $uid = trim($userInfo['login_uid'] ?? $userInfo['username'] ?? $userInfo['code'] ?? '');
        return $this->isStudentUid($uid);
    }

    // -------------------------------------------------------------------------
    // HTTP helpers (ใช้ file_get_contents + stream_context เพื่อไม่ต้องพึ่ง cURL)
    // -------------------------------------------------------------------------

    /**
     * HTTP POST — รองรับ form-encoded และ JSON
     * @return array|null [status_code, body] หรือ null ถ้า request ล้มเหลว
     */
    private function httpPost(string $url, array $data, string $contentType = 'form'): ?array
    {
        if ($contentType === 'json') {
            $body    = json_encode($data);
            $ctype   = 'application/json';
        } else {
            $body    = http_build_query($data);
            $ctype   = 'application/x-www-form-urlencoded';
        }

        $opts = [
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-Type: {$ctype}\r\nContent-Length: " . strlen($body) . "\r\nAccept: application/json\r\nUser-Agent: SciOAuth/1.0 (PHP)\r\n",
                'content'       => $body,
                'timeout'       => $this->config->httpTimeout,
                'ignore_errors' => true,
            ],
            'ssl' => $this->sslStreamOptions(),
        ];

        return $this->doRequest($url, $opts);
    }

    /**
     * HTTP GET พร้อม Bearer token
     * @return array|null [status_code, body] หรือ null ถ้า request ล้มเหลว
     */
    private function httpGet(string $url, string $bearerToken): ?array
    {
        $timeout = $this->config->httpTimeout;
        $userInfoTimeout = (int) ($this->config->userInfoTimeout ?? $timeout);
        if ($userInfoTimeout < 5) {
            $userInfoTimeout = 60;
        }
        $opts = [
            'http' => [
                'method'        => 'GET',
                'header'        => "Authorization: Bearer {$bearerToken}\r\nAccept: application/json\r\nUser-Agent: SciOAuth/1.0 (PHP)\r\n",
                'timeout'       => $userInfoTimeout,
                'ignore_errors' => true,
            ],
            'ssl' => $this->sslStreamOptions(),
        ];

        return $this->doRequest($url, $opts);
    }

    /**
     * @return array{verify_peer: bool, verify_peer_name: bool}
     */
    private function sslStreamOptions(): array
    {
        $v = $this->config->httpVerifySsl;

        return [
            'verify_peer'      => $v,
            'verify_peer_name' => $v,
        ];
    }

    /** ปิดบัง token ใน JSON ของ OAuth / API */
    private function redactSensitiveHttpBody(string $body): string
    {
        $trim = trim($body);
        if ($trim === '') {
            return '(empty)';
        }
        $decoded = json_decode($trim, true);
        if (!is_array($decoded)) {
            return '(non-json, len=' . strlen($body) . ')';
        }
        foreach (['access_token', 'refresh_token', 'id_token', 'token'] as $k) {
            if (!empty($decoded[$k]) && is_string($decoded[$k])) {
                $decoded[$k] = '[REDACTED]';
            }
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE) ?: '(encode-failed)';
    }

    /**
     * ตัดข้อมูลระบุตัวตนใน /info แบบหยาบๆ สำหรับ log error
     */
    private function redactUserInfoLikeBody(string $body): string
    {
        $trim = trim($body);
        if ($trim === '') {
            return '(empty)';
        }
        $decoded = json_decode($trim, true);
        if (!is_array($decoded)) {
            return '(non-json, len=' . strlen($body) . ')';
        }
        if (isset($decoded['adInfo']) && is_array($decoded['adInfo'])) {
            $decoded['adInfo'] = ['[REDACTED]' => 'adInfo fields omitted'];
        }
        if (isset($decoded['accountInfo']) && is_array($decoded['accountInfo'])) {
            $decoded['accountInfo'] = ['[REDACTED]' => 'accountInfo omitted'];
        }
        if (isset($decoded['personInfo']) && is_array($decoded['personInfo'])) {
            $decoded['personInfo'] = ['[REDACTED]' => 'personInfo omitted'];
        }
        foreach (['access_token', 'refresh_token', 'id_token', 'token', 'password'] as $k) {
            if (!empty($decoded[$k])) {
                $decoded[$k] = '[REDACTED]';
            }
        }

        return json_encode($decoded, JSON_UNESCAPED_UNICODE) ?: '(encode-failed)';
    }

    private function jsonTopLevelKeys(string $body): string
    {
        $decoded = json_decode(trim($body), true);
        if (!is_array($decoded)) {
            return '(n/a)';
        }

        return implode(',', array_keys($decoded));
    }

    private function truncateForLog(string $text, int $maxLen = 400): string
    {
        if (strlen($text) <= $maxLen) {
            return $text;
        }

        return substr($text, 0, $maxLen) . '…(truncated)';
    }

    /**
     * ส่ง HTTP request แล้วคืน [status_code, body]
     */
    private function doRequest(string $url, array $opts): ?array
    {
        $context = stream_context_create($opts);
        $timeout = (int) ($opts['http']['timeout'] ?? 0);
        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                $err = error_get_last();
                $msg = $err['message'] ?? 'unknown';
                $lowerMsg = strtolower($msg);
                if (strpos($lowerMsg, 'timeout') !== false || strpos($lowerMsg, 'timed out') !== false) {
                    $this->setLastError('http_request', 'Request timed out', [
                        'url' => $url,
                        'timeout' => $timeout,
                        'error' => $msg,
                    ]);
                    log_message('error', self::LOG_PREFIX . 'doRequest timeout url=' . $url . ' timeout=' . $timeout . ' err=' . $msg . ' (increase httpTimeout or userInfoTimeout in config)');
                } else {
                    $this->setLastError('http_request', 'Request failed', [
                        'url' => $url,
                        'timeout' => $timeout,
                        'error' => $msg,
                    ]);
                    log_message('error', self::LOG_PREFIX . 'doRequest failed url=' . $url . ' err=' . $msg);
                }
                return null;
            }

            $status = 200;
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $m)) {
                        $status = (int) $m[1];
                        break;
                    }
                }
            }

            return [$status, $response];
        } catch (\Throwable $e) {
            $this->setLastError('http_request', 'Request exception', [
                'url' => $url,
                'timeout' => $timeout,
                'error' => $e->getMessage(),
            ]);
            log_message('error', self::LOG_PREFIX . 'doRequest exception url=' . $url . ' msg=' . $e->getMessage());
            return null;
        }
    }
}

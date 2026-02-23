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

    public function __construct(?UruPortalOAuth $config = null)
    {
        $this->config = $config ?? config(UruPortalOAuth::class);
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
            return null;
        }

        [$status, $body] = $response;
        $preview = strlen($body) > 300 ? substr($body, 0, 300) . '...' : $body;
        log_message('info', self::LOG_PREFIX . 'exchangeCodeForToken response status=' . $status . ' body=' . $preview);

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'exchangeCodeForToken non-200 status=' . $status . ' body=' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['access_token'])) {
            log_message('error', self::LOG_PREFIX . 'exchangeCodeForToken missing access_token in response body=' . $body);
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
        log_message('info', self::LOG_PREFIX . 'fetchUserInfo GET url=' . $this->config->userInfoUrl);

        $response = $this->httpGet($this->config->userInfoUrl, $accessToken);
        if ($response === null) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo HTTP request failed');
            return null;
        }

        [$status, $body] = $response;
        $preview = strlen($body) > 300 ? substr($body, 0, 300) . '...' : $body;
        log_message('info', self::LOG_PREFIX . 'fetchUserInfo response status=' . $status . ' body=' . $preview);

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo non-200 status=' . $status . ' body=' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo response not JSON body=' . $body);
            return null;
        }

        if (empty($data['email'])) {
            log_message('error', self::LOG_PREFIX . 'fetchUserInfo missing email in response keys=' . implode(',', array_keys($data)));
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'fetchUserInfo success email=' . ($data['email'] ?? '') . ' login_uid=' . ($data['login_uid'] ?? $data['username'] ?? ''));
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
            return null;
        }

        [$status, $body] = $response;
        log_message('info', self::LOG_PREFIX . 'refreshAccessToken response status=' . $status);

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'refreshAccessToken non-200 status=' . $status . ' body=' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data) || empty($data['access_token'])) {
            log_message('error', self::LOG_PREFIX . 'refreshAccessToken missing access_token body=' . $body);
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
                'header'        => "Content-Type: {$ctype}\r\nContent-Length: " . strlen($body) . "\r\nAccept: application/json\r\n",
                'content'       => $body,
                'timeout'       => $this->config->httpTimeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];

        return $this->doRequest($url, $opts);
    }

    /**
     * HTTP GET พร้อม Bearer token
     * @return array|null [status_code, body] หรือ null ถ้า request ล้มเหลว
     */
    private function httpGet(string $url, string $bearerToken): ?array
    {
        $opts = [
            'http' => [
                'method'        => 'GET',
                'header'        => "Authorization: Bearer {$bearerToken}\r\nAccept: application/json\r\n",
                'timeout'       => $this->config->httpTimeout,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];

        return $this->doRequest($url, $opts);
    }

    /**
     * ส่ง HTTP request แล้วคืน [status_code, body]
     */
    private function doRequest(string $url, array $opts): ?array
    {
        $context = stream_context_create($opts);
        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                $err = error_get_last();
                log_message('error', self::LOG_PREFIX . 'doRequest failed url=' . $url . ' err=' . ($err['message'] ?? 'unknown'));
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
            log_message('error', self::LOG_PREFIX . 'doRequest exception url=' . $url . ' msg=' . $e->getMessage());
            return null;
        }
    }
}

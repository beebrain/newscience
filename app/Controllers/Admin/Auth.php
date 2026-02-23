<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use Config\EdocSso;
use Config\ResearchRecordSso;

class Auth extends BaseController
{
    protected $userModel;

    /** Log prefix สำหรับ SSO/Portal */
    private const LOG_PREFIX = 'Admin Auth SSO: ';

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Display login form
     */
    public function login()
    {
        // If already logged in, redirect to admin dashboard
        if (session()->get('admin_logged_in')) {
            log_message('debug', 'Admin Auth: already logged in, redirect to admin/news. admin_id=' . (session()->get('admin_id') ?? ''));
            return redirect()->to(base_url('admin/news'));
        }

        $data = [
            'page_title' => 'Admin Login'
        ];

        return view('admin/auth/login', $data);
    }

    /**
     * Process login attempt
     */
    public function attemptLogin()
    {
        $login = trim($this->request->getPost('login') ?? '');
        log_message('debug', 'Admin Auth: login attempt, identifier=' . $login);

        // Block student emails from normal user login - redirect to student login
        if ($this->isStudentEmail($login)) {
            log_message('debug', 'Admin Auth: student email detected, redirect to student login=' . $login);
            return redirect()->to(base_url('student/login'))
                ->with('error', 'กรุณาเข้าสู่ระบบที่หน้า Student Portal');
        }

        // รับได้ทั้งอีเมลและ login_uid (เช่น admin)
        $rules = [
            'login'    => 'required|string',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            log_message('debug', 'Admin Auth: validation failed. errors=' . json_encode($this->validator->getErrors()));
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $password = $this->request->getPost('password');

        // Find user by email or username (login_uid)
        $user = $this->userModel->findByIdentifier($login);

        if (!$user || !is_array($user)) {
            log_message('debug', 'Admin Auth: user not found, identifier=' . $login);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        $uid = $user['uid'] ?? '';
        log_message('debug', 'Admin Auth: user found uid=' . $uid . ' role=' . ($user['role'] ?? '') . ' active=' . ($user['active'] ?? ''));

        // Check if user is active (ตาราง user ใช้คอลัมน์ active 1=ใช้งาน)
        $active = (int) ($user['active'] ?? 0);
        if ($active !== 1) {
            log_message('debug', 'Admin Auth: user inactive, uid=' . $uid);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Your account is inactive.');
        }

        // อนุญาตทั้ง admin และ user — user ไปหน้า Dashboard, admin ไป Admin ได้
        $role = $user['role'] ?? '';
        $adminRole = (!empty($user['admin'])) ? 'admin' : $role;

        // Verify password (hash ต้องไม่ว่าง)
        $storedHash = $user['password'] ?? '';
        if (!$this->userModel->verifyPassword($password, $storedHash)) {
            log_message('debug', 'Admin Auth: password verify failed, uid=' . $uid);
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid email or password.');
        }

        // Set session data (admin_role: ถ้า admin=1 ใช้ 'admin' ไม่ก็ใช้ role — ตาราง user มี role enum user,faculty_admin,super_admin และคอลัมน์ admin)
        $sessionData = [
            'admin_logged_in' => true,
            'admin_id' => $user['uid'],
            'admin_email' => $user['email'],
            'admin_name' => $this->userModel->getFullName($user),
            'admin_role' => $adminRole,
        ];
        session()->set($sessionData);

        // Redirect to intended URL or หน้า การจัดการ (Dashboard)
        $redirectUrl = session()->get('redirect_url') ?? base_url('dashboard');
        session()->remove('redirect_url');

        log_message('debug', 'Admin Auth: login success uid=' . $uid . ' role=' . $role . ' redirect=' . $redirectUrl);

        // Prepare SSO Auto-Login URLs
        $ssoUrls = [];
        if ($ssoUrl = $this->getEdocSsoUrl($user)) $ssoUrls[] = $ssoUrl;
        if ($ssoUrl = $this->getResearchSsoUrl($user)) $ssoUrls[] = $ssoUrl;

        return redirect()->to($redirectUrl)
            ->with('success', 'Welcome back, ' . $sessionData['admin_name'] . '!')
            ->with('sso_autologin_urls', $ssoUrls);
    }

    /**
     * Logout — ล้าง session ของ newScience แล้ว redirect กลับมาหน้า login ของ newScience เท่านั้น
     * ไม่ส่งไป Edoc/Research Record เพื่อให้ผู้ใช้เด้งกลับมาที่ newScience เสมอ
     */
    public function logout()
    {
        $session = session();
        $adminId = $session->get('admin_id');
        $loginVia = $session->get('admin_login_via');
        log_message('info', self::LOG_PREFIX . 'logout admin_id=' . ($adminId ?? '') . ' login_via=' . ($loginVia ?? 'form'));

        $session->remove([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_role',
            'redirect_url',
            'admin_access_token',
            'admin_token_expires',
            'admin_login_via',
        ]);
        $session->destroy();

        // เด้งกลับมาหน้า login ของ newScience เสมอ (ไม่ redirect ไป logout ที่ Edoc)
        $loginUrl = base_url('admin/login?logout=1');
        return redirect()->to($loginUrl)
            ->with('success', 'ออกจากระบบแล้ว');
    }

    /**
     * รับ redirect หลังผู้ใช้กดออกจากระบบบน Edoc — ให้ Edoc ตั้งค่า redirect หลัง logout มาที่ URL นี้
     * GET /admin/edoc-logout-return
     * เมื่อ login ผ่าน Edoc แล้วผู้ใช้ไปกด logout ที่ Edoc จะได้เด้งกลับมาหน้า login ของ newScience
     */
    public function edocLogoutReturn()
    {
        log_message('info', self::LOG_PREFIX . 'edocLogoutReturn redirect to newScience login');
        return redirect()->to(base_url('admin/login?logout=1'))
            ->with('success', 'ออกจากระบบแล้ว');
    }

    /**
     * สร้าง URL redirect ลูกโซ่: ไป logout แอปแรก → แอปสอง → ... → กลับหน้า login
     * แต่ละแอปต้องรองรับ query return_url (หรือชื่อที่กำหนด) เพื่อ redirect ต่อไป
     *
     * @return string|null URL แรกที่ต้อง redirect ไป หรือ null ถ้าไม่มี logout URL ใดๆ
     */
    private function buildLogoutRedirectChain(string $finalReturnUrl): ?string
    {
        $edocConfig = config(EdocSso::class);
        $researchConfig = config(ResearchRecordSso::class);

        // ลำดับ: แอปแรกที่ผู้ใช้จะถูกส่งไป logout คือตัวแรกใน array (จากนั้นค่อย redirect ต่อ)
        $steps = [];
        if ($edocConfig->logoutUrl !== '') {
            $steps[] = [
                'url'   => rtrim($edocConfig->logoutUrl, '?'),
                'param' => $edocConfig->logoutReturnParam,
            ];
        }
        if ($researchConfig->logoutUrl !== '') {
            $steps[] = [
                'url'   => rtrim($researchConfig->logoutUrl, '?'),
                'param' => $researchConfig->logoutReturnParam,
            ];
        }
        if (empty($steps)) {
            return null;
        }

        $next = $finalReturnUrl;
        foreach (array_reverse($steps) as $step) {
            $sep = str_contains($step['url'], '?') ? '&' : '?';
            $next = $step['url'] . $sep . $step['param'] . '=' . rawurlencode($next);
        }
        // Log Edoc signout redirect (URL แรกใน chain มักเป็น Edoc) เพื่อตรวจสอบว่า return กลับ newScience
        if ($edocConfig->logoutUrl !== '' && strpos($next, rtrim($edocConfig->logoutUrl, '?')) === 0) {
            log_message('info', self::LOG_PREFIX . 'logout redirect to Edoc signout, return_to=' . $finalReturnUrl);
        }
        return $next;
    }

    /**
     * ล้าง session ทั้งหมด (ใช้เมื่อ session ค้างหรือเด้งไปหน้าแรกโดยไม่คาดคิด)
     * GET /admin/clear-session
     */
    public function clearSession()
    {
        $session = session();
        $adminId = $session->get('admin_id');
        log_message('info', self::LOG_PREFIX . 'clearSession admin_id=' . ($adminId ?? ''));
        $session->remove([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_role',
            'redirect_url',
            'admin_access_token',
            'admin_token_expires',
            'admin_login_via',
        ]);
        $session->destroy();

        return redirect()->to(base_url('admin/login'))
            ->with('success', 'ล้าง session แล้ว กรุณาเข้าสู่ระบบใหม่');
    }

    // -------------------------------------------------------------------------
    // SSO ผ่าน Edoc (URU Portal)
    // -------------------------------------------------------------------------

    /**
     * Redirect ไปหน้า Login ของ Edoc พร้อม from=newscience และ return_url
     * GET /admin/portal-login
     */
    public function portalLogin()
    {
        if (session()->get('admin_logged_in')) {
            log_message('debug', self::LOG_PREFIX . 'portalLogin already logged in, redirect to admin/news');
            return redirect()->to(base_url('admin/news'));
        }

        $config = config(EdocSso::class);
        if (!$config->enabled || $config->baseUrl === '') {
            log_message('warning', self::LOG_PREFIX . 'portalLogin SSO disabled or baseUrl empty');
            return redirect()->to(base_url('admin/login'))->with('error', 'การเข้าสู่ระบบผ่าน Portal ยังไม่เปิดใช้');
        }

        $returnUrl = $config->returnUrl ?: base_url('admin/oauth-callback?provider=edoc');
        if (strpos($returnUrl, 'provider=') === false) {
            $returnUrl = rtrim($returnUrl, '&') . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'provider=edoc';
        }
        // Edoc เรียกผ่าน index.php จึงใช้ /index.php/auth/login (ถ้าไม่มีจะได้ 404)
        $edocLoginUrl = rtrim($config->baseUrl, '/') . '/index.php/auth/login?' . http_build_query([
            'from'       => 'newscience',
            'return_url' => $returnUrl,
        ]);

        if (session()->get('redirect_url')) {
            log_message('debug', self::LOG_PREFIX . 'portalLogin redirect_url already in session');
        } else {
            $intended = $this->request->getGet('redirect_url');
            if ($intended && is_string($intended)) {
                $base = base_url();
                if (strpos($intended, $base) === 0) {
                    session()->set('redirect_url', $intended);
                    log_message('debug', self::LOG_PREFIX . 'portalLogin stored redirect_url from query');
                }
            }
        }

        log_message('info', self::LOG_PREFIX . 'portalLogin redirect to Edoc edocLoginUrl=' . $edocLoginUrl . ' return_url=' . $returnUrl);
        return redirect()->to($edocLoginUrl);
    }

    /**
     * Redirect ไปหน้า Login ของ Research Record พร้อม from=newscience และ return_url (ใช้ secret เดียวกับ Edoc ได้)
     * GET /admin/portal-login-research
     * 
     * DEPRECATED: ใช้ Sci OAuth แทน
     */
    public function portalLoginResearch()
    {
        // Redirect ไป Sci OAuth แทนการไป Research Record
        return redirect()->to(base_url('oauth/login'));
    }

    /**
     * Callback หลังล็อกอินที่ Edoc หรือ Research Record — รับ code และ provider แล้วแลก API เป็น user info
     * GET /admin/oauth-callback?code=xxx&provider=edoc|researchrecord
     */
    public function oauthCallback()
    {
        $code = $this->request->getGet('code');
        $error = $this->request->getGet('error');
        $errorDesc = $this->request->getGet('error_description');
        $provider = $this->request->getGet('provider') ?? 'edoc';

        log_message('info', self::LOG_PREFIX . 'oauthCallback START provider=' . $provider . ' code_len=' . (is_string($code) ? strlen($code) : 0) . ' error=' . ($error ?? '') . ' error_description=' . ($errorDesc ?? ''));

        if ($error !== null && $error !== '') {
            log_message('error', self::LOG_PREFIX . 'oauthCallback OAuth error from ' . $provider . ': ' . $error . ' ' . ($errorDesc ?? ''));
            return redirect()->to(base_url('admin/login'))->with('error', 'เข้าสู่ระบบผ่าน Portal ไม่สำเร็จ: ' . ($errorDesc ?: $error));
        }

        if (!$code || !is_string($code)) {
            log_message('error', self::LOG_PREFIX . 'oauthCallback missing or invalid code');
            return redirect()->to(base_url('admin/login'))->with('error', 'ไม่ได้รับรหัสจาก Portal กรุณาลองใหม่อีกครั้ง');
        }

        if ($provider === 'researchrecord' || $provider === 'edoc') {
            log_message('warning', self::LOG_PREFIX . 'oauthCallback deprecated provider=' . $provider . ' - use Sci OAuth instead');
            return redirect()->to(base_url('admin/login'))->with('error', 'การเข้าสู่ระบบผ่าน ' . $provider . ' ไม่รองรับแล้ว กรุณาใช้ Sci OAuth');
        }

        if ($provider === 'researchrecord') {
            $config = config(ResearchRecordSso::class);
            $loginVia = 'researchrecord_sso';
        } else {
            $config = config(EdocSso::class);
            $loginVia = 'edoc_sso';
        }

        if (!$config->enabled || $config->exchangeCodeUrl === '') {
            log_message('error', self::LOG_PREFIX . 'oauthCallback SSO disabled or exchangeCodeUrl empty provider=' . $provider);
            return redirect()->to(base_url('admin/login'))->with('error', 'การแลกรหัสกับ Portal ยังไม่เปิดใช้');
        }

        log_message('info', self::LOG_PREFIX . 'oauthCallback exchangeCodeUrl=' . $config->exchangeCodeUrl);
        $userInfo = $this->exchangeCodeForUserInfo($code, $config);
        if ($userInfo === null) {
            log_message('error', self::LOG_PREFIX . 'oauthCallback exchange code failed provider=' . $provider . ' (check exchangeCode response in log above)');
            return redirect()->to(base_url('admin/login'))->with('error', 'ไม่สามารถตรวจสอบข้อมูลจาก Portal ได้ กรุณาลองใหม่อีกครั้ง');
        }

        log_message('info', self::LOG_PREFIX . 'oauthCallback user info received provider=' . $provider . ' email=' . ($userInfo['email'] ?? ''));

        $user = $this->processPortalUser($userInfo);
        if ($user === null) {
            log_message('warning', self::LOG_PREFIX . 'oauthCallback processPortalUser denied (no user or no admin role)');
            return redirect()->to(base_url('admin/login'))->with('error', 'คุณไม่มีสิทธิ์เข้าสู่ระบบ Admin กรุณาติดต่อผู้ดูแลระบบ');
        }

        $redirectUrl = session()->get('redirect_url') ?? base_url('dashboard');
        session()->remove('redirect_url');

        $adminRole = (!empty($user['admin'])) ? 'admin' : ($user['role'] ?? 'user');
        $sessionData = [
            'admin_logged_in'   => true,
            'admin_id'          => $user['uid'],
            'admin_email'       => $user['email'],
            'admin_name'        => $this->userModel->getFullName($user),
            'admin_role'        => $adminRole,
            'admin_login_via'   => $loginVia,
        ];
        if (!empty($userInfo['access_token'])) {
            $sessionData['admin_access_token'] = $userInfo['access_token'];
            $sessionData['admin_token_expires'] = $userInfo['token_expires'] ?? (time() + 3600);
        }
        session()->set($sessionData);

        log_message('info', self::LOG_PREFIX . 'oauthCallback login success provider=' . $provider . ' uid=' . $user['uid'] . ' role=' . $user['role'] . ' redirect=' . $redirectUrl);

        // SSO Auto-Login disabled - using Sci OAuth only
        return redirect()->to($redirectUrl)
            ->with('success', 'เข้าสู่ระบบสำเร็จ (URU Portal)');
    }

    /**
     * แลก one-time code กับ SSO API (Edoc หรือ Research Record) ได้ user info (และ optional access_token)
     * @param object $config ต้องมี exchangeCodeUrl, sharedSecret (EdocSso หรือ ResearchRecordSso)
     * @return array|null user info + optional access_token, token_expires; null ถ้าแลกไม่สำเร็จ
     */
    private function exchangeCodeForUserInfo(string $code, object $config): ?array
    {
        $url = $config->exchangeCodeUrl;
        $payload = [
            'code' => $code,
            'secret' => $config->sharedSecret,
        ];

        log_message('info', self::LOG_PREFIX . 'exchangeCode POST url=' . $url . ' code_len=' . strlen($code));

        $body = null;
        $status = 0;
        $jsonPayload = json_encode($payload);
        $contextOptions = [
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($jsonPayload) . "\r\n",
                'content' => $jsonPayload,
                'timeout' => 15,
                'ignore_errors' => true,
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ];
        $context = stream_context_create($contextOptions);
        try {
            $response = @file_get_contents($url, false, $context);
            if ($response === false) {
                $err = error_get_last();
                log_message('error', self::LOG_PREFIX . 'exchangeCode request failed: ' . ($err['message'] ?? 'unknown'));
                return null;
            }
            $body = $response;
            if (isset($http_response_header) && is_array($http_response_header)) {
                foreach ($http_response_header as $header) {
                    if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $m)) {
                        $status = (int) $m[1];
                        break;
                    }
                }
            }
            if ($status === 0) {
                $status = 200;
            }
        } catch (\Throwable $e) {
            log_message('error', self::LOG_PREFIX . 'exchangeCode request failed: ' . $e->getMessage());
            return null;
        }

        $bodyPreview = strlen($body) > 500 ? substr($body, 0, 500) . '...' : $body;
        log_message('info', self::LOG_PREFIX . 'exchangeCode response status=' . $status . ' body=' . $bodyPreview);

        if ($status !== 200) {
            log_message('error', self::LOG_PREFIX . 'exchangeCode non-200 status=' . $status . ' full_body=' . $body);
            return null;
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            log_message('error', self::LOG_PREFIX . 'exchangeCode response not JSON body=' . $body);
            return null;
        }
        if (empty($data['email'])) {
            log_message('error', self::LOG_PREFIX . 'exchangeCode missing email in response keys=' . implode(',', array_keys($data)));
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'exchangeCode success email=' . ($data['email'] ?? ''));
        return $data;
    }

    /**
     * หา/สร้าง user จาก API user info (JSON จาก Edoc) — แมปฟิลด์ตามแบบ Edoc แล้ว update ลง table user
     * คืน user array หรือ null
     */
    private function processPortalUser(array $userInfo): ?array
    {
        // แมป JSON จาก Edoc ไป apiUser (รองรับ key หลายแบบตามแบบอย่างใน Edoc)
        $apiUser = [
            'email'         => $userInfo['email'] ?? '',
            'login_uid'     => $userInfo['login_uid'] ?? $userInfo['code'] ?? $userInfo['username'] ?? '',
            'code'          => $userInfo['code'] ?? '',
            'title'         => $userInfo['title'] ?? '',
            'gf_name'       => $userInfo['gf_name'] ?? $userInfo['first_name_en'] ?? $userInfo['firstname_en'] ?? '',
            'gl_name'       => $userInfo['gl_name'] ?? $userInfo['last_name_en'] ?? $userInfo['lastname_en'] ?? '',
            'tf_name'       => $userInfo['tf_name'] ?? $userInfo['first_name_th'] ?? $userInfo['firstname_th'] ?? '',
            'tl_name'       => $userInfo['tl_name'] ?? $userInfo['last_name_th'] ?? $userInfo['lastname_th'] ?? '',
            'th_name'       => $userInfo['th_name'] ?? $userInfo['thai_name'] ?? $userInfo['first_name_th'] ?? '',
            'thai_name'     => $userInfo['thai_name'] ?? $userInfo['first_name_th'] ?? $userInfo['firstname_th'] ?? '',
            'thai_lastname' => $userInfo['thai_lastname'] ?? $userInfo['last_name_th'] ?? $userInfo['lastname_th'] ?? '',
            'first_name_th' => $userInfo['first_name_th'] ?? $userInfo['firstname_th'] ?? '',
            'last_name_th'  => $userInfo['last_name_th'] ?? $userInfo['lastname_th'] ?? '',
            'first_name_en' => $userInfo['first_name_en'] ?? $userInfo['firstname_en'] ?? '',
            'last_name_en'  => $userInfo['last_name_en'] ?? $userInfo['lastname_en'] ?? '',
            // 'profile_picture'  => $userInfo['profile_picture'] ?? $userInfo['profile_image'] ?? $userInfo['avatar'] ?? $userInfo['picture'] ?? '',
        ];

        $user = $this->userModel->findOrCreateFromApiUser($apiUser);
        if (!$user) {
            log_message('warning', self::LOG_PREFIX . 'processPortalUser findOrCreate failed email=' . ($apiUser['email'] ?? ''));
            return null;
        }

        log_message('info', self::LOG_PREFIX . 'processPortalUser user uid=' . ($user['uid'] ?? '') . ' role=' . ($user['role'] ?? '') . ' email=' . ($user['email'] ?? ''));

        // อนุญาตทุก role — user ไป Dashboard, admin ไป Admin ได้ (ตาราง user ใช้ active 1=ใช้งาน)
        $active = (int) ($user['active'] ?? 0);
        if ($active !== 1) {
            log_message('info', self::LOG_PREFIX . 'processPortalUser user inactive uid=' . ($user['uid'] ?? ''));
            return null;
        }

        return $user;
    }

    /**
     * ไป Research Record โดยไม่ต้อง login ซ้ำ — สร้าง signed token จาก email (ตัวระบุตัวตนร่วม) แล้ว redirect
     * GET /admin/go-research-record (ต้องล็อกอิน admin แล้ว)
     * 
     * DEPRECATED: ใช้ Sci OAuth แทน
     */
    public function goResearchRecord()
    {
        // Redirect ไป Sci OAuth แทนการไป Research Record
        return redirect()->to(base_url('oauth/login'));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getEdocSsoUrl(array $user): ?string
    {
        $config = config(EdocSso::class);
        if (!$config->enabled || $config->baseUrl === '' || $config->sharedSecret === '') return null;

        return $this->generateSsoUrl($user, $config->baseUrl . $config->ssoEntryPath, $config->sharedSecret);
    }

    private function getResearchSsoUrl(array $user): ?string
    {
        $config = config(ResearchRecordSso::class);
        if (!$config->enabled || $config->baseUrl === '' || $config->sharedSecret === '') return null;

        return $this->generateSsoUrl($user, $config->baseUrl . $config->ssoEntryPath, $config->sharedSecret);
    }

    private function generateSsoUrl(array $user, string $entryUrl, string $secret): string
    {
        $email = $user['email'] ?? '';
        // Use Thai Name if available for friendlier display, else first name
        $name  = $user['th_name'] ?? $user['first_name_th'] ?? $user['thai_name'] ?? $user['first_name_en'] ?? 'User';

        $exp = time() + 120; // 2 minutes expiry
        $payload = [
            'email' => $email,
            'name'  => $name,
            'exp'   => $exp,
        ];
        $payloadJson = json_encode($payload);
        $payloadB64 = $this->base64UrlEncode($payloadJson);
        $signature = hash_hmac('sha256', $payloadB64, $secret, true);
        $sigB64 = $this->base64UrlEncode($signature);
        $token = $payloadB64 . '.' . $sigB64;

        $sep = str_contains($entryUrl, '?') ? '&' : '?';
        return $entryUrl . $sep . 'token=' . rawurlencode($token);
    }

    /**
     * Base64 URL-safe encode (no padding)
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Check if email is a student email format
     * Student emails: u<student_id>@live.uru.ac.th or @uru.ac.th domain
     */
    private function isStudentEmail(string $login): bool
    {
        // Check if it's an email format ending with student domain
        if (str_ends_with($login, '@live.uru.ac.th')) {
            return true;
        }

        // Check if it matches student ID pattern: u<number>@...
        if (preg_match('/^u\d+@/', $login)) {
            return true;
        }

        return false;
    }
}

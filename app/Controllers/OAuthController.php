<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\UruPortalOAuthService;
use App\Models\UserModel;
use App\Models\StudentUserModel;
use Config\UruPortalOAuth;

/**
 * OAuthController — จัดการ URU Portal OAuth 2.0 Login/Logout
 *
 * Routes:
 *   GET  /oauth/login    → redirect ไป URU Portal
 *   GET  /oauth          → callback จาก URU Portal (รับ ?code=xxx&state=xxx)
 *   GET  /oauth/logout   → ออกจากระบบ (ล้าง session)
 *
 * Flow:
 *   1. ผู้ใช้กด "เข้าสู่ระบบด้วย URU Portal" → /oauth/login
 *   2. redirect ไป https://uruportal.uru.ac.th/oauth_login?response_type=code&client_id=sci&...
 *   3. URU Portal redirect กลับมาที่ /oauth?code=xxx&state=xxx
 *   4. แลก code → access_token (POST /oauth/token)
 *   5. ดึงข้อมูลผู้ใช้ (GET /me)
 *   6. ตรวจสอบว่าเป็นนักศึกษา (login_uid ขึ้นต้น u+ตัวเลข) หรือบุคลากร
 *   7. หา/สร้าง record ในฐานข้อมูล (ใช้ email เป็น key)
 *   8. ตั้ง session แล้ว redirect ไปหน้าที่เหมาะสม
 */
class OAuthController extends BaseController
{
    protected UruPortalOAuthService $oauthService;
    protected UserModel $userModel;
    protected StudentUserModel $studentModel;
    protected UruPortalOAuth $config;

    private const LOG_PREFIX    = 'OAuthController: ';
    private const LOG_FILE      = 'oauth_login';
    private const SESSION_STATE = 'uru_oauth_state';

    public function __construct()
    {
        $this->config        = config(UruPortalOAuth::class);
        $this->oauthService  = new UruPortalOAuthService($this->config);
        $this->userModel     = new UserModel();
        $this->studentModel  = new StudentUserModel();
    }

    // -------------------------------------------------------------------------
    // Step 1: Redirect ไป URU Portal
    // -------------------------------------------------------------------------

    /**
     * GET /oauth/login
     * สร้าง state แล้ว redirect ผู้ใช้ไปล็อกอินที่ URU Portal
     */
    public function login(): \CodeIgniter\HTTP\RedirectResponse
    {
        if (!$this->config->enabled) {
            $this->writeLog('login', 'OAuth disabled', []);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'การเข้าสู่ระบบผ่าน URU Portal ยังไม่เปิดใช้งาน');
        }

        // ถ้า login อยู่แล้ว redirect ไปหน้าที่เหมาะสม
        if (session()->get('admin_logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }
        if (session()->get('student_logged_in')) {
            return redirect()->to(base_url('student'));
        }

        // สร้าง state สำหรับป้องกัน CSRF
        $state = bin2hex(random_bytes(16));
        session()->set(self::SESSION_STATE, $state);

        // เก็บ intended URL ถ้ามี
        $intended = $this->request->getGet('redirect_url');
        if ($intended && is_string($intended) && strpos($intended, base_url()) === 0) {
            session()->set('oauth_redirect_url', $intended);
        }

        $authUrl = $this->config->buildAuthUrl($state);

        $this->writeLog('login', 'redirect to URU Portal', [
            'auth_url' => $authUrl,
            'state'    => $state,
        ]);

        return redirect()->to($authUrl);
    }

    // -------------------------------------------------------------------------
    // Step 2: Callback จาก URU Portal
    // -------------------------------------------------------------------------

    /**
     * GET /oauth  (Callback URL: https://sci.uru.ac.th/index.php/oauth)
     * รับ ?code=xxx&state=xxx จาก URU Portal แล้วดำเนินการ login
     */
    public function callback(): \CodeIgniter\HTTP\RedirectResponse
    {
        $code  = $this->request->getGet('code');
        $state = $this->request->getGet('state');
        $error = $this->request->getGet('error');
        $ip    = $this->request->getIPAddress();

        $this->writeLog('callback_start', 'OAuth callback received', [
            'ip'       => $ip,
            'code_len' => is_string($code) ? strlen($code) : 0,
            'state'    => $state ?? '',
            'error'    => $error ?? '',
        ]);

        // ตรวจสอบ error จาก Portal
        if ($error !== null && $error !== '') {
            $errDesc = $this->request->getGet('error_description') ?? $error;
            $this->writeLog('callback_error', 'Portal returned error', ['error' => $error, 'description' => $errDesc, 'ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'เข้าสู่ระบบไม่สำเร็จ: ' . $errDesc);
        }

        // ตรวจสอบ code
        if (!$code || !is_string($code) || trim($code) === '') {
            $this->writeLog('callback_error', 'Missing code parameter', ['ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่ได้รับรหัสจาก URU Portal กรุณาลองใหม่อีกครั้ง');
        }

        // ตรวจสอบ state (ป้องกัน CSRF) — ถ้า Portal ไม่ส่ง state กลับมาให้ข้ามการตรวจสอบ
        $savedState = session()->get(self::SESSION_STATE);
        if ($savedState !== null && $state !== null && $state !== $savedState) {
            $this->writeLog('callback_error', 'State mismatch (CSRF?)', [
                'expected' => $savedState,
                'received' => $state,
                'ip'       => $ip,
            ]);
            session()->remove(self::SESSION_STATE);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'คำขอไม่ถูกต้อง (state mismatch) กรุณาลองใหม่อีกครั้ง');
        }
        session()->remove(self::SESSION_STATE);

        // ---- Step 2: แลก code → token ----
        $tokenSet = $this->oauthService->exchangeCodeForToken(trim($code));
        if ($tokenSet === null) {
            $this->writeLog('callback_error', 'Token exchange failed', ['ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่สามารถแลกรหัสกับ URU Portal ได้ กรุณาลองใหม่อีกครั้ง');
        }

        $accessToken  = $tokenSet['access_token'];
        $refreshToken = $tokenSet['refresh_token'] ?? '';
        $expiresIn    = (int) ($tokenSet['expires_in'] ?? 3600);
        $tokenExpires = time() + $expiresIn;

        // ---- Step 3: ดึงข้อมูลผู้ใช้ ----
        $portalUser = $this->oauthService->fetchUserInfo($accessToken);
        if ($portalUser === null) {
            $this->writeLog('callback_error', 'fetchUserInfo failed', ['ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่สามารถดึงข้อมูลผู้ใช้จาก URU Portal ได้ กรุณาลองใหม่อีกครั้ง');
        }

        $email    = strtolower(trim($portalUser['email'] ?? ''));
        $loginUid = trim($portalUser['login_uid'] ?? $portalUser['username'] ?? $portalUser['code'] ?? '');

        $this->writeLog('callback_user', 'User info received from Portal', [
            'email'     => $email,
            'login_uid' => $loginUid,
            'ip'        => $ip,
        ]);

        // ---- Step 4: แยกประเภทผู้ใช้ ----
        if ($this->oauthService->isStudent($portalUser)) {
            return $this->handleStudentLogin($portalUser, $accessToken, $refreshToken, $tokenExpires, $ip);
        }

        return $this->handlePersonnelLogin($portalUser, $accessToken, $refreshToken, $tokenExpires, $ip);
    }

    // -------------------------------------------------------------------------
    // Logout
    // -------------------------------------------------------------------------

    /**
     * GET /oauth/logout
     * ล้าง session ของ newScience แล้ว redirect ไปหน้า login
     * (URU Portal ไม่มี revoke endpoint — ล้างแค่ฝั่ง newScience)
     */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        $session  = session();
        $adminId  = $session->get('admin_id');
        $studentId = $session->get('student_id');
        $email    = $session->get('admin_email') ?? $session->get('student_email') ?? '';

        $this->writeLog('logout', 'User logged out', [
            'admin_id'   => $adminId ?? '',
            'student_id' => $studentId ?? '',
            'email'      => $email,
        ]);

        // ล้าง session ทั้งหมด
        $session->remove([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_role',
            'admin_access_token',
            'admin_refresh_token',
            'admin_token_expires',
            'admin_login_via',
            'student_logged_in',
            'student_id',
            'student_email',
            'student_name',
            'student_role',
            'student_access_token',
            'student_refresh_token',
            'student_token_expires',
            'oauth_redirect_url',
            self::SESSION_STATE,
        ]);
        $session->destroy();

        return redirect()->to(base_url('admin/login?logout=1'))
            ->with('success', 'ออกจากระบบแล้ว');
    }

    // -------------------------------------------------------------------------
    // Private: จัดการ login แยกตามประเภทผู้ใช้
    // -------------------------------------------------------------------------

    /**
     * จัดการ login สำหรับนักศึกษา (login_uid ขึ้นต้น u+ตัวเลข)
     * — เพิ่มลง table student_user
     * — ตั้ง session student_*
     * — redirect ไป /student
     */
    private function handleStudentLogin(
        array $portalUser,
        string $accessToken,
        string $refreshToken,
        int $tokenExpires,
        string $ip
    ): \CodeIgniter\HTTP\RedirectResponse {
        $email    = strtolower(trim($portalUser['email'] ?? ''));
        $loginUid = trim($portalUser['login_uid'] ?? $portalUser['username'] ?? '');

        $student = $this->studentModel->findOrCreateFromPortalUser($portalUser);
        if ($student === null) {
            $this->writeLog('student_error', 'findOrCreateFromPortalUser failed', ['email' => $email, 'ip' => $ip]);
            return redirect()->to(base_url('student/login'))
                ->with('error', 'ไม่สามารถสร้างหรือค้นหาบัญชีนักศึกษาได้ กรุณาติดต่อผู้ดูแลระบบ');
        }

        if (($student['status'] ?? '') !== 'active') {
            $this->writeLog('student_inactive', 'Student account inactive', ['email' => $email, 'id' => $student['id'], 'ip' => $ip]);
            return redirect()->to(base_url('student/login'))
                ->with('error', 'บัญชีนักศึกษาของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        $isNew = (trim($student['login_uid'] ?? '') === '' || $student['login_uid'] === $loginUid) &&
                 (strtotime($student['created_at'] ?? '') > (time() - 5));

        $this->writeLog('student_login', 'Student login success', [
            'id'        => $student['id'],
            'email'     => $email,
            'login_uid' => $loginUid,
            'is_new'    => $isNew ? 'yes' : 'no',
            'ip'        => $ip,
        ]);

        session()->set([
            'student_logged_in'      => true,
            'student_id'             => $student['id'],
            'student_email'          => $student['email'],
            'student_name'           => $this->studentModel->getFullName($student),
            'student_role'           => $student['role'] ?? 'student',
            'student_login_via'      => 'uru_portal_oauth',
            'student_access_token'   => $accessToken,
            'student_refresh_token'  => $refreshToken,
            'student_token_expires'  => $tokenExpires,
        ]);

        $redirectUrl = session()->get('oauth_redirect_url') ?? session()->get('student_redirect_url') ?? base_url('student');
        session()->remove(['oauth_redirect_url', 'student_redirect_url']);

        return redirect()->to($redirectUrl)->with('success', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . $this->studentModel->getFullName($student));
    }

    /**
     * จัดการ login สำหรับบุคลากร (ไม่ใช่ u+ตัวเลข)
     * — เพิ่มลง table user
     * — ตั้ง session admin_*
     * — redirect ไป /dashboard
     */
    private function handlePersonnelLogin(
        array $portalUser,
        string $accessToken,
        string $refreshToken,
        int $tokenExpires,
        string $ip
    ): \CodeIgniter\HTTP\RedirectResponse {
        $email    = strtolower(trim($portalUser['email'] ?? ''));
        $loginUid = trim($portalUser['login_uid'] ?? $portalUser['username'] ?? '');

        $user = $this->userModel->findOrCreateFromPortalUser($portalUser);
        if ($user === null) {
            $this->writeLog('personnel_error', 'findOrCreateFromPortalUser failed', ['email' => $email, 'ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่สามารถสร้างหรือค้นหาบัญชีผู้ใช้ได้ กรุณาติดต่อผู้ดูแลระบบ');
        }

        // ตรวจสอบ status (ตาราง user ใช้ status enum active/inactive)
        $status = $user['status'] ?? '';
        $active = (int) ($user['active'] ?? ($status === 'active' ? 1 : 0));
        if ($status !== 'active' && $active !== 1) {
            $this->writeLog('personnel_inactive', 'Personnel account inactive', ['email' => $email, 'uid' => $user['uid'], 'ip' => $ip]);
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'บัญชีของคุณถูกปิดใช้งาน กรุณาติดต่อผู้ดูแลระบบ');
        }

        $adminRole = (!empty($user['admin'])) ? 'admin' : ($user['role'] ?? 'user');
        $isNew     = strtotime($user['created_at'] ?? '') > (time() - 5);

        $this->writeLog('personnel_login', 'Personnel login success', [
            'uid'       => $user['uid'],
            'email'     => $email,
            'login_uid' => $loginUid,
            'role'      => $adminRole,
            'is_new'    => $isNew ? 'yes' : 'no',
            'ip'        => $ip,
        ]);

        session()->set([
            'admin_logged_in'      => true,
            'admin_id'             => $user['uid'],
            'admin_email'          => $user['email'],
            'admin_name'           => $this->userModel->getFullName($user),
            'admin_role'           => $adminRole,
            'admin_login_via'      => 'uru_portal_oauth',
            'admin_access_token'   => $accessToken,
            'admin_refresh_token'  => $refreshToken,
            'admin_token_expires'  => $tokenExpires,
        ]);

        $redirectUrl = session()->get('oauth_redirect_url') ?? session()->get('redirect_url') ?? base_url('dashboard');
        session()->remove(['oauth_redirect_url', 'redirect_url']);

        return redirect()->to($redirectUrl)->with('success', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับ ' . $this->userModel->getFullName($user));
    }

    // -------------------------------------------------------------------------
    // Log helper
    // -------------------------------------------------------------------------

    /**
     * เขียน log ลงไฟล์ writable/logs/oauth_login-YYYY-MM-DD.log
     * เพื่อตรวจสอบปัญหาการ login ครั้งแรกและ debug OAuth flow
     */
    private function writeLog(string $event, string $message, array $context = []): void
    {
        $level = str_contains($event, 'error') || str_contains($event, 'inactive') ? 'error' : 'info';
        $contextStr = empty($context) ? '' : ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        log_message($level, self::LOG_PREFIX . '[' . $event . '] ' . $message . $contextStr);

        // เขียน log file แยกต่างหากเพื่อง่ายต่อการตรวจสอบ
        $logDir  = WRITEPATH . 'logs/';
        $logFile = $logDir . self::LOG_FILE . '-' . date('Y-m-d') . '.log';
        $line    = date('Y-m-d H:i:s') . ' [' . strtoupper($level) . '] [' . $event . '] ' . $message . $contextStr . PHP_EOL;

        if (is_dir($logDir) && is_writable($logDir)) {
            @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
        }
    }
}

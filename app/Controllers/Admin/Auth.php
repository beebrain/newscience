<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AdminImpersonation;
use App\Libraries\ResearchRecordSsoBridge;
use App\Models\UserModel;

/**
 * Admin auth — login ผ่าน URU Portal OAuth (ดู OAuthController) เท่านั้น
 * SSO ภายนอกที่ยังใช้ = Research Record (sci.uru.ac.th/recordresearch)
 * (เลิกเชื่อม edoc.sci.uru.ac.th แล้ว — Edoc เป็น sub-app ใน NS ที่ /edoc)
 */
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
        $intent = $this->request->getGet('intent');
        $forResearchRecord = is_string($intent) && trim($intent) === 'researchrecord';

        if (session()->get('admin_logged_in')) {
            if ($forResearchRecord) {
                return $this->redirectToResearchRecordSso();
            }
            log_message('debug', 'Admin Auth: already logged in, redirect to admin/news. admin_id=' . (session()->get('admin_id') ?? ''));
            return redirect()->to(base_url('admin/news'));
        }

        // จาก RR — ตั้ง session NS ผ่าน OAuth แล้วส่งตรงไป RR sso-entry (ไม่วนที่ go-research-record)
        if ($forResearchRecord) {
            return redirect()->to(base_url('oauth/login') . '?' . http_build_query([
                'intent' => 'researchrecord',
            ]));
        }

        $data = [
            'page_title'      => 'Admin Login',
            'oauth_login_url' => base_url('oauth/login'),
        ];

        return view('admin/auth/login', $data);
    }

    /**
     * Process login attempt
     */
    public function attemptLogin()
    {
        // ปิด login email/password — ใช้เฉพาะ URU Portal OAuth
        log_message('info', 'Admin Auth: attemptLogin blocked — redirecting to OAuth');
        $intent = $this->request->getGet('intent');
        $params = [];
        if (is_string($intent) && trim($intent) === 'researchrecord') {
            $params = ['intent' => 'researchrecord'];
        }

        return redirect()->to(base_url('oauth/login') . ($params ? '?' . http_build_query($params) : ''))
            ->with('error', 'กรุณาเข้าสู่ระบบผ่าน URU Portal');
    }

    /**
     * Logout — ล้าง session ของ newScience แล้ว redirect กลับมาหน้า login ของ newScience เท่านั้น
     */
    public function logout()
    {
        $session = session();
        $adminId = $session->get('admin_id');
        $loginVia = $session->get('admin_login_via');
        log_message('info', self::LOG_PREFIX . 'logout admin_id=' . ($adminId ?? '') . ' login_via=' . ($loginVia ?? 'form'));
        AdminImpersonation::endIfActive('logout');

        $session->remove([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_role',
            'redirect_url',
            'admin_access_token',
            'admin_refresh_token',
            'admin_token_expires',
            'admin_login_via',
        ]);
        $session->destroy();

        $loginUrl = base_url('admin/login?logout=1');
        return redirect()->to($loginUrl)
            ->with('success', 'ออกจากระบบแล้ว');
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
        AdminImpersonation::endIfActive('clear_session');
        $session->remove([
            'admin_logged_in',
            'admin_id',
            'admin_email',
            'admin_name',
            'admin_role',
            'redirect_url',
            'admin_access_token',
            'admin_refresh_token',
            'admin_token_expires',
            'admin_login_via',
        ]);
        $session->destroy();

        return redirect()->to(base_url('admin/login'))
            ->with('success', 'ล้าง session แล้ว กรุณาเข้าสู่ระบบใหม่');
    }

    // -------------------------------------------------------------------------
    // SSO ผ่าน Research Record
    // -------------------------------------------------------------------------

    /**
     * Redirect ไปหน้า Login ของ Research Record (ใช้เมื่อล็อกอิน NS แล้ว)
     * GET /admin/portal-login-research
     *
     * DEPRECATED: ใช้ Sci OAuth แทน
     */
    public function portalLoginResearch()
    {
        if (! session()->get('admin_logged_in')) {
            return redirect()->to(base_url('oauth/login'));
        }

        $adminId = session()->get('admin_id');
        $user    = $this->userModel->find($adminId);
        if (! $user) {
            return redirect()->to(base_url('dashboard'))->with('error', 'ไม่พบข้อมูลผู้ใช้');
        }

        $url = $this->getResearchSsoUrl($user);
        if ($url === null) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Research Record SSO ยังไม่พร้อมใช้งาน');
        }

        return redirect()->to($url);
    }

    /**
     * ไป Research Record โดยไม่ต้อง login ซ้ำ
     * GET /admin/go-research-record (ต้องล็อกอิน admin แล้ว)
     *
     * DEPRECATED: ใช้ Sci OAuth แทน
     */
    public function goResearchRecord()
    {
        if (AdminImpersonation::isActive()) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'ไม่สามารถไปยังระบบภายนอกระหว่าง Login As ได้ กรุณาหยุดใช้งานแทนก่อน');
        }

        if (! session()->get('admin_logged_in')) {
            return redirect()->to(base_url('oauth/login') . '?' . http_build_query([
                'intent' => 'researchrecord',
            ]));
        }

        // RR-initiated only: ไม่อนุญาตให้เข้า RR จาก NS หลัง login ปกติ
        return redirect()->to(base_url('dashboard'))
            ->with('error', 'ไม่อนุญาตให้เข้าสู่ Research Record จาก newScience โดยตรง (ต้องเริ่มจากหน้า Research Record)');
    }

    /**
     * Redirect ไป RR /auth/sso-entry โดยตรง (ไม่ผ่าน go-research-record ในแถบที่อยู่)
     */
    private function redirectToResearchRecordSso(): \CodeIgniter\HTTP\RedirectResponse
    {
        $adminId = session()->get('admin_id');
        $user    = $this->userModel->find($adminId);
        if (! $user) {
            return redirect()->to(base_url('dashboard'))->with('error', 'ไม่พบข้อมูลผู้ใช้');
        }

        $url = ResearchRecordSsoBridge::entryUrlForUser($user);
        if ($url === null) {
            return redirect()->to(base_url('dashboard'))
                ->with('error', 'Research Record SSO ยังไม่พร้อมใช้งาน');
        }

        return redirect()->to($url);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function getResearchSsoUrl(array $user): ?string
    {
        return ResearchRecordSsoBridge::entryUrlForUser($user);
    }

    /**
     * Check if email is a student email format
     * Student emails: u<student_id>@live.uru.ac.th or @uru.ac.th domain
     */
    private function isStudentEmail(string $login): bool
    {
        if (str_ends_with($login, '@live.uru.ac.th')) {
            return true;
        }

        if (preg_match('/^u\d+@/', $login)) {
            return true;
        }

        return false;
    }
}

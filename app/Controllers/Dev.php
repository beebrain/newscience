<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\StudentUserModel;

/**
 * Local-only: ข้าม Authen สำหรับทดสอบในเครื่อง (development เท่านั้น)
 * ใช้ได้เฉพาะเมื่อ ENVIRONMENT === 'development'
 * ใน production จะตอบ 404
 */
class Dev extends BaseController
{
    private function isLocal(): bool
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    }

    /**
     * เข้าเป็น Admin (user คนแรก หรือระบุ uid/email ผ่าน query)
     * GET /dev/login-as-admin          → user คนแรก
     * GET /dev/login-as-admin?uid=5    → user ที่ uid=5
     * GET /dev/login-as-admin?email=xxx@yyy  → user ที่ email ตรง
     */
    public function loginAsAdmin()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = new UserModel();
        $uidParam = $this->request->getGet('uid');
        $emailParam = $this->request->getGet('email');

        if ($uidParam !== null && $uidParam !== '') {
            $user = $userModel->find((int) $uidParam);
        } elseif ($emailParam !== null && $emailParam !== '') {
            // ใช้ findByEmail เท่านั้น เพื่อไม่ให้ไปตรงกับ login_uid ของ user อื่น (เช่น pisit)
            $user = $userModel->findByEmail(trim($emailParam));
        } else {
            $user = $userModel->first();
        }

        if (!$user || !is_array($user)) {
            return redirect()->to(base_url('admin/login'))
                ->with('error', 'ไม่พบ user ในระบบ (สร้าง user ก่อน หรือตรวจสอบ uid/email)');
        }

        $role = $user['role'] ?? 'user';
        $allowedRoles = ['admin', 'editor', 'super_admin', 'faculty_admin'];
        $adminRole = in_array($role, $allowedRoles, true) ? $role : 'admin';
        // ใน development: ถ้าระบุ email หรือ uid มา ให้ใช้ role จริงจาก DB (เพื่อทดสอบการจำกัดหลักสูตรตาม personnel_programs)
        if (ENVIRONMENT === 'development' && $emailParam === null && $uidParam === null) {
            $adminRole = 'super_admin';
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_id' => $user['uid'],
            'admin_email' => $user['email'],
            'admin_name' => $userModel->getFullName($user),
            'admin_role' => $adminRole,
        ]);

        $who = $user['email'] . ' (uid=' . $user['uid'] . ')';
        return redirect()->to(base_url('dashboard'))->with('success', '[Local] เข้าสู่ระบบเป็น ' . $who . ' แล้ว');
    }

    /**
     * เข้าเป็น Student (student_user คนแรก)
     * GET /dev/login-as-student
     */
    public function loginAsStudent()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $student = $studentModel->first();
        if (!$student) {
            return redirect()->to(base_url('student/login'))
                ->with('error', 'ไม่พบ student_user ในระบบ (รัน migration และสร้างนักศึกษาทดสอบก่อน)');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id' => $student['id'],
            'student_email' => $student['email'],
            'student_name' => $studentModel->getFullName($student),
            'student_role' => $student['role'] ?? 'student',
        ]);

        return redirect()->to(base_url('student'))->with('success', '[Local] เข้าสู่ระบบเป็น Student แล้ว');
    }

    /**
     * เข้าเป็น Student Admin (นักศึกษาสโมสร role=club) แล้วไปหน้า Student Admin
     * GET /dev/login-as-student-admin
     */
    public function loginAsStudentAdmin()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $club = $studentModel->where('role', 'club')->where('status', 'active')->first();
        if (!$club) {
            return redirect()->to(base_url('student/login'))
                ->with('error', 'ไม่พบนักศึกษาสโมสร (role=club) ในระบบ — รัน php scripts/seed_student_users.php ก่อน');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id' => $club['id'],
            'student_email' => $club['email'],
            'student_name' => $studentModel->getFullName($club),
            'student_role' => 'club',
        ]);

        return redirect()->to(base_url('student-admin/barcode-events'))
            ->with('success', '[Local] เข้าสู่ระบบเป็น Student Admin แล้ว');
    }

    // -------------------------------------------------------------------------
    // Mock URU Portal OAuth (ทดสอบ OAuth flow โดยไม่ต้องผ่าน Portal จริง)
    // -------------------------------------------------------------------------

    /**
     * จำลอง OAuth callback สำหรับนักศึกษา
     * GET /dev/mock-oauth-student?uid=u6512345&email=u6512345@live.uru.ac.th&name_th=ทดสอบ&lastname_th=นักศึกษา
     *
     * ทดสอบ:
     *   - การสร้าง student_user ใหม่ (first login)
     *   - การ update login_uid ถ้า email มีอยู่แล้วแต่ login_uid ว่าง
     *   - session student_* ถูกตั้งค่าถูกต้อง
     *   - log file ถูกเขียน
     */
    public function mockOAuthStudent()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $uid    = trim($this->request->getGet('uid') ?? 'u6512345');
        $email  = strtolower(trim($this->request->getGet('email') ?? 'u6512345@live.uru.ac.th'));
        $nameTh = trim($this->request->getGet('name_th') ?? 'ทดสอบ');
        $lastTh = trim($this->request->getGet('lastname_th') ?? 'นักศึกษา');
        $nameEn = trim($this->request->getGet('name_en') ?? 'Test');
        $lastEn = trim($this->request->getGet('lastname_en') ?? 'Student');

        // จำลองข้อมูลจาก URU Portal /me endpoint
        $portalUser = [
            'email'         => $email,
            'login_uid'     => $uid,
            'first_name_th' => $nameTh,
            'last_name_th'  => $lastTh,
            'first_name_en' => $nameEn,
            'last_name_en'  => $lastEn,
            'thai_name'     => $nameTh,
            'thai_lastname' => $lastTh,
            'gf_name'       => $nameEn,
            'gl_name'       => $lastEn,
            'profile_image' => '',
        ];

        // เรียก StudentUserModel::findOrCreateFromPortalUser โดยตรง
        $studentModel = new StudentUserModel();
        $student = $studentModel->findOrCreateFromPortalUser($portalUser);

        if (!$student) {
            return redirect()->to(base_url('student/login'))
                ->with('error', '[Mock] ไม่สามารถสร้างหรือค้นหาบัญชีนักศึกษาได้');
        }

        // ตั้ง session เหมือน OAuthController::handleStudentLogin
        session()->set([
            'student_logged_in'      => true,
            'student_id'             => $student['id'],
            'student_email'          => $student['email'],
            'student_name'           => $studentModel->getFullName($student),
            'student_role'           => $student['role'] ?? 'student',
            'student_login_via'      => 'uru_portal_oauth_mock',
            'student_access_token'   => 'mock_access_token_' . bin2hex(random_bytes(8)),
            'student_refresh_token'  => 'mock_refresh_token_' . bin2hex(random_bytes(8)),
            'student_token_expires'  => time() + 3600,
        ]);

        return redirect()->to(base_url('student'))
            ->with('success', '[Mock] เข้าสู่ระบบสำเร็จ (นักศึกษา) ยินดีต้อนรับ ' . $studentModel->getFullName($student));
    }

    /**
     * จำลอง OAuth callback สำหรับบุคลากร
     * GET /dev/mock-oauth-personnel?uid=staff123&email=staff@sci.uru.ac.th&name_th=ทดสอบ&lastname_th=บุคลากร
     *
     * ทดสอบ:
     *   - การสร้าง user ใหม่ (first login)
     *   - การ update login_uid ถ้า email มีอยู่แล้วแต่ login_uid ว่าง
     *   - session admin_* ถูกตั้งค่าถูกต้อง
     */
    public function mockOAuthPersonnel()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $uid    = trim($this->request->getGet('uid') ?? 'staff123');
        $email  = strtolower(trim($this->request->getGet('email') ?? 'staff@sci.uru.ac.th'));
        $nameTh = trim($this->request->getGet('name_th') ?? 'ทดสอบ');
        $lastTh = trim($this->request->getGet('lastname_th') ?? 'บุคลากร');
        $nameEn = trim($this->request->getGet('name_en') ?? 'Test');
        $lastEn = trim($this->request->getGet('lastname_en') ?? 'Staff');

        // จำลองข้อมูลจาก URU Portal /me endpoint
        $portalUser = [
            'email'         => $email,
            'login_uid'     => $uid,
            'first_name_th' => $nameTh,
            'last_name_th'  => $lastTh,
            'first_name_en' => $nameEn,
            'last_name_en'  => $lastEn,
            'thai_name'     => $nameTh,
            'thai_lastname' => $lastTh,
            'gf_name'       => $nameEn,
            'gl_name'       => $lastEn,
            'profile_image' => '',
        ];

        // เรียก UserModel::findOrCreateFromPortalUser โดยตรง
        $userModel = new UserModel();
        $user = $userModel->findOrCreateFromPortalUser($portalUser);

        if (!$user) {
            return redirect()->to(base_url('admin/login'))
                ->with('error', '[Mock] ไม่สามารถสร้างหรือค้นหาบัญชีผู้ใช้ได้');
        }

        // ตั้ง session เหมือน OAuthController::handlePersonnelLogin
        $adminRole = (!empty($user['admin'])) ? 'admin' : ($user['role'] ?? 'user');
        session()->set([
            'admin_logged_in'      => true,
            'admin_id'             => $user['uid'],
            'admin_email'          => $user['email'],
            'admin_name'           => $userModel->getFullName($user),
            'admin_role'           => $adminRole,
            'admin_login_via'      => 'uru_portal_oauth_mock',
            'admin_access_token'   => 'mock_access_token_' . bin2hex(random_bytes(8)),
            'admin_refresh_token'  => 'mock_refresh_token_' . bin2hex(random_bytes(8)),
            'admin_token_expires'  => time() + 3600,
        ]);

        return redirect()->to(base_url('dashboard'))
            ->with('success', '[Mock] เข้าสู่ระบบสำเร็จ (บุคลากร) ยินดีต้อนรับ ' . $userModel->getFullName($user));
    }

    /**
     * เข้าเป็น super_admin และไปที่ Content Builder ทันที (สำหรับทดสอบ)
     * GET /dev/test-content-builder
     */
    public function testContentBuilder()
    {
        if (!$this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $userModel = new UserModel();
        // หา user ที่เป็น super_admin ก่อน ถ้าไม่มีใช้ user แรกและตั้ง role เป็น super_admin
        $user = $userModel->where('role', 'super_admin')->where('active', 1)->first();

        if (!$user) {
            $user = $userModel->first();
            if (!$user) {
                return redirect()->to(base_url('admin/login'))
                    ->with('error', 'ไม่พบ user ในระบบ');
            }
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_id' => $user['uid'],
            'admin_email' => $user['email'],
            'admin_name' => $userModel->getFullName($user),
            'admin_role' => 'super_admin',
        ]);

        return redirect()->to(base_url('program-admin'))
            ->with('success', '[Local] เข้าสู่ระบบเป็น super_admin และไปที่ Content Builder');
    }
}

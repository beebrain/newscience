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
    /** นักศึกษาทดสอบ: สโมสร (role=club) */
    public const DUMMY_STUDENT_CLUB_EMAIL  = 'u59@live.uru.ac.th';
    public const DUMMY_STUDENT_CLUB_UID    = 'u59';

    /** นักศึกษาทดสอบ: นักศึกษาปกติ (role=student) */
    public const DUMMY_STUDENT_PLAIN_EMAIL = 'u69@live.uru.ac.th';
    public const DUMMY_STUDENT_PLAIN_UID   = 'u69';

    /** รหัสผ่านร่วมสำหรับทดสอบหน้า /student/login (development เท่านั้น) */
    public const DUMMY_STUDENT_PASSWORD = 'dev1234';

    private function isLocal(): bool
    {
        return defined('ENVIRONMENT') && ENVIRONMENT === 'development';
    }

    /**
     * สร้างหรืออัปเดต student_user ทดสอบ 2 บัญชี (สโมสร + นักศึกษาปกติ)
     * GET /dev/seed-student-dummies
     */
    public function seedStudentDummies()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->ensureBothDevDummyStudents();

        return redirect()->to(base_url('dev/student-test'))->with('success', '[Local] สร้าง/อัปเดตนักศึกษาทดสอบ u59 (สโมสร) และ u69 (นักศึกษา) แล้ว');
    }

    /**
     * สร้าง/อัปเดตนักศึกษา dev สองบัญชีใน DB (idempotent)
     */
    private function ensureBothDevDummyStudents(): void
    {
        $this->ensureDummyStudent(
            self::DUMMY_STUDENT_CLUB_EMAIL,
            self::DUMMY_STUDENT_CLUB_UID,
            'club',
            'ทดสอบสโมสร',
            'DevClub'
        );
        $this->ensureDummyStudent(
            self::DUMMY_STUDENT_PLAIN_EMAIL,
            self::DUMMY_STUDENT_PLAIN_UID,
            'student',
            'ทดสอบนักศึกษา',
            'DevStudent'
        );
    }

    /**
     * @param mixed $idParam
     * @param mixed $emailParam
     */
    private function resolveStudentForDevLogin(StudentUserModel $studentModel, $idParam, $emailParam): ?array
    {
        if ($idParam !== null && $idParam !== '') {
            $row = $studentModel->find((int) $idParam);

            return is_array($row) ? $row : null;
        }
        if (is_string($emailParam) && trim($emailParam) !== '') {
            $row = $studentModel->findByEmail(strtolower(trim($emailParam)));

            return is_array($row) ? $row : null;
        }
        $club = $studentModel->where('role', 'club')->where('status', 'active')->first();
        if ($club) {
            return $club;
        }
        $row = $studentModel->first();

        return is_array($row) ? $row : null;
    }

    /**
     * Mock OAuth แล้วเข้าเป็นนักศึกษาสโมสร (u59@live.uru.ac.th)
     * GET /dev/login-dummy-club
     */
    public function loginDummyStudentClub()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->ensureBothDevDummyStudents();

        return $this->mockOAuthStudentSession(
            self::DUMMY_STUDENT_CLUB_UID,
            self::DUMMY_STUDENT_CLUB_EMAIL,
            'ทดสอบสโมสร',
            'สโมสร',
            'DevClub',
            'Club',
            'club',
            base_url('student-admin/barcode-events')
        );
    }

    /**
     * Mock OAuth แล้วเข้าเป็นนักศึกษาปกติ (u69@live.uru.ac.th)
     * GET /dev/login-dummy-student
     */
    public function loginDummyStudentRegular()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $this->ensureBothDevDummyStudents();

        return $this->mockOAuthStudentSession(
            self::DUMMY_STUDENT_PLAIN_UID,
            self::DUMMY_STUDENT_PLAIN_EMAIL,
            'ทดสอบนักศึกษา',
            'ปกติ',
            'DevStudent',
            'Student',
            'student',
            base_url('student')
        );
    }

    /**
     * หน้ารวมลิงก์ทดสอบนักศึกษา (development เท่านั้น)
     * GET /dev/student-test
     */
    public function studentTest()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('dev/student_test', [
            'page_title'       => '[Dev] ทดสอบนักศึกษา',
            'dummy_password'   => self::DUMMY_STUDENT_PASSWORD,
            'dummy_club_email'   => self::DUMMY_STUDENT_CLUB_EMAIL,
            'dummy_plain_email'  => self::DUMMY_STUDENT_PLAIN_EMAIL,
        ]);
    }

    /**
     * สร้าง/อัปเดตแถว student_user สำหรับทดสอบ (มีรหัสผ่านสำหรับ /student/login)
     *
     * @return array<string, mixed>|null
     */
    private function ensureDummyStudent(
        string $emailNorm,
        string $loginUid,
        string $role,
        string $nameTh,
        string $nameEnLast
    ): ?array {
        $emailNorm = strtolower(trim($emailNorm));
        $model   = new StudentUserModel();
        $pwHash  = password_hash(self::DUMMY_STUDENT_PASSWORD, PASSWORD_DEFAULT);
        $data    = [
            'email'     => $emailNorm,
            'login_uid' => $loginUid,
            'role'      => $role,
            'status'    => 'active',
            'password'  => $pwHash,
            'tf_name'   => $nameTh,
            'tl_name'   => $role === 'club' ? 'สโมสร' : 'นักศึกษา',
            'gf_name'   => 'Dev',
            'gl_name'   => $nameEnLast,
        ];

        $row = $model->findByEmail($emailNorm);
        if ($row) {
            $model->skipValidation(true)->update((int) $row['id'], $data);

            return $model->find((int) $row['id']);
        }
        $model->skipValidation(true)->insert($data);
        $id = (int) $model->getInsertID();

        return $id > 0 ? $model->find($id) : $model->findByEmail($emailNorm);
    }

    /**
     * @return \CodeIgniter\HTTP\RedirectResponse
     */
    private function mockOAuthStudentSession(
        string $uid,
        string $email,
        string $nameTh,
        string $lastTh,
        string $nameEn,
        string $lastEn,
        string $forceRole,
        string $redirectTo
    ) {
        $portalUser = [
            'email'         => $email,
            'login_uid'     => $uid,
            'first_name_th' => $nameTh,
            'last_name_th'  => $lastTh,
            'first_name_en' => $nameEn,
            'last_name_en'  => $lastEn,
            'tf_name'       => $nameTh,
            'tl_name'       => $lastTh,
            'gf_name'       => $nameEn,
            'gl_name'       => $lastEn,
            'profile_image' => '',
        ];

        $studentModel = new StudentUserModel();
        $student      = $studentModel->findOrCreateFromPortalUser($portalUser);
        if (! $student) {
            return redirect()->to(base_url('dev/student-test'))
                ->with('error', '[Mock] ไม่สามารถสร้างหรือค้นหาบัญชีนักศึกษาได้');
        }

        if (($student['role'] ?? '') !== $forceRole) {
            $studentModel->skipValidation(true)->update((int) $student['id'], ['role' => $forceRole]);
            $student = $studentModel->find((int) $student['id']);
        }

        session()->set([
            'student_logged_in'     => true,
            'student_id'            => $student['id'],
            'student_email'         => $student['email'],
            'student_name'          => $studentModel->getFullName($student),
            'student_role'          => $student['role'] ?? $forceRole,
            'student_login_via'     => 'uru_portal_oauth_mock_dummy',
            'student_access_token'  => 'mock_access_token_' . bin2hex(random_bytes(8)),
            'student_refresh_token' => 'mock_refresh_token_' . bin2hex(random_bytes(8)),
            'student_token_expires' => time() + 3600,
        ]);

        return redirect()->to($redirectTo)
            ->with('success', '[Local] เข้าสู่ระบบเป็น ' . $email . ' (role=' . $forceRole . ')');
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
     * เข้าเป็น Student (ระบุ email หรือ id — แนวเดียวกับ /dev/login-as-admin?email=...)
     * ตั้ง session ตาม role ในฐานข้อมูล: role=club → redirect student-admin, อื่นๆ → student portal
     *
     * GET /dev/login-as-student
     * GET /dev/login-as-student?email=u69@live.uru.ac.th
     * GET /dev/login-as-student?id=12
     * ไม่ส่งพารามิเตอร์ → ลองหานักศึกษาสโมสร (club) ที่ active ก่อน แล้วค่อยคนแรกในตาราง
     * ถ้ายังหาไม่เจอ → สร้าง/อัปเดตนักศึกษาทดสอบ u59 (สโมสร) + u69 (นักศึกษา) ใน DB อัตโนมัติ แล้วลองอีกครั้ง
     */
    public function loginAsStudent()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $emailParam   = $this->request->getGet('email');
        $idParam      = $this->request->getGet('id');

        $student = $this->resolveStudentForDevLogin($studentModel, $idParam, $emailParam);
        if (! $student) {
            $this->ensureBothDevDummyStudents();
            $student = $this->resolveStudentForDevLogin($studentModel, $idParam, $emailParam);
        }

        if (! $student || ! is_array($student)) {
            return redirect()->to(base_url('student/login'))
                ->with('error', '[Local] ไม่พบนักศึกษา — ตรวจสอบ ?email= หรือ ?id= (หลังสร้างบัญชีทดสอบ u59/u69 แล้วยังไม่ตรงกับที่ระบุ)');
        }

        $role = $student['role'] ?? 'student';
        if (($student['status'] ?? '') !== 'active') {
            return redirect()->to(base_url('student/login'))
                ->with('error', '[Local] บัญชีนี้ไม่ active — ไม่สามารถเข้าสู่ระบบทดสอบได้');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id'        => $student['id'],
            'student_email'     => $student['email'],
            'student_name'      => $studentModel->getFullName($student),
            'student_role'      => $role,
        ]);

        $redirectTo = $role === 'club'
            ? base_url('student-admin/barcode-events')
            : base_url('student');
        $who        = ($student['email'] ?? '') . ' (id=' . ($student['id'] ?? '') . ', role=' . $role . ')';

        return redirect()->to($redirectTo)
            ->with('success', '[Local] เข้าสู่ระบบเป็น ' . $who . ' แล้ว');
    }

    /**
     * เข้าเป็นนักศึกษาสโมสร (delegate ไป login-as-student ตาม email ของ club คนแรก)
     * GET /dev/login-as-student-admin
     */
    public function loginAsStudentAdmin()
    {
        if (! $this->isLocal()) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $studentModel = new StudentUserModel();
        $this->ensureBothDevDummyStudents();
        $club = $studentModel->where('role', 'club')->where('status', 'active')->first();
        if (! $club) {
            return redirect()->to(base_url('student/login'))
                ->with('error', '[Local] ยังไม่มีนักศึกษา role=club หลังสร้างข้อมูลทดสอบ — ตรวจสอบฐานข้อมูล');
        }

        return redirect()->to(base_url('dev/login-as-student?email=' . rawurlencode((string) ($club['email'] ?? ''))));
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
            'tf_name'       => $nameTh,
            'tl_name'       => $lastTh,
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
            'tf_name'       => $nameTh,
            'tl_name'       => $lastTh,
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

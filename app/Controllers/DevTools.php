<?php

namespace App\Controllers;

use App\Models\StudentUserModel;
use App\Models\UserModel;

/**
 * DevTools Controller - สำหรับทดสอบโดยไม่ต้องผ่าน Authen
 * ใช้ได้เฉพาะ ENVIRONMENT = development เท่านั้น
 */
class DevTools extends BaseController
{
    public function __construct()
    {
        // ป้องกันการใช้งานบน production
        if (ENVIRONMENT !== 'development') {
            exit('DevTools ใช้ได้เฉพาะ development environment เท่านั้น');
        }
    }

    /**
     * หน้าเลือกโหมดทดสอบ
     */
    public function index()
    {
        return view('dev/tools', [
            'page_title' => 'Dev Tools - Test Mode',
        ]);
    }

    /**
     * Login เป็นนักศึกษา (auto)
     */
    public function loginAsStudent(int $studentId = 1)
    {
        $model = new StudentUserModel();
        $student = $model->find($studentId);

        if (!$student) {
            // สร้าง student test data ถ้าไม่มี
            $student = $this->createTestStudent();
        }

        session()->set([
            'student_logged_in' => true,
            'student_id'        => $student['id'],
            'student_email'     => $student['email'] ?? 'test@student.com',
            'student_uid'       => $student['login_uid'] ?? 'TEST001',
            'student_name'      => ($student['th_name'] ?? 'ทดสอบ') . ' ' . ($student['thai_lastname'] ?? 'นักศึกษา'),
            'student_role'      => $student['role'] ?? 'student',
            'student_program_id' => $student['program_id'] ?? 1,
        ]);

        return redirect()->to(base_url('student/certificates'))
            ->with('success', '[DEV] Logged in as Student ID: ' . $student['id']);
    }

    /**
     * Login เป็นเจ้าหน้าที่/Admin (auto)
     */
    public function loginAsStaff(int $userId = 1)
    {
        $model = new UserModel();
        $user = $model->find($userId);

        if (!$user) {
            $user = $this->createTestStaff();
        }

        session()->set([
            'admin_logged_in' => true,
            'admin_id'        => $user['uid'] ?? $userId,
            'admin_email'     => $user['email'] ?? 'staff@science.uru.ac.th',
            'admin_uid'       => $user['login_uid'] ?? 'STAFF001',
            'admin_name'      => ($user['thai_name'] ?? $user['gf_name'] ?? 'ทดสอบ') . ' ' . ($user['thai_lastname'] ?? $user['gl_name'] ?? 'เจ้าหน้าที่'),
            'admin_role'      => $user['role'] ?? 'admin',
            'admin_program_id' => $user['program_id'] ?? null,
        ]);

        $uid = $user['uid'] ?? $userId;
        return redirect()->to(base_url('admin/certificates'))
            ->with('success', '[DEV] Logged in as Staff UID: ' . $uid);
    }

    /**
     * Login เป็นผู้อนุมัติ (Program Chair/Dean)
     */
    public function loginAsApprover(string $type = 'chair', int $userId = 1)
    {
        $model = new UserModel();
        $user = $model->find($userId);

        if (!$user) {
            $user = $this->createTestApprover($type);
        }

        $role = $type === 'dean' ? 'super_admin' : 'faculty_admin';
        $nameSuffix = $type === 'dean' ? 'คณบดี' : 'ประธานหลักสูตร';

        session()->set([
            'admin_logged_in' => true,
            'admin_id'        => $user['uid'] ?? $userId,
            'admin_email'     => $user['email'] ?? "{$type}@science.uru.ac.th",
            'admin_uid'       => $user['login_uid'] ?? strtoupper($type) . '001',
            'admin_name'      => ($user['thai_name'] ?? $user['gf_name'] ?? 'ทดสอบ') . ' ' . ($user['thai_lastname'] ?? $user['gl_name'] ?? $nameSuffix),
            'admin_role'      => $user['role'] ?? $role,
            'admin_program_id' => $user['program_id'] ?? 1,
        ]);

        $uid = $user['uid'] ?? $userId;
        return redirect()->to(base_url('approve/certificates'))
            ->with('success', '[DEV] Logged in as ' . ucfirst($type) . ' UID: ' . $uid);
    }

    /**
     * Logout ทั้งหมด
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to(base_url('dev'))->with('success', '[DEV] Logged out');
    }

    /**
     * สร้าง test student
     */
    protected function createTestStudent(): array
    {
        $model = new StudentUserModel();

        $data = [
            'login_uid'    => 'TEST' . time(),
            'email'        => 'test' . time() . '@student.com',
            'th_name'      => 'ทดสอบ',
            'thai_lastname' => 'นักศึกษา',
            'en_name'      => 'Test',
            'eng_lastname' => 'Student',
            'program_id'   => 1,
            'role'         => 'student',
            'status'       => 'active',
        ];

        $id = $model->insert($data, true);
        return $model->find($id);
    }

    /**
     * สร้าง test staff
     */
    protected function createTestStaff(): array
    {
        $model = new UserModel();

        $data = [
            'login_uid'    => 'STAFF' . time(),
            'email'        => 'staff' . time() . '@science.uru.ac.th',
            'thai_name'    => 'ทดสอบ',
            'thai_lastname' => 'เจ้าหน้าที่',
            'role'         => 'super_admin',
            'active'       => 1,
            'password'     => password_hash('test123', PASSWORD_DEFAULT),
        ];

        $id = $model->insert($data, true);
        return $model->find($id);
    }

    /**
     * สร้าง test approver
     */
    protected function createTestApprover(string $type): array
    {
        $model = new UserModel();

        $role = $type === 'dean' ? 'super_admin' : 'faculty_admin';
        $nameSuffix = $type === 'dean' ? 'คณบดี' : 'ประธานหลักสูตร';

        $data = [
            'login_uid'    => strtoupper($type) . time(),
            'email'        => $type . time() . '@science.uru.ac.th',
            'thai_name'    => 'ทดสอบ',
            'thai_lastname' => $nameSuffix,
            'role'         => $role,
            'active'       => 1,
            'password'     => password_hash('test123', PASSWORD_DEFAULT),
        ];

        $id = $model->insert($data, true);
        return $model->find($id);
    }

    /**
     * สร้าง test certificate request
     */
    public function createTestRequest(int $templateId = 1)
    {
        // ตรวจสอบว่ามี template หรือยัง
        $templateModel = new \App\Models\CertTemplateModel();
        $template = $templateModel->find($templateId);

        if (!$template) {
            // สร้าง template ทดสอบ
            $templateId = $templateModel->insert([
                'name_th'       => 'ใบรับรองการศึกษา (ทดสอบ)',
                'name_en'       => 'Test Certificate',
                'level'         => 'program',
                'template_file' => null,
                'field_mapping' => json_encode([
                    'student_name' => ['x' => 100, 'y' => 200, 'font_size' => 16],
                    'date' => ['x' => 100, 'y' => 250, 'font_size' => 14],
                ]),
                'status'        => 'active',
            ], true);
        }

        // ตรวจสอบว่า login เป็น student หรือยัง
        if (!session()->get('student_logged_in')) {
            return redirect()->to(base_url('dev/login-as-student'));
        }

        $requestModel = new \App\Models\CertRequestModel();
        $requestNumber = $requestModel->generateRequestNumber();

        $requestId = $requestModel->insert([
            'request_number' => $requestNumber,
            'student_id'     => session()->get('student_id'),
            'template_id'    => $templateId,
            'program_id'     => session()->get('student_program_id'),
            'level'          => 'program',
            'purpose'        => 'ทดสอบระบบ E-Certificate',
            'copies'         => 1,
            'status'         => 'pending',
        ], true);

        // Log การ submit
        $approvalModel = new \App\Models\CertApprovalModel();
        $approvalModel->log($requestId, 'submit', session()->get('student_id'), 'student', 'สร้างคำขอทดสอบ');

        return redirect()->to(base_url('student/certificates/' . $requestId))
            ->with('success', '[DEV] สร้างคำขอทดสอบ #' . $requestNumber . ' เรียบร้อย');
    }
}

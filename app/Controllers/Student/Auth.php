<?php

namespace App\Controllers\Student;

use App\Controllers\BaseController;
use App\Models\StudentUserModel;

class Auth extends BaseController
{
    protected $studentUserModel;

    public function __construct()
    {
        $this->studentUserModel = new StudentUserModel();
    }

    /**
     * Student Portal login form
     */
    public function login()
    {
        if (session()->get('student_logged_in')) {
            return redirect()->to(base_url('student'));
        }

        $data = [
            'page_title' => 'Student Portal - Login',
        ];
        return view('student/auth/login', $data);
    }

    /**
     * Process student login (email + password)
     */
    public function attemptLogin()
    {
        $login = trim($this->request->getPost('login') ?? '');
        $rules = [
            'login'    => 'required|string',
            'password' => 'required|min_length[1]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $password = $this->request->getPost('password');
        $user = $this->studentUserModel->findByIdentifier($login);

        if (!$user || !is_array($user)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        }

        if (($user['status'] ?? '') !== 'active') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'บัญชีนี้ถูกปิดใช้งาน');
        }

        $storedHash = $user['password'] ?? '';
        if (!$this->studentUserModel->verifyPassword($password, $storedHash)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'อีเมลหรือรหัสผ่านไม่ถูกต้อง');
        }

        session()->set([
            'student_logged_in' => true,
            'student_id' => $user['id'],
            'student_email' => $user['email'],
            'student_name' => $this->studentUserModel->getFullName($user),
            'student_role' => $user['role'] ?? 'student',
        ]);

        $redirectUrl = session()->get('student_redirect_url') ?? base_url('student');
        session()->remove('student_redirect_url');

        return redirect()->to($redirectUrl)->with('success', 'เข้าสู่ระบบสำเร็จ');
    }

    /**
     * Logout student portal
     */
    public function logout()
    {
        session()->remove([
            'student_logged_in',
            'student_id',
            'student_email',
            'student_name',
            'student_role',
        ]);
        return redirect()->to(base_url('student/login'))->with('success', 'ออกจากระบบแล้ว');
    }
}

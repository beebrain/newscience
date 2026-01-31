<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Auth extends BaseController
{
    protected $userModel;

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
        $rules = [
            'login'    => 'required|min_length[1]',
            'password' => 'required|min_length[6]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                            ->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $login = trim($this->request->getPost('login') ?? '');
        $password = $this->request->getPost('password');

        // Find user by email or username (login_uid)
        $user = $this->userModel->findByIdentifier($login);

        if (!$user) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Invalid email or password.');
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Your account is inactive.');
        }

        // Check if user has admin role
        if ($user['role'] !== 'admin' && $user['role'] !== 'editor') {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'You do not have admin access.');
        }

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password'])) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Invalid email or password.');
        }

        // Set session data
        $sessionData = [
            'admin_logged_in' => true,
            'admin_id' => $user['uid'],
            'admin_email' => $user['email'],
            'admin_name' => $this->userModel->getFullName($user),
            'admin_role' => $user['role']
        ];
        session()->set($sessionData);

        // Redirect to intended URL or admin dashboard
        $redirectUrl = session()->get('redirect_url') ?? base_url('admin/news');
        session()->remove('redirect_url');

        return redirect()->to($redirectUrl)
                        ->with('success', 'Welcome back, ' . $sessionData['admin_name'] . '!');
    }

    /**
     * Logout
     */
    public function logout()
    {
        // Remove session data
        session()->remove(['admin_logged_in', 'admin_id', 'admin_email', 'admin_name', 'admin_role']);
        session()->destroy();

        return redirect()->to(base_url('admin/login'))
                        ->with('success', 'You have been logged out successfully.');
    }
}

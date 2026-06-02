<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * ตรวจว่าเข้าสู่ระบบแล้ว (admin_logged_in) — ใช้กับหน้า Dashboard การจัดการ (ทุก role)
 */
class LoggedInFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('admin_logged_in')) {
            $returnUrl = current_url();
            if (str_contains($returnUrl, 'go-research-record')) {
                return redirect()->to(base_url('oauth/login') . '?' . http_build_query([
                    'intent' => 'researchrecord',
                ]))
                    ->with('error', 'กรุณาเข้าสู่ระบบ');
            }

            return redirect()->to(base_url('oauth/login') . '?' . http_build_query([
                'redirect_url' => $returnUrl,
            ]))
                ->with('error', 'กรุณาเข้าสู่ระบบ');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}

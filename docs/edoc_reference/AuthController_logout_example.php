<?php

/**
 * ตัวอย่าง method logout สำหรับ Edoc (edoc.sci.uru.ac.th)
 * นำไปใส่ใน AuthController หรือ Controller ที่จัดการ logout ของ Edoc
 *
 * หลังล้าง session แล้ว redirect ไปที่ newScience: /admin/edoc-logout-return
 * เพื่อให้ผู้ใช้เด้งกลับมาหน้า login ของ newScience (sci.uru.ac.th)
 *
 * ตั้งค่า URL ใน config หรือ .env:
 *   newscience.logout_return_url = https://sci.uru.ac.th/admin/edoc-logout-return
 */

namespace App\Controllers;

// ปรับ namespace ตามโครงสร้าง Edoc

class AuthController extends BaseController
{
    /**
     * URL ปลายทางหลัง logout — ต้องเป็น newScience
     * อ่านจาก config หรือ .env (แนะนำ)
     */
    protected function getNewscienceLogoutReturnUrl(): string
    {
        $url = config('NewscienceSso')->logoutReturnUrl ?? '';
        if ($url !== '') {
            return rtrim($url, '?&');
        }
        // fallback คงที่ (แทน sci.uru.ac.th ด้วยโดเมนจริงของ newScience)
        return 'https://sci.uru.ac.th/admin/edoc-logout-return';
    }

    /**
     * ออกจากระบบ — ล้าง session ของ Edoc แล้ว redirect กลับ newScience
     * Route ตัวอย่าง: GET /auth/logout หรือ /index.php/auth/logout
     *
     * รองรับ query return_url (optional): ถ้ามีและผ่าน allowlist ให้ redirect ไปนั้น
     * ไม่มีหรือไม่ผ่าน allowlist ให้ใช้ URL คงที่ของ newScience
     */
    public function logout()
    {
        // 1. ล้าง session ของ Edoc
        $session = session();
        $session->destroy();

        // 2. กำหนด URL ปลายทาง
        $returnUrl = $this->request->getGet('return_url');
        $allowedHosts = ['sci.uru.ac.th', 'localhost']; // allowlist โดเมน newScience เท่านั้น
        if ($returnUrl !== null && $returnUrl !== '') {
            $parsed = parse_url($returnUrl);
            $host = $parsed['host'] ?? '';
            if (in_array($host, $allowedHosts, true)) {
                return redirect()->to($returnUrl);
            }
        }

        // 3. redirect ไป newScience เสมอ
        $newscienceUrl = $this->getNewscienceLogoutReturnUrl();
        return redirect()->to($newscienceUrl);
    }
}

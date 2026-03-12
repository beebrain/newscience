<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Libraries\AccessControl;

/**
 * ตรวจสอบสิทธิ์การเข้าถึงแต่ละส่วนของ Admin ตาม user_system_access (และ role)
 * ต้องรันหลัง adminauth (ผู้ใช้ล็อกอินและมี role admin/editor/faculty_admin/super_admin แล้ว)
 *
 * Faculty Admin (role = faculty_admin): มีสิทธิ์ admin_core โดยอัตโนมัติ (ดู AccessControl)
 * จึงเข้าได้ทุก Feature ที่อนุญาตผ่าน admin_core ได้แก่
 * - จัดการดาวน์โหลดคณะ (admin/downloads)
 * - ประกาศด่วน ป๊อปอัป (admin/urgent-popups)
 * - ประกาศข่าว (admin/news), องค์กร, หลักสูตร, Hero Slides, Events
 *
 * แมป URI -> system slug:
 * - admin/news -> admin_news (หรือ admin_core)
 * - admin/organization, programs, hero-slides, events -> admin_core
 * - admin/urgent-popups -> admin_urgent_popup (หรือ admin_core)
 * - admin/downloads -> admin_downloads (หรือ admin_core)
 * - admin/users -> user_management
 * - admin/settings -> site_settings
 * - admin/cert-templates, cert-events, certificates -> ecert
 */
class AdminSystemAccessFilter implements FilterInterface
{
    /** @var array URI segment หลัง admin/ -> system_slug (เฉพาะ path ที่ต้องเช็คสิทธิ์) */
    private const URI_TO_SYSTEM = [
        'news'           => 'admin_news',   // สิทธิ์แยก: ประกาศข่าว (หรือ admin_core ก็เข้าได้)
        'organization'   => 'admin_core',
        'programs'       => 'admin_core',
        'hero-slides'    => 'admin_core',
        'events'         => 'admin_core',
        'urgent-popups'  => 'admin_urgent_popup',  // จัดการป๊อปอัปประกาศด่วน (หรือ admin_core)
        'downloads'      => 'admin_downloads',     // จัดการไฟล์ดาวน์โหลดคณะ (หรือ admin_core)
        'academic-services' => 'academic_service',  // ข้อมูลการบริการวิชาการ (หรือ admin_core)
        'users'          => 'user_management',
        'settings'       => 'site_settings',
        'cert-templates' => 'ecert',
        'cert-events'    => 'ecert',
        'certificates'   => 'ecert',
    ];

    public function before(RequestInterface $request, $arguments = null): ?ResponseInterface
    {
        $session = session();
        $adminId = $session->get('admin_id');
        if ($adminId === null) {
            return redirect()->to(base_url('admin/login'))->with('error', 'กรุณาเข้าสู่ระบบ');
        }

        $uri = trim($request->getUri()->getPath(), '/');

        // utility/* ใช้สิทธิ์ระบบ utility
        if (strpos($uri, 'utility') === 0) {
            if (AccessControl::hasAccess((int) $adminId, 'utility')) {
                return null;
            }
            log_message('debug', 'AdminSystemAccessFilter: access denied (utility). admin_id=' . $adminId . ' uri=' . $uri);
            return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์เข้าใช้ส่วนนี้');
        }

        $segment = $this->uriToFirstSegment($uri);
        if ($segment === null) {
            return null;
        }

        $slug = self::URI_TO_SYSTEM[$segment] ?? null;
        if ($slug === null) {
            return null;
        }

        $allowed = false;
        if ($slug === 'admin_news') {
            // หน้าประกาศข่าว: เข้าได้ถ้ามี admin_news หรือ admin_core
            $allowed = AccessControl::hasAccess((int) $adminId, 'admin_news')
                || AccessControl::hasAccess((int) $adminId, 'admin_core');
        } elseif ($slug === 'admin_urgent_popup') {
            // จัดการป๊อปอัปประกาศด่วน: เข้าได้ถ้ามี admin_urgent_popup หรือ admin_core
            $allowed = AccessControl::hasAccess((int) $adminId, 'admin_urgent_popup')
                || AccessControl::hasAccess((int) $adminId, 'admin_core');
        } elseif ($slug === 'admin_downloads') {
            // จัดการไฟล์ดาวน์โหลดคณะ: เข้าได้ถ้ามี admin_downloads หรือ admin_core
            $allowed = AccessControl::hasAccess((int) $adminId, 'admin_downloads')
                || AccessControl::hasAccess((int) $adminId, 'admin_core');
        } elseif ($slug === 'academic_service') {
            // ข้อมูลการบริการวิชาการ: เข้าได้ถ้ามี academic_service หรือ admin_core
            $allowed = AccessControl::hasAccess((int) $adminId, 'academic_service')
                || AccessControl::hasAccess((int) $adminId, 'admin_core');
        } else {
            $allowed = AccessControl::hasAccess((int) $adminId, $slug);
        }

        if ($allowed) {
            return null;
        }

        log_message('debug', 'AdminSystemAccessFilter: access denied. admin_id=' . $adminId . ' segment=' . $segment . ' uri=' . $uri);
        return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์เข้าใช้ส่วนนี้');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }

    private function uriToFirstSegment(string $uri): ?string
    {
        $uri = trim($uri, '/');
        if (strpos($uri, 'utility') === 0) {
            return 'utility';
        }
        if (strpos($uri, 'admin/') !== 0) {
            return null;
        }
        $after = substr($uri, 6); // หลัง "admin/"
        return strpos($after, '/') !== false ? strstr($after, '/', true) : $after;
    }
}

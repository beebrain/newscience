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
 * แมป URI -> system slug:
 * - admin/news, organization, programs, hero-slides, events -> admin_core
 * - admin/users -> user_management
 * - admin/settings -> site_settings
 * - admin/cert-templates, cert-events, certificates -> ecert
 */
class AdminSystemAccessFilter implements FilterInterface
{
    /** @var array URI segment หลัง admin/ -> system_slug (เฉพาะ path ที่ต้องเช็คสิทธิ์) */
    private const URI_TO_SYSTEM = [
        'news'           => 'admin_core',
        'organization'   => 'admin_core',
        'programs'       => 'admin_core',
        'hero-slides'    => 'admin_core',
        'events'         => 'admin_core',
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

        $uri = $request->getUri()->getPath();
        $slug = $this->uriToSystemSlug($uri);
        if ($slug === null) {
            return null; // ไม่ได้อยู่ในรายการที่ต้องเช็ค (หรือ path อื่นให้ผ่าน)
        }

        if (AccessControl::hasAccess((int) $adminId, $slug)) {
            return null;
        }

        log_message('debug', 'AdminSystemAccessFilter: access denied. admin_id=' . $adminId . ' slug=' . $slug . ' uri=' . $uri);
        return redirect()->to(base_url('dashboard'))->with('error', 'คุณไม่มีสิทธิ์เข้าใช้ส่วนนี้');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): ResponseInterface
    {
        return $response;
    }

    private function uriToSystemSlug(string $uri): ?string
    {
        $uri = trim($uri, '/');
        if (strpos($uri, 'utility') === 0) {
            return 'utility';
        }
        if (strpos($uri, 'admin/') !== 0) {
            return null;
        }
        $after = substr($uri, 6); // หลัง "admin/"
        $segment = strpos($after, '/') !== false ? strstr($after, '/', true) : $after;
        return self::URI_TO_SYSTEM[$segment] ?? 'admin_core'; // default กลุ่มหลัก = admin_core
    }
}

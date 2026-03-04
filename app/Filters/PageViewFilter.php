<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * After filter: records page views for Executive Dashboard analytics.
 * Skips API, assets, and non-GET requests.
 */
class PageViewFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // No-op
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($request->getMethod() !== 'get') {
            return $response;
        }

        $uri = $request->getUri();
        $path = $uri->getPath();

        // Skip API routes
        if (strpos($path, 'api/') === 0 || strpos($path, '/api/') !== false) {
            return $response;
        }
        // Skip asset/serve routes
        if (strpos($path, 'serve/') === 0 || strpos($path, '/serve/') !== false) {
            return $response;
        }
        if (strpos($path, 'assets/') === 0 || strpos($path, '/assets/') !== false) {
            return $response;
        }
        // Skip AJAX (e.g. dashboard loading its own data)
        if ($request->hasHeader('X-Requested-With') && strtolower($request->getHeaderLine('X-Requested-With')) === 'xmlhttprequest') {
            return $response;
        }

        try {
            $db = \Config\Database::connect();
            if (! $db->tableExists('page_views')) {
                return $response;
            }
        } catch (\Throwable $e) {
            return $response;
        }

        $session = session();
        $userType = 'guest';
        $userId  = null;
        if ($session->get('admin_logged_in')) {
            $userType = 'admin';
            $userId   = $session->get('admin_id');
        } elseif ($session->get('student_logged_in')) {
            $userType = 'student';
            $userId   = $session->get('student_uid');
        }

        $route = $request->getUri()->getPath();
        $url   = $uri->getScheme() . '://' . $uri->getHost() . ($uri->getPort() && ! in_array($uri->getPort(), [80, 443], true) ? ':' . $uri->getPort() : '') . $uri->getPath();
        if ($uri->getQuery()) {
            $url .= '?' . $uri->getQuery();
        }
        if (strlen($url) > 500) {
            $url = substr($url, 0, 500);
        }

        $userAgent = method_exists($request, 'getUserAgent') ? $request->getUserAgent() : null;
        $userAgent = $userAgent && method_exists($userAgent, 'getAgent') ? $userAgent->getAgent() : '';
        if (strlen($userAgent) > 500) {
            $userAgent = substr($userAgent, 0, 500);
        }

        $sessionId = function_exists('session_id') ? session_id() : '';
        $routeNorm = strlen($route) > 255 ? substr($route, 0, 255) : $route;

        try {
            $model = new \App\Models\PageViewModel();
            // ป้องกันการนับซ้ำเมื่อผู้ใช้กดรีเฟรช: ถ้า session เดียวกันดู path เดียวกันภายใน 30 นาที ไม่บันทึกซ้ำ
            if ($model->hasRecentView($sessionId, $routeNorm, 30)) {
                return $response;
            }
            $data = [
                'url'         => $url,
                'route'       => $routeNorm,
                'ip_address'  => $request->getIPAddress(),
                'user_agent'  => $userAgent,
                'session_id'  => $sessionId,
                'user_id'     => $userId,
                'user_type'   => $userType,
                'created_at'  => date('Y-m-d H:i:s'),
            ];
            $model->recordView($data);
        } catch (\Throwable $e) {
            log_message('error', 'PageViewFilter: ' . $e->getMessage());
        }

        return $response;
    }
}

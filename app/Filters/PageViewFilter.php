<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * After filter: records public website page views for admin analytics reports.
 * Skips APIs, assets, internal apps, XHR, and non-GET requests.
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
        $path = $this->normalizePath($uri->getPath());

        if ($this->shouldSkipPath($path)) {
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

        $route = $path;
        $url   = $uri->getScheme() . '://' . $uri->getHost() . ($uri->getPort() && ! in_array($uri->getPort(), [80, 443], true) ? ':' . $uri->getPort() : '') . $uri->getPath();
        if ($uri->getQuery()) {
            $url .= '?' . $uri->getQuery();
        }
        if (strlen($url) > 500) {
            $url = substr($url, 0, 500);
        }

        $userAgent = $request->getHeaderLine('User-Agent');
        if (strlen($userAgent) > 500) {
            $userAgent = substr($userAgent, 0, 500);
        }

        $sessionId = function_exists('session_id') ? session_id() : '';
        $routeNorm = strlen($route) > 255 ? substr($route, 0, 255) : $route;
        $sourceInfo = $this->classifyTrafficSource($request, $uri->getHost());
        $agentInfo = $this->summarizeUserAgent($userAgent);
        $contentInfo = $this->classifyContent($db, $routeNorm);

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
            $analyticsData = array_merge(
                $contentInfo,
                $sourceInfo,
                $agentInfo,
                $this->extractUtmParams($request)
            );
            foreach ($analyticsData as $field => $value) {
                if ($db->fieldExists($field, 'page_views')) {
                    $data[$field] = $value;
                }
            }
            $model->recordView($data);
        } catch (\Throwable $e) {
            log_message('error', 'PageViewFilter: ' . $e->getMessage());
        }

        return $response;
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path === '/') {
            return '/';
        }
        return '/' . trim($path, '/');
    }

    private function shouldSkipPath(string $path): bool
    {
        $segmentPath = trim($path, '/');
        if ($segmentPath === '') {
            return false;
        }

        $skipPrefixes = [
            'api',
            'assets',
            'serve',
            'admin',
            'dashboard',
            'student',
            'student-admin',
            'program-admin',
            'edoc',
            'evaluate',
            'utility',
            'dev',
            'exam',
            'oauth',
            'verify',
        ];

        foreach ($skipPrefixes as $prefix) {
            if ($segmentPath === $prefix || strpos($segmentPath, $prefix . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    private function classifyContent($db, string $path): array
    {
        $cleanPath = trim($path, '/');
        if ($cleanPath === '') {
            return [
                'content_type' => 'home',
                'content_id' => null,
                'content_title_snapshot' => 'หน้าแรก',
            ];
        }

        if (preg_match('#^news/(\d+)$#', $cleanPath, $matches)) {
            return $this->contentFromTable($db, 'news', (int) $matches[1], 'news', 'title');
        }

        if (preg_match('#^events/(\d+)$#', $cleanPath, $matches)) {
            return $this->contentFromTable($db, 'events', (int) $matches[1], 'event', 'title');
        }

        if (preg_match('#^(p|program|program-site)/(\d+)$#', $cleanPath, $matches)) {
            return $this->programContent($db, (int) $matches[2]);
        }

        $staticTitles = [
            'about' => ['static', 'เกี่ยวกับคณะ'],
            'academics' => ['static', 'วิชาการ'],
            'research' => ['static', 'งานวิจัย'],
            'campus-life' => ['static', 'ชีวิตในรั้วคณะ'],
            'admission' => ['static', 'รับสมัครนักศึกษา'],
            'news' => ['news_list', 'รายการข่าว'],
            'events' => ['event_list', 'รายการกิจกรรม'],
            'calendar' => ['static', 'ปฏิทินกิจกรรม'],
            'contact' => ['static', 'ติดต่อเรา'],
            'complaints' => ['static', 'ร้องเรียน/ข้อเสนอแนะ'],
            'personnel' => ['static', 'บุคลากร'],
            'executives' => ['static', 'ผู้บริหาร'],
            'documents' => ['document', 'เอกสารเผยแพร่'],
            'support-documents' => ['document', 'เอกสารสนับสนุน'],
            'official-documents' => ['document', 'เอกสารราชการ'],
            'promotion-criteria' => ['document', 'หลักเกณฑ์การขอตำแหน่ง'],
            'internal-documents' => ['document', 'เอกสารภายใน'],
        ];

        if (isset($staticTitles[$cleanPath])) {
            return [
                'content_type' => $staticTitles[$cleanPath][0],
                'content_id' => null,
                'content_title_snapshot' => $staticTitles[$cleanPath][1],
            ];
        }

        return [
            'content_type' => 'other',
            'content_id' => null,
            'content_title_snapshot' => $this->truncate($path, 255),
        ];
    }

    private function contentFromTable($db, string $table, int $id, string $type, string $titleColumn): array
    {
        $title = null;
        if ($db->tableExists($table) && $db->fieldExists($titleColumn, $table)) {
            $row = $db->table($table)->select($titleColumn)->where('id', $id)->get()->getRowArray();
            $title = $row[$titleColumn] ?? null;
        }

        return [
            'content_type' => $type,
            'content_id' => $id,
            'content_title_snapshot' => $this->truncate((string) ($title ?: $type . ' #' . $id), 255),
        ];
    }

    private function programContent($db, int $id): array
    {
        $title = null;
        if ($db->tableExists('programs')) {
            $row = $db->table('programs')->select('name_th, name_en')->where('id', $id)->get()->getRowArray();
            $title = $row['name_th'] ?? $row['name_en'] ?? null;
        }

        return [
            'content_type' => 'program',
            'content_id' => $id,
            'content_title_snapshot' => $this->truncate((string) ($title ?: 'หลักสูตร #' . $id), 255),
        ];
    }

    private function classifyTrafficSource(RequestInterface $request, string $currentHost): array
    {
        $referer = $request->getHeaderLine('Referer');
        if ($referer === '') {
            return ['referrer_host' => null, 'traffic_source' => 'direct'];
        }

        $host = parse_url($referer, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return ['referrer_host' => null, 'traffic_source' => 'direct'];
        }

        $host = strtolower($host);
        $currentHost = strtolower($currentHost);
        if ($host === $currentHost) {
            return ['referrer_host' => $this->truncate($host, 255), 'traffic_source' => 'internal'];
        }

        $searchHosts = ['google.', 'bing.', 'yahoo.', 'duckduckgo.', 'baidu.'];
        foreach ($searchHosts as $needle) {
            if (strpos($host, $needle) !== false) {
                return ['referrer_host' => $this->truncate($host, 255), 'traffic_source' => 'search'];
            }
        }

        $socialHosts = ['facebook.', 'm.facebook.', 'line.me', 'twitter.', 'x.com', 'instagram.', 'linkedin.', 'youtube.', 'tiktok.'];
        foreach ($socialHosts as $needle) {
            if (strpos($host, $needle) !== false) {
                return ['referrer_host' => $this->truncate($host, 255), 'traffic_source' => 'social'];
            }
        }

        return ['referrer_host' => $this->truncate($host, 255), 'traffic_source' => 'referral'];
    }

    private function summarizeUserAgent(string $userAgent): array
    {
        $ua = strtolower($userAgent);
        $device = 'desktop';
        if (strpos($ua, 'bot') !== false || strpos($ua, 'crawl') !== false || strpos($ua, 'spider') !== false) {
            $device = 'bot';
        } elseif (strpos($ua, 'tablet') !== false || strpos($ua, 'ipad') !== false) {
            $device = 'tablet';
        } elseif (strpos($ua, 'mobile') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'android') !== false) {
            $device = 'mobile';
        }

        $browser = 'Other';
        if (strpos($userAgent, 'Edg/') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'OPR/') !== false || strpos($userAgent, 'Opera') !== false) {
            $browser = 'Opera';
        } elseif (strpos($userAgent, 'Chrome/') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Firefox/') !== false) {
            $browser = 'Firefox';
        } elseif (strpos($userAgent, 'Safari/') !== false) {
            $browser = 'Safari';
        }

        $os = 'Other';
        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Mac OS') !== false || strpos($userAgent, 'Macintosh') !== false) {
            $os = 'macOS';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iPhone') !== false || strpos($userAgent, 'iPad') !== false) {
            $os = 'iOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        }

        return [
            'device_type' => $device,
            'browser_family' => $browser,
            'os_family' => $os,
        ];
    }

    private function extractUtmParams(RequestInterface $request): array
    {
        $params = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $field) {
            $value = $request instanceof IncomingRequest ? $request->getGet($field) : null;
            $params[$field] = is_string($value) && $value !== '' ? $this->truncate($value, 150) : null;
        }
        return $params;
    }

    private function truncate(string $value, int $length): string
    {
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $length, 'UTF-8');
        }
        return substr($value, 0, $length);
    }
}

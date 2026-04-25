<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Page view tracking for Executive Dashboard analytics.
 */
class PageViewModel extends Model
{
    protected $table         = 'page_views';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'url',
        'route',
        'content_type',
        'content_id',
        'content_title_snapshot',
        'ip_address',
        'user_agent',
        'referrer_host',
        'traffic_source',
        'device_type',
        'browser_family',
        'os_family',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'session_id',
        'user_id',
        'user_type',
        'created_at',
    ];

    /**
     * Check if this session already viewed this path recently (to avoid counting refresh).
     *
     * @param string $sessionId Session ID
     * @param string $path      URL path (no query string), e.g. /news
     * @param int    $windowMinutes Within this many minutes count as same view (default 30)
     * @return bool True if a recent view exists (should not record again)
     */
    public function hasRecentView(string $sessionId, string $path, int $windowMinutes = 30): bool
    {
        if ($sessionId === '' || $path === '') {
            return false;
        }
        $since = date('Y-m-d H:i:s', time() - ($windowMinutes * 60));
        $count = $this->where('session_id', $sessionId)
            ->where('route', $path)
            ->where('created_at >=', $since)
            ->countAllResults();
        return $count > 0;
    }

    /**
     * Record a page view (called from PageViewFilter).
     * Call hasRecentView() first to avoid counting refresh.
     */
    public function recordView(array $data): bool
    {
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        return $this->insert($data) !== false;
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\NewsTagModel;
use App\Models\EventModel;

/**
 * API Controller
 * Provides JSON endpoints for AJAX data fetching
 */
class Api extends BaseController
{
    protected $newsModel;
    protected $newsTagModel;
    protected $eventModel;

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        $this->newsTagModel = new NewsTagModel();
        $this->eventModel = new EventModel();
    }

    /**
     * Attach tags to each article (if news_tags table exists). 1 ข่าวมีได้หลาย tag.
     */
    protected function attachTagsToArticles(array $articles): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            foreach ($articles as &$a) {
                $a['tags'] = [];
            }
            return $articles;
        }
        foreach ($articles as &$a) {
            $id = is_array($a) ? ($a['id'] ?? null) : null;
            $a['tags'] = $id ? $this->newsTagModel->getTagsByNewsId((int) $id) : [];
        }
        return $articles;
    }

    /**
     * Format featured image URL (รูปเต็ม) — ใช้ในหน้ารายละเอียดข่าว
     * Handles: full URLs, newsimages/, uploads/news/, and plain filenames
     */
    protected function formatFeaturedImage($imagePath)
    {
        return $this->formatFeaturedImageUrl($imagePath, false);
    }

    /**
     * Format featured image URL เป็น thumbnail — ใช้ในรายการข่าว (หน้าแรก, หน้ารายการข่าว)
     * รูปอยู่ใน writable/uploads/news/thumbs/ หรือ fallback เป็นรูปเต็ม
     */
    protected function formatFeaturedImageThumb($imagePath)
    {
        return $this->formatFeaturedImageUrl($imagePath, true);
    }

    /**
     * @param string $imagePath path หรือ URL
     * @param bool $useThumb true = URL ไปที่ serve/thumb/news/ (สำหรับรายการ), false = รูปเต็ม (สำหรับหน้ารายละเอียด)
     */
    protected function formatFeaturedImageUrl($imagePath, bool $useThumb)
    {
        if (empty($imagePath) || trim($imagePath) === '') {
            return '';
        }
        $imagePath = trim($imagePath);

        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }

        if (strpos($imagePath, 'newsimages/') === 0) {
            return base_url($imagePath);
        }

        $basename = basename($imagePath);
        if (strpos($imagePath, 'uploads/') === 0) {
            return $useThumb
                ? base_url('serve/thumb/news/' . $basename)
                : base_url('serve/uploads/news/' . $basename);
        }

        $newsImagesPath = FCPATH . 'newsimages/' . $imagePath;
        if (file_exists($newsImagesPath)) {
            return base_url('newsimages/' . $imagePath);
        }

        return $useThumb
            ? base_url('serve/thumb/news/' . $basename)
            : base_url('serve/uploads/news/' . basename($imagePath));
    }

    /**
     * Get news articles with pagination
     * GET /api/news
     * 
     * Query params:
     * - page: page number (default: 1)
     * - limit: items per page (default: 6)
     * - status: filter by status (default: published)
     */
    public function news()
    {
        $page = (int) $this->request->getGet('page') ?? 1;
        $limit = (int) $this->request->getGet('limit') ?? 6;
        $status = $this->request->getGet('status') ?? 'published';

        $page = max(1, $page);
        $limit = min(50, max(1, $limit));
        $offset = ($page - 1) * $limit;

        // Get total count
        $total = $this->newsModel->where('news.status', $status)->countAllResults(false);

        // Get news articles
        $news = $this->newsModel
            ->select('news.*, user.gf_name, user.gl_name')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->where('news.status', $status)
            ->orderBy('news.published_at', 'DESC')
            ->limit($limit, $offset)
            ->find();

        // Format the response
        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 150) . '...',
                'content' => $article['content'],
                'featured_image' => $this->formatFeaturedImageThumb($article['featured_image'] ?? ''),
                'author' => trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? '')),
                'published_at' => $article['published_at'],
                'formatted_date' => date('M j, Y', strtotime($article['published_at'] ?? $article['created_at']))
            ];
        }
        $data = $this->attachTagsToArticles($data);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $limit,
                'total' => $total,
                'total_pages' => ceil($total / $limit),
                'has_more' => ($page * $limit) < $total
            ]
        ]);
    }

    /**
     * Get single news article
     * GET /api/news/:id
     */
    public function newsDetail($id)
    {
        $news = $this->newsModel
            ->select('news.*, user.gf_name, user.gl_name')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->where('news.id', $id)
            ->first();

        if (!$news) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'News article not found'
            ]);
        }

        // Increment view count
        $this->newsModel->incrementViews($id);

        // Get images
        $imageModel = new \App\Models\NewsImageModel();
        $images = $imageModel->getImagesByNewsId($id);

        $article = [
            'id' => $news['id'],
            'title' => $news['title'],
            'slug' => $news['slug'],
            'content' => $news['content'],
            'excerpt' => $news['excerpt'],
            'featured_image' => $this->formatFeaturedImage($news['featured_image'] ?? ''),
            'author' => trim(($news['gf_name'] ?? '') . ' ' . ($news['gl_name'] ?? '')),
            'published_at' => $news['published_at'],
            'formatted_date' => date('F j, Y', strtotime($news['published_at'] ?? $news['created_at'])),
            'view_count' => $news['view_count'],
            'images' => array_map(function ($img) {
                return [
                    'id' => $img['id'],
                    'url' => base_url('serve/uploads/news/' . basename($img['image_path'])),
                    'caption' => $img['caption']
                ];
            }, $images)
        ];
        $withTags = $this->attachTagsToArticles([$article]);
        $article = $withTags[0];

        return $this->response->setJSON([
            'success' => true,
            'data' => $article
        ]);
    }

    /**
     * Get featured news (latest 3)
     * GET /api/news/featured
     */
    public function newsFeatured()
    {
        $news = $this->newsModel
            ->select('news.*, user.gf_name, user.gl_name')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->where('news.status', 'published')
            ->orderBy('news.published_at', 'DESC')
            ->limit(3)
            ->find();

        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 100) . '...',
                'featured_image' => $this->formatFeaturedImageThumb($article['featured_image'] ?? ''),
                'formatted_date' => date('M j, Y', strtotime($article['published_at'] ?? $article['created_at']))
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get news by tag slug (ใช้เฉพาะ news_tags + news_news_tags)
     * GET /api/news/tag/:segment
     *
     * Query params:
     * - limit: number of items (default: 6)
     */
    public function newsByTag($tagSlug = null)
    {
        if (!$tagSlug) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Tag slug is required'
            ]);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 6);
        $limit = min(50, max(1, $limit));
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'tag' => $tagSlug,
                'count' => 0
            ]);
        }

        $tagRow = $this->newsTagModel->findBySlug($tagSlug);
        $news = [];
        if ($tagRow) {
            $news = $this->newsModel
                ->select('news.*, user.gf_name, user.gl_name')
                ->join('news_news_tags', 'news_news_tags.news_id = news.id')
                ->join('news_tags', 'news_tags.id = news_news_tags.news_tag_id')
                ->join('user', 'user.uid = news.author_id', 'left')
                ->where('news.status', 'published')
                ->where('news_tags.slug', $tagSlug)
                ->orderBy('news.published_at', 'DESC')
                ->groupBy('news.id')
                ->limit($limit)
                ->find();
        }

        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 150) . '...',
                'content' => $article['content'] ?? '',
                'featured_image' => $this->formatFeaturedImageThumb($article['featured_image'] ?? ''),
                'author' => trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? '')),
                'published_at' => $article['published_at'],
                'formatted_date' => date('d M Y', strtotime($article['published_at'] ?? $article['created_at'])),
                'primary_tag' => $tagSlug
            ];
        }
        $data = $this->attachTagsToArticles($data);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'tag' => $tagSlug,
            'count' => count($data)
        ]);
    }

    /**
     * Get research news (ข่าววิจัย) - filters by 'research' or 'research_grant' tags
     * GET /api/news/research
     * 
     * Query params:
     * - limit: number of items (default: 6)
     */
    public function newsResearch()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 6);
        $limit = min(50, max(1, $limit));

        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            return $this->response->setJSON([
                'success' => true,
                'data' => [],
                'category' => 'research',
                'count' => 0
            ]);
        }

        // Get news that has either 'research' or 'research_grant' tags
        $news = $this->newsModel
            ->select('news.*, user.gf_name, user.gl_name')
            ->join('news_news_tags', 'news_news_tags.news_id = news.id')
            ->join('news_tags', 'news_tags.id = news_news_tags.news_tag_id')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->where('news.status', 'published')
            ->groupStart()
            ->where('news_tags.slug', 'research')
            ->orWhere('news_tags.slug', 'research_grant')
            ->groupEnd()
            ->orderBy('news.published_at', 'DESC')
            ->groupBy('news.id')
            ->limit($limit)
            ->find();

        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? mb_substr(strip_tags($article['content'] ?? ''), 0, 150) . '...',
                'content' => $article['content'] ?? '',
                'featured_image' => $this->formatFeaturedImageThumb($article['featured_image'] ?? ''),
                'author' => trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? '')),
                'published_at' => $article['published_at'],
                'formatted_date' => date('d M Y', strtotime($article['published_at'] ?? $article['created_at'])),
                'category' => 'research'
            ];
        }
        $data = $this->attachTagsToArticles($data);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'category' => 'research',
            'count' => count($data)
        ]);
    }

    /**
     * Get list of news tags (ชนิดข่าว) for filter / admin
     * GET /api/news-tags
     */
    public function newsTags()
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags')) {
            return $this->response->setJSON(['success' => true, 'data' => []]);
        }
        $tags = $this->newsTagModel->getAllOrdered();
        return $this->response->setJSON(['success' => true, 'data' => $tags]);
    }

    /**
     * Search news
     * GET /api/news/search?q=keyword
     */
    public function newsSearch()
    {
        $query = $this->request->getGet('q');
        $limit = (int) ($this->request->getGet('limit') ?? 10);

        if (empty($query) || strlen($query) < 2) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Search query must be at least 2 characters'
            ]);
        }

        $news = $this->newsModel
            ->where('news.status', 'published')
            ->groupStart()
            ->like('news.title', $query)
            ->orLike('news.content', $query)
            ->groupEnd()
            ->orderBy('news.published_at', 'DESC')
            ->limit($limit)
            ->find();

        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => mb_substr(strip_tags($article['content']), 0, 100) . '...',
                'featured_image' => $this->formatFeaturedImageThumb($article['featured_image'] ?? '')
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'query' => $query,
            'count' => count($data),
            'data' => $data
        ]);
    }

    /**
     * Get website statistics
     * GET /api/stats
     */
    public function stats()
    {
        $newsCount = $this->newsModel->where('news.status', 'published')->countAllResults();

        $programModel = new \App\Models\ProgramModel();
        $departmentModel = new \App\Models\DepartmentModel();

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'news_count' => $newsCount,
                'programs' => $programModel->where('status', 'active')->countAllResults(),
                'departments' => $departmentModel->where('status', 'active')->countAllResults(),
                'students' => '15,000+',
                'faculty' => '500+'
            ]
        ]);
    }

    /**
     * Get personnel/staff list
     * GET /api/personnel
     */
    public function personnel()
    {
        $personnelModel = new \App\Models\PersonnelModel();
        $personnel = $personnelModel->getActive();

        $data = [];
        foreach ($personnel as $person) {
            $data[] = [
                'id' => $person['id'],
                'full_name' => $personnelModel->getFullName($person),
                'position' => $person['position'],
                'department_id' => $person['department_id'],
                'email' => $person['email'],
                'phone' => $person['phone'],
                'image' => $person['image'] ? base_url('serve/thumb/staff/' . basename($person['image'])) : null
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get dean information
     * GET /api/personnel/dean
     */
    public function dean()
    {
        $personnelModel = new \App\Models\PersonnelModel();
        $dean = $personnelModel->getDean();

        if (!$dean) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dean not found'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => [
                'id' => $dean['id'],
                'full_name' => $personnelModel->getFullName($dean),
                'position' => $dean['position'],
                'image' => $dean['image'] ? base_url('serve/thumb/staff/' . basename($dean['image'])) : null,
                'bio' => $dean['bio']
            ]
        ]);
    }

    /**
     * Get executives/organization structure (tiers + program chairs) for Ajax progressive load.
     * GET /api/executives
     */
    public function executives()
    {
        $data = \App\Libraries\ExecutivesData::getExecutivesData();
        $personnelModel = new \App\Models\PersonnelModel();

        $normalizePerson = function (array $p) use ($personnelModel) {
            $img = trim($p['image'] ?? '');
            $imageUrl = $img !== '' ? base_url('serve/thumb/staff/' . basename(str_replace('\\', '/', $img))) : null;
            if ($imageUrl !== null && strpos($img, 'http') === 0) {
                $imageUrl = $img;
            }
            $fullName = trim($personnelModel->getFullName($p));
            $academicTitle = trim($p['academic_title'] ?? '');
            $name = $academicTitle !== '' ? $academicTitle . ' ' . $fullName : $fullName;
            return [
                'id' => (int) ($p['id'] ?? 0),
                'name' => $name,
                'position' => trim($p['position'] ?? ''),
                'position_en' => trim($p['position_en'] ?? ''),
                'position_detail' => trim($p['position_detail'] ?? ''),
                'image' => $imageUrl,
            ];
        };

        $data['tier1'] = array_map($normalizePerson, $data['tier1']);
        $data['tier2'] = array_map($normalizePerson, $data['tier2']);
        $data['tier3'] = array_map($normalizePerson, $data['tier3']);
        $data['headOffice'] = array_map($normalizePerson, $data['headOffice'] ?? []);
        $data['headResearch'] = array_map($normalizePerson, $data['headResearch'] ?? []);
        $data['programChairs'] = array_map(function ($item) use ($normalizePerson) {
            $person = $item['person'] ?? null;
            if (! is_array($person)) {
                $person = [];
            }
            return [
                'program_name' => $item['program_name'] ?? '',
                'person' => $normalizePerson($person),
            ];
        }, $data['programChairs'] ?? []);

        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get departments list
     * GET /api/departments
     */
    public function departments()
    {
        $departmentModel = new \App\Models\DepartmentModel();
        $departments = $departmentModel->getActive();

        return $this->response->setJSON([
            'success' => true,
            'data' => $departments
        ]);
    }

    /**
     * Get programs/curriculum list
     * GET /api/programs
     */
    public function programs()
    {
        $programModel = new \App\Models\ProgramModel();
        $level = $this->request->getGet('level');

        if ($level) {
            $programs = $programModel->getByLevel($level);
        } else {
            $programs = $programModel->getWithDepartment();
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $programs
        ]);
    }

    /**
     * Get site settings
     * GET /api/settings
     */
    public function settings()
    {
        $settingModel = new \App\Models\SiteSettingModel();
        $settings = $settingModel->getAll();

        return $this->response->setJSON([
            'success' => true,
            'data' => $settings
        ]);
    }

    /**
     * Get active hero slides
     * GET /api/hero-slides
     */
    public function heroSlides()
    {
        $heroSlideModel = new \App\Models\HeroSlideModel();
        $slides = $heroSlideModel->getActiveSlides();

        $data = [];
        foreach ($slides as $slide) {
            $data[] = [
                'id' => $slide['id'],
                'title' => $slide['title'],
                'subtitle' => $slide['subtitle'],
                'description' => $slide['description'],
                'image' => $slide['image'] ? base_url('serve/uploads/hero/' . basename($slide['image'])) : '',
                'link' => $slide['link'],
                'link_text' => $slide['link_text'] ?: 'ดูรายละเอียด',
                'show_buttons' => (bool)$slide['show_buttons'],
            ];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Format event featured image URL
     */
    protected function formatEventImage(?string $imagePath): string
    {
        if (empty($imagePath) || trim($imagePath) === '') {
            return '';
        }
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        return base_url('serve/uploads/events/' . $imagePath);
    }

    /**
     * Get upcoming events (event_date >= today, published) + news marked as "Event ที่จะเกิดขึ้น" (display_as_event=1).
     * GET /api/events/upcoming?limit=4
     */
    public function eventsUpcoming()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 10);
        $limit = min(50, max(1, $limit));
        $rows = [];
        $db = \Config\Database::connect();

        try {
            if ($db->tableExists('events')) {
                foreach ($this->eventModel->getUpcoming($limit * 2) as $e) {
                    $rows[] = [
                        'sort_date' => ($e['event_date'] ?? '') . ($e['event_time'] ?? ' 00:00:00'),
                        'id' => $e['id'],
                        'title' => $e['title'] ?? '',
                        'slug' => $e['slug'] ?? '',
                        'excerpt' => $e['excerpt'] ?? '',
                        'event_date' => $e['event_date'] ?? null,
                        'event_time' => $e['event_time'] ?? null,
                        'event_end_date' => $e['event_end_date'] ?? null,
                        'event_end_time' => $e['event_end_time'] ?? null,
                        'location' => $e['location'] ?? '',
                        'featured_image' => $this->formatEventImage($e['featured_image'] ?? null),
                        'formatted_date' => isset($e['event_date']) ? date('j M Y', strtotime($e['event_date'])) : '',
                        'formatted_time' => !empty($e['event_time']) ? date('g:i A', strtotime($e['event_time'])) : '',
                    ];
                }
            }
            if ($db->fieldExists('display_as_event', 'news')) {
                foreach ($this->newsModel->getPublishedMarkedAsEvent($limit * 2) as $n) {
                    $pub = $n['published_at'] ?? '';
                    $rows[] = [
                        'sort_date' => $pub,
                        'id' => $n['id'],
                        'title' => $n['title'] ?? '',
                        'slug' => $n['slug'] ?? '',
                        'excerpt' => $n['excerpt'] ?? '',
                        'published_at' => $pub,
                        'featured_image' => $this->formatFeaturedImageThumb($n['featured_image'] ?? ''),
                        'formatted_date' => $pub ? date('j M Y', strtotime($pub)) : '',
                    ];
                }
            }
            usort($rows, function ($a, $b) {
                return strcmp($a['sort_date'] ?? '', $b['sort_date'] ?? '');
            });
            $rows = array_slice($rows, 0, $limit);
            $data = [];
            foreach ($rows as $r) {
                unset($r['sort_date']);
                $data[] = $r;
            }
        } catch (\Throwable $e) {
            log_message('error', 'Api::eventsUpcoming: ' . $e->getMessage());
            $data = [];
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $data ?? []
        ]);
    }

    /**
     * Get published events (all upcoming + optional limit)
     * GET /api/events?limit=20
     */
    public function events()
    {
        $limit = (int) ($this->request->getGet('limit') ?? 20);
        $limit = min(100, max(1, $limit));
        $events = $this->eventModel->getUpcoming($limit);
        $data = [];
        foreach ($events as $e) {
            $data[] = [
                'id' => $e['id'],
                'title' => $e['title'],
                'slug' => $e['slug'],
                'excerpt' => $e['excerpt'] ?? '',
                'event_date' => $e['event_date'],
                'event_time' => $e['event_time'],
                'event_end_date' => $e['event_end_date'],
                'event_end_time' => $e['event_end_time'],
                'location' => $e['location'] ?? '',
                'featured_image' => $this->formatEventImage($e['featured_image'] ?? null),
                'formatted_date' => date('j M Y', strtotime($e['event_date'])),
                'formatted_time' => $e['event_time'] ? date('g:i A', strtotime($e['event_time'])) : '',
            ];
        }
        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Dummy Barcode API — สร้าง JSON รหัสบาร์โค้ดสำหรับทดสอบ
     * รูปแบบบาร์โค้ด: รหัสเฉยๆ (เช่น BC001, BC002, ...)
     * GET /api/barcode-dummy?count=20
     * Response: {"barcodes": ["BC001", "BC002", ...]}
     */
    public function barcodeDummy()
    {
        $count = (int) ($this->request->getGet('count') ?? 20);
        $count = min(500, max(1, $count));
        $barcodes = [];
        for ($i = 1; $i <= $count; $i++) {
            $barcodes[] = 'BC' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
        }
        return $this->response->setJSON([
            'barcodes' => $barcodes,
        ])->setHeader('Content-Type', 'application/json');
    }
}

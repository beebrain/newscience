<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'display_as_event',
        'featured_image',
        'facebook_url',
        'author_id',
        'view_count',
        'published_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'id' => 'permit_empty|integer', // จำเป็นสำหรับ placeholder {id} ใน is_unique rule
        'title' => 'required|min_length[3]|max_length[500]',
        'slug' => 'required|is_unique[news.slug,id,{id}]',
    ];

    /**
     * Get published news
     */
    public function getPublished(int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Get published news marked as "Event ที่จะเกิดขึ้น" (display_as_event=1), upcoming only (published_at >= today).
     * Used to merge with events table in API events/upcoming.
     */
    public function getPublishedMarkedAsEvent(int $limit = 10): array
    {
        $db = \Config\Database::connect();
        if (!$db->fieldExists('display_as_event', 'news')) {
            return [];
        }
        return $this->where('status', 'published')
            ->where('display_as_event', 1)
            ->where('published_at >=', date('Y-m-d'))
            ->orderBy('published_at', 'ASC')
            ->findAll($limit);
    }

    /**
     * Get published news by tag slug (news_tags + news_news_tags only)
     */
    public function getPublishedByCategory(string $tagSlug, int $limit = 10, int $offset = 0)
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            return [];
        }
        return $this->getPublishedByTag($tagSlug, $limit, $offset);
    }

    /**
     * Get published news by tag slug (news_tags; 1 ข่าวมีได้หลาย tag)
     */
    public function getPublishedByTag(string $tagSlug, int $limit = 10, int $offset = 0)
    {
        return $this->select('news.*')
            ->join('news_news_tags', 'news_news_tags.news_id = news.id')
            ->join('news_tags', 'news_tags.id = news_news_tags.news_tag_id')
            ->where('news.status', 'published')
            ->where('news_tags.slug', $tagSlug)
            ->orderBy('news.published_at', 'DESC')
            ->groupBy('news.id')
            ->findAll($limit, $offset);
    }

    /**
     * Get news by slug
     */
    public function findBySlug(string $slug)
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Get news with author info
     */
    public function getNewsWithAuthor(int $id)
    {
        return $this->select('news.*, user.gf_name, user.gl_name, user.title as author_title')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->find($id);
    }

    /**
     * Get all news with author
     */
    public function getAllWithAuthor()
    {
        return $this->select('news.*, user.gf_name, user.gl_name')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->orderBy('news.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Generate slug from title
     */
    public function generateSlug(string $title, ?int $excludeId = null): string
    {
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $counter = 1;

        while (true) {
            $builder = $this->builder()->where('slug', $slug);
            if ($excludeId) {
                $builder->where('id !=', $excludeId);
            }

            if ($builder->countAllResults() === 0) {
                break;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Increment view count
     */
    public function incrementViews(int $id)
    {
        return $this->builder()
            ->where('id', $id)
            ->set('view_count', 'view_count + 1', false)
            ->update();
    }

    /**
     * แนะนำ tag slug จากเนื้อหาข่าว
     * Returns suggested tag slug: 'general', 'student_activity', or 'research_grant'
     */
    public function suggestCategory(array $news): string
    {
        $title = mb_strtolower($news['title'] ?? '');
        $content = mb_strtolower($news['content'] ?? '');
        $excerpt = mb_strtolower($news['excerpt'] ?? '');

        // Keywords for each category
        $keywords = [
            'student_activity' => [
                'นักศึกษา',
                'กิจกรรมนักศึกษา',
                'ค่าย',
                'ทัศนศึกษา',
                'แข่งขัน',
                'ประกวด',
                'นิทรรศการ',
                'งานแสดง',
                'การแข่งขัน',
                'รางวัล',
                'เกียรติบัตร',
                'ประกาศนียบัตร',
                'รับสมัคร',
                'สอบ',
                'สัมมนา',
                'อบรม',
                'workshop',
                'student',
                'activity',
                'กิจกรรม',
                'โครงการ',
                'ค่าย',
                'ทัศนศึกษา'
            ],
            'research_grant' => [
                'วิจัย',
                'งานวิจัย',
                'โครงการวิจัย',
                'ทุนวิจัย',
                'research',
                'grant',
                'funding',
                'ทุน',
                'scholarship',
                'fellowship',
                'ทุนสนับสนุน',
                'ผลงานวิจัย',
                'ตีพิมพ์',
                'publication',
                'journal',
                'วารสาร',
                'conference',
                'การประชุมวิชาการ',
                'symposium',
                'seminar',
                'การนำเสนอผลงาน'
            ],
            'general' => [
                'ประกาศ',
                'แจ้งเตือน',
                'ข่าว',
                'ประชาสัมพันธ์',
                'announcement',
                'notice',
                'general',
                'ทั่วไป',
                'ข้อมูล',
                'information'
            ]
        ];

        $scores = [
            'student_activity' => 0,
            'research_grant' => 0,
            'general' => 0
        ];

        // Score each category based on keyword matches
        foreach ($keywords as $category => $categoryKeywords) {
            foreach ($categoryKeywords as $keyword) {
                $keywordLower = mb_strtolower($keyword);
                // Weight: title (3x), excerpt (2x), content (1x)
                $titleCount = substr_count($title, $keywordLower) * 3;
                $excerptCount = substr_count($excerpt, $keywordLower) * 2;
                $contentCount = substr_count($content, $keywordLower) * 1;

                $scores[$category] += $titleCount + $excerptCount + $contentCount;
            }
        }

        // Find category with highest score
        $maxScore = max($scores);

        // If no strong match, default to general
        if ($maxScore === 0) {
            return 'general';
        }

        // Return category with highest score
        return array_search($maxScore, $scores);
    }

    /**
     * จัดหมวดหมู่ข่าวอัตโนมัติโดยใช้ tag (news_news_tags)
     * Returns array with statistics (by_tag)
     */
    public function autoCategorizeAll(): array
    {
        $db = \Config\Database::connect();
        if (!$db->tableExists('news_tags') || !$db->tableExists('news_news_tags')) {
            return [
                'total' => 0,
                'updated' => 0,
                'unchanged' => 0,
                'by_tag' => ['general' => 0, 'student_activity' => 0, 'research_grant' => 0]
            ];
        }

        $tagModel = model(\App\Models\NewsTagModel::class);
        $allNews = $this->findAll();
        $stats = [
            'total' => count($allNews),
            'updated' => 0,
            'unchanged' => 0,
            'by_tag' => [
                'general' => 0,
                'student_activity' => 0,
                'research_grant' => 0
            ]
        ];

        foreach ($allNews as $news) {
            $suggestedSlug = $this->suggestCategory($news);
            $tagRow = $tagModel->findBySlug($suggestedSlug);
            if (!$tagRow) {
                $tagRow = $tagModel->findBySlug('general');
            }
            $suggestedTagId = $tagRow ? (int) $tagRow['id'] : null;

            $currentTagIds = $tagModel->getTagIdsByNewsId((int) $news['id']);
            $currentHasSuggested = $suggestedTagId && in_array($suggestedTagId, $currentTagIds, true);

            if ($suggestedTagId && !$currentHasSuggested) {
                $tagModel->setTagsForNews((int) $news['id'], [$suggestedTagId]);
                $stats['updated']++;
            } else {
                $stats['unchanged']++;
            }

            if (!isset($stats['by_tag'][$suggestedSlug])) {
                $stats['by_tag'][$suggestedSlug] = 0;
            }
            $stats['by_tag'][$suggestedSlug]++;
        }

        return $stats;
    }
}

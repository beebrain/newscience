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
        'category',
        'featured_image',
        'author_id',
        'view_count',
        'published_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
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
     * Get published news by category
     */
    public function getPublishedByCategory(string $category, int $limit = 10, int $offset = 0)
    {
        return $this->where('status', 'published')
                    ->where('category', $category)
                    ->orderBy('published_at', 'DESC')
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
     * Categorize a news article based on content analysis
     * Returns suggested category: 'general', 'student_activity', or 'research_grant'
     */
    public function suggestCategory(array $news): string
    {
        $title = mb_strtolower($news['title'] ?? '');
        $content = mb_strtolower($news['content'] ?? '');
        $excerpt = mb_strtolower($news['excerpt'] ?? '');
        
        // Keywords for each category
        $keywords = [
            'student_activity' => [
                'นักศึกษา', 'กิจกรรมนักศึกษา', 'ค่าย', 'ทัศนศึกษา', 'แข่งขัน', 'ประกวด',
                'นิทรรศการ', 'งานแสดง', 'การแข่งขัน', 'รางวัล', 'เกียรติบัตร', 'ประกาศนียบัตร',
                'รับสมัคร', 'สอบ', 'สัมมนา', 'อบรม', 'workshop', 'student', 'activity',
                'กิจกรรม', 'โครงการ', 'ค่าย', 'ทัศนศึกษา'
            ],
            'research_grant' => [
                'วิจัย', 'งานวิจัย', 'โครงการวิจัย', 'ทุนวิจัย', 'research',
                'grant', 'funding', 'ทุน', 'scholarship', 'fellowship', 'ทุนสนับสนุน',
                'ผลงานวิจัย', 'ตีพิมพ์', 'publication', 'journal', 'วารสาร', 'conference',
                'การประชุมวิชาการ', 'symposium', 'seminar', 'การนำเสนอผลงาน'
            ],
            'general' => [
                'ประกาศ', 'แจ้งเตือน', 'ข่าว', 'ประชาสัมพันธ์', 'announcement', 'notice',
                'general', 'ทั่วไป', 'ข้อมูล', 'information'
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
     * Auto-categorize all news articles
     * Returns array with statistics
     */
    public function autoCategorizeAll(): array
    {
        $allNews = $this->findAll();
        $stats = [
            'total' => count($allNews),
            'updated' => 0,
            'unchanged' => 0,
            'by_category' => [
                'general' => 0,
                'student_activity' => 0,
                'research_grant' => 0
            ]
        ];

        foreach ($allNews as $news) {
            $currentCategory = $news['category'] ?? 'general';
            $suggestedCategory = $this->suggestCategory($news);
            
            if ($currentCategory !== $suggestedCategory) {
                $this->update($news['id'], ['category' => $suggestedCategory]);
                $stats['updated']++;
            } else {
                $stats['unchanged']++;
            }
            
            $stats['by_category'][$suggestedCategory]++;
        }

        return $stats;
    }
}

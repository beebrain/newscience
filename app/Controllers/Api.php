<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NewsModel;

/**
 * API Controller
 * Provides JSON endpoints for AJAX data fetching
 */
class Api extends BaseController
{
    protected $newsModel;
    
    public function __construct()
    {
        $this->newsModel = new NewsModel();
    }
    
    /**
     * Format featured image URL based on where it's stored
     * Handles: full URLs, newsimages/, uploads/news/, and plain filenames
     */
    protected function formatFeaturedImage($imagePath)
    {
        // Return empty string if no image
        if (empty($imagePath) || trim($imagePath) === '') {
            return '';
        }
        
        $imagePath = trim($imagePath);
        
        // Already a full URL (http or https)
        if (strpos($imagePath, 'http') === 0) {
            return $imagePath;
        }
        
        // Path starts with newsimages/ (local scraped images)
        if (strpos($imagePath, 'newsimages/') === 0) {
            return base_url($imagePath);
        }
        
        // Path starts with uploads/ (already has folder path)
        if (strpos($imagePath, 'uploads/') === 0) {
            return base_url($imagePath);
        }
        
        // Check if file exists in newsimages folder first
        $newsImagesPath = FCPATH . 'newsimages/' . $imagePath;
        if (file_exists($newsImagesPath)) {
            return base_url('newsimages/' . $imagePath);
        }
        
        // Check if file exists in uploads/news folder
        $uploadsPath = FCPATH . 'uploads/news/' . $imagePath;
        if (file_exists($uploadsPath)) {
            return base_url('uploads/news/' . $imagePath);
        }
        
        // Default to uploads/news path
        return base_url('uploads/news/' . $imagePath);
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
                'featured_image' => $this->formatFeaturedImage($article['featured_image'] ?? ''),
                'author' => trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? '')),
                'published_at' => $article['published_at'],
                'formatted_date' => date('M j, Y', strtotime($article['published_at'] ?? $article['created_at']))
            ];
        }
        
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
        
        return $this->response->setJSON([
            'success' => true,
            'data' => [
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
                'images' => array_map(function($img) {
                    return [
                        'id' => $img['id'],
                        'url' => base_url('uploads/news/' . $img['image_path']),
                        'caption' => $img['caption']
                    ];
                }, $images)
            ]
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
                'featured_image' => $this->formatFeaturedImage($article['featured_image'] ?? ''),
                'formatted_date' => date('M j, Y', strtotime($article['published_at'] ?? $article['created_at']))
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * Get news by category
     * GET /api/news/category/:category
     * 
     * Query params:
     * - limit: number of items (default: 6)
     */
    public function newsByCategory($category = null)
    {
        if (!$category) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Category parameter is required'
            ]);
        }
        
        $limit = (int) $this->request->getGet('limit') ?? 6;
        $limit = min(50, max(1, $limit));
        
        $news = $this->newsModel
            ->select('news.*, user.gf_name, user.gl_name')
            ->join('user', 'user.uid = news.author_id', 'left')
            ->where('news.status', 'published')
            ->where('news.category', $category)
            ->orderBy('news.published_at', 'DESC')
            ->limit($limit)
            ->find();
        
        $data = [];
        foreach ($news as $article) {
            $data[] = [
                'id' => $article['id'],
                'title' => $article['title'],
                'slug' => $article['slug'],
                'excerpt' => $article['excerpt'] ?? mb_substr(strip_tags($article['content']), 0, 150) . '...',
                'content' => $article['content'],
                'featured_image' => $this->formatFeaturedImage($article['featured_image'] ?? ''),
                'author' => trim(($article['gf_name'] ?? '') . ' ' . ($article['gl_name'] ?? '')),
                'published_at' => $article['published_at'],
                'formatted_date' => date('d M Y', strtotime($article['published_at'] ?? $article['created_at'])),
                'category' => $article['category'] ?? 'general'
            ];
        }
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $data,
            'category' => $category,
            'count' => count($data)
        ]);
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
                'featured_image' => $this->formatFeaturedImage($article['featured_image'] ?? '')
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
                'image' => $person['image'] ? (strpos($person['image'], 'staff/') === 0 ? base_url('uploads/' . $person['image']) : base_url('uploads/personnel/' . $person['image'])) : null
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
                'image' => $dean['image'] ? (strpos($dean['image'], 'staff/') === 0 ? base_url('uploads/' . $dean['image']) : base_url('uploads/personnel/' . $dean['image'])) : null,
                'bio' => $dean['bio']
            ]
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
                'image' => base_url($slide['image']),
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
}

<?php

namespace App\Controllers\Utility;

use App\Controllers\BaseController;
use App\Models\NewsModel;

/**
 * Utility controller for categorizing news articles
 * Access via: /utility/categorize-news (admin only)
 */
class CategorizeNews extends BaseController
{
    protected $newsModel;
    
    public function __construct()
    {
        $this->newsModel = new NewsModel();
    }
    
    /**
     * Main categorization page
     */
    public function index()
    {
        // ปกติใช้ filter adminauth; ตรวจสอบ session เดียวกับ Admin
        $session = session();
        if (!$session->get('admin_logged_in') || ($session->get('admin_role') !== 'admin' && $session->get('admin_role') !== 'editor')) {
            return redirect()->to(base_url('admin/login'))->with('error', 'Please login to access.');
        }
        
        // Get statistics (by tag)
        $db = \Config\Database::connect();
        $allNews = $this->newsModel->findAll();
        $stats = [
            'total' => count($allNews),
            'by_tag' => [
                'general' => 0,
                'student_activity' => 0,
                'research_grant' => 0,
                'uncategorized' => 0
            ]
        ];
        if ($db->tableExists('news_news_tags') && $db->tableExists('news_tags')) {
            $tagModel = model(\App\Models\NewsTagModel::class);
            foreach ($allNews as $news) {
                $tagIds = $tagModel->getTagIdsByNewsId((int) $news['id']);
                if (empty($tagIds)) {
                    $stats['by_tag']['uncategorized']++;
                    continue;
                }
                $firstTag = $tagModel->find($tagIds[0]);
                $slug = $firstTag['slug'] ?? null;
                if ($slug && isset($stats['by_tag'][$slug])) {
                    $stats['by_tag'][$slug]++;
                } elseif ($slug) {
                    $stats['by_tag'][$slug] = 1;
                } else {
                    $stats['by_tag']['uncategorized']++;
                }
            }
        } else {
            $stats['by_tag']['uncategorized'] = count($allNews);
        }
        
        $data = [
            'page_title' => 'จัดหมวดหมู่ข่าวอัตโนมัติ (Tag)',
            'stats' => $stats,
            'news_list' => array_slice($allNews, 0, 20)
        ];
        
        return view('admin/categorize_news', $data);
    }
    
    /**
     * Run categorization (AJAX)
     */
    public function run()
    {
        $session = session();
        if (!$session->get('admin_logged_in') || ($session->get('admin_role') !== 'admin' && $session->get('admin_role') !== 'editor')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }
        
        try {
            $stats = $this->newsModel->autoCategorizeAll();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Categorization completed successfully.',
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get tag suggestion for a single news article (AJAX)
     */
    public function suggest($id)
    {
        $session = session();
        if (!$session->get('admin_logged_in') || ($session->get('admin_role') !== 'admin' && $session->get('admin_role') !== 'editor')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access.'
            ])->setStatusCode(401);
        }
        
        $news = $this->newsModel->find($id);
        if (!$news) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'News article not found.'
            ]);
        }
        
        $suggestedSlug = $this->newsModel->suggestCategory($news);
        $db = \Config\Database::connect();
        $currentSlug = 'general';
        if ($db->tableExists('news_news_tags') && $db->tableExists('news_tags')) {
            $tagModel = model(\App\Models\NewsTagModel::class);
            $tagIds = $tagModel->getTagIdsByNewsId((int) $id);
            if (!empty($tagIds)) {
                $first = $tagModel->find($tagIds[0]);
                $currentSlug = $first['slug'] ?? 'general';
            }
        }
        
        return $this->response->setJSON([
            'success' => true,
            'current' => $currentSlug,
            'suggested' => $suggestedSlug,
            'needs_update' => $currentSlug !== $suggestedSlug
        ]);
    }
}

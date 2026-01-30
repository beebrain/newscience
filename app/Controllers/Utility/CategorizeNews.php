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
        // Check if admin is logged in
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
            return redirect()->to('/admin/login')->with('error', 'Please login as admin');
        }
        
        // Get statistics
        $allNews = $this->newsModel->findAll();
        $stats = [
            'total' => count($allNews),
            'by_category' => [
                'general' => 0,
                'student_activity' => 0,
                'research_grant' => 0,
                'uncategorized' => 0
            ]
        ];
        
        foreach ($allNews as $news) {
            $category = $news['category'] ?? null;
            if ($category && isset($stats['by_category'][$category])) {
                $stats['by_category'][$category]++;
            } else {
                $stats['by_category']['uncategorized']++;
            }
        }
        
        $data = [
            'page_title' => 'จัดหมวดหมู่ข่าวอัตโนมัติ',
            'stats' => $stats,
            'news_list' => array_slice($allNews, 0, 20) // Show first 20 for preview
        ];
        
        return view('admin/categorize_news', $data);
    }
    
    /**
     * Run categorization (AJAX)
     */
    public function run()
    {
        // Check if admin is logged in
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
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
     * Get category suggestion for a single news article (AJAX)
     */
    public function suggest($id)
    {
        // Check if admin is logged in
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('role') !== 'admin') {
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
        
        $suggestedCategory = $this->newsModel->suggestCategory($news);
        $currentCategory = $news['category'] ?? 'general';
        
        return $this->response->setJSON([
            'success' => true,
            'current' => $currentCategory,
            'suggested' => $suggestedCategory,
            'needs_update' => $currentCategory !== $suggestedCategory
        ]);
    }
}

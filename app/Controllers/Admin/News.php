<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\NewsImageModel;

class News extends BaseController
{
    protected $newsModel;
    protected $newsImageModel;

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        $this->newsImageModel = new NewsImageModel();
    }

    /**
     * List all news articles
     */
    public function index()
    {
        $data = [
            'page_title' => 'Manage News',
            'news' => $this->newsModel->getAllWithAuthor()
        ];

        return view('admin/news/index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create News'
        ];

        return view('admin/news/create', $data);
    }

    /**
     * Store new news article
     */
    public function store()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[500]',
            'content' => 'required',
            'status' => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                            ->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $slug = $this->newsModel->generateSlug($title);

        $newsData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $this->request->getPost('status'),
            'author_id' => session()->get('admin_id'),
            'published_at' => $this->request->getPost('status') === 'published' ? date('Y-m-d H:i:s') : null
        ];

        // Handle featured image
        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            $newName = $featuredImage->getRandomName();
            $featuredImage->move(FCPATH . 'uploads/news', $newName);
            $newsData['featured_image'] = $newName;
        }

        $newsId = $this->newsModel->insert($newsData);

        if (!$newsId) {
            return redirect()->back()
                            ->withInput()
                            ->with('error', 'Failed to create news article.');
        }

        // Handle additional images
        $additionalImages = $this->request->getFiles();
        if (isset($additionalImages['images'])) {
            $order = 0;
            foreach ($additionalImages['images'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $imgName = $img->getRandomName();
                    $img->move(FCPATH . 'uploads/news', $imgName);
                    
                    $caption = $this->request->getPost('captions')[$order] ?? null;
                    $this->newsImageModel->addImage($newsId, $imgName, $caption, $order);
                    $order++;
                }
            }
        }

        return redirect()->to(base_url('admin/news'))
                        ->with('success', 'News article created successfully.');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $news = $this->newsModel->find($id);

        if (!$news) {
            return redirect()->to(base_url('admin/news'))
                            ->with('error', 'News article not found.');
        }

        $data = [
            'page_title' => 'Edit News',
            'news' => $news,
            'images' => $this->newsImageModel->getImagesByNewsId($id)
        ];

        return view('admin/news/edit', $data);
    }

    /**
     * Update news article
     */
    public function update($id)
    {
        $news = $this->newsModel->find($id);

        if (!$news) {
            return redirect()->to(base_url('admin/news'))
                            ->with('error', 'News article not found.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[500]',
            'content' => 'required',
            'status' => 'required|in_list[draft,published]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                            ->withInput()
                            ->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        
        // Only regenerate slug if title changed
        $slug = $news['slug'];
        if ($title !== $news['title']) {
            $slug = $this->newsModel->generateSlug($title, $id);
        }

        $newsData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $this->request->getPost('status'),
        ];

        // Set published_at if publishing for first time
        if ($this->request->getPost('status') === 'published' && $news['status'] !== 'published') {
            $newsData['published_at'] = date('Y-m-d H:i:s');
        }

        // Handle featured image
        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            // Delete old image
            if ($news['featured_image']) {
                $oldPath = FCPATH . 'uploads/news/' . $news['featured_image'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            $newName = $featuredImage->getRandomName();
            $featuredImage->move(FCPATH . 'uploads/news', $newName);
            $newsData['featured_image'] = $newName;
        }

        $this->newsModel->update($id, $newsData);

        // Handle additional images
        $additionalImages = $this->request->getFiles();
        if (isset($additionalImages['images'])) {
            $existingImages = $this->newsImageModel->getImagesByNewsId($id);
            $order = count($existingImages);
            
            foreach ($additionalImages['images'] as $img) {
                if ($img->isValid() && !$img->hasMoved()) {
                    $imgName = $img->getRandomName();
                    $img->move(FCPATH . 'uploads/news', $imgName);
                    
                    $this->newsImageModel->addImage($id, $imgName, null, $order);
                    $order++;
                }
            }
        }

        return redirect()->to(base_url('admin/news'))
                        ->with('success', 'News article updated successfully.');
    }

    /**
     * Delete news article
     */
    public function delete($id)
    {
        $news = $this->newsModel->find($id);

        if (!$news) {
            return redirect()->to(base_url('admin/news'))
                            ->with('error', 'News article not found.');
        }

        // Delete featured image
        if ($news['featured_image']) {
            $filePath = FCPATH . 'uploads/news/' . $news['featured_image'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Delete additional images (handled by model)
        $this->newsImageModel->deleteByNewsId($id);

        // Delete news
        $this->newsModel->delete($id);

        return redirect()->to(base_url('admin/news'))
                        ->with('success', 'News article deleted successfully.');
    }
}

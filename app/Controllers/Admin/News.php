<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\NewsImageModel;
use App\Models\NewsTagModel;

class News extends BaseController
{
    protected $newsModel;
    protected $newsImageModel;
    protected $newsTagModel;

    public function __construct()
    {
        $this->newsModel = new NewsModel();
        $this->newsImageModel = new NewsImageModel();
        $this->newsTagModel = new NewsTagModel();
    }

    /**
     * List all news articles with search and filter
     */
    public function index()
    {
        $db = \Config\Database::connect();

        // Get search parameters (passed to view for AJAX JS)
        $keyword = $this->request->getGet('keyword') ?? '';
        $tagId = $this->request->getGet('tag_id');
        $tagId = !empty($tagId) ? (int) $tagId : '';

        // Get all tags for filter dropdown
        $tags = ($db->tableExists('news_tags')) ? $this->newsTagModel->getAllOrdered() : [];

        $data = [
            'page_title' => 'Manage News',
            'tags' => $tags,
            'keyword' => $keyword,
            'selected_tag' => $tagId
        ];

        return view('admin/news/index', $data);
    }

    /**
     * AJAX endpoint for paginated news
     */
    public function getPaginated()
    {
        $page = (int) ($this->request->getGet('page') ?? 1);
        $keyword = $this->request->getGet('keyword');
        $tagId = $this->request->getGet('tag_id');
        $tagId = !empty($tagId) ? (int) $tagId : null;

        // Get paginated results
        if (!empty($keyword) || !empty($tagId)) {
            $result = $this->newsModel->searchNewsPaginated($keyword, $tagId, $page, 20);
        } else {
            $result = $this->newsModel->getAllWithAuthorPaginated($page, 20);
        }

        // Fetch tags for each article
        $db = \Config\Database::connect();
        foreach ($result['data'] as &$article) {
            $article['tags'] = [];
            if ($db->tableExists('news_tags') && $db->tableExists('news_news_tags')) {
                $article['tags'] = $this->newsTagModel->getTagsByNewsId((int)$article['id']);
            }
            // Format date for display
            $article['created_at_formatted'] = date('d/m/Y', strtotime($article['created_at']));
        }

        return $this->response->setJSON($result);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $db = \Config\Database::connect();
        $tags = ($db->tableExists('news_tags')) ? $this->newsTagModel->getAllOrdered() : [];
        $data = [
            'page_title' => 'Create News',
            'tags' => $tags
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

        $facebookUrl = trim((string) $this->request->getPost('facebook_url'));
        $newsData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $this->request->getPost('status'),
            'display_as_event' => (int) $this->request->getPost('display_as_event'),
            'facebook_url' => $facebookUrl !== '' ? $facebookUrl : null,
            'author_id' => session()->get('admin_id'),
            'published_at' => $this->request->getPost('status') === 'published' ? date('Y-m-d H:i:s') : null
        ];

        $newsId = $this->newsModel->insert($newsData);

        if (!$newsId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create news article.');
        }

        // Handle featured image — ตั้งชื่อเป็นรหัสข่าว (id)
        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            $uploadPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
            if (!is_dir($uploadPath)) {
                if (!@mkdir($uploadPath, 0755, true)) {
                    log_message('error', "News Upload Error: Failed to create upload directory for News ID $newsId");
                    return redirect()->back()->withInput()->with('error', 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดภาพได้');
                }
            }
            if (!is_writable($uploadPath)) {
                log_message('error', "News Upload Error: Upload directory not writable for News ID $newsId");
                return redirect()->back()->withInput()->with('error', 'โฟลเดอร์อัปโหลดไม่มีสิทธิ์เขียน');
            }
            $ext = $this->featuredImageExtension($featuredImage);
            $featuredFileName = (int) $newsId . '.' . $ext;
            try {
                $featuredImage->move($uploadPath, $featuredFileName);
                $this->newsModel->update($newsId, ['featured_image' => $featuredFileName]);
                helper('image');
                $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $featuredFileName;
                if (is_file($fullPath) && create_news_thumbnail($fullPath)) {
                    log_message('info', "News Upload Success: Thumbnail created for News ID $newsId");
                }
                log_message('info', "News Upload Success: Featured image uploaded for News ID $newsId ($featuredFileName)");
            } catch (\Throwable $e) {
                log_message('error', "News Upload Error: Featured image upload failed for News ID $newsId. Error: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'อัปโหลดภาพปกไม่สำเร็จ: ' . $e->getMessage());
            }
        } elseif ($featuredImage && !$featuredImage->isValid() && $featuredImage->getError() !== UPLOAD_ERR_NO_FILE) {
            log_message('error', "News Upload Error: Invalid featured image for News ID $newsId. Error: " . $featuredImage->getErrorString());
            return redirect()->back()->withInput()->with('error', 'ภาพที่เลือกไม่ถูกต้อง: ' . $featuredImage->getErrorString());
        }

        // Save tags (1 ข่าวมีได้หลาย tag)
        $db = \Config\Database::connect();
        if ($db->tableExists('news_news_tags')) {
            $tagIds = $this->request->getPost('tag_ids') ?: [];
            $this->newsTagModel->setTagsForNews((int) $newsId, (array) $tagIds);
        }

        // Handle attachments: รูปภาพ (attachments_images) และ ไฟล์แนบ (attachments_docs)
        $uploadPathNews = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
        if (!is_dir($uploadPathNews)) {
            @mkdir($uploadPathNews, 0755, true);
        }
        $files = $this->request->getFiles();
        $sortOrder = 0;
        foreach (['attachments_images' => 'image', 'attachments_docs' => 'document'] as $inputName => $type) {
            if (empty($files[$inputName]) || !is_dir($uploadPathNews) || !is_writable($uploadPathNews)) {
                continue;
            }
            $list = is_array($files[$inputName]) ? $files[$inputName] : [$files[$inputName]];
            foreach ($list as $file) {
                if (!$file->isValid() || $file->hasMoved()) {
                    if ($file->getError() !== UPLOAD_ERR_NO_FILE) {
                        log_message('error', "News Upload: Invalid file for News ID $newsId (" . $file->getClientName() . ")");
                    }
                    continue;
                }
                if (!$this->isAllowedAttachment($file)) {
                    log_message('notice', "News Upload Skipped: File type not allowed for News ID $newsId (" . $file->getClientName() . ")");
                    continue;
                }
                $fileName = $file->getRandomName();
                try {
                    $file->move($uploadPathNews, $fileName);
                    $caption = $type === 'document' ? $file->getClientName() : null;
                    $this->newsImageModel->addAttachment($newsId, $fileName, $type, $caption, $sortOrder);
                    log_message('info', "News Attachment Success: Uploaded $type for News ID $newsId ($fileName)");
                    $sortOrder++;
                } catch (\Throwable $e) {
                    log_message('error', "News Attachment Error: News ID $newsId. " . $e->getMessage());
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

        $db = \Config\Database::connect();
        $tags = ($db->tableExists('news_tags')) ? $this->newsTagModel->getAllOrdered() : [];
        $newsTagIds = ($db->tableExists('news_news_tags')) ? $this->newsTagModel->getTagIdsByNewsId((int) $id) : [];

        $data = [
            'page_title' => 'Edit News',
            'news' => $news,
            'images' => $this->newsImageModel->getImagesByNewsId($id),
            'documents' => $this->newsImageModel->getDocumentsByNewsId((int) $id),
            'attachments' => $this->newsImageModel->getAttachmentsByNewsId((int) $id),
            'tags' => $tags,
            'news_tag_ids' => $newsTagIds
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

        $facebookUrl = trim((string) $this->request->getPost('facebook_url'));
        $newsData = [
            'id' => $id, // จำเป็นสำหรับ validation rule is_unique[news.slug,id,{id}]
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $this->request->getPost('status'),
            'display_as_event' => (int) $this->request->getPost('display_as_event'),
            'facebook_url' => $facebookUrl !== '' ? $facebookUrl : null,
        ];

        // Set published_at if publishing for first time
        if ($this->request->getPost('status') === 'published' && $news['status'] !== 'published') {
            $newsData['published_at'] = date('Y-m-d H:i:s');
        }

        // Handle featured image — ตั้งชื่อเป็นรหัสข่าว (id)
        $featuredImage = $this->request->getFile('featured_image');
        if ($featuredImage && $featuredImage->isValid() && !$featuredImage->hasMoved()) {
            $uploadPath = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
            if (!is_dir($uploadPath)) {
                if (!@mkdir($uploadPath, 0755, true)) {
                    log_message('error', "News Update Error: Failed to create upload directory for News ID $id");
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'ไม่สามารถสร้างโฟลเดอร์สำหรับอัปโหลดภาพได้');
                }
            }
            if (!is_writable($uploadPath)) {
                log_message('error', "News Update Error: Upload directory not writable for News ID $id");
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'โฟลเดอร์อัปโหลดไม่มีสิทธิ์เขียน');
            }
            $ext = $this->featuredImageExtension($featuredImage);
            $featuredFileName = (int) $id . '.' . $ext;
            try {
                if ($news['featured_image']) {
                    $oldFilename = basename($news['featured_image']);
                    $oldPath = $uploadPath . DIRECTORY_SEPARATOR . $oldFilename;
                    $oldThumb = $uploadPath . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $oldFilename;
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    } else {
                        $publicPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . $oldFilename;
                        if (file_exists($publicPath)) {
                            @unlink($publicPath);
                        }
                    }
                    if (file_exists($oldThumb)) {
                        @unlink($oldThumb);
                    }
                }
                $featuredImage->move($uploadPath, $featuredFileName);
                $newsData['featured_image'] = $featuredFileName;
                helper('image');
                $fullPath = $uploadPath . DIRECTORY_SEPARATOR . $featuredFileName;
                if (is_file($fullPath) && create_news_thumbnail($fullPath)) {
                    log_message('info', "News Update Success: Thumbnail created for News ID $id");
                }
                log_message('info', "News Update Success: Featured image updated for News ID $id ($featuredFileName)");
            } catch (\Throwable $e) {
                log_message('error', "News Update Error: Featured image update failed for News ID $id. Error: " . $e->getMessage());
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'อัปโหลดภาพปกไม่สำเร็จ: ' . $e->getMessage());
            }
        } elseif ($featuredImage && !$featuredImage->isValid() && $featuredImage->getError() !== UPLOAD_ERR_NO_FILE) {
            log_message('error', "News Update Error: Invalid featured image for News ID $id. Error: " . $featuredImage->getErrorString());
            return redirect()->back()
                ->withInput()
                ->with('error', 'ภาพที่เลือกไม่ถูกต้อง: ' . $featuredImage->getErrorString());
        }

        $this->newsModel->update($id, $newsData);

        // Save tags (1 ข่าวมีได้หลาย tag)
        $db = \Config\Database::connect();
        if ($db->tableExists('news_news_tags')) {
            $tagIds = $this->request->getPost('tag_ids') ?: [];
            $this->newsTagModel->setTagsForNews((int) $id, (array) $tagIds);
        }

        // Handle attachments: รูปภาพ (attachments_images) และ ไฟล์แนบ (attachments_docs)
        $uploadPathNews = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
        if (!is_dir($uploadPathNews)) {
            @mkdir($uploadPathNews, 0755, true);
        }
        $files = $this->request->getFiles();
        $sortOrder = 0;
        foreach (['attachments_images' => 'image', 'attachments_docs' => 'document'] as $inputName => $type) {
            if (empty($files[$inputName]) || !is_dir($uploadPathNews) || !is_writable($uploadPathNews)) {
                continue;
            }
            $list = is_array($files[$inputName]) ? $files[$inputName] : [$files[$inputName]];
            foreach ($list as $file) {
                if (!$file->isValid() || $file->hasMoved()) {
                    if ($file->getError() !== UPLOAD_ERR_NO_FILE) {
                        log_message('error', "News Update: Invalid file for News ID $id (" . $file->getClientName() . ")");
                    }
                    continue;
                }
                if (!$this->isAllowedAttachment($file)) {
                    log_message('notice', "News Update Skipped: File type not allowed for News ID $id (" . $file->getClientName() . ")");
                    continue;
                }
                $fileName = $file->getRandomName();
                try {
                    $file->move($uploadPathNews, $fileName);
                    $caption = $type === 'document' ? $file->getClientName() : null;
                    $this->newsImageModel->addAttachment($id, $fileName, $type, $caption, $sortOrder);
                    log_message('info', "News Attachment Update Success: Uploaded $type for News ID $id ($fileName)");
                    $sortOrder++;
                } catch (\Throwable $e) {
                    log_message('error', "News Attachment Update Error: News ID $id. " . $e->getMessage());
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

        // Delete featured image และ thumbnail (writable first, then public fallback)
        if ($news['featured_image']) {
            $oldFilename = basename($news['featured_image']);
            $uploadPathNews = rtrim(WRITEPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news';
            $filePath = $uploadPathNews . DIRECTORY_SEPARATOR . $oldFilename;
            $thumbPath = $uploadPathNews . DIRECTORY_SEPARATOR . 'thumbs' . DIRECTORY_SEPARATOR . $oldFilename;
            if (file_exists($filePath)) {
                @unlink($filePath);
            } else {
                $publicPath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'news' . DIRECTORY_SEPARATOR . $oldFilename;
                if (file_exists($publicPath)) {
                    @unlink($publicPath);
                }
            }
            if (file_exists($thumbPath)) {
                @unlink($thumbPath);
            }
        }



        // Delete additional images (handled by model)
        $this->newsImageModel->deleteByNewsId($id);

        // Delete news
        $this->newsModel->delete($id);

        return redirect()->to(base_url('admin/news'))
            ->with('success', 'News article deleted successfully.');
    }

    /**
     * คืนนามสกุลไฟล์ที่ปลอดภัยสำหรับภาพหน้าปก (jpg, jpeg, png, gif, webp)
     */
    private function featuredImageExtension($uploadedFile): string
    {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower($uploadedFile->getClientExtension() ?? '');
        if (in_array($ext, $allowed, true)) {
            return $ext === 'jpeg' ? 'jpg' : $ext;
        }
        $guessed = strtolower($uploadedFile->guessExtension() ?? '');
        return in_array($guessed, $allowed, true) ? ($guessed === 'jpeg' ? 'jpg' : $guessed) : 'jpg';
    }



    /**
     * ตรวจสอบไฟล์แนบ (images + docs)
     */
    private function isAllowedAttachment($uploadedFile): bool
    {
        $allowed = [
            // Images
            'jpg',
            'jpeg',
            'png',
            'gif',
            'webp',
            // Docs
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'ppt',
            'pptx'
        ];
        $ext = strtolower($uploadedFile->getClientExtension() ?? '');
        if (in_array($ext, $allowed, true)) {
            return true;
        }
        $guessed = strtolower($uploadedFile->guessExtension() ?? '');
        return in_array($guessed, $allowed, true);
    }
}

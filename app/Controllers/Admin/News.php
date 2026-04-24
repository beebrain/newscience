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
        helper('image_manager');
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
        $displayAsEvent = $this->request->getPost('display_as_event');
        $displayAsEvent = ($displayAsEvent === '1' || $displayAsEvent === 1) ? 1 : 0;
        $postStatus = $this->request->getPost('status');
        $parsedPublished = NewsModel::publishedAtFromUserInput($this->request->getPost('published_at'));
        $publishedAt = null;
        if ($postStatus === 'published') {
            $publishedAt = $parsedPublished ?? date('Y-m-d H:i:s');
        }
        $newsData = [
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $postStatus,
            'display_as_event' => $displayAsEvent,
            'facebook_url' => $facebookUrl !== '' ? $facebookUrl : null,
            'author_id' => session()->get('admin_id'),
            'published_at' => $publishedAt,
        ];

        $newsId = $this->newsModel->insert($newsData);

        if (!$newsId) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create news article.');
        }

        // Handle featured image (ไฟล์ปกติหรือ base64 crop) ผ่าน image_manager
        $imageData = $this->handleFeaturedImage((int) $newsId);
        if ($imageData !== null) {
            $this->newsModel->update($newsId, $imageData);
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
     * Show edit form — อ่านข้อมูลข่าวจากฐานข้อมูลเป็นหลัก (news/edit/364)
     */
    public function edit($id)
    {
        $news = $this->newsModel->find($id);
        if (!$news) {
            return redirect()->to(base_url('admin/news'))
                ->with('error', 'ไม่พบข่าวที่ต้องการแก้ไข');
        }

        $db = \Config\Database::connect();
        $tags = ($db->tableExists('news_tags')) ? $this->newsTagModel->getAllOrdered() : [];
        $newsTagIds = ($db->tableExists('news_news_tags')) ? $this->newsTagModel->getTagIdsByNewsId((int) $id) : [];

        $data = [
            'page_title' => 'แก้ไขข่าว',
            'news' => $news,
            'images' => $this->newsImageModel->getImagesByNewsId($id),
            'documents' => $this->newsImageModel->getDocumentsByNewsId((int) $id),
            'attachments' => $this->newsImageModel->getAttachmentsByNewsId((int) $id),
            'tags' => $tags,
            'news_tag_ids' => $newsTagIds,
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
        $displayAsEvent = $this->request->getPost('display_as_event');
        $displayAsEvent = ($displayAsEvent === '1' || $displayAsEvent === 1) ? 1 : 0;
        $postStatus = $this->request->getPost('status');
        $newsData = [
            'id' => $id, // จำเป็นสำหรับ validation rule is_unique[news.slug,id,{id}]
            'title' => $title,
            'slug' => $slug,
            'content' => $this->request->getPost('content'),
            'excerpt' => $this->request->getPost('excerpt'),
            'status' => $postStatus,
            'display_as_event' => $displayAsEvent,
            'facebook_url' => $facebookUrl !== '' ? $facebookUrl : null,
        ];

        if ($postStatus === 'published') {
            $parsedPublished = NewsModel::publishedAtFromUserInput($this->request->getPost('published_at'));
            if ($parsedPublished !== null) {
                $newsData['published_at'] = $parsedPublished;
            } elseif (($news['status'] ?? '') !== 'published') {
                $newsData['published_at'] = date('Y-m-d H:i:s');
            }
        } else {
            $newsData['published_at'] = null;
        }

        // Handle featured image (ไฟล์ปกติหรือ base64 crop) ผ่าน image_manager
        $imageData = $this->handleFeaturedImage((int) $id, $news['featured_image'] ?? null);
        if ($imageData !== null) {
            $newsData = array_merge($newsData, $imageData);
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

        // Delete featured image + thumbnail
        image_manager_delete('news', $news['featured_image'] ?? null);



        // Delete additional images (handled by model)
        $this->newsImageModel->deleteByNewsId($id);

        // Delete news
        $this->newsModel->delete($id);

        return redirect()->to(base_url('admin/news'))
            ->with('success', 'News article deleted successfully.');
    }

    /**
     * จัดการอัปโหลด featured image — รองรับทั้ง UploadedFile และ base64 จาก crop
     * ถ้ามีรูปใหม่ จะลบรูปเก่า (+ thumb) อัตโนมัติ
     *
     * @return array|null {featured_image, featured_image_width, featured_image_height} หรือ null ถ้าไม่มีรูปใหม่
     */
    private function handleFeaturedImage(int $newsId, ?string $oldImagePath = null): ?array
    {
        $base64 = $this->request->getPost('featured_image_base64');
        if (!empty($base64) && is_string($base64) && strpos($base64, 'base64,') !== false) {
            $result = image_manager_save_base64('news', $newsId, $base64);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('news', $oldImagePath);
                return [
                    'featured_image'        => $result['path'],
                    'featured_image_width'  => $result['width'],
                    'featured_image_height' => $result['height'],
                ];
            }
        }

        $file = $this->request->getFile('featured_image');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $result = image_manager_save_file('news', $newsId, $file);
            if ($result !== null) {
                if ($oldImagePath) image_manager_delete('news', $oldImagePath);
                return [
                    'featured_image'        => $result['path'],
                    'featured_image_width'  => $result['width'],
                    'featured_image_height' => $result['height'],
                ];
            }
        }

        return null;
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

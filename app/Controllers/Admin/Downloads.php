<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DownloadCategoryModel;
use App\Models\DownloadDocumentModel;

class Downloads extends BaseController
{
    protected DownloadCategoryModel $categoryModel;
    protected DownloadDocumentModel $documentModel;

    /** @var array Allowed file extensions for upload */
    private const ALLOWED_EXTENSIONS = ['pdf', 'doc', 'docx', 'xlsx', 'ppt', 'pptx', 'zip', 'jpg', 'jpeg', 'png', 'gif', 'mp4', 'mp3', 'txt'];

    /** @var int Max file size in bytes (10MB) */
    private const MAX_FILE_SIZE = 10 * 1024 * 1024;

    public function __construct()
    {
        $this->categoryModel = new DownloadCategoryModel();
        $this->documentModel = new DownloadDocumentModel();
    }

    /**
     * List all categories grouped by page_type (tabs)
     */
    public function index()
    {
        $categoriesByPage = $this->categoryModel->getAllCategories();
        $pageTypes = [
            'support' => 'แบบฟอร์มดาวน์โหลด (support-documents)',
            'official' => 'คำสั่ง/ประกาศ/ระเบียบ (official-documents)',
            'promotion' => 'เกณฑ์การประเมินบุคคล (promotion-criteria)',
            'internal' => 'เอกสารภายใน (internal)',
        ];

        $data = [
            'page_title' => 'จัดการหมวดดาวน์โหลดคณะ',
            'categories_by_page' => $categoriesByPage,
            'page_types' => $pageTypes,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/downloads/index', $data);
    }

    /**
     * POST: Create category
     */
    public function storeCategory()
    {
        $name = trim((string) $this->request->getPost('name'));
        $slug = trim((string) $this->request->getPost('slug'));
        $pageType = $this->request->getPost('page_type');
        $icon = trim((string) $this->request->getPost('icon')) ?: 'folder';

        $allowedPageTypes = ['support', 'official', 'promotion', 'internal'];
        if (empty($name) || empty($slug) || !in_array($pageType, $allowedPageTypes, true)) {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อและ slug และเลือกประเภทหน้า');
        }

        $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($slug));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('error', 'slug ไม่ถูกต้อง');
        }

        if ($this->categoryModel->findBySlug($slug)) {
            return redirect()->back()->withInput()->with('error', 'slug ซ้ำกับที่มีอยู่แล้ว');
        }

        $this->categoryModel->addCategory([
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'page_type' => $pageType,
            'sort_order' => (int) $this->request->getPost('sort_order') ?: 0,
        ]);

        return redirect()->back()->with('success', 'เพิ่มหมวดหมู่เรียบร้อยแล้ว');
    }

    /**
     * POST: Update category
     */
    public function updateCategory($id)
    {
        $id = (int) $id;
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            return redirect()->back()->with('error', 'ไม่พบหมวดหมู่');
        }

        $name = trim((string) $this->request->getPost('name'));
        $slug = trim((string) $this->request->getPost('slug'));
        $icon = trim((string) $this->request->getPost('icon')) ?: 'folder';

        if (empty($name) || empty($slug)) {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อและ slug');
        }

        $slug = preg_replace('/[^a-z0-9_-]/', '', strtolower($slug));
        if ($slug === '') {
            return redirect()->back()->withInput()->with('error', 'slug ไม่ถูกต้อง');
        }

        $existing = $this->categoryModel->findBySlug($slug);
        if ($existing && (int) $existing['id'] !== $id) {
            return redirect()->back()->withInput()->with('error', 'slug ซ้ำกับหมวดอื่น');
        }

        $this->categoryModel->updateCategory($id, [
            'name' => $name,
            'slug' => $slug,
            'icon' => $icon,
            'sort_order' => (int) $this->request->getPost('sort_order'),
        ]);

        return redirect()->back()->with('success', 'บันทึกการแก้ไขหมวดหมู่เรียบร้อยแล้ว');
    }

    /**
     * GET: Delete category (cascade deletes documents)
     */
    public function deleteCategory($id)
    {
        $id = (int) $id;
        $category = $this->categoryModel->findById($id);
        if (!$category) {
            return redirect()->back()->with('error', 'ไม่พบหมวดหมู่');
        }

        $this->categoryModel->deleteCategory($id);
        return redirect()->to(base_url('admin/downloads'))->with('success', 'ลบหมวดหมู่และเอกสารในหมวดเรียบร้อยแล้ว');
    }

    /**
     * POST: Reorder categories
     */
    public function updateCategoryOrder()
    {
        $order = $this->request->getPost('sort_order');
        if (!is_array($order)) {
            return redirect()->back()->with('error', 'ข้อมูลไม่ถูกต้อง');
        }
        foreach ($order as $index => $id) {
            $this->categoryModel->update($id, ['sort_order' => $index + 1]);
        }
        return redirect()->back()->with('success', 'อัปเดตลำดับหมวดหมู่แล้ว');
    }

    /**
     * List documents in a category
     */
    public function documents($categoryId)
    {
        $categoryId = (int) $categoryId;
        $category = $this->categoryModel->findById($categoryId);
        if (!$category) {
            return redirect()->to(base_url('admin/downloads'))->with('error', 'ไม่พบหมวดหมู่');
        }

        $documents = $this->documentModel->getByCategoryId($categoryId);
        // Include inactive for admin list
        $allDocs = $this->documentModel->where('category_id', $categoryId)->orderBy('sort_order', 'ASC')->findAll();

        $data = [
            'page_title' => 'จัดการเอกสาร - ' . $category['name'],
            'category' => $category,
            'documents' => $allDocs,
            'documentModel' => $this->documentModel,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/downloads/documents', $data);
    }

    /**
     * POST: Upload file or add external link
     */
    public function upload($categoryId)
    {
        $categoryId = (int) $categoryId;
        $category = $this->categoryModel->findById($categoryId);
        if (!$category) {
            return redirect()->back()->with('error', 'ไม่พบหมวดหมู่');
        }

        $title = trim((string) $this->request->getPost('title'));
        $externalUrl = trim((string) $this->request->getPost('external_url'));
        $fileType = trim((string) $this->request->getPost('file_type'));
        $file = $this->request->getFile('file');

        if (empty($title)) {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อเอกสาร');
        }

        $isExternal = !empty($externalUrl);
        $hasFile = $file && $file->isValid() && !$file->hasMoved();

        if ($isExternal) {
            $fileType = 'link';
            $filePath = null;
            $fileSize = 0;
        } elseif ($hasFile) {
            $ext = strtolower($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                return redirect()->back()->withInput()->with('error', 'ประเภทไฟล์ไม่อนุญาต รองรับ: ' . implode(', ', self::ALLOWED_EXTENSIONS));
            }
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                return redirect()->back()->withInput()->with('error', 'ขนาดไฟล์เกิน 10MB');
            }

            helper('program_upload');
            $uploadDir = upload_path('faculty-downloads');
            $filename = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!$file->move($uploadDir, $filename)) {
                return redirect()->back()->withInput()->with('error', 'บันทึกไฟล์ไม่สำเร็จ');
            }
            $filePath = 'faculty-downloads/' . $filename;
            $fileSize = $file->getSize();
            if (empty($fileType)) {
                $fileType = $ext;
            }
        } else {
            return redirect()->back()->withInput()->with('error', 'กรุณาเลือกไฟล์อัปโหลดหรือกรอกลิงก์ภายนอก');
        }

        $userId = session()->get('admin_id');

        $this->documentModel->addDocument([
            'category_id' => $categoryId,
            'title' => $title,
            'file_path' => $filePath,
            'external_url' => $isExternal ? $externalUrl : null,
            'file_type' => $fileType,
            'file_size' => $fileSize,
            'uploaded_by' => $userId ?: null,
        ]);

        return redirect()->back()->with('success', 'เพิ่มเอกสารเรียบร้อยแล้ว');
    }

    /**
     * GET: Edit document form
     */
    public function edit($id)
    {
        $id = (int) $id;
        $document = $this->documentModel->findById($id);
        if (!$document) {
            return redirect()->back()->with('error', 'ไม่พบเอกสาร');
        }

        $category = $this->categoryModel->findById((int) $document['category_id']);
        if (!$category) {
            return redirect()->to(base_url('admin/downloads'))->with('error', 'ไม่พบหมวดหมู่');
        }

        $data = [
            'page_title' => 'แก้ไขเอกสาร - ' . $document['title'],
            'document' => $document,
            'category' => $category,
            'documentModel' => $this->documentModel,
            'layout' => 'admin/layouts/admin_layout',
        ];

        return view('admin/downloads/edit', $data);
    }

    /**
     * POST: Update document
     */
    public function update($id)
    {
        $id = (int) $id;
        $document = $this->documentModel->findById($id);
        if (!$document) {
            return redirect()->back()->with('error', 'ไม่พบเอกสาร');
        }

        $title = trim((string) $this->request->getPost('title'));
        $externalUrl = trim((string) $this->request->getPost('external_url'));
        $fileType = trim((string) $this->request->getPost('file_type'));
        $file = $this->request->getFile('file');

        if (empty($title)) {
            return redirect()->back()->withInput()->with('error', 'กรุณากรอกชื่อเอกสาร');
        }

        $data = ['title' => $title];

        $hasNewFile = $file && $file->isValid() && !$file->hasMoved();
        if (!empty($externalUrl)) {
            $data['external_url'] = $externalUrl;
            $data['file_type'] = 'link';
            $data['file_path'] = null;
            $data['file_size'] = 0;
        } elseif ($hasNewFile) {
            $ext = strtolower($file->getExtension() ?: pathinfo($file->getClientName(), PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                return redirect()->back()->withInput()->with('error', 'ประเภทไฟล์ไม่อนุญาต');
            }
            if ($file->getSize() > self::MAX_FILE_SIZE) {
                return redirect()->back()->withInput()->with('error', 'ขนาดไฟล์เกิน 10MB');
            }

            helper('program_upload');
            $uploadDir = upload_path('faculty-downloads');
            $filename = 'doc_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            if (!$file->move($uploadDir, $filename)) {
                return redirect()->back()->withInput()->with('error', 'บันทึกไฟล์ไม่สำเร็จ');
            }
            $oldPath = $document['file_path'];
            if ($oldPath) {
                $fullPath = upload_resolve_full_path($oldPath);
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
            }
            $data['file_path'] = 'faculty-downloads/' . $filename;
            $data['file_size'] = $file->getSize();
            $data['file_type'] = $fileType ?: $ext;
            $data['external_url'] = null;
        } else {
            $data['file_type'] = $fileType ?: $document['file_type'];
        }

        $this->documentModel->updateDocument($id, $data);
        return redirect()->to(base_url('admin/downloads/documents/' . $document['category_id']))->with('success', 'บันทึกการแก้ไขเรียบร้อยแล้ว');
    }

    /**
     * GET: Delete document and remove file if local
     */
    public function delete($id)
    {
        $id = (int) $id;
        $document = $this->documentModel->findById($id);
        if (!$document) {
            return redirect()->back()->with('error', 'ไม่พบเอกสาร');
        }

        $categoryId = (int) $document['category_id'];

        if (!empty($document['file_path'])) {
            helper('program_upload');
            $fullPath = upload_resolve_full_path($document['file_path']);
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $this->documentModel->deleteDocument($id);
        return redirect()->to(base_url('admin/downloads/documents/' . $categoryId))->with('success', 'ลบเอกสารเรียบร้อยแล้ว');
    }

    /**
     * POST: Reorder documents
     */
    public function updateDocOrder()
    {
        $categoryId = (int) $this->request->getPost('category_id');
        $order = $this->request->getPost('sort_order');
        if (!is_array($order)) {
            return redirect()->back()->with('error', 'ข้อมูลไม่ถูกต้อง');
        }
        foreach ($order as $index => $docId) {
            $this->documentModel->update($docId, ['sort_order' => $index + 1]);
        }
        return redirect()->back()->with('success', 'อัปเดตลำดับเอกสารแล้ว');
    }
}

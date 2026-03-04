<?php

namespace App\Models;

use CodeIgniter\Model;

class DownloadDocumentModel extends Model
{
    protected $table = 'download_documents';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'category_id', 'title', 'file_path', 'external_url', 'file_type', 'file_size',
        'description', 'sort_order', 'is_active', 'uploaded_by'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active documents in a category, ordered by sort_order
     */
    public function getByCategoryId(int $categoryId): array
    {
        return $this->where('category_id', $categoryId)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get all documents for a page type, grouped by category (for public pages)
     * Returns array of categories with 'category' and 'documents' keys
     */
    public function getByPageType(string $pageType): array
    {
        $categoryModel = new DownloadCategoryModel();
        $categories = $categoryModel->getByPageType($pageType);
        $result = [];
        foreach ($categories as $cat) {
            $docs = $this->getByCategoryId((int) $cat['id']);
            $result[] = [
                'category' => $cat,
                'documents' => $docs,
            ];
        }
        return $result;
    }

    /**
     * Get download URL for a document (external_url or serve/uploads/ file)
     */
    public static function getDocumentUrl(array $doc): string
    {
        if (!empty($doc['external_url'])) {
            return $doc['external_url'];
        }
        if (!empty($doc['file_path'])) {
            return base_url('serve/uploads/' . $doc['file_path']);
        }
        return '';
    }

    /**
     * Get file size in human readable format
     */
    public function getFormattedSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $i = 0;
        while ($bytes > 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get file type icon class (for admin UI)
     */
    public function getFileIcon(string $fileType): string
    {
        $icons = [
            'pdf' => 'fas fa-file-pdf',
            'doc' => 'fas fa-file-word',
            'docx' => 'fas fa-file-word',
            'xls' => 'fas fa-file-excel',
            'xlsx' => 'fas fa-file-excel',
            'ppt' => 'fas fa-file-powerpoint',
            'pptx' => 'fas fa-file-powerpoint',
            'zip' => 'fas fa-file-archive',
            'rar' => 'fas fa-file-archive',
            'jpg' => 'fas fa-file-image',
            'jpeg' => 'fas fa-file-image',
            'png' => 'fas fa-file-image',
            'gif' => 'fas fa-file-image',
            'mp4' => 'fas fa-video',
            'mp3' => 'fas fa-music',
            'txt' => 'fas fa-file-alt',
            'link' => 'fas fa-link',
            'default' => 'fas fa-file',
        ];
        return $icons[strtolower($fileType)] ?? $icons['default'];
    }

    /**
     * Find document by ID
     */
    public function findById(int $id): ?array
    {
        $row = $this->find($id);
        return $row ?: null;
    }

    /**
     * Add document
     */
    public function addDocument(array $data): int
    {
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;
        $data['file_size'] = $data['file_size'] ?? 0;
        return (int) $this->insert($data);
    }

    /**
     * Update document
     */
    public function updateDocument(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Delete document
     */
    public function deleteDocument(int $id): bool
    {
        return $this->delete($id);
    }
}

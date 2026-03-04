<?php

namespace App\Models;

use CodeIgniter\Model;

class DownloadCategoryModel extends Model
{
    protected $table = 'download_categories';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name', 'slug', 'icon', 'page_type', 'sort_order', 'is_active'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get active categories for a page type, ordered by sort_order
     */
    public function getByPageType(string $pageType): array
    {
        return $this->where('page_type', $pageType)
            ->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get all categories (for admin), grouped by page_type
     */
    public function getAllCategories(): array
    {
        $all = $this->orderBy('page_type', 'ASC')
            ->orderBy('sort_order', 'ASC')
            ->findAll();
        $grouped = [];
        foreach ($all as $row) {
            $grouped[$row['page_type']][] = $row;
        }
        return $grouped;
    }

    /**
     * Find category by ID
     */
    public function findById(int $id): ?array
    {
        $row = $this->find($id);
        return $row ?: null;
    }

    /**
     * Find category by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $row = $this->where('slug', $slug)->first();
        return $row ?: null;
    }

    /**
     * Create category
     */
    public function addCategory(array $data): int
    {
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $data['is_active'] ?? 1;
        return (int) $this->insert($data);
    }

    /**
     * Update category
     */
    public function updateCategory(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Delete category (cascade deletes documents)
     */
    public function deleteCategory(int $id): bool
    {
        return $this->delete($id);
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsImageModel extends Model
{
    protected $table = 'news_images';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'news_id',
        'image_path',
        'caption',
        'sort_order'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Get images for a news article
     */
    public function getImagesByNewsId(int $newsId)
    {
        return $this->where('news_id', $newsId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Add image to news
     */
    public function addImage(int $newsId, string $imagePath, ?string $caption = null, int $sortOrder = 0)
    {
        return $this->insert([
            'news_id' => $newsId,
            'image_path' => $imagePath,
            'caption' => $caption,
            'sort_order' => $sortOrder
        ]);
    }

    /**
     * Delete all images for a news article
     */
    public function deleteByNewsId(int $newsId)
    {
        // Get images first to delete files
        $images = $this->getImagesByNewsId($newsId);
        
        foreach ($images as $image) {
            $filePath = FCPATH . 'uploads/news/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        return $this->where('news_id', $newsId)->delete();
    }

    /**
     * Delete single image
     */
    public function deleteImage(int $imageId)
    {
        $image = $this->find($imageId);
        
        if ($image) {
            $filePath = FCPATH . 'uploads/news/' . $image['image_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            return $this->delete($imageId);
        }
        
        return false;
    }

    /**
     * Update sort order
     */
    public function updateSortOrder(array $imageIds)
    {
        foreach ($imageIds as $order => $id) {
            $this->update($id, ['sort_order' => $order]);
        }
    }
}

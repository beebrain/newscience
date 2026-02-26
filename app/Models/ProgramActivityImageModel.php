<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramActivityImageModel extends Model
{
    protected $table = 'program_activity_images';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'activity_id',
        'image_path',
        'caption',
        'sort_order',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Get all images for an activity
     */
    public function getByActivityId(int $activityId): array
    {
        return $this->where('activity_id', $activityId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Add image to activity
     */
    public function addImage(int $activityId, string $imagePath, ?string $caption = null, int $sortOrder = 0)
    {
        $maxOrder = $this->where('activity_id', $activityId)->selectMax('sort_order')->first();
        $sortOrder = $sortOrder > 0 ? $sortOrder : (($maxOrder['sort_order'] ?? 0) + 1);
        return $this->insert([
            'activity_id' => $activityId,
            'image_path' => $imagePath,
            'caption' => $caption,
            'sort_order' => $sortOrder,
        ]);
    }

    /**
     * Delete image by id (and optionally remove file)
     */
    public function deleteImage(int $imageId): bool
    {
        $image = $this->find($imageId);
        if (!$image) {
            return false;
        }
        return $this->delete($imageId);
    }

    /**
     * Get next sort order for activity
     */
    public function getNextSortOrder(int $activityId): int
    {
        $row = $this->where('activity_id', $activityId)->selectMax('sort_order')->first();
        return (int) ($row['sort_order'] ?? 0) + 1;
    }
}

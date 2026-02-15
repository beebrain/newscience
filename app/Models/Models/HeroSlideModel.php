<?php

namespace App\Models;

use CodeIgniter\Model;

class HeroSlideModel extends Model
{
    protected $table = 'hero_slides';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'title',
        'subtitle',
        'description',
        'image',
        'link',
        'link_text',
        'show_buttons',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'permit_empty|max_length[255]',
        'image' => 'required',
    ];

    /**
     * Get all active slides ordered by sort_order
     */
    public function getActiveSlides()
    {
        $now = date('Y-m-d H:i:s');
        
        return $this->where('is_active', 1)
            ->groupStart()
                ->where('start_date IS NULL')
                ->orWhere('start_date <=', $now)
            ->groupEnd()
            ->groupStart()
                ->where('end_date IS NULL')
                ->orWhere('end_date >=', $now)
            ->groupEnd()
            ->orderBy('sort_order', 'ASC')
            ->findAll();
    }

    /**
     * Get all slides for admin
     */
    public function getAllSlides()
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Update sort order
     */
    public function updateSortOrder($id, $sortOrder)
    {
        return $this->update($id, ['sort_order' => $sortOrder]);
    }
}

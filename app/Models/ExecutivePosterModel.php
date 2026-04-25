<?php

namespace App\Models;

use CodeIgniter\Model;

class ExecutivePosterModel extends Model
{
    protected $table = 'executive_posters';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'caption',
        'image',
        'image_width',
        'image_height',
        'image_focal_x',
        'image_focal_y',
        'link_url',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * โปสเตอร์ที่เปิดใช้งาน — สำหรับแสดงบน slider หน้า About
     */
    public function getActivePosters(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * รายการทั้งหมดสำหรับ admin
     */
    public function getAllForAdmin(): array
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}

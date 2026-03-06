<?php

namespace App\Models;

use CodeIgniter\Model;

class UrgentPopupModel extends Model
{
    protected $table = 'urgent_popups';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'content',
        'link_url',
        'link_text',
        'image',
        'sort_order',
        'is_active',
        'start_date',
        'end_date',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /** จำนวนสูงสุดที่แสดงบนหน้าแรก */
    public const MAX_ACTIVE = 3;

    /**
     * ดึงประกาศด่วนที่เปิดอยู่ (สูงสุด 3 รายการ) สำหรับแสดงป๊อปอัปหน้าแรก
     */
    public function getActivePopups(): array
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
            ->orderBy('id', 'ASC')
            ->limit(self::MAX_ACTIVE)
            ->findAll();
    }

    /**
     * ดึงรายการทั้งหมดสำหรับแอดมิน (เรียงตาม sort_order)
     */
    public function getAllForAdmin(): array
    {
        return $this->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * ตรวจสอบว่าสามารถเพิ่มได้หรือไม่ (ไม่เกิน 3 รายการ)
     */
    public function canAdd(): bool
    {
        return $this->countAll() < self::MAX_ACTIVE;
    }
}

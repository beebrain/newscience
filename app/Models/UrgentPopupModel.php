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

    /** จำนวนสูงสุดที่แสดงบนหน้าแรก (ไม่จำกัดการเพิ่ม) */
    public const MAX_ACTIVE = 5;

    /**
     * ดึงประกาศด่วนที่เปิดอยู่ (สูงสุด MAX_ACTIVE รายการ) สำหรับแสดงป๊อปอัปหน้าแรก
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
     * สามารถเพิ่มได้เสมอ (ไม่จำกัดจำนวน — แต่หน้าแรกจะแสดงไม่เกิน MAX_ACTIVE รายการ)
     */
    public function canAdd(): bool
    {
        return true;
    }

    /**
     * นับจำนวนที่ "เปิด" (is_active = 1) — ใช้เตือนแอดมินว่าเกินจำนวนแสดงผลหรือยัง
     */
    public function countActive(): int
    {
        return $this->where('is_active', 1)->countAllResults();
    }
}

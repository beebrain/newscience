<?php

namespace App\Models;

use CodeIgniter\Model;

class CvEntryModel extends Model
{
    protected $table         = 'cv_entries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'section_id',
        'title',
        'organization',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'metadata',
        'description',
        'sort_order',
        'visible_on_public',
    ];

    /**
     * ตาราง cv_entries ถูกสร้างจาก migration แล้วหรือไม่
     *
     * @param \CodeIgniter\Database\BaseConnection|null $db
     */
    public static function isTablePresent($db = null): bool
    {
        $db ??= \Config\Database::connect();

        return $db->tableExists('cv_entries');
    }

    public function nextSortOrder(int $sectionId): int
    {
        $row = $this->builder()
            ->selectMax('sort_order', 'm')
            ->where('section_id', $sectionId)
            ->get()
            ->getRowArray();

        return (int) (($row['m'] ?? 0) + 1);
    }

    /**
     * ลำดับแสดงรายการ CV: วันที่ตีพิมพ์/เริ่มล่าสุดก่อน แล้วตาม sort_order (ใช้จัดลำดับภายในวันเดียวกัน)
     *
     * ห้ามใช้ '0000-00-00' หรือ '' ในนิพจน์กับ DATE — MySQL strict (NO_ZERO_DATE) จะ error
     */
    public function orderedForCvDisplay(): self
    {
        $expr = '(CASE '
            . 'WHEN start_date IS NOT NULL AND start_date >= \'1000-01-01\' THEN start_date '
            . 'WHEN end_date IS NOT NULL AND end_date >= \'1000-01-01\' THEN end_date '
            . "ELSE '1900-01-01' END)";

        return $this->orderBy($expr, 'DESC', false)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'DESC');
    }

    /**
     * @return array<string, mixed>
     */
    public static function decodeMetadata(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }
        $d = json_decode($json, true);

        return is_array($d) ? $d : [];
    }
}

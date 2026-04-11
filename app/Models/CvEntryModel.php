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
     * ห้ามใช้ NULLIF(..., '') กับคอลัมน์ DATE — MySQL strict จะ error "Incorrect DATE value: ''"
     */
    public function orderedForCvDisplay(): self
    {
        $expr = "COALESCE(NULLIF(start_date, '0000-00-00'), NULLIF(end_date, '0000-00-00'), '1900-01-01')";

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

<?php

namespace App\Models;

use CodeIgniter\Model;

class CvSectionModel extends Model
{
    protected $table         = 'cv_sections';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $allowedFields = [
        'personnel_id',
        'type',
        'title',
        'description',
        'sort_order',
        'is_default',
        'visible_on_public',
    ];

    public function nextSortOrder(int $personnelId): int
    {
        $row = $this->builder()
            ->selectMax('sort_order', 'm')
            ->where('personnel_id', $personnelId)
            ->get()
            ->getRowArray();

        return (int) (($row['m'] ?? 0) + 1);
    }

    public function belongsToPersonnel(int $sectionId, int $personnelId): bool
    {
        $row = $this->find($sectionId);

        return $row !== null && (int) ($row['personnel_id'] ?? 0) === $personnelId;
    }
}

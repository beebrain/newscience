<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramFacilityModel extends Model
{
    protected $table = 'program_facilities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'program_id', 'title', 'description', 'image', 'facility_type', 'sort_order', 'is_published',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all facilities for a program
     */
    public function getByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    /**
     * Get published facilities for a program
     */
    public function getPublishedByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->where('is_published', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramActivityModel extends Model
{
    protected $table = 'program_activities';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'program_id',
        'title',
        'description',
        'activity_date',
        'location',
        'sort_order',
        'is_published',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all activities for a program
     */
    public function getByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('activity_date', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get published activities for a program
     */
    public function getPublishedByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
            ->where('is_published', 1)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('activity_date', 'DESC')
            ->findAll();
    }

    /**
     * Count activities for a program
     */
    public function countByProgramId(int $programId): int
    {
        return $this->where('program_id', $programId)->countAllResults();
    }
}

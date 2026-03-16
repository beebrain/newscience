<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamPublishVersionModel extends Model
{
    protected $table            = 'exam_publish_versions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'semester_label',
        'academic_year',
        'semester_no',
        'exam_type',
        'batch_id',
        'published_by',
        'published_at',
        'is_active',
    ];

    protected $useTimestamps = false;

    /**
     * Get active version for semester/type
     */
    public function getActive(string $semesterLabel, string $examType): ?array
    {
        return $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Set new active version (deactivate others)
     */
    public function setActive(int $batchId, string $semesterLabel, string $examType, int $publishedBy): bool
    {
        // Deactivate existing
        $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->set(['is_active' => 0])
            ->update();

        // Create new active
        return $this->insert([
            'semester_label' => $semesterLabel,
            'academic_year'  => $this->extractYear($semesterLabel),
            'semester_no'    => $this->extractSemester($semesterLabel),
            'exam_type'      => $examType,
            'batch_id'       => $batchId,
            'published_by'   => $publishedBy,
            'published_at'   => date('Y-m-d H:i:s'),
            'is_active'      => 1,
        ]) !== false;
    }

    /**
     * Extract year from semester label (e.g., "1/2568" -> 2568)
     */
    private function extractYear(string $semesterLabel): int
    {
        $parts = explode('/', $semesterLabel);
        return isset($parts[1]) ? (int) $parts[1] : 0;
    }

    /**
     * Extract semester number from label (e.g., "1/2568" -> 1)
     */
    private function extractSemester(string $semesterLabel): int
    {
        $parts = explode('/', $semesterLabel);
        return isset($parts[0]) ? (int) $parts[0] : 0;
    }

    /**
     * Get all versions for a semester
     */
    public function getVersions(string $semesterLabel, string $examType): array
    {
        return $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->orderBy('published_at', 'DESC')
            ->findAll();
    }
}

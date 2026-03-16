<?php

namespace App\Models;

use CodeIgniter\Model;

class ExamImportBatchModel extends Model
{
    protected $table            = 'exam_import_batches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'semester_label',
        'academic_year',
        'semester_no',
        'exam_type',
        'source_filename',
        'source_hash',
        'source_snapshot_path',
        'status',
        'imported_by',
        'published_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get batch by semester and type
     */
    public function getBySemester(string $semesterLabel, string $examType): ?array
    {
        return $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->first();
    }

    /**
     * Get all batches with filtering
     */
    public function getBatches(array $filters = []): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if (!empty($filters['semester_label'])) {
            $builder->where('semester_label', $filters['semester_label']);
        }

        if (!empty($filters['exam_type'])) {
            $builder->where('exam_type', $filters['exam_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->findAll();
    }

    /**
     * Publish a batch
     */
    public function publish(int $batchId, int $publishedBy): bool
    {
        return $this->update($batchId, [
            'status'       => 'published',
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get latest batch for semester/type
     */
    public function getLatestPublished(string $semesterLabel, string $examType): ?array
    {
        return $this->where('semester_label', $semesterLabel)
            ->where('exam_type', $examType)
            ->where('status', 'published')
            ->orderBy('published_at', 'DESC')
            ->first();
    }
}

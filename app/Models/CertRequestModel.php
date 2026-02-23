<?php

namespace App\Models;

use CodeIgniter\Model;

class CertRequestModel extends Model
{
    protected $table = 'cert_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'request_number',
        'student_id',
        'template_id',
        'program_id',
        'level',
        'purpose',
        'copies',
        'note',
        'status',
        'rejected_reason',
        'verified_by',
        'verified_at',
        'approved_by',
        'approved_at',
        'completed_at',
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public const STATUS_PENDING     = 'pending';
    public const STATUS_VERIFIED    = 'verified';
    public const STATUS_APPROVED    = 'approved';
    public const STATUS_GENERATING  = 'generating';
    public const STATUS_COMPLETED   = 'completed';
    public const STATUS_REJECTED    = 'rejected';

    /**
     * Get requests for a specific student ordered by newest first
     */
    public function getByStudent(int $studentId, int $limit = 50): array
    {
        return $this->select('cert_requests.*, cert_templates.name_th as template_name')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->where('cert_requests.student_id', $studentId)
            ->orderBy('cert_requests.id', 'DESC')
            ->findAll($limit);
    }

    /**
     * Fetch pending approvals for a given level (program/faculty)
     */
    public function getPendingForLevel(string $level): array
    {
        return $this->where('level', $level)
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_VERIFIED])
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function findForStudent(int $requestId, int $studentId): ?array
    {
        return $this->select('cert_requests.*, cert_templates.name_th as template_name')
            ->join('cert_templates', 'cert_templates.id = cert_requests.template_id', 'left')
            ->where('cert_requests.id', $requestId)
            ->where('cert_requests.student_id', $studentId)
            ->first();
    }
}

<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * Model for evaluation_referees (from EdocSci emailEvaluate).
 * รายชื่อผู้ทรงคุณวุฒิ/ผู้ประเมิน
 */
class EvaluationRefereeModel extends Model
{
    protected $table            = 'evaluation_referees';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'email',
        'name',
        'status',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat   = 'datetime';

    public function getAll(): array
    {
        return $this->findAll();
    }

    public function getByCondition(array $condition): array
    {
        return $this->where($condition)->orderBy('id', 'ASC')->findAll();
    }

    /**
     * Get active referees (status = 1) for dropdown.
     */
    public function getActiveReferees(): array
    {
        return $this->where('status', 1)->orderBy('name', 'ASC')->findAll();
    }
}

<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * Model for evaluation_scores (from EdocSci evaluatescore).
 * คะแนน/ข้อเสนอแนะจากผู้ประเมิน
 */
class EvaluationScoreModel extends Model
{
    protected $table            = 'evaluate_scores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'teaching_id',
        'email',
        'name',
        'comment',
        'file_doc',
        'score',
        'comment_date',
        'send_date',
        'status',
        'ref_num',
        'created_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $dateFormat   = 'datetime';

    /** Status -1 = soft deleted */
    public const STATUS_DELETED = -1;

    /**
     * Get scores by condition (array or raw where string).
     */
    public function getByCondition($condition): array
    {
        $builder = $this->db->table($this->table);
        if (is_array($condition)) {
            $builder->where($condition);
        } else {
            $builder->where($condition, null, false);
        }
        return $builder->get()->getResultArray();
    }

    public function insertRecord(array $data)
    {
        return $this->insert($data);
    }

    public function updateByCondition($condition, array $data): bool
    {
        $builder = $this->db->table($this->table);
        if (is_array($condition)) {
            $builder->where($condition);
        } else {
            $builder->where($condition, null, false);
        }
        return $builder->update($data);
    }

    /**
     * Check duplicate: same teaching_id + email + ref_num, status != -1.
     */
    public function checkDuplicate(array $data): bool
    {
        $builder = $this->db->table($this->table);
        if (isset($data['teaching_id'])) {
            $builder->where('teaching_id', $data['teaching_id']);
        }
        if (isset($data['email'])) {
            $builder->where('email', $data['email']);
        }
        if (isset($data['ref_num'])) {
            $builder->where('ref_num', $data['ref_num']);
        }
        $builder->where('status !=', self::STATUS_DELETED);
        return $builder->countAllResults() > 0;
    }

    public function getByTeachingId(int $teachingId): array
    {
        return $this->where('teaching_id', $teachingId)
            ->where('status !=', self::STATUS_DELETED)
            ->findAll();
    }

    public function getActiveByTeachingId(int $teachingId): array
    {
        return $this->where('teaching_id', $teachingId)
            ->where('status >=', 0)
            ->orderBy('ref_num', 'ASC')
            ->findAll();
    }

    public function softDelete(int $id): bool
    {
        return (bool) $this->update($id, ['status' => self::STATUS_DELETED]);
    }

    public function countByTeachingId(int $teachingId): int
    {
        return $this->where('teaching_id', $teachingId)
            ->where('status !=', self::STATUS_DELETED)
            ->countAllResults();
    }

    /**
     * Update one score row by teaching_id + email where status >= 0.
     */
    public function updateByTeachingAndEmail(int $teachingId, string $email, array $data): bool
    {
        $builder = $this->db->table($this->table)
            ->where('teaching_id', $teachingId)
            ->where('email', $email)
            ->where('status >=', 0);
        return $builder->update($data);
    }
}

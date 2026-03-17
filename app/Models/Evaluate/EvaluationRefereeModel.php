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
        'institution',
        'expertise',
        'phone',
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

    /**
     * Get all referees with optional search, ordered by name.
     */
    public function getAllPaginated(int $perPage = 20, ?string $search = null): array
    {
        $builder = $this->builder();
        if ($search !== null && $search !== '') {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->orLike('institution', $search)
                ->orLike('expertise', $search)
                ->groupEnd();
        }
        $builder->orderBy('name', 'ASC');
        return $builder->get()->getResultArray();
    }

    /**
     * Create or update a referee record.
     */
    public function saveReferee(array $data, ?int $id = null): bool
    {
        if ($id !== null && $id > 0) {
            return (bool) $this->update($id, $data);
        }
        return (bool) $this->insert($data);
    }

    /**
     * Soft-delete (set status = 0) or hard-delete a referee.
     */
    public function deleteReferee(int $id, bool $hard = false): bool
    {
        if ($hard) {
            return (bool) $this->delete($id);
        }
        return (bool) $this->update($id, ['status' => 0]);
    }
}

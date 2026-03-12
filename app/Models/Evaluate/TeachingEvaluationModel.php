<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * Model for teaching_evaluations (from EdocSci teachingEvaluate).
 * ข้อมูลผู้ขอรับการประเมินผลการสอน
 */
class TeachingEvaluationModel extends Model
{
    protected $table            = 'teaching_evaluations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = [
        'uid',
        'status',
        'first_name',
        'last_name',
        'title_thai',
        'curriculum',
        'position',
        'position_major',
        'position_major_id',
        'start_date',
        'subject_id',
        'subject_name',
        'subject_credit',
        'subject_teacher',
        'subject_detail',
        'file_doc',
        'link_video',
        'submit_date',
        'stop_date',
        'detail',
        'teaching_data',
        'created_at',
        'updated_at',
    ];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat   = 'datetime';

    /**
     * Get submissions by user with status join (if status table exists).
     */
    public function getSubmissionsWithStatus(int $uid)
    {
        $builder = $this->db->table($this->table)
            ->select($this->table . '.*, ' . $this->table . '.id as tid');
        if ($this->db->tableExists('status')) {
            $builder->join('status', 'status.id = ' . $this->table . '.status', 'left');
        }
        return $builder->where('uid', $uid)->get();
    }

    /**
     * Get distinct positions already submitted by user (status 0 or 1).
     */
    public function getSubmittedPositions(int $uid): array
    {
        $result = $this->db->table($this->table)
            ->select('position')
            ->where('uid', $uid)
            ->whereIn('status', [0, 1])
            ->get()
            ->getResultArray();
        $positions = [];
        foreach ($result as $row) {
            if (! empty($row['position'])) {
                $positions[] = $row['position'];
            }
        }
        return array_unique($positions);
    }

    public function getById(int $id): ?array
    {
        $row = $this->find($id);
        return is_array($row) ? $row : null;
    }

    public function getByUser(int $uid): array
    {
        return $this->where('uid', $uid)->findAll();
    }

    public function getAllForAdmin(): array
    {
        return $this->orderBy('id', 'DESC')->findAll();
    }

    public function updateRecord(array $data, int $id): bool
    {
        return (bool) $this->update($id, $data);
    }

    public function countWhere($condition): int
    {
        if (is_array($condition)) {
            foreach ($condition as $k => $v) {
                $this->where($k, $v);
            }
            return $this->countAllResults();
        }
        return $this->db->table($this->table)->where($condition, null, false)->countAllResults();
    }
}

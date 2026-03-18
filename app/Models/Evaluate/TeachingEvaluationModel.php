<?php

namespace App\Models\Evaluate;

use CodeIgniter\Model;

/**
 * Model for teaching_evaluations (from EdocSci teachingEvaluate).
 * ข้อมูลผู้ขอรับการประเมินผลการสอน
 */
class TeachingEvaluationModel extends Model
{
    protected $table            = 'evaluate_teaching';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    /** Status constants */
    public const STATUS_PENDING  = 0;
    public const STATUS_APPROVED = 1;
    public const STATUS_REJECTED = 2;
    public const STATUS_EXPIRED  = 3;

    /** Cooldown period in years before re-application */
    public const COOLDOWN_YEARS = 2;

    protected $allowedFields    = [
        'uid',
        'email',
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
        'approval_date',
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
     * Validate email must be @live.uru.ac.th domain
     */
    public function isValidEvaluateEmail(string $email): bool
    {
        return str_ends_with(strtolower($email), '@live.uru.ac.th');
    }

    /**
     * Get error message for invalid email
     */
    public function getEmailValidationError(): string
    {
        return 'Email must be @live.uru.ac.th domain only';
    }

    /**
     * Get submissions by email with status join (if status table exists).
     */
    public function getSubmissionsWithStatusByEmail(string $email)
    {
        $builder = $this->db->table($this->table)
            ->select($this->table . '.*, ' . $this->table . '.id as tid');
        if ($this->db->tableExists('status')) {
            $builder->join('status', 'status.id = ' . $this->table . '.status', 'left');
        }
        return $builder->where('email', $email)->get();
    }

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
    public function getSubmittedPositionsByEmail(string $email): array
    {
        $result = $this->db->table($this->table)
            ->select('position')
            ->where('email', $email)
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

    public function getByEmail(string $email): array
    {
        return $this->where('email', $email)->findAll();
    }

    /**
     * Search teaching evaluations by partial email match
     */
    public function searchByEmail(string $emailPattern): array
    {
        return $this->like('email', $emailPattern, 'both')->findAll();
    }

    /**
     * Get teaching evaluation with referee scores by email
     */
    public function getByEmailWithScores(string $email): array
    {
        $evaluations = $this->where('email', $email)->findAll();
        $scoreModel = new EvaluationScoreModel();

        foreach ($evaluations as &$eval) {
            $eval['referees'] = $scoreModel->getByTeachingId($eval['id']);
        }

        return $evaluations;
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

    /**
     * ตรวจสอบตำแหน่งที่ผู้ใช้สามารถขอได้:
     * - บล็อกตำแหน่งที่มีคำร้องที่ยัง active อยู่ (stop_date IS NULL)
     * - ถ้าทุกคำร้องของตำแหน่งนั้นมี stop_date แล้ว → ส่งใหม่ได้
     * - ไม่มี cooldown years, ไม่จำกัดระดับ
     */
    public function getAvailablePositionsWithCooldown(int $uid): array
    {
        $allPositions = ['ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์'];

        $active = $this->db->table($this->table)
            ->select('position')
            ->where('uid', $uid)
            ->groupStart()
            ->where('stop_date IS NULL', null, false)
            ->orWhere('stop_date', '')
            ->groupEnd()
            ->get()
            ->getResultArray();

        $blocked = [];
        foreach ($active as $row) {
            if (!empty($row['position'])) {
                $blocked[$row['position']] = true;
            }
        }

        $available = [];
        foreach ($allPositions as $pos) {
            if (empty($blocked[$pos])) {
                $available[] = $pos;
            }
        }

        return $available;
    }

    /**
     * ตรวจสอบว่าตำแหน่งนั้นมีคำร้องที่ยัง active (stop_date IS NULL) หรือไม่
     */
    public function isPositionInCooldown(int $uid, string $position): bool
    {
        $count = $this->db->table($this->table)
            ->where('uid', $uid)
            ->where('position', $position)
            ->groupStart()
            ->where('stop_date IS NULL', null, false)
            ->orWhere('stop_date', '')
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    /**
     * คำนวณวันที่สามารถขอใหม่ได้
     */
    /**
     * Get available positions using email:
     * - บล็อกถ้ามีคำร้อง active (stop_date IS NULL)
     * - ส่งซ้ำระดับเดิมได้เมื่อ stop_date ถูก set แล้ว
     */
    public function getAvailablePositionsWithCooldownByEmail(string $email): array
    {
        $allPositions = ['ผู้ช่วยศาสตราจารย์', 'รองศาสตราจารย์', 'ศาสตราจารย์'];

        $active = $this->db->table($this->table)
            ->select('position')
            ->where('email', $email)
            ->groupStart()
            ->where('stop_date IS NULL', null, false)
            ->groupEnd()
            ->get()
            ->getResultArray();

        $blocked = [];
        foreach ($active as $row) {
            if (!empty($row['position'])) {
                $blocked[$row['position']] = true;
            }
        }

        $available = [];
        foreach ($allPositions as $pos) {
            if (empty($blocked[$pos])) {
                $available[] = $pos;
            }
        }

        return $available;
    }

    /**
     * Check if position has an active (no stop_date) submission using email
     */
    public function isPositionInCooldownByEmail(string $email, string $position): bool
    {
        $count = $this->db->table($this->table)
            ->where('email', $email)
            ->where('position', $position)
            ->groupStart()
            ->where('stop_date IS NULL', null, false)
            ->groupEnd()
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Returns null — no time-based cooldown. Position unlocks when stop_date is set.
     */
    public function getCooldownEndDateByEmail(string $email, string $position): ?string
    {
        return null;
    }
}

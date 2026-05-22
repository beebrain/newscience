<?php

namespace App\Models;

use CodeIgniter\Model;

class SwRegistrationModel extends Model
{
    protected $table         = 'sw_registrations';
    protected $primaryKey    = 'id';
    protected $useTimestamps  = true;
    protected $useSoftDeletes = true;
    protected $deletedField   = 'deleted_at';

    protected $allowedFields = [
        'competition_key',
        'level_key',
        'school_name',
        'school_address',
        'contact_phone',
        'contact_email',
        'team_name',
        'coach_name',
        'coach_position',
        'coach_phone',
        'coach_email',
        'extra',
        'status',
        'ip_address',
    ];

    protected array $casts = [
        'extra' => 'json-array',
    ];

    /**
     * นับจำนวนทีมที่ยังไม่ถูกลบ แยกตาม competition + level
     */
    public function countByLevel(string $competitionKey, string $levelKey): int
    {
        return $this->where('competition_key', $competitionKey)
                    ->where('level_key', $levelKey)
                    ->countAllResults();
    }

    /**
     * นับจำนวนทีมของโรงเรียนนี้ใน competition (ทุก level รวมกัน — สำหรับ python ≤2/สถาบัน)
     */
    public function countBySchool(string $competitionKey, string $schoolName): int
    {
        return $this->where('competition_key', $competitionKey)
                    ->where('school_name', $schoolName)
                    ->countAllResults();
    }

    /**
     * นับจำนวนทีมของโรงเรียนนี้ใน competition + level (สำหรับ ROV 1/สถาบัน/ระดับ)
     */
    public function countBySchoolAndLevel(string $competitionKey, string $levelKey, string $schoolName): int
    {
        return $this->where('competition_key', $competitionKey)
                    ->where('level_key', $levelKey)
                    ->where('school_name', $schoolName)
                    ->countAllResults();
    }

    /**
     * นับจำนวนทีมทั้งหมดใน competition (ทุก level — สำหรับ ROV cap_total 16 ทีม)
     */
    public function countTotal(string $competitionKey): int
    {
        return $this->where('competition_key', $competitionKey)
                    ->countAllResults();
    }

    /**
     * ดึงรายการพร้อม participants สำหรับหน้า verify (public)
     */
    public function getWithParticipants(string $competitionKey, string $levelKey): array
    {
        return $this->select('sw_registrations.*')
                    ->where('competition_key', $competitionKey)
                    ->where('level_key', $levelKey)
                    ->orderBy('created_at', 'ASC')
                    ->findAll();
    }

    /**
     * ดึงรายการทั้งหมดรวมถูกลบแล้ว — สำหรับอาจารย์
     */
    public function getAllIncludingDeleted(string $competitionKey, ?string $levelKey = null): array
    {
        $builder = $this->withDeleted()
                        ->where('competition_key', $competitionKey);
        if ($levelKey !== null) {
            $builder->where('level_key', $levelKey);
        }
        return $builder->orderBy('created_at', 'ASC')->findAll();
    }
}

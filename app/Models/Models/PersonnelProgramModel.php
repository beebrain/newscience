<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot: บุคลากร–หลักสูตร (personnel_programs)
 *
 * กฎธุรกิจ (Relation):
 * - อาจารย์ 1 คน เป็นประธานหลักสูตรได้เพียง 1 หลักสูตร
 * - อาจารย์ 1 คน เป็นอาจารย์ประจำได้หลายหลักสูตร
 * - หลักสูตร 1 หลักสูตร มีประธานหลักสูตรได้เพียง 1 คน
 *
 * DB: UNIQUE(personnel_id, program_id) → คนหนึ่งต่อหลักสูตรหนึ่งมีได้แค่ 1 แถว
 * การบังคับ "1 ประธานต่อหลักสูตร" และ "1 คนเป็นประธานได้แค่ 1 หลักสูตร" ทำในแอป (setProgramsForPersonnelWithPrimary)
 */
class PersonnelProgramModel extends Model
{
    protected $table = 'personnel_programs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'personnel_id', 'program_id', 'role_in_curriculum', 'is_primary', 'sort_order'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * หลักสูตรที่บุคลากรสังกัด (หลายหลักสูตรได้)
     */
    public function getByPersonnelId(int $personnelId): array
    {
        return $this->where('personnel_id', $personnelId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * ดึง personnel_programs ทั้งหมดที่ personnel_id อยู่ในรายการ (สำหรับ batch load)
     */
    public function getByPersonnelIds(array $personnelIds): array
    {
        $ids = array_values(array_filter(array_map('intval', $personnelIds), fn($id) => $id > 0));
        if (empty($ids)) {
            return [];
        }
        return $this->whereIn('personnel_id', $ids)
                    ->orderBy('personnel_id', 'ASC')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * บุคลากรในหลักสูตรนี้ (ผ่าน pivot)
     */
    public function getPersonnelIdsByProgramId(int $programId): array
    {
        $rows = $this->where('program_id', $programId)->findAll();
        return array_map(fn($r) => (int) $r['personnel_id'], $rows);
    }

    /**
     * บุคลากร + บทบาทในหลักสูตรนี้ (สำหรับหน้า personnel ระดับหลักสูตร)
     */
    public function getByProgramId(int $programId): array
    {
        return $this->where('program_id', $programId)
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * ลบการสังกัดหลักสูตรของบุคลากร แล้วใส่ชุดใหม่
     * เคารพกฎ: 1 คนเป็นประธานได้ 1 หลักสูตร, 1 หลักสูตรมีประธานได้ 1 คน
     */
    public function setProgramsForPersonnel(int $personnelId, array $programRoles): void
    {
        $programRoles = $this->normalizeChairRoles($programRoles);
        $this->where('personnel_id', $personnelId)->delete();

        foreach ($programRoles as $i => $pr) {
            $programId = (int) ($pr['program_id'] ?? 0);
            if ($programId <= 0) continue;
            $role = $pr['role_in_curriculum'] ?? null;
            if ($this->isChairRole($role)) {
                $this->where('program_id', $programId)
                     ->like('role_in_curriculum', 'ประธาน')
                     ->delete();
            }
            $this->insert([
                'personnel_id' => $personnelId,
                'program_id' => $programId,
                'role_in_curriculum' => $role,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * บุคลากรนี้ยังมีบทบาทประธานหลักสูตรในหลักสูตรใดหรือไม่ (ใช้สำหรับซิงค์ position)
     */
    public function personnelHasChairRole(int $personnelId): bool
    {
        $row = $this->where('personnel_id', $personnelId)
                    ->like('role_in_curriculum', 'ประธาน')
                    ->first();
        return $row !== null;
    }

    /**
     * เพิ่มการสังกัดหลักสูตร (ถ้ายังไม่มี)
     */
    public function addProgram(int $personnelId, int $programId, ?string $roleInCurriculum = null, bool $isPrimary = false): bool
    {
        $exists = $this->where('personnel_id', $personnelId)
                       ->where('program_id', $programId)
                       ->first();
        if ($exists) return true;
        return (bool) $this->insert([
            'personnel_id' => $personnelId,
            'program_id' => $programId,
            'role_in_curriculum' => $roleInCurriculum,
            'is_primary' => $isPrimary ? 1 : 0,
            'sort_order' => 0,
        ]);
    }

    /**
     * Get coordinator (ประธานหลักสูตร) for a program
     * This replaces programs.coordinator_id as Single Source of Truth
     *
     * @param int $programId Program ID
     * @return array|null Personnel data or null
     */
    public function getCoordinatorByProgramId(int $programId): ?array
    {
        $row = $this->where('program_id', $programId)
                    ->like('role_in_curriculum', 'ประธาน')
                    ->first();

        if (!$row) {
            return null;
        }

        $personnelModel = new \App\Models\PersonnelModel();
        return $personnelModel->find($row['personnel_id']);
    }

    /**
     * Get coordinator ID for a program
     *
     * @param int $programId Program ID
     * @return int|null Personnel ID or null
     */
    public function getCoordinatorIdByProgramId(int $programId): ?int
    {
        $row = $this->where('program_id', $programId)
                    ->like('role_in_curriculum', 'ประธาน')
                    ->first();

        return $row ? (int) $row['personnel_id'] : null;
    }

    /**
     * Get primary program for a personnel
     *
     * @param int $personnelId Personnel ID
     * @return array|null Program data or null
     */
    public function getPrimaryProgramForPersonnel(int $personnelId): ?array
    {
        // Check if is_primary column exists
        if (!$this->db->fieldExists('is_primary', 'personnel_programs')) {
            // Fallback: return first program
            $row = $this->where('personnel_id', $personnelId)
                        ->orderBy('sort_order', 'ASC')
                        ->first();
        } else {
            // Use is_primary flag
            $row = $this->where('personnel_id', $personnelId)
                        ->where('is_primary', 1)
                        ->first();

            // Fallback to first if no primary set
            if (!$row) {
                $row = $this->where('personnel_id', $personnelId)
                            ->orderBy('sort_order', 'ASC')
                            ->first();
            }
        }

        if (!$row) {
            return null;
        }

        $programModel = new \App\Models\ProgramModel();
        return $programModel->find($row['program_id']);
    }

    /**
     * Set a program as primary for a personnel
     *
     * @param int $personnelId Personnel ID
     * @param int $programId Program ID to set as primary
     * @return void
     */
    public function setAsPrimary(int $personnelId, int $programId): void
    {
        // Check if is_primary column exists
        if (!$this->db->fieldExists('is_primary', 'personnel_programs')) {
            return;
        }

        // Clear existing primary
        $this->where('personnel_id', $personnelId)
             ->set('is_primary', 0)
             ->update();

        // Set new primary
        $this->where('personnel_id', $personnelId)
             ->where('program_id', $programId)
             ->set('is_primary', 1)
             ->update();
    }

    /**
     * Get all coordinators (ประธานหลักสูตร) for all programs
     *
     * @return array Array of [program_id => personnel_id]
     */
    public function getAllCoordinators(): array
    {
        $rows = $this->like('role_in_curriculum', 'ประธาน')
                     ->findAll();

        $result = [];
        foreach ($rows as $row) {
            $result[(int) $row['program_id']] = (int) $row['personnel_id'];
        }
        return $result;
    }

    /**
     * Set programs for personnel with is_primary support.
     * Enforces: 1 person = at most 1 chair role; 1 program = 1 chair.
     *
     * @param int $personnelId Personnel ID
     * @param array $programRoles Array of [program_id, role_in_curriculum, is_primary]
     * @return void
     */
    public function setProgramsForPersonnelWithPrimary(int $personnelId, array $programRoles): void
    {
        $programRoles = $this->normalizeChairRoles($programRoles);

        $this->where('personnel_id', $personnelId)->delete();

        $chairProgramIds = [];
        foreach ($programRoles as $i => $pr) {
            $programId = (int) ($pr['program_id'] ?? 0);
            if ($programId <= 0) continue;

            $role = $pr['role_in_curriculum'] ?? null;
            if ($role !== null && $role !== '' && $this->isChairRole($role)) {
                $chairProgramIds[] = $programId;
            }
        }

        foreach ($chairProgramIds as $programId) {
            $this->where('program_id', $programId)
                 ->like('role_in_curriculum', 'ประธาน')
                 ->delete();
        }

        $hasPrimary = false;
        foreach ($programRoles as $i => $pr) {
            $programId = (int) ($pr['program_id'] ?? 0);
            if ($programId <= 0) continue;

            $isPrimary = !empty($pr['is_primary']) ? 1 : 0;
            if ($isPrimary) $hasPrimary = true;

            $this->insert([
                'personnel_id' => $personnelId,
                'program_id' => $programId,
                'role_in_curriculum' => $pr['role_in_curriculum'] ?? null,
                'is_primary' => $isPrimary,
                'sort_order' => $i,
            ]);
        }

        if (!$hasPrimary && !empty($programRoles)) {
            $firstProgramId = (int) ($programRoles[0]['program_id'] ?? 0);
            if ($firstProgramId > 0) {
                $this->where('personnel_id', $personnelId)
                     ->where('program_id', $firstProgramId)
                     ->set('is_primary', 1)
                     ->update();
            }
        }
    }

    /**
     * บทบาทนับเป็นประธานหลักสูตรหรือไม่
     */
    public function isChairRole(?string $role): bool
    {
        if ($role === null || $role === '') {
            return false;
        }
        return mb_strpos($role, 'ประธาน') !== false;
    }

    /**
     * ปรับรายการหลักสูตรให้คนหนึ่งเป็นประธานได้แค่ 1 หลักสูตร (เก็บเฉพาะตัวแรก)
     */
    protected function normalizeChairRoles(array $programRoles): array
    {
        $chairFound = false;
        $out = [];
        foreach ($programRoles as $pr) {
            $role = $pr['role_in_curriculum'] ?? null;
            $isChair = $this->isChairRole($role);
            if ($isChair && $chairFound) {
                $pr['role_in_curriculum'] = 'อาจารย์ประจำหลักสูตร';
            }
            if ($isChair) {
                $chairFound = true;
            }
            $out[] = $pr;
        }
        return $out;
    }
}

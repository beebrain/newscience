<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Pivot: อาจารย์ 1 คน สังกัดได้หลายหลักสูตร
 */
class PersonnelProgramModel extends Model
{
    protected $table = 'personnel_programs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'personnel_id', 'program_id', 'role_in_curriculum', 'sort_order'
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
     * บุคลากรในหลักสูตรนี้ (ผ่าน pivot)
     */
    public function getPersonnelIdsByProgramId(int $programId): array
    {
        $rows = $this->where('program_id', $programId)->findAll();
        return array_map(fn($r) => (int) $r['personnel_id'], $rows);
    }

    /**
     * ลบการสังกัดหลักสูตรของบุคลากร แล้วใส่ชุดใหม่
     */
    public function setProgramsForPersonnel(int $personnelId, array $programRoles): void
    {
        $this->where('personnel_id', $personnelId)->delete();
        foreach ($programRoles as $i => $pr) {
            $programId = (int) ($pr['program_id'] ?? 0);
            if ($programId <= 0) continue;
            $this->insert([
                'personnel_id' => $personnelId,
                'program_id' => $programId,
                'role_in_curriculum' => $pr['role_in_curriculum'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * เพิ่มการสังกัดหลักสูตร (ถ้ายังไม่มี)
     */
    public function addProgram(int $personnelId, int $programId, ?string $roleInCurriculum = null): bool
    {
        $exists = $this->where('personnel_id', $personnelId)
                       ->where('program_id', $programId)
                       ->first();
        if ($exists) return true;
        return (bool) $this->insert([
            'personnel_id' => $personnelId,
            'program_id' => $programId,
            'role_in_curriculum' => $roleInCurriculum,
            'sort_order' => 0,
        ]);
    }
}

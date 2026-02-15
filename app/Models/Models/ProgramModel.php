<?php

namespace App\Models;

use CodeIgniter\Model;

class ProgramModel extends Model
{
    protected $table = 'programs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name_th', 'name_en', 'degree_th', 'degree_en', 'level',
        'department_id', 'description', 'description_en', 'credits',
        'duration', 'website', 'curriculum_file', 'image',
        'coordinator_id', 'chair_personnel_id', 'sort_order', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    /** ปิดการกรองเฉพาะฟิลด์ที่เปลี่ยน เพื่อไม่ให้ update([]) เมื่อค่าที่ส่งเท่ากับค่าเดิมแล้วเกิด DataException */
    protected bool $updateOnlyChanged = false;

    /**
     * Get active programs
     */
    public function getActive()
    {
        return $this->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get programs by level
     */
    public function getByLevel($level)
    {
        return $this->where('level', $level)
                    ->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get programs by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
                    ->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get bachelor programs
     */
    public function getBachelor()
    {
        return $this->getByLevel('bachelor');
    }
    
    /**
     * Get master programs
     */
    public function getMaster()
    {
        return $this->getByLevel('master');
    }
    
    /**
     * Get doctorate programs
     */
    public function getDoctorate()
    {
        return $this->getByLevel('doctorate');
    }
    
    /**
     * Get programs with department info
     */
    public function getWithDepartment()
    {
        return $this->select('programs.*, departments.name_th as department_name, departments.name_en as department_name_en')
                    ->join('departments', 'departments.id = programs.department_id', 'left')
                    ->where('programs.status', 'active')
                    ->orderBy('programs.sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * Get all programs (including inactive) with department for admin
     */
    public function getAllWithDepartment()
    {
        return $this->select('programs.*, departments.name_th as department_name, departments.name_en as department_name_en')
                    ->join('departments', 'departments.id = programs.department_id', 'left')
                    ->orderBy('programs.sort_order', 'ASC')
                    ->orderBy('programs.name_th', 'ASC')
                    ->findAll();
    }

    /**
     * Get programs with coordinator (ประธานหลักสูตร) info.
     * Prefers programs.chair_personnel_id; falls back to personnel_programs.role_in_curriculum.
     *
     * @return array Programs with coordinator data
     */
    public function getWithCoordinator()
    {
        $programs = $this->getAllWithDepartment();

        if (empty($programs)) {
            return $programs;
        }

        $coordinatorIds = [];
        foreach ($programs as $p) {
            $cid = (int) ($p['chair_personnel_id'] ?? 0);
            if ($cid > 0) {
                $coordinatorIds[] = $cid;
            }
        }

        $ppModel = new PersonnelProgramModel();
        $coordinatorsFromPp = $ppModel->getAllCoordinators();
        foreach ($programs as $p) {
            $programId = (int) ($p['id'] ?? 0);
            if (empty($coordinatorIds) || !((int)($p['chair_personnel_id'] ?? 0) > 0)) {
                $cid = $coordinatorsFromPp[$programId] ?? null;
                if ($cid) {
                    $coordinatorIds[] = $cid;
                }
            }
        }
        $coordinatorIds = array_values(array_unique(array_filter($coordinatorIds, fn($id) => $id > 0)));

        $personnelMap = [];
        if (!empty($coordinatorIds)) {
            $personnelModel = new PersonnelModel();
            $personnelRows = $personnelModel->whereIn('id', $coordinatorIds)->findAll();
            foreach ($personnelRows as $p) {
                $personnelMap[(int) $p['id']] = $p;
            }
        }

        foreach ($programs as &$program) {
            $programId = (int) $program['id'];
            $coordId = (int) ($program['chair_personnel_id'] ?? 0);
            if ($coordId <= 0) {
                $coordId = $coordinatorsFromPp[$programId] ?? 0;
            }

            if ($coordId > 0 && isset($personnelMap[$coordId])) {
                $coord = $personnelMap[$coordId];
                $program['coordinator_name'] = trim($coord['name'] ?? '');
                $program['coordinator_name_en'] = trim($coord['name_en'] ?? '') !== '' ? trim($coord['name_en']) : trim($coord['name'] ?? '');
                $program['coordinator_id_from_pp'] = $coordId;
            } else {
                $program['coordinator_name'] = null;
                $program['coordinator_name_en'] = null;
                $program['coordinator_id_from_pp'] = null;
            }
        }

        return $programs;
    }

    /**
     * Get active programs with coordinator info
     *
     * @return array Active programs with coordinator data
     */
    public function getActiveWithCoordinator()
    {
        $programs = $this->getWithCoordinator();
        return array_filter($programs, fn($p) => $p['status'] === 'active');
    }

    /**
     * Sync programs.chair_personnel_id from personnel_programs (role ประธานหลักสูตร).
     * Call after saving organization program assignments.
     */
    public function syncChairFromPersonnelPrograms(): void
    {
        if (!$this->db->fieldExists('chair_personnel_id', 'programs')) {
            return;
        }
        $ppModel = new PersonnelProgramModel();
        $coordinators = $ppModel->getAllCoordinators();
        $programs = $this->select('id')->findAll();
        foreach ($programs as $row) {
            $programId = (int) ($row['id'] ?? 0);
            if ($programId <= 0) continue;
            $chairId = $coordinators[$programId] ?? null;
            $this->update($programId, ['chair_personnel_id' => $chairId]);
        }
    }

    /**
     * Get programs for dropdown selection
     *
     * @param string $lang Language ('th' or 'en')
     * @return array [id => name]
     */
    public function getForDropdown(string $lang = 'th'): array
    {
        $programs = $this->getActive();
        $result = [];
        foreach ($programs as $p) {
            $name = $lang === 'en' && !empty($p['name_en']) ? $p['name_en'] : $p['name_th'];
            $level = $p['level'] ?? 'bachelor';
            $levelLabel = match ($level) {
                'master' => '(ป.โท)',
                'doctorate' => '(ป.เอก)',
                default => '(ป.ตรี)',
            };
            $result[(int) $p['id']] = $name . ' ' . $levelLabel;
        }
        return $result;
    }
}

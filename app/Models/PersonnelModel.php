<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonnelModel extends Model
{
    protected $table = 'personnel';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title', 'first_name', 'last_name', 'first_name_en', 'last_name_en',
        'position', 'position_en', 'department_id', 'program_id', 'email', 'phone',
        'image', 'bio', 'bio_en', 'education', 'expertise',
        'sort_order', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get full name in Thai
     */
    public function getFullName($person)
    {
        return trim($person['title'] . ' ' . $person['first_name'] . ' ' . $person['last_name']);
    }
    
    /**
     * Get full name in English
     */
    public function getFullNameEn($person)
    {
        return trim($person['first_name_en'] . ' ' . $person['last_name_en']);
    }
    
    /**
     * Get active personnel
     */
    public function getActive()
    {
        return $this->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get personnel by department
     */
    public function getByDepartment($departmentId)
    {
        return $this->where('department_id', $departmentId)
                    ->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get executives (dean, vice deans)
     */
    public function getExecutives()
    {
        return $this->where('status', 'active')
                    ->where('position IS NOT NULL')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get dean
     */
    public function getDean()
    {
        return $this->like('position', 'คณบดี')
                    ->where('status', 'active')
                    ->first();
    }

    /**
     * Get active personnel with department and program names (join)
     * Run database/add_personnel_program_id.sql to add program_id column if missing.
     */
    public function getActiveWithDepartment()
    {
        $select = 'personnel.*, departments.name_th as department_name_th, departments.name_en as department_name_en';
        if ($this->db->fieldExists('program_id', 'personnel')) {
            $select .= ', programs.name_th as program_name_th, programs.name_en as program_name_en';
        }
        $builder = $this->select($select)
                    ->join('departments', 'departments.id = personnel.department_id', 'left')
                    ->where('personnel.status', 'active');
        if ($this->db->fieldExists('program_id', 'personnel')) {
            $builder->join('programs', 'programs.id = personnel.program_id', 'left');
        }
        return $builder->orderBy('departments.sort_order', 'ASC')
                    ->orderBy('personnel.sort_order', 'ASC')
                    ->findAll();
    }

    /**
     * หลักสูตรที่บุคลากรสังกัด (หลายหลักสูตร) จากตาราง personnel_programs
     * คืนค่า [] ถ้าตารางไม่มีหรือยังไม่มีข้อมูล
     */
    public function getProgramsForPersonnel(int $personnelId): array
    {
        if (!$this->db->tableExists('personnel_programs')) {
            return [];
        }
        $model = new \App\Models\PersonnelProgramModel();
        return $model->getByPersonnelId($personnelId);
    }
}

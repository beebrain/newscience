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
        'coordinator_id', 'sort_order', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
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
}

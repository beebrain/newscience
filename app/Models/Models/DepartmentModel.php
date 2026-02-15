<?php

namespace App\Models;

use CodeIgniter\Model;

class DepartmentModel extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name_th', 'name_en', 'code', 'description', 'description_en',
        'image', 'website', 'phone', 'email', 'head_personnel_id',
        'sort_order', 'status'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    /**
     * Get active departments
     */
    public function getActive()
    {
        return $this->where('status', 'active')
                    ->orderBy('sort_order', 'ASC')
                    ->findAll();
    }
    
    /**
     * Get department with head personnel
     */
    public function getWithHead($departmentId)
    {
        $dept = $this->find($departmentId);
        if ($dept && $dept['head_personnel_id']) {
            $personnelModel = new PersonnelModel();
            $dept['head'] = $personnelModel->find($dept['head_personnel_id']);
        }
        return $dept;
    }
}

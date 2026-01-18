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
        'position', 'position_en', 'department_id', 'email', 'phone',
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
}

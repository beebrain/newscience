<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * หน่วยงาน 5 กลุ่มคงที่: ผู้บริหาร, สำนักงานคณบดี, หัวหน้าหน่วยวิจัย, หลักสูตรป.ตรี, หลักสูตรบัณฑิต
 * ใช้สำหรับจัดกลุ่มในหน้าบุคลากร (programs = สาขา ภายใต้หลักสูตรป.ตรี/บัณฑิต)
 */
class OrganizationUnitModel extends Model
{
    protected $table = 'organization_units';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name_th', 'name_en', 'code', 'sort_order'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * ดึงหน่วยงานทั้ง 5 เรียงตาม sort_order
     */
    public function getOrdered(): array
    {
        if (! $this->db->tableExists($this->table)) {
            return $this->getDefaultUnits();
        }
        $rows = $this->orderBy('sort_order', 'ASC')->findAll();
        return $rows ?: $this->getDefaultUnits();
    }

    /**
     * ค่าเริ่มต้นถ้าตารางยังไม่มี
     */
    public function getDefaultUnits(): array
    {
        return [
            ['id' => 1, 'name_th' => 'ผู้บริหาร', 'name_en' => 'Executives', 'code' => 'executives', 'sort_order' => 1],
            ['id' => 2, 'name_th' => 'สำนักงานคณบดี', 'name_en' => "Dean's Office", 'code' => 'office', 'sort_order' => 2],
            ['id' => 3, 'name_th' => 'หัวหน้าหน่วยการจัดการงานวิจัย', 'name_en' => 'Research Management Unit', 'code' => 'research', 'sort_order' => 3],
            ['id' => 4, 'name_th' => 'หลักสูตรระดับปริญญาตรี', 'name_en' => "Bachelor's Degree Programs", 'code' => 'bachelor', 'sort_order' => 4],
            ['id' => 5, 'name_th' => 'หลักสูตรระดับบัณฑิตศึกษา', 'name_en' => 'Graduate Programs', 'code' => 'graduate', 'sort_order' => 5],
        ];
    }
}

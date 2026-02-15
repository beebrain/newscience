<?php

namespace App\Libraries;

use CodeIgniter\Database\BaseConnection;

/**
 * สร้างข้อมูลเริ่มต้น (หน่วยงาน/หลักสูตร) อัตโนมัติเมื่อยังไม่มี
 * ไม่ใช้ตาราง departments (ยกเลิกแล้ว ใช้ organization_units เท่านั้น)
 */
class SeedEnsure
{
    /** organization_units: 4 = หลักสูตรป.ตรี, 5 = หลักสูตรบัณฑิต */
    private const ORG_UNIT_BACHELOR = 4;
    private const ORG_UNIT_GRADUATE = 5;

    /**
     * ให้มีหลักสูตร "วิศวกรรมคอมพิวเตอร์" ในระบบ (สังกัดหน่วยงานหลักสูตรป.ตรี)
     * เรียกเมื่อโหลดหน้าแอดมินที่ใช้รายการหลักสูตร
     * ใช้เฉพาะ organization_units และ programs — ไม่อ้างอิงตาราง departments
     */
    public static function ensureComputerEngineering(?BaseConnection $db = null): void
    {
        if ($db === null) {
            $db = \Config\Database::connect();
        }

        if (! $db->tableExists('organization_units') || ! $db->fieldExists('organization_unit_id', 'programs')) {
            return;
        }

        $progName = 'วิศวกรรมคอมพิวเตอร์';

        $progExists = $db->table('programs')
            ->where('name_th', $progName)
            ->where('organization_unit_id', self::ORG_UNIT_BACHELOR)
            ->countAllResults();
        if ($progExists > 0) {
            return;
        }

        $maxOrder = $db->table('programs')->selectMax('sort_order')->get()->getRow();
        $nextOrder = ((int) ($maxOrder->sort_order ?? 0)) + 1;

        $db->table('programs')->insert([
            'name_th'               => $progName,
            'name_en'               => 'Computer Engineering',
            'degree_th'             => 'วิทยาศาสตรบัณฑิต',
            'degree_en'             => 'Bachelor of Science',
            'level'                 => 'bachelor',
            'organization_unit_id'  => self::ORG_UNIT_BACHELOR,
            'duration'               => '4 ปี',
            'sort_order'             => $nextOrder,
            'status'                => 'active',
        ]);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ลบตาราง user_programs — ใช้ personnel + personnel_programs กำหนดสิทธิ์หลักสูตรแทน
 */
class DropUserPrograms extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('user_programs')) {
            $this->forge->dropTable('user_programs', true);
        }
    }

    public function down()
    {
        // ไม่สร้างกลับ — ให้ใช้ migration เดิม create_user_programs ถ้าต้องการ
    }
}

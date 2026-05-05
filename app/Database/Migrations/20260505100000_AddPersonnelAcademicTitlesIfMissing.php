<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * คำนำหน้า/ชื่อแสดงหน้าเว็บและ CV ใช้ personnel เป็นหลัก — เพิ่มคอลัมน์ถ้ายังไม่มี (กรณีเคยรัน migration ลบ)
 */
class AddPersonnelAcademicTitlesIfMissing extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('personnel')) {
            return;
        }
        if (! $this->db->fieldExists('academic_title', 'personnel')) {
            $this->forge->addColumn('personnel', [
                'academic_title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
        if (! $this->db->fieldExists('academic_title_en', 'personnel')) {
            $this->forge->addColumn('personnel', [
                'academic_title_en' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('academic_title_en', 'personnel')) {
            $this->forge->dropColumn('personnel', 'academic_title_en');
        }
        if ($this->db->fieldExists('academic_title', 'personnel')) {
            $this->forge->dropColumn('personnel', 'academic_title');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * คำนำหน้าชื่อใช้จากตาราง user เท่านั้น — ลบ academic_title / academic_title_en ออกจาก personnel
 */
class DropPersonnelAcademicTitles extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('personnel')) {
            return;
        }
        if ($this->db->fieldExists('academic_title_en', 'personnel')) {
            $this->forge->dropColumn('personnel', 'academic_title_en');
        }
        if ($this->db->fieldExists('academic_title', 'personnel')) {
            $this->forge->dropColumn('personnel', 'academic_title');
        }
    }

    public function down(): void
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
}

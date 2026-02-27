<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มคอลัมน์ elos_json และ curriculum_json ใน program_pages (สำหรับ AUN-QA / เนื้อหาหลักสูตร)
 * รันบน server ที่สร้างตารางจาก AddProgramPages.sql เดิมที่ยังไม่มีคอลัมน์เหล่านี้
 */
class AddElosCurriculumJsonToProgramPages extends Migration
{
    public function up()
    {
        $fields = [];
        if (!$this->db->fieldExists('elos_json', 'program_pages')) {
            $fields['elos_json'] = [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'graduate_profile',
            ];
        }
        if (!$this->db->fieldExists('curriculum_json', 'program_pages')) {
            $fields['curriculum_json'] = [
                'type'       => 'TEXT',
                'null'       => true,
                'after'      => 'elos_json',
            ];
        }
        if (!empty($fields)) {
            $this->forge->addColumn('program_pages', $fields);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('elos_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'elos_json');
        }
        if ($this->db->fieldExists('curriculum_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'curriculum_json');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มคอลัมน์ alumni_messages_json ใน program_pages (ศิษย์เก่าถึงรุ่นน้อง)
 */
class AddAlumniMessagesToProgramPages extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('alumni_messages_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'alumni_messages_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'curriculum_json',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('alumni_messages_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'alumni_messages_json');
        }
    }
}

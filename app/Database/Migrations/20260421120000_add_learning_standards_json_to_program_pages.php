<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * มาตรฐานการเรียนรู้ (Learning Standards) คู่กับ PLO/ELO บนหน้าหลักสูตร AUN-QA
 */
class AddLearningStandardsJsonToProgramPages extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('learning_standards_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'learning_standards_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'after' => 'elos_json',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('learning_standards_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'learning_standards_json');
        }
    }
}

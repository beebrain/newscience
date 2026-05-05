<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropUnusedProgramPageFields extends Migration
{
    private array $columns = ['teaching_methods', 'assessment_methods', 'success_outcomes'];

    public function up(): void
    {
        foreach ($this->columns as $col) {
            if ($this->db->fieldExists($col, 'program_pages')) {
                $this->forge->dropColumn('program_pages', $col);
            }
        }
    }

    public function down(): void
    {
        $fields = [
            'teaching_methods' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'after'      => 'course_details',
            ],
            'assessment_methods' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'after'      => 'teaching_methods',
            ],
            'success_outcomes' => [
                'type'       => 'TEXT',
                'null'       => true,
                'default'    => null,
                'after'      => 'admission_info',
            ],
        ];
        $this->forge->addColumn('program_pages', $fields);
    }
}

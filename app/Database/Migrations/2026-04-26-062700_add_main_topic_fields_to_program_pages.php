<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMainTopicFieldsToProgramPages extends Migration
{
    public function up(): void
    {
        $fields = [
            'course_details' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Main topic 5: course spec, CLO, curriculum mapping details',
                'after'   => 'study_plan',
            ],
            'teaching_methods' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Main topic 6: teaching and learning approaches',
                'after'   => 'course_details',
            ],
            'assessment_methods' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Main topic 7: assessment, rubrics, exam policy and appeal process',
                'after'   => 'teaching_methods',
            ],
            'graduation_requirements' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Main topic 8: graduation criteria and completion requirements',
                'after'   => 'assessment_methods',
            ],
            'success_outcomes' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'comment' => 'Main topic 11: graduate outcomes, employment, awards and success metrics',
                'after'   => 'admission_info',
            ],
        ];

        foreach ($fields as $name => $definition) {
            if (! $this->hasProgramPageField($name)) {
                $this->forge->addColumn('program_pages', [$name => $definition]);
            }
        }
    }

    public function down(): void
    {
        foreach (['success_outcomes', 'graduation_requirements', 'assessment_methods', 'teaching_methods', 'course_details'] as $name) {
            if ($this->hasProgramPageField($name)) {
                $this->forge->dropColumn('program_pages', $name);
            }
        }
    }

    private function hasProgramPageField(string $field): bool
    {
        $result = $this->db->query('SHOW COLUMNS FROM `program_pages` LIKE ?', [$field]);
        return $result !== false && $result->getRowArray() !== null;
    }
}

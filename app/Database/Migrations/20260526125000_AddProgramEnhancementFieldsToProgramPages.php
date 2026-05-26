<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProgramEnhancementFieldsToProgramPages extends Migration
{
    public function up(): void
    {
        $fields = [];
        if (! $this->db->fieldExists('program_identity', 'program_pages')) {
            $fields['program_identity'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'objectives',
            ];
        }
        if (! $this->db->fieldExists('ylo_json', 'program_pages')) {
            $fields['ylo_json'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'elos_json',
            ];
        }
        if (! $this->db->fieldExists('curriculum_credit_structure_json', 'program_pages')) {
            $fields['curriculum_credit_structure_json'] = [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'curriculum_json',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('program_pages', $fields);
        }
    }

    public function down(): void
    {
        foreach (['program_identity', 'ylo_json', 'curriculum_credit_structure_json'] as $column) {
            if ($this->db->fieldExists($column, 'program_pages')) {
                $this->forge->dropColumn('program_pages', $column);
            }
        }
    }
}

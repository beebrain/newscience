<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTuitionFeesJsonToProgramPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('tuition_fees_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'tuition_fees_json' => [
                    'type'    => 'LONGTEXT',
                    'null'    => true,
                    'comment' => 'JSON: [{label,amount,note}]',
                    'after'   => 'tuition_fees',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('tuition_fees_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'tuition_fees_json');
        }
    }
}

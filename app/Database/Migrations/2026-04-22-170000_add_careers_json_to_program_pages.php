<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * รายการอาชีพแบบการ์ม (title, desc, icon) สำหรับหน้า SPA
 */
class AddCareersJsonToProgramPages extends Migration
{
    public function up(): void
    {
        if (! $this->db->fieldExists('careers_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'careers_json' => [
                    'type'    => 'LONGTEXT',
                    'null'    => true,
                    'comment' => 'JSON: รายการอาชีพ [{title,desc,icon}]',
                    'after'   => 'career_prospects',
                ],
            ]);
        }
    }

    public function down(): void
    {
        if ($this->db->fieldExists('careers_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'careers_json');
        }
    }
}

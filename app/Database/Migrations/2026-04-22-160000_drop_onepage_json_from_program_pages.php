<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ลบคอลัมน์ onepage_json หลังถอดฟีเจอร์ Onepage
 */
class DropOnepageJsonFromProgramPages extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('program_pages') && $this->db->fieldExists('onepage_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'onepage_json');
        }
    }

    public function down(): void
    {
        if ($this->db->tableExists('program_pages') && ! $this->db->fieldExists('onepage_json', 'program_pages')) {
            $this->forge->addColumn('program_pages', [
                'onepage_json' => [
                    'type'    => 'LONGTEXT',
                    'null'    => true,
                    'comment' => 'JSON: เนื้อหาแยก section สำหรับ p/{id}/onepage',
                    'after'   => 'meta_description',
                ],
            ]);
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ข้อมูลหน้า Onepage รวมต่อหลักสูตร (HTML ราย section ใน JSON)
 */
class AddOnepageJsonToProgramPages extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('onepage_json', 'program_pages')) {
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

    public function down()
    {
        if ($this->db->fieldExists('onepage_json', 'program_pages')) {
            $this->forge->dropColumn('program_pages', 'onepage_json');
        }
    }
}

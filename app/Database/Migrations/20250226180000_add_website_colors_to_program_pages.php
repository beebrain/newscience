<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มคอลัมน์สีข้อความและสีพื้นหลังสำหรับการตั้งค่าเว็บหลักสูตร (program_pages)
 */
class AddWebsiteColorsToProgramPages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('program_pages', [
            'text_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'null'       => true,
                'after'      => 'theme_color',
            ],
            'background_color' => [
                'type'       => 'VARCHAR',
                'constraint' => 7,
                'null'       => true,
                'after'      => 'text_color',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('program_pages', ['text_color', 'background_color']);
    }
}

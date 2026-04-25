<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตาราง executive_posters: โปสเตอร์แนะนำผู้บริหารคณะ
 * แสดงเป็น slider บนหน้า About (แท็บผู้บริหาร) — อัปโหลดรูปได้ไม่จำกัด
 */
class CreateExecutivePosters extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('executive_posters')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'comment'    => 'ชื่อ/ตำแหน่งบนโปสเตอร์ (ใช้เป็น alt text และ caption)',
            ],
            'caption' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment'    => 'คำบรรยายรอง (ไม่บังคับ)',
            ],
            'image' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'image_width' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'image_height' => [
                'type'     => 'INT',
                'unsigned' => true,
                'null'     => true,
            ],
            'image_focal_x' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'image_focal_y' => [
                'type'     => 'TINYINT',
                'unsigned' => true,
                'null'     => true,
            ],
            'link_url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['is_active', 'sort_order']);
        $this->forge->createTable('executive_posters');
    }

    public function down()
    {
        $this->forge->dropTable('executive_posters', true);
    }
}

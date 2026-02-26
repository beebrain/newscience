<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEdocVolumesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'unsigned'   => true,
            ],
            'volume_type' => [
                'type'       => 'ENUM',
                'constraint' => ['send_internal', 'receive_internal', 'external', 'order', 'announcement'],
            ],
            'volume_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
            'created_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
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
        $this->forge->addUniqueKey(['year', 'volume_type']);
        $this->forge->addKey('year');
        $this->forge->addKey('is_active');
        $this->forge->createTable('edoc_volumes', true);
    }

    public function down()
    {
        $this->forge->dropTable('edoc_volumes', true);
    }
}

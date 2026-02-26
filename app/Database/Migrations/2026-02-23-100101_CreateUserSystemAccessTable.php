<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSystemAccessTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_uid' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'system_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'access_level' => [
                'type'       => 'ENUM',
                'constraint' => ['view', 'manage', 'admin'],
                'null'       => false,
                'default'    => 'view',
            ],
            'granted_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'granted_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['user_uid', 'system_id'], 'uk_user_system');
        $this->forge->addKey('system_id', false, false, 'idx_system');
        $this->forge->addKey('user_uid', false, false, 'idx_user');

        // Foreign keys
        $this->forge->addForeignKey('user_uid', 'user', 'uid', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('system_id', 'systems', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('granted_by', 'user', 'uid', 'SET NULL', 'CASCADE');

        $this->forge->createTable('user_system_access', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_system_access');
    }
}

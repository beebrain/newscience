<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSwParticipants extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'registration_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => false,
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => false,
            ],
            'level_class' => [
                'type'       => 'VARCHAR',
                'constraint' => 80,
                'null'       => true,
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'main',
                'null'       => false,
            ],
            'game_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'age' => [
                'type'     => 'INT',
                'constraint' => 3,
                'null'     => true,
            ],
            'occupation' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'line_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'sort_order' => [
                'type'    => 'INT',
                'constraint' => 4,
                'default' => 0,
                'null'    => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('registration_id');
        $this->forge->addForeignKey('registration_id', 'sw_registrations', 'id', 'CASCADE', 'CASCADE');

        $this->forge->createTable('sw_participants');
    }

    public function down(): void
    {
        $this->forge->dropTable('sw_participants', true);
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSwRegistrations extends Migration
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
            'competition_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'level_key' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'school_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'school_address' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'contact_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => false,
            ],
            'contact_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'team_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'coach_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => false,
            ],
            'coach_position' => [
                'type'       => 'VARCHAR',
                'constraint' => 120,
                'null'       => true,
            ],
            'coach_phone' => [
                'type'       => 'VARCHAR',
                'constraint' => 40,
                'null'       => true,
            ],
            'coach_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 190,
                'null'       => true,
            ],
            'extra' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
                'null'       => false,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
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
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['competition_key', 'level_key']);
        $this->forge->addKey('deleted_at');

        $this->forge->createTable('sw_registrations');
    }

    public function down(): void
    {
        $this->forge->dropTable('sw_registrations', true);
    }
}

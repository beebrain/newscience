<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePersonnelOrgRoles extends Migration
{
    public function up(): void
    {
        if ($this->db->tableExists('personnel_org_roles')) {
            return;
        }
        if (! $this->db->tableExists('personnel')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'personnel_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'role_kind' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'position_title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'organization_unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'position_detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('personnel_id');
        $this->forge->addForeignKey('personnel_id', 'personnel', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('personnel_org_roles');
    }

    public function down(): void
    {
        if ($this->db->tableExists('personnel_org_roles')) {
            $this->forge->dropTable('personnel_org_roles');
        }
    }
}

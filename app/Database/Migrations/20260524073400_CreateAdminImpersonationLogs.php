<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminImpersonationLogs extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('admin_impersonation_logs')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'actor_uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'actor_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'target_uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'target_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'target_role' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'default'    => 'started',
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'session_id_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'context_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ended_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'ended_by' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
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
        $this->forge->addKey('actor_uid');
        $this->forge->addKey('target_uid');
        $this->forge->addKey('event');
        $this->forge->addKey('status');
        $this->forge->addKey('created_at');
        $this->forge->createTable('admin_impersonation_logs', true);
    }

    public function down()
    {
        if ($this->db->tableExists('admin_impersonation_logs')) {
            $this->forge->dropTable('admin_impersonation_logs', true);
        }
    }
}

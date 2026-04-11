<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCvSyncLog extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('cv_sync_log')) {
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
            'direction' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
            ],
            'ns_content_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'rr_content_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'decisions_json' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('personnel_id');
        $this->forge->createTable('cv_sync_log');
    }

    public function down()
    {
        if ($this->db->tableExists('cv_sync_log')) {
            $this->forge->dropTable('cv_sync_log');
        }
    }
}

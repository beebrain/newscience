<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalendarParticipantsTable extends Migration
{
    public function up()
    {
        $tableExists = $this->db->query("SHOW TABLES LIKE 'calendar_participants'")->getRowArray();
        if ($tableExists) {
            $addedByColumn = $this->db->query("SHOW COLUMNS FROM `calendar_participants` LIKE 'added_by'")->getRowArray();
            if (! $addedByColumn) {
                $this->db->query("ALTER TABLE `calendar_participants` ADD COLUMN `added_by` INT(11) UNSIGNED NULL AFTER `user_email`");
            }
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'event_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'user_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'added_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
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
        $this->forge->addKey('event_id');
        $this->forge->addKey('user_email');
        $this->forge->addKey('added_by');
        $this->forge->addUniqueKey(['event_id', 'user_email']);

        $this->forge->createTable('calendar_participants', true);
    }

    public function down()
    {
        $tableExists = $this->db->query("SHOW TABLES LIKE 'calendar_participants'")->getRowArray();
        if ($tableExists) {
            $this->forge->dropTable('calendar_participants', true);
        }
    }
}

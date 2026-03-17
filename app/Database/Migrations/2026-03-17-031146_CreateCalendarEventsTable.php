<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCalendarEventsTable extends Migration
{
    public function up()
    {
        $tableExists = $this->db->query("SHOW TABLES LIKE 'calendar_events'")->getRowArray();
        if ($tableExists) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'start_datetime' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'end_datetime' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'all_day' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
            'location' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'color' => [
                'type' => 'VARCHAR',
                'constraint' => 7,
                'null' => true,
                'default' => '#3b82f6',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'cancelled'],
                'default' => 'active',
                'null' => false,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_by');
        $this->forge->addKey('status');
        $this->forge->addKey('start_datetime');
        $this->forge->addKey('end_datetime');

        $this->forge->createTable('calendar_events', true);
    }

    public function down()
    {
        $tableExists = $this->db->query("SHOW TABLES LIKE 'calendar_events'")->getRowArray();
        if ($tableExists) {
            $this->forge->dropTable('calendar_events', true);
        }
    }
}

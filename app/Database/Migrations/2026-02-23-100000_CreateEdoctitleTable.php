<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEdoctitleTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'iddoc' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'officeiddoc' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'title' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'datedoc' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'doctype' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'owner' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'participant' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'fileaddress' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'userid' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'pages' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'copynum' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
            ],
            'regisdate' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);

        $this->forge->addKey('iddoc', true);
        $this->forge->addKey('doctype');
        $this->forge->addKey('regisdate');
        $this->forge->createTable('edoctitle', true);
    }

    public function down()
    {
        $this->forge->dropTable('edoctitle', true);
    }
}

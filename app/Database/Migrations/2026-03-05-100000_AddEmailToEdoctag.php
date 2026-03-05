<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToEdoctag extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('email', 'edoctag')) {
            return;
        }
        $this->forge->addColumn('edoctag', [
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'nickname',
            ],
        ]);
        $this->db->query('CREATE INDEX idx_edoctag_email ON edoctag (email)');
    }

    public function down()
    {
        if ($this->db->fieldExists('email', 'edoctag')) {
            $this->forge->dropColumn('edoctag', 'email');
        }
    }
}

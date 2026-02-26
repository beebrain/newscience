<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVolumeToEdoctitle extends Migration
{
    public function up()
    {
        $fields = [
            'volume_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'iddoc',
            ],
            'doc_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'volume_id',
            ],
        ];

        $this->forge->addColumn('edoctitle', $fields);

        // Add indexes
        $this->db->query('ALTER TABLE edoctitle ADD INDEX idx_volume_id (volume_id)');
        $this->db->query('ALTER TABLE edoctitle ADD INDEX idx_doc_year (doc_year)');
    }

    public function down()
    {
        $this->forge->dropColumn('edoctitle', ['volume_id', 'doc_year']);
    }
}

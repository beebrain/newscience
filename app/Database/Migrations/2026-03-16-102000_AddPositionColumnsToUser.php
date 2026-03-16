<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPositionColumnsToUser extends Migration
{
    public function up()
    {
        $fields = [];

        // Position in Thai
        if (!$this->db->fieldExists('position_th', 'user')) {
            $fields['position_th'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'title',
            ];
        }

        // Position in English
        if (!$this->db->fieldExists('position_en', 'user')) {
            $fields['position_en'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'position_th',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('user', $fields);
        }
    }

    public function down()
    {
        $drop = ['position_th', 'position_en'];
        foreach ($drop as $col) {
            if ($this->db->fieldExists($col, 'user')) {
                $this->forge->dropColumn('user', $col);
            }
        }
    }
}

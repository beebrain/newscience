<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEdocFieldsToUser extends Migration
{
    public function up()
    {
        $fields = [];

        // Check if edoc column exists
        if (!$this->db->fieldExists('edoc', 'user')) {
            $fields['edoc'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }

        // Check if admin_edoc column exists
        if (!$this->db->fieldExists('admin_edoc', 'user')) {
            $fields['admin_edoc'] = [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ];
        }

        if (!empty($fields)) {
            $this->forge->addColumn('user', $fields);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('user', ['edoc', 'admin_edoc']);
    }
}

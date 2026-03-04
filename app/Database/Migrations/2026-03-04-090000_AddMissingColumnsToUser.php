<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddMissingColumnsToUser extends Migration
{
    public function up()
    {
        $fields = [];

        if (! $this->db->fieldExists('tf_name', 'user')) {
            $fields['tf_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'gl_name',
            ];
        }

        if (! $this->db->fieldExists('tl_name', 'user')) {
            $fields['tl_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'tf_name',
            ];
        }

        if (! $this->db->fieldExists('th_name', 'user')) {
            $fields['th_name'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'tl_name',
            ];
        }

        if (! $this->db->fieldExists('profile_image', 'user')) {
            $fields['profile_image'] = [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
                'default'    => null,
                'after'      => 'profile_picture',
            ];
        }

        if (! $this->db->fieldExists('status', 'user')) {
            $fields['status'] = [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'default'    => 'active',
                'after'      => 'active',
            ];
        }

        if ($fields !== []) {
            $this->forge->addColumn('user', $fields);
        }
    }

    public function down()
    {
        $drop = ['tf_name', 'tl_name', 'th_name', 'profile_image', 'status'];
        foreach ($drop as $col) {
            if ($this->db->fieldExists($col, 'user')) {
                $this->forge->dropColumn('user', $col);
            }
        }
    }
}

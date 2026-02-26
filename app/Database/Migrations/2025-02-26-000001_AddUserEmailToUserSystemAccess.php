<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserEmailToUserSystemAccess extends Migration
{
    public function up()
    {
        if (!$this->db->fieldExists('user_email', 'user_system_access')) {
            $this->forge->addColumn('user_system_access', [
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'user_uid',
                ],
            ]);
        }

        // Backfill: set user_email from user.uid
        $sql = "UPDATE user_system_access usa
                INNER JOIN user u ON u.uid = usa.user_uid
                SET usa.user_email = u.email
                WHERE usa.user_email IS NULL OR usa.user_email = ''";
        $this->db->query($sql);

        // Make user_email NOT NULL after backfill (optional: keep nullable for compatibility)
        // $this->forge->modifyColumn('user_system_access', ['user_email' => ['null' => false]]);
    }

    public function down()
    {
        if ($this->db->fieldExists('user_email', 'user_system_access')) {
            $this->forge->dropColumn('user_system_access', 'user_email');
        }
    }
}

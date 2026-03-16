<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add nickname column to user table for exam matching
 */
class AddNicknameToUser extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('nickname', 'user')) {
            $this->forge->addColumn('user', [
                'nickname' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'tl_name',
                ],
            ]);
            
            // Create unique index for nickname (allowing multiple NULLs)
            $this->db->query('CREATE UNIQUE INDEX idx_user_nickname ON user (nickname)');
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('nickname', 'user')) {
            $this->db->query('DROP INDEX idx_user_nickname ON user');
            $this->forge->dropColumn('user', 'nickname');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Table page_views: site-wide page view tracking for Executive Dashboard analytics.
 */
class CreatePageViewsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'BIGINT',
                'constraint'     => 20,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
            ],
            'route' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'session_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'user_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'user_type' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'student', 'guest'],
                'default'    => 'guest',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->addKey('session_id');
        $this->forge->createTable('page_views');
        // Prefix index on url for MySQL key length limit (utf8mb4)
        $tableName = $this->db->prefixTable('page_views');
        $this->db->query('CREATE INDEX idx_url ON `' . $tableName . '` (url(191))');
    }

    public function down()
    {
        $this->forge->dropTable('page_views', true);
    }
}

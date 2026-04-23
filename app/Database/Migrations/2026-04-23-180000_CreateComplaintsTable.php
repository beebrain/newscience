<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateComplaintsTable extends Migration
{
    public function up()
    {
        if (! $this->hasTable('complaints')) {
            $this->forge->addField([
                'id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'auto_increment' => true,
                ],
                'complainant_name' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'complainant_email' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'complainant_phone' => [
                    'type' => 'VARCHAR',
                    'constraint' => 50,
                    'null' => true,
                ],
                'subject' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                ],
                'detail' => [
                    'type' => 'TEXT',
                ],
                'attachment_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                ],
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => 20,
                    'default' => 'new',
                ],
                'ip_address' => [
                    'type' => 'VARCHAR',
                    'constraint' => 45,
                    'null' => true,
                ],
                'user_agent' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'created_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
                'updated_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                ],
            ]);

            $this->forge->addKey('id', true);
            $this->forge->addKey('status');
            $this->forge->addKey('created_at');
            $this->forge->createTable('complaints', true);
        }

        if ($this->hasTable('site_settings')) {
            $exists = $this->db->table('site_settings')
                ->where('setting_key', 'complaint_notification_emails')
                ->countAllResults();

            if ($exists === 0) {
                $this->db->table('site_settings')->insert([
                    'setting_key' => 'complaint_notification_emails',
                    'setting_value' => '',
                    'setting_type' => 'textarea',
                    'category' => 'contact',
                    'description' => 'อีเมลผู้รับแจ้งเรื่องร้องเรียน คั่นหลายอีเมลด้วย comma หรือขึ้นบรรทัดใหม่',
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        if ($this->hasTable('site_settings')) {
            $this->db->table('site_settings')
                ->where('setting_key', 'complaint_notification_emails')
                ->delete();
        }

        if ($this->hasTable('complaints')) {
            $this->forge->dropTable('complaints', true);
        }
    }

    private function hasTable(string $table): bool
    {
        $result = $this->db->query('SHOW TABLES LIKE ' . $this->db->escape($table));

        return $result->getNumRows() > 0;
    }
}

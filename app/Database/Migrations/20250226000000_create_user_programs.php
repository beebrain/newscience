<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตาราง pivot user–programs: User อาจอยู่ได้หลายหลักสูตร
 * ใช้ email ของ user เป็น key (user_email) — แหล่งความจริงคือ user_programs
 * ต้องมี UNIQUE บน user.email เพื่อสร้าง FK
 */
class CreateUserPrograms extends Migration
{
    public function up()
    {
        // ให้ user.email เป็น UNIQUE เพื่อใช้เป็น FK (ข้ามถ้ามีแล้ว)
        $db = \Config\Database::connect();
        $indexData = $db->getIndexData('user');
        $hasUniqueEmail = false;
        foreach ($indexData as $name => $idx) {
            $idx = (array) $idx;
            $fields = $idx['fields'] ?? [];
            $unique = $idx['unique'] ?? false;
            if ($name === 'user_email_unique' || (is_array($fields) && in_array('email', $fields, true) && $unique)) {
                $hasUniqueEmail = true;
                break;
            }
        }
        if (!$hasUniqueEmail) {
            try {
                $this->db->query('ALTER TABLE `user` ADD UNIQUE KEY `user_email_unique` (`email`)');
            } catch (\Throwable $e) {
                // ข้ามถ้ามี key อยู่แล้ว (Duplicate key name)
            }
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'program_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'is_primary' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'sort_order' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
        $this->forge->addUniqueKey(['user_email', 'program_id']);
        $this->forge->createTable('user_programs');

        // ใส่ FK แยกหลังสร้างตาราง และให้ user_email ใช้ collation เดียวกับ user.email (ป้องกัน errno 150)
        $row = $this->db->query("SELECT COLLATION_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user' AND COLUMN_NAME = 'email'")->getRow();
        $collation = $row && !empty($row->COLLATION_NAME) ? $row->COLLATION_NAME : 'utf8mb4_unicode_ci';
        $this->db->query("ALTER TABLE `user_programs` MODIFY `user_email` VARCHAR(255) NOT NULL COLLATE {$collation}");
        $this->db->query("ALTER TABLE `user_programs` ADD CONSTRAINT `user_programs_user_email_foreign` FOREIGN KEY (`user_email`) REFERENCES `user` (`email`) ON DELETE CASCADE ON UPDATE CASCADE");
        $this->db->query("ALTER TABLE `user_programs` ADD CONSTRAINT `user_programs_program_id_foreign` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE");
    }

    public function down()
    {
        $this->forge->dropTable('user_programs', true);
    }
}

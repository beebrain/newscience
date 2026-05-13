<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEdocGmailTables extends Migration
{
    private array $tables = [
        'edoc_user_labels',
        'edoc_document_labels',
        'edoc_user_flags',
        'edoc_forwards',
    ];

    public function up(): void
    {
        // 1) Labels ที่ผู้ใช้ตั้งชื่อเอง (per-user, Gmail-style)
        if (! $this->db->tableExists('edoc_user_labels')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'name' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 100,
                    'null'       => false,
                ],
                'color' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 20,
                    'null'       => false,
                    'default'    => '#6b7280',
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'null'       => false,
                    'default'    => 0,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
                'updated_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addUniqueKey(['user_email', 'name'], 'uniq_user_label_name');
            $this->forge->addKey('user_email', false, false, 'idx_user_email');
            $this->forge->createTable('edoc_user_labels', true, [
                'ENGINE'  => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ]);
        }

        // 2) Mapping เอกสาร <-> label (per user, M:N)
        if (! $this->db->tableExists('edoc_document_labels')) {
            $this->forge->addField([
                'document_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'label_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
            ]);
            $this->forge->addPrimaryKey(['document_id', 'user_email', 'label_id']);
            $this->forge->addKey(['user_email', 'label_id'], false, false, 'idx_user_label');
            $this->forge->addKey('label_id', false, false, 'idx_label');
            $this->forge->createTable('edoc_document_labels', true, [
                'ENGINE'  => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ]);

            // FK: label_id → edoc_user_labels.id (CASCADE)
            $this->db->query('
                ALTER TABLE `edoc_document_labels`
                ADD CONSTRAINT `fk_edl_label`
                FOREIGN KEY (`label_id`)
                REFERENCES `edoc_user_labels`(`id`)
                ON DELETE CASCADE ON UPDATE CASCADE
            ');
        }

        // 3) Per-user flags: star / important / read / archive
        if (! $this->db->tableExists('edoc_user_flags')) {
            $this->forge->addField([
                'document_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'user_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'is_starred' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                ],
                'is_important' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                ],
                'is_archived' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'null'       => false,
                    'default'    => 0,
                ],
                'read_at' => [
                    'type' => 'DATETIME',
                    'null' => true,
                    'default' => null,
                ],
                'created_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
                'updated_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
            ]);
            $this->forge->addPrimaryKey(['document_id', 'user_email']);
            $this->forge->addKey(['user_email', 'is_starred'],  false, false, 'idx_user_starred');
            $this->forge->addKey(['user_email', 'is_archived'], false, false, 'idx_user_archived');
            $this->forge->addKey(['user_email', 'read_at'],     false, false, 'idx_user_unread');
            $this->forge->createTable('edoc_user_flags', true, [
                'ENGINE'  => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ]);
        }

        // 4) Forward log — ไม่ duplicate ไฟล์
        if (! $this->db->tableExists('edoc_forwards')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 10,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'document_id' => [
                    'type'       => 'INT',
                    'constraint' => 10,
                    'unsigned'   => true,
                    'null'       => false,
                ],
                'from_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'to_email' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => false,
                ],
                'note' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'default' => null,
                ],
                'forwarded_at' => [
                    'type'    => 'DATETIME',
                    'null'    => true,
                    'default' => null,
                ],
            ]);
            $this->forge->addPrimaryKey('id');
            $this->forge->addKey(['document_id', 'to_email'],   false, false, 'idx_doc_to');
            $this->forge->addKey(['document_id', 'from_email'], false, false, 'idx_doc_from');
            $this->forge->addKey('to_email', false, false, 'idx_to_email');
            $this->forge->createTable('edoc_forwards', true, [
                'ENGINE'  => 'InnoDB',
                'CHARSET' => 'utf8mb4',
                'COLLATE' => 'utf8mb4_unicode_ci',
            ]);
        }
    }

    public function down(): void
    {
        // ลบตามลำดับ: FK ก่อน แล้วค่อยลบตารางหลัก
        foreach (array_reverse($this->tables) as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}

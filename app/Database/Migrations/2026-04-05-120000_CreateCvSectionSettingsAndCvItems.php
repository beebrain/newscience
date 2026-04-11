<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCvSectionSettingsAndCvItems extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('personnel')) {
            return;
        }

        if (!$this->db->tableExists('cv_section_settings')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'personnel_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'section_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'visible' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey(['personnel_id', 'section_key']);
            $this->forge->addKey('personnel_id');
            $this->forge->createTable('cv_section_settings');
            $this->db->query('ALTER TABLE `cv_section_settings` ADD CONSTRAINT `cv_section_settings_personnel_fk` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        if (!$this->db->tableExists('cv_items')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'personnel_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'section_key' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'default'    => '',
                ],
                'subtitle' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ],
                'body' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'url' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 2048,
                    'null'       => true,
                ],
                'year' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 32,
                    'null'       => true,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'visible_on_public_cv' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'extra_json' => [
                    'type' => 'TEXT',
                    'null' => true,
                    'comment' => 'Optional JSON metadata per item type',
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey(['personnel_id', 'section_key']);
            $this->forge->createTable('cv_items');
            $this->db->query('ALTER TABLE `cv_items` ADD CONSTRAINT `cv_items_personnel_fk` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('cv_items')) {
            try {
                $this->db->query('ALTER TABLE `cv_items` DROP FOREIGN KEY `cv_items_personnel_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_items', true);
        }
        if ($this->db->tableExists('cv_section_settings')) {
            try {
                $this->db->query('ALTER TABLE `cv_section_settings` DROP FOREIGN KEY `cv_section_settings_personnel_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_section_settings', true);
        }
    }
}

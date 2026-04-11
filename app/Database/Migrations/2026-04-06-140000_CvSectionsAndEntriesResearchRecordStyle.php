<?php

namespace App\Database\Migrations;

use App\Libraries\CvProfile;
use CodeIgniter\Database\Migration;

/**
 * CV แบบ researchRecord: cv_sections + cv_entries (ผูก personnel_id)
 * ย้ายข้อมูลจาก cv_section_settings / cv_items ถ้ามี แล้วลบตารางเก่า
 */
class CvSectionsAndEntriesResearchRecordStyle extends Migration
{
    public function up()
    {
        $db = $this->db;

        if (!$db->tableExists('personnel')) {
            return;
        }

        if (!$db->tableExists('cv_sections')) {
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
                'type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 64,
                    'default'    => 'custom',
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'is_default' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'visible_on_public' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('personnel_id');
            $this->forge->createTable('cv_sections');
            $db->query('ALTER TABLE `cv_sections` ADD CONSTRAINT `cv_sections_personnel_fk` FOREIGN KEY (`personnel_id`) REFERENCES `personnel` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        if (!$db->tableExists('cv_entries')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'section_id' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                ],
                'title' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'default'    => '',
                ],
                'organization' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ],
                'location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                ],
                'start_date' => ['type' => 'DATE', 'null' => true],
                'end_date' => ['type' => 'DATE', 'null' => true],
                'is_current' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 0,
                ],
                'metadata' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'description' => [
                    'type' => 'TEXT',
                    'null' => true,
                ],
                'sort_order' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'default'    => 0,
                ],
                'visible_on_public' => [
                    'type'       => 'TINYINT',
                    'constraint' => 1,
                    'default'    => 1,
                ],
                'created_at' => ['type' => 'DATETIME', 'null' => true],
                'updated_at' => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('section_id');
            $this->forge->createTable('cv_entries');
            $db->query('ALTER TABLE `cv_entries` ADD CONSTRAINT `cv_entries_section_fk` FOREIGN KEY (`section_id`) REFERENCES `cv_sections` (`id`) ON DELETE CASCADE ON UPDATE CASCADE');
        }

        $this->migrateLegacyData();

        if ($db->tableExists('cv_items')) {
            try {
                $db->query('ALTER TABLE `cv_items` DROP FOREIGN KEY `cv_items_personnel_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_items', true);
        }
        if ($db->tableExists('cv_section_settings')) {
            try {
                $db->query('ALTER TABLE `cv_section_settings` DROP FOREIGN KEY `cv_section_settings_personnel_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_section_settings', true);
        }
    }

    private function migrateLegacyData(): void
    {
        $db = $this->db;
        if (!$db->tableExists('cv_sections') || !$db->tableExists('cv_entries')) {
            return;
        }

        $hasLegacySettings = $db->tableExists('cv_section_settings');
        $hasLegacyItems    = $db->tableExists('cv_items');
        if (!$hasLegacySettings && !$hasLegacyItems) {
            return;
        }

        $labels = CvProfile::sectionLabelsTh();
        /** @var array<string, int> map "personnelId|sectionKey" => new section id */
        $map = [];

        if ($hasLegacySettings) {
            $rows = $db->table('cv_section_settings')->orderBy('personnel_id', 'ASC')->orderBy('sort_order', 'ASC')->get()->getResultArray();
            foreach ($rows as $row) {
                $pid = (int) ($row['personnel_id'] ?? 0);
                $key = (string) ($row['section_key'] ?? 'custom');
                if ($pid <= 0) {
                    continue;
                }
                $title = $labels[$key] ?? $key;
                $db->table('cv_sections')->insert([
                    'personnel_id'        => $pid,
                    'type'                => $key,
                    'title'               => $title,
                    'description'         => null,
                    'sort_order'          => (int) ($row['sort_order'] ?? 0),
                    'is_default'          => 0,
                    'visible_on_public'   => !empty($row['visible']) ? 1 : 0,
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
                $map[$pid . '|' . $key] = (int) $db->insertID();
            }
        }

        if ($hasLegacyItems) {
            $items = $db->table('cv_items')->orderBy('personnel_id', 'ASC')->orderBy('section_key', 'ASC')->orderBy('sort_order', 'ASC')->get()->getResultArray();
            foreach ($items as $it) {
                $pid = (int) ($it['personnel_id'] ?? 0);
                $key = (string) ($it['section_key'] ?? 'custom');
                if ($pid <= 0) {
                    continue;
                }
                $mk = $pid . '|' . $key;
                if (!isset($map[$mk])) {
                    $title = $labels[$key] ?? $key;
                    $db->table('cv_sections')->insert([
                        'personnel_id'      => $pid,
                        'type'              => $key,
                        'title'             => $title,
                        'description'       => null,
                        'sort_order'        => 0,
                        'is_default'        => 0,
                        'visible_on_public' => 1,
                        'created_at'        => date('Y-m-d H:i:s'),
                        'updated_at'        => date('Y-m-d H:i:s'),
                    ]);
                    $map[$mk] = (int) $db->insertID();
                }
                $sectionId = $map[$mk];

                $meta = [];
                if (!empty($it['url'])) {
                    $meta['legacy_url'] = $it['url'];
                }
                if (!empty($it['extra_json'])) {
                    $meta['legacy_extra_json'] = $it['extra_json'];
                }
                $year = trim((string) ($it['year'] ?? ''));
                $startDate = null;
                if (preg_match('/^\d{4}$/', $year)) {
                    $startDate = $year . '-01-01';
                }

                $db->table('cv_entries')->insert([
                    'section_id'          => $sectionId,
                    'title'               => (string) ($it['title'] ?? ''),
                    'organization'        => !empty($it['subtitle']) ? (string) $it['subtitle'] : null,
                    'location'            => null,
                    'start_date'          => $startDate,
                    'end_date'            => null,
                    'is_current'          => 0,
                    'metadata'            => $meta !== [] ? json_encode($meta, JSON_UNESCAPED_UNICODE) : null,
                    'description'         => !empty($it['body']) ? (string) $it['body'] : null,
                    'sort_order'          => (int) ($it['sort_order'] ?? 0),
                    'visible_on_public'   => !empty($it['visible_on_public_cv']) ? 1 : 0,
                    'created_at'          => date('Y-m-d H:i:s'),
                    'updated_at'          => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        $db = $this->db;
        if ($db->tableExists('cv_entries')) {
            try {
                $db->query('ALTER TABLE `cv_entries` DROP FOREIGN KEY `cv_entries_section_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_entries', true);
        }
        if ($db->tableExists('cv_sections')) {
            try {
                $db->query('ALTER TABLE `cv_sections` DROP FOREIGN KEY `cv_sections_personnel_fk`');
            } catch (\Throwable $e) {
            }
            $this->forge->dropTable('cv_sections', true);
        }
        // ไม่สร้างตารางเก่าคืนอัตโนมัติ (ซับซ้อน) — ใช้ backup ถ้าจำเป็น
    }
}

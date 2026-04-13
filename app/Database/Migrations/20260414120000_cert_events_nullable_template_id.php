<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * กิจกรรมใช้รูป/PDF ใบรับรองของตัวเองได้โดยไม่บังคับ template_id
 */
class CertEventsNullableTemplateId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('cert_events')) {
            return;
        }

        // ลบ FK เดิม (ชื่อจาก database/cert_events_schema.sql)
        try {
            $this->db->query('ALTER TABLE cert_events DROP FOREIGN KEY fk_cert_events_template');
        } catch (\Throwable $e) {
            // ชื่อ FK อาจต่าง — ลองดึงจาก information_schema
            $rows = $this->db->query(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'cert_events' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
            )->getResultArray();
            foreach ($rows as $r) {
                $name = $r['CONSTRAINT_NAME'] ?? '';
                if ($name !== '') {
                    try {
                        $this->db->query('ALTER TABLE cert_events DROP FOREIGN KEY `' . str_replace('`', '', $name) . '`');
                    } catch (\Throwable $e2) {
                    }
                }
            }
        }

        $this->db->query('ALTER TABLE cert_events MODIFY template_id INT UNSIGNED NULL COMMENT \'เทมเพลตเดิม (optional)\'');

        // FK ใหม่: อนุญาต NULL
        try {
            $this->db->query(
                'ALTER TABLE cert_events ADD CONSTRAINT fk_cert_events_template
                 FOREIGN KEY (template_id) REFERENCES cert_templates(id) ON DELETE SET NULL'
            );
        } catch (\Throwable $e) {
            log_message('warning', 'Could not re-add fk_cert_events_template: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('cert_events')) {
            return;
        }
        try {
            $this->db->query('ALTER TABLE cert_events DROP FOREIGN KEY fk_cert_events_template');
        } catch (\Throwable $e) {
        }
        $first = $this->db->table('cert_templates')->select('id')->orderBy('id', 'ASC')->limit(1)->get()->getFirstRow('array');
        $fallback = isset($first['id']) ? (int) $first['id'] : 0;
        if ($fallback > 0) {
            $this->db->table('cert_events')->where('template_id', null)->update(['template_id' => $fallback]);
        }
        $this->db->query('ALTER TABLE cert_events MODIFY template_id INT UNSIGNED NOT NULL');
        try {
            $this->db->query(
                'ALTER TABLE cert_events ADD CONSTRAINT fk_cert_events_template
                 FOREIGN KEY (template_id) REFERENCES cert_templates(id) ON DELETE RESTRICT'
            );
        } catch (\Throwable $e) {
        }
    }
}

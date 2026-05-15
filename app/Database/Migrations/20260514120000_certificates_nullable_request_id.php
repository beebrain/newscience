<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Event-based certificates are not tied to cert_requests; FK rejected request_id = 0.
 */
class CertificatesNullableRequestId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('certificates')) {
            return;
        }

        try {
            $this->db->query('ALTER TABLE certificates DROP FOREIGN KEY fk_certificates_request');
        } catch (\Throwable $e) {
            $rows = $this->db->query(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'certificates' AND CONSTRAINT_TYPE = 'FOREIGN KEY'"
            )->getResultArray();
            foreach ($rows as $r) {
                $name = $r['CONSTRAINT_NAME'] ?? '';
                if ($name !== '') {
                    try {
                        $this->db->query('ALTER TABLE certificates DROP FOREIGN KEY `' . str_replace('`', '', $name) . '`');
                    } catch (\Throwable $e2) {
                    }
                }
            }
        }

        $this->db->query(
            'ALTER TABLE certificates MODIFY request_id INT UNSIGNED NULL COMMENT \'cert_requests.id; NULL for event certificates\''
        );

        try {
            $this->db->query('UPDATE certificates SET request_id = NULL WHERE request_id = 0');
        } catch (\Throwable $e) {
        }

        try {
            $this->db->query(
                'ALTER TABLE certificates ADD CONSTRAINT fk_certificates_request
                 FOREIGN KEY (request_id) REFERENCES cert_requests(id)'
            );
        } catch (\Throwable $e) {
            log_message('warning', 'Could not re-add fk_certificates_request: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        if (! $this->db->tableExists('certificates')) {
            return;
        }
        try {
            $this->db->query('ALTER TABLE certificates DROP FOREIGN KEY fk_certificates_request');
        } catch (\Throwable $e) {
        }

        $first = $this->db->table('cert_requests')->select('id')->orderBy('id', 'ASC')->limit(1)->get()->getFirstRow('array');
        $fallback = isset($first['id']) ? (int) $first['id'] : 0;
        if ($fallback > 0) {
            $this->db->table('certificates')->where('request_id', null)->update(['request_id' => $fallback]);
        }

        $this->db->query('ALTER TABLE certificates MODIFY request_id INT UNSIGNED NOT NULL');

        try {
            $this->db->query(
                'ALTER TABLE certificates ADD CONSTRAINT fk_certificates_request
                 FOREIGN KEY (request_id) REFERENCES cert_requests(id)'
            );
        } catch (\Throwable $e) {
            log_message('warning', 'Could not restore fk_certificates_request: ' . $e->getMessage());
        }
    }
}

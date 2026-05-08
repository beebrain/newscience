<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropPersonnelOrganizationUnitId extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('personnel')) {
            return;
        }
        if (! $this->db->fieldExists('organization_unit_id', 'personnel')) {
            return;
        }

        // Must drop foreign key(s) before dropping the column (MySQL restriction)
        try {
            $dbName = (string) ($this->db->getDatabase() ?? '');
            if ($dbName !== '') {
                $rows = $this->db->query(
                    'SELECT CONSTRAINT_NAME
                     FROM information_schema.KEY_COLUMN_USAGE
                     WHERE TABLE_SCHEMA = ?
                       AND TABLE_NAME = ?
                       AND COLUMN_NAME = ?
                       AND REFERENCED_TABLE_NAME IS NOT NULL',
                    [$dbName, 'personnel', 'organization_unit_id']
                )->getResultArray();

                foreach ($rows as $r) {
                    $fk = trim((string) ($r['CONSTRAINT_NAME'] ?? ''));
                    if ($fk === '' || strtoupper($fk) === 'PRIMARY') {
                        continue;
                    }
                    // Backticks are required because FK names may contain special chars
                    $this->db->query('ALTER TABLE `personnel` DROP FOREIGN KEY `' . str_replace('`', '', $fk) . '`');
                }
            } else {
                // Fallback: drop known FK name if present (older deployments)
                $this->db->query('ALTER TABLE `personnel` DROP FOREIGN KEY `fk_personnel_organization_unit`');
            }
        } catch (\Throwable $e) {
            // Ignore: FK might not exist or DB user lacks information_schema access in some envs
        }

        $this->forge->dropColumn('personnel', 'organization_unit_id');
    }

    public function down(): void
    {
        if (! $this->db->tableExists('personnel')) {
            return;
        }
        if ($this->db->fieldExists('organization_unit_id', 'personnel')) {
            return;
        }

        $this->forge->addColumn('personnel', [
            'organization_unit_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
        ]);
    }
}


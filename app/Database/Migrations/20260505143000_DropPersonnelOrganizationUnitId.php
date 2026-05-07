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


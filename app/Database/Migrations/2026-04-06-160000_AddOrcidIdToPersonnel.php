<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เก็บ ORCID iD ของบุคลากรสำหรับนำเข้า CV / แสดงผล
 */
class AddOrcidIdToPersonnel extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('personnel')) {
            return;
        }
        if ($this->db->fieldExists('orcid_id', 'personnel')) {
            return;
        }

        $this->forge->addColumn('personnel', [
            'orcid_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 19,
                'null'       => true,
                'comment'    => 'ORCID iD e.g. 0000-0001-2345-6789',
                'after'      => 'academic_title_en',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('orcid_id', 'personnel')) {
            $this->forge->dropColumn('personnel', 'orcid_id');
        }
    }
}

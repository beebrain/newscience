<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * รูปโปรไฟล์เฉพาะหน้า CV สาธารณะ — แยกจาก user.profile_image
 */
class AddCvProfileImageToPersonnel extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('personnel')) {
            return;
        }
        if ($this->db->fieldExists('cv_profile_image', 'personnel')) {
            return;
        }

        $after = 'image';
        if ($this->db->fieldExists('orcid_id', 'personnel')) {
            $after = 'orcid_id';
        }

        $this->forge->addColumn('personnel', [
            'cv_profile_image' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment'    => 'Relative path e.g. staff/xxx.jpg — public CV only',
                'after'      => $after,
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('cv_profile_image', 'personnel')) {
            $this->forge->dropColumn('personnel', 'cv_profile_image');
        }
    }
}

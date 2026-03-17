<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * เพิ่มคอลัมน์สำหรับระบบประเมินผลการสอน:
 * - teaching_evaluations: email (อ้างอิงบุคคล), approval_date (วันอนุมัติ สำหรับ cooldown 2 ปี)
 * - evaluation_referees: institution, expertise, phone (ข้อมูลเพิ่มเติมผู้ทรงคุณวุฒิ)
 */
class AddEvaluateEmailCooldownRefereeFields extends Migration
{
    public function up()
    {
        // --- teaching_evaluations: เพิ่ม email + approval_date ---
        $fieldsTeaching = [];

        if (! $this->db->fieldExists('email', 'teaching_evaluations')) {
            $fieldsTeaching['email'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'uid',
            ];
        }

        if (! $this->db->fieldExists('approval_date', 'teaching_evaluations')) {
            $fieldsTeaching['approval_date'] = [
                'type'  => 'DATE',
                'null'  => true,
                'after' => 'stop_date',
            ];
        }

        if ($fieldsTeaching !== []) {
            $this->forge->addColumn('teaching_evaluations', $fieldsTeaching);
        }

        // Populate email from user table where uid matches
        $this->db->query("
            UPDATE teaching_evaluations te
            INNER JOIN user u ON u.uid = te.uid
            SET te.email = u.email
            WHERE te.email IS NULL
        ");

        // Add index on email
        if (! $this->db->fieldExists('email', 'teaching_evaluations')) {
            // Field was just added above, index will be added below
        }
        // Safe add index using raw SQL (CI4 forge doesn't support IF NOT EXISTS for keys)
        try {
            $this->db->query("ALTER TABLE teaching_evaluations ADD INDEX idx_te_email (email)");
        } catch (\Exception $e) {
            // Index may already exist
        }

        // --- evaluation_referees: เพิ่ม institution, expertise, phone ---
        $fieldsReferee = [];

        if (! $this->db->fieldExists('institution', 'evaluation_referees')) {
            $fieldsReferee['institution'] = [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'name',
            ];
        }

        if (! $this->db->fieldExists('expertise', 'evaluation_referees')) {
            $fieldsReferee['expertise'] = [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'after'      => 'institution',
            ];
        }

        if (! $this->db->fieldExists('phone', 'evaluation_referees')) {
            $fieldsReferee['phone'] = [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'after'      => 'expertise',
            ];
        }

        if ($fieldsReferee !== []) {
            $this->forge->addColumn('evaluation_referees', $fieldsReferee);
        }
    }

    public function down()
    {
        // teaching_evaluations
        if ($this->db->fieldExists('email', 'teaching_evaluations')) {
            try {
                $this->db->query("ALTER TABLE teaching_evaluations DROP INDEX idx_te_email");
            } catch (\Exception $e) {
            }
            $this->forge->dropColumn('teaching_evaluations', 'email');
        }
        if ($this->db->fieldExists('approval_date', 'teaching_evaluations')) {
            $this->forge->dropColumn('teaching_evaluations', 'approval_date');
        }

        // evaluation_referees
        foreach (['institution', 'expertise', 'phone'] as $col) {
            if ($this->db->fieldExists($col, 'evaluation_referees')) {
                $this->forge->dropColumn('evaluation_referees', $col);
            }
        }
    }
}

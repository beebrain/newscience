<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตารางระบบประเมินผลการสอน (ย้ายจาก EdocSci: teachingEvaluate, evaluatescore, emailEvaluate)
 * - teaching_evaluations: ข้อมูลผู้ขอประเมิน
 * - evaluation_scores: คะแนน/ข้อเสนอแนะจากผู้ประเมิน
 * - evaluation_referees: รายชื่อผู้ทรงคุณวุฒิ
 */
class CreateTeachingEvaluationTables extends Migration
{
    public function up()
    {
        // Drop old generic evaluation tables if they exist (from previous migration)
        if ($this->db->tableExists('evaluation_responses')) {
            $this->forge->dropTable('evaluation_responses', true);
        }
        if ($this->db->tableExists('evaluation_questions')) {
            $this->forge->dropTable('evaluation_questions', true);
        }
        if ($this->db->tableExists('evaluation_forms')) {
            $this->forge->dropTable('evaluation_forms', true);
        }

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'default'    => 0,
            ],
            'first_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'last_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'title_thai' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'curriculum' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'position' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'position_major' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'position_major_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'start_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'subject_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'subject_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subject_credit' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'subject_teacher' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subject_detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_doc' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'link_video' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'submit_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'stop_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'detail' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'teaching_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('uid');
        $this->forge->addKey('status');
        $this->forge->createTable('teaching_evaluations');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'teaching_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'file_doc' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'score' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'comment_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'send_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'default'    => 0,
            ],
            'ref_num' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('teaching_id');
        $this->forge->addKey(['teaching_id', 'email']);
        $this->forge->addForeignKey('teaching_id', 'teaching_evaluations', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('evaluation_scores');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'TINYINT',
                'constraint' => 4,
                'default'    => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('evaluation_referees');
    }

    public function down()
    {
        $this->forge->dropTable('evaluation_scores', true);
        $this->forge->dropTable('evaluation_referees', true);
        $this->forge->dropTable('teaching_evaluations', true);
    }
}

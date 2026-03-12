<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตารางแบบประเมินตนเอง (ทุกคนเข้าได้) และตารางสิทธิ์รายบุคคลสำหรับระบบประเมิน
 */
class AddSelfEvaluationAndRights extends Migration
{
    public function up()
    {
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
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'academic_year' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'semester' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'score_1' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'score_2' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'score_3' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'score_4' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'score_5' => ['type' => 'TINYINT', 'constraint' => 2, 'null' => true],
            'comment' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('uid');
        $this->forge->addKey(['academic_year', 'semester']);
        $this->forge->createTable('self_evaluations');

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
            ],
            'can_submit_teaching' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'can_be_referee' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'can_manage_evaluate' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
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
        $this->forge->addUniqueKey('uid');
        $this->forge->createTable('evaluate_user_rights');
    }

    public function down()
    {
        $this->forge->dropTable('self_evaluations', true);
        $this->forge->dropTable('evaluate_user_rights', true);
    }
}

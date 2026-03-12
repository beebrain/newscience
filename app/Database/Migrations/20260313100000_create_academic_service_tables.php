<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ตารางเก็บข้อมูลการบริการวิชาการของบุคลากร (ตามแบบฟอร์มคณะวิทยาศาสตร์และเทคโนโลยี)
 * - academic_services: ข้อมูลหลักโครงการ/กิจกรรม (รายการต่อปี, ค้นหาได้)
 * - academic_service_participants: ผู้มีส่วนร่วม (แท็ก user ผ่าน user_uid ลิงก์กับ user table)
 */
class CreateAcademicServiceTables extends Migration
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
            'service_date' => [
                'type' => 'DATE',
                'null' => false,
                'comment' => 'วัน/เดือน/ปี ที่บริการวิชาการ',
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
                'comment' => 'ชื่อโครงการ/กิจกรรม/หัวข้อ',
            ],
            'project_owner_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'internal_faculty | external',
            ],
            'project_owner_spec' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment' => 'ระบุเมื่อโครงการภายนอกที่มาขอความอนุเคราะห์',
            ],
            'venue_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'within_faculty | within_university | outside',
            ],
            'venue_spec' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment' => 'ระบุสถานที่เมื่อภายนอกมหาวิทยาลัย',
            ],
            'target_group_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'internal | external',
            ],
            'target_group_spec' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment' => 'ระบุกลุ่มผู้รับบริการเมื่อภายนอกมหาวิทยาลัย',
            ],
            'responsible_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'faculty | program | person',
            ],
            'responsible_program' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment' => 'หลักสูตร/สาขาวิชา',
            ],
            'responsible_person_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment' => 'ชื่อ-นามสกุล/หลักสูตร เมื่อเลือกบุคคล',
            ],
            'service_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'training_seminar | workshop | consultant | lab_testing | expert_evaluator | other',
            ],
            'service_type_spec' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
                'comment' => 'อื่นๆ ระบุ',
            ],
            'budget_source' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
                'comment' => 'university | faculty | external | other',
            ],
            'budget_source_spec' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'has_compensation' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'comment' => 'yes | no | unknown',
            ],
            'revenue_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'null'       => true,
                'comment' => 'รายได้ที่เกิดขึ้นกับคณะ (บาท)',
            ],
            'revenue_unknown' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
                'comment' => '1 = ไม่มีข้อมูล',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by_uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment' => 'ผู้สร้างรายการ (admin)',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('service_date');
        $this->forge->addKey('created_by_uid');
        $this->forge->addKey(['service_date', 'title'], false, false, 'idx_academic_services_search');
        $this->forge->createTable('academic_services');

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'academic_service_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'comment' => 'ลิงก์ user.uid ถ้าเป็นบุคลากรในระบบ',
            ],
            'role' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => false,
                'default'    => 'co_participant',
                'comment' => 'responsible | co_participant',
            ],
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment' => 'ชื่อ-นามสกุล กรณีไม่ลิงก์ user',
            ],
            'program_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'comment' => 'หลักสูตร',
            ],
            'sort_order' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('academic_service_id');
        $this->forge->addKey('user_uid');
        $this->forge->addKey(['user_uid', 'academic_service_id'], false, false, 'idx_participants_user_service');
        $this->forge->addForeignKey('academic_service_id', 'academic_services', 'id', 'CASCADE', 'CASCADE');
        if ($this->db->tableExists('user')) {
            $this->forge->addForeignKey('user_uid', 'user', 'uid', 'SET NULL', 'CASCADE');
        }
        $this->forge->createTable('academic_service_participants');

        if ($this->db->tableExists('user')) {
            $this->forge->addForeignKey('created_by_uid', 'user', 'uid', 'SET NULL', 'CASCADE');
            $this->db->query('ALTER TABLE `academic_services` ADD CONSTRAINT `academic_services_created_by_uid_foreign` FOREIGN KEY (`created_by_uid`) REFERENCES `user` (`uid`) ON DELETE SET NULL ON UPDATE CASCADE');
        }
    }

    public function down()
    {
        if ($this->db->tableExists('academic_services')) {
            $this->db->query('ALTER TABLE `academic_services` DROP FOREIGN KEY `academic_services_created_by_uid_foreign`');
        }
        $this->forge->dropTable('academic_service_participants', true);
        $this->forge->dropTable('academic_services', true);
    }
}

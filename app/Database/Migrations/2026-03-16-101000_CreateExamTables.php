<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Create exam schedule management tables
 */
class CreateExamTables extends Migration
{
    public function up()
    {
        // Exam import batches
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'semester_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'academic_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => false,
            ],
            'semester_no' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ],
            'exam_type' => [
                'type'       => 'ENUM',
                'constraint' => ['midterm', 'final'],
                'null'       => false,
            ],
            'source_filename' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'source_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'source_snapshot_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 512,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['draft', 'published', 'archived'],
                'default'    => 'draft',
            ],
            'imported_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['semester_label', 'exam_type']);
        $this->forge->addKey('status');
        $this->forge->addKey('imported_by');
        $this->forge->createTable('exam_import_batches');

        // Exam schedules
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'batch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'section_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'course_code' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
            ],
            'course_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'student_group' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'student_program' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'instructor_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'exam_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'exam_time_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'room' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'examiner1_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'examiner2_text' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'semester_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'academic_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => false,
            ],
            'semester_no' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ],
            'exam_type' => [
                'type'       => 'ENUM',
                'constraint' => ['midterm', 'final'],
                'null'       => false,
            ],
            'is_published' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('batch_id');
        $this->forge->addKey(['semester_label', 'exam_type']);
        $this->forge->addKey(['exam_date', 'exam_time_text']);
        $this->forge->addKey('is_published');
        $this->forge->addForeignKey('batch_id', 'exam_import_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('exam_schedules');

        // Exam schedule user links
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'schedule_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'user_uid' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'link_role' => [
                'type'       => 'ENUM',
                'constraint' => ['examiner1', 'examiner2', 'instructor'],
                'null'       => false,
            ],
            'matched_value' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
            ],
            'match_source' => [
                'type'       => 'ENUM',
                'constraint' => ['auto_nickname', 'auto_name', 'manual'],
                'null'       => false,
            ],
            'confidence' => [
                'type'       => 'DECIMAL',
                'constraint' => '3,2',
                'null'       => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['schedule_id', 'user_uid']);
        $this->forge->addKey('user_uid');
        $this->forge->addKey('link_role');
        $this->forge->addForeignKey('schedule_id', 'exam_schedules', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('user_uid', 'user', 'uid', 'CASCADE', 'CASCADE');
        $this->forge->createTable('exam_schedule_user_links');

        // Exam publish versions
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'semester_label' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'academic_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'null'       => false,
            ],
            'semester_no' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
            ],
            'exam_type' => [
                'type'       => 'ENUM',
                'constraint' => ['midterm', 'final'],
                'null'       => false,
            ],
            'batch_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'published_by' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['semester_label', 'exam_type', 'is_active']);
        $this->forge->addForeignKey('batch_id', 'exam_import_batches', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('exam_publish_versions');
    }

    public function down()
    {
        $this->forge->dropTable('exam_publish_versions');
        $this->forge->dropTable('exam_schedule_user_links');
        $this->forge->dropTable('exam_schedules');
        $this->forge->dropTable('exam_import_batches');
    }
}

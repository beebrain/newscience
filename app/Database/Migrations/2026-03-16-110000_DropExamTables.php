<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropExamTables extends Migration
{
    public function up()
    {
        // Drop tables in reverse order of dependencies
        $this->forge->dropTable('exam_schedule_user_links', true);
        $this->forge->dropTable('exam_publish_versions', true);
        $this->forge->dropTable('exam_schedules', true);
        $this->forge->dropTable('exam_import_batches', true);
    }

    public function down()
    {
        // Recreate tables if needed (rollback)
        // exam_import_batches
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'semester_label' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'academic_year' => ['type' => 'INT', 'constraint' => 4, 'null' => false],
            'semester_no' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => false],
            'exam_type' => ['type' => 'ENUM', 'constraint' => ['midterm', 'final'], 'null' => false],
            'source_filename' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'source_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'source_snapshot_path' => ['type' => 'VARCHAR', 'constraint' => 512, 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['draft', 'published', 'archived'], 'default' => 'draft', 'null' => false],
            'imported_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['semester_label', 'exam_type']);
        $this->forge->addKey('status');
        $this->forge->addKey('imported_by');
        $this->forge->createTable('exam_import_batches');

        // exam_schedules
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'batch_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'section_text' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'course_code' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'course_name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'student_group' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'student_program' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'instructor_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'exam_date' => ['type' => 'DATE', 'null' => true],
            'exam_time_text' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'room' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'examiner1_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'examiner2_text' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'semester_label' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'academic_year' => ['type' => 'INT', 'constraint' => 4, 'null' => true],
            'semester_no' => ['type' => 'TINYINT', 'constraint' => 1, 'null' => true],
            'exam_type' => ['type' => 'ENUM', 'constraint' => ['midterm', 'final'], 'null' => true],
            'is_published' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('batch_id');
        $this->forge->addKey(['semester_label', 'exam_type']);
        $this->forge->addKey('exam_date');
        $this->forge->createTable('exam_schedules');

        // exam_schedule_user_links
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'schedule_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'user_uid' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'link_role' => ['type' => 'ENUM', 'constraint' => ['examiner1', 'examiner2', 'instructor'], 'null' => false],
            'match_source' => ['type' => 'ENUM', 'constraint' => ['auto_nickname', 'auto_name', 'manual'], 'default' => 'manual', 'null' => false],
            'matched_value' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['schedule_id', 'user_uid']);
        $this->forge->addKey('user_uid');
        $this->forge->createTable('exam_schedule_user_links');

        // exam_publish_versions
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'batch_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'semester_label' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'exam_type' => ['type' => 'ENUM', 'constraint' => ['midterm', 'final'], 'null' => false],
            'is_active' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1, 'null' => false],
            'published_by' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'published_at' => ['type' => 'DATETIME', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => false],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['semester_label', 'exam_type', 'is_active']);
        $this->forge->createTable('exam_publish_versions');
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAdminStudentRoleToStudentUser extends Migration
{
    public function up(): void
    {
        if (! $this->db->tableExists('student_user')) {
            return;
        }

        $this->db->query(
            "ALTER TABLE `student_user`
             MODIFY COLUMN `role` ENUM('student', 'club', 'admin_student') DEFAULT 'student'
             COMMENT 'student=basic student, club=student club member, admin_student=student admin'"
        );
    }

    public function down(): void
    {
        if (! $this->db->tableExists('student_user')) {
            return;
        }

        $this->db->query("UPDATE `student_user` SET `role` = 'club' WHERE `role` = 'admin_student'");
        $this->db->query(
            "ALTER TABLE `student_user`
             MODIFY COLUMN `role` ENUM('student', 'club') DEFAULT 'student'
             COMMENT 'student=นักศึกษา, club=นักศึกษาสโมสร'"
        );
    }
}

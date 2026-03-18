<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFacultyToUser extends Migration
{
    public function up()
    {
        $this->forge->addColumn('user', [
            'faculty' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'คณะที่สังกัด เช่น คณะวิทยาศาสตร์และเทคโนโลยี',
                'after' => 'program_id',
            ],
        ]);

        // Set default value for existing users who are from Science and Technology faculty
        // based on common patterns in email or other indicators
        $this->db->query("UPDATE user SET faculty = 'คณะวิทยาศาสตร์และเทคโนโลยี' WHERE faculty IS NULL");
    }

    public function down()
    {
        $this->forge->dropColumn('user', 'faculty');
    }
}

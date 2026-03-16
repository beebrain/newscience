<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DropExamTablesSeeder extends Seeder
{
    public function run()
    {
        echo "Dropping exam tables...\n";
        
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->forge->dropTable('exam_schedule_user_links', true);
        $this->forge->dropTable('exam_publish_versions', true);
        $this->forge->dropTable('exam_schedules', true);
        $this->forge->dropTable('exam_import_batches', true);
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');
        
        echo "Exam tables dropped successfully!\n";
    }
}

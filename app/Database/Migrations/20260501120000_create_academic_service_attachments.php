<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * ไฟล์แนบที่เกี่ยวข้องกับรายการบริการวิชาการ
 */
class CreateAcademicServiceAttachments extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('academic_service_attachments')) {
            return;
        }

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
            'original_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'stored_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
                'comment'    => 'สัมพันธ์จาก writable/uploads/ เช่น academic-services/as_1_xxx.pdf',
            ],
            'file_size' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
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
        $this->forge->addForeignKey('academic_service_id', 'academic_services', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('academic_service_attachments');
    }

    public function down()
    {
        $this->forge->dropTable('academic_service_attachments', true);
    }
}

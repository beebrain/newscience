<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEdocDocumentTagsTable extends Migration
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
            'document_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tag_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'source_table' => [
                'type'       => 'ENUM',
                'constraint' => ['user', 'student_user'],
                'default'    => 'user',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['document_id', 'tag_email']);
        $this->forge->addKey('document_id');
        $this->forge->addKey('tag_email');
        $this->forge->createTable('edoc_document_tags', true);
    }

    public function down()
    {
        $this->forge->dropTable('edoc_document_tags', true);
    }
}

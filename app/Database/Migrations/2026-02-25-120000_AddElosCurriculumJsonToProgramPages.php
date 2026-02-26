<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddElosCurriculumJsonToProgramPages extends Migration
{
    public function up()
    {
        $fields = [
            'elos_json' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'graduate_profile',
            ],
            'curriculum_json' => [
                'type'    => 'TEXT',
                'null'    => true,
                'after'   => 'elos_json',
            ],
        ];

        $this->forge->addColumn('program_pages', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('program_pages', ['elos_json', 'curriculum_json']);
    }
}

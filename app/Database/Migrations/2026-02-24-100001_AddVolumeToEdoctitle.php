<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AddVolumeToEdoctitle extends Migration
{
    public function up()
    {
         = Database::connect();

         = [
            'volume_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'iddoc',
            ],
            'doc_year' => [
                'type'       => 'INT',
                'constraint' => 4,
                'unsigned'   => true,
                'null'       => true,
                'after'      => 'volume_id',
            ],
        ];

         = [];

        if (! ->fieldExists('volume_id', 'edoctitle')) {
            ['volume_id'] = ['volume_id'];
        }

        if (! ->fieldExists('doc_year', 'edoctitle')) {
             = ['doc_year'];

            // If volume_id already exists (or will not be added in this run),
            // place doc_year after the most appropriate column.
            if (! isset(['volume_id']) && ! ->fieldExists('volume_id', 'edoctitle')) {
                ['after'] = 'iddoc';
            }

            ['doc_year'] = ;
        }

        if (! empty()) {
            ->forge->addColumn('edoctitle', );
        }

        if (! ->indexExists('edoctitle', 'idx_volume_id')) {
            ->db->query('ALTER TABLE edoctitle ADD INDEX idx_volume_id (volume_id)');
        }

        if (! ->indexExists('edoctitle', 'idx_doc_year')) {
            ->db->query('ALTER TABLE edoctitle ADD INDEX idx_doc_year (doc_year)');
        }
    }

    public function down()
    {
        if (->indexExists('edoctitle', 'idx_volume_id')) {
            ->db->query('ALTER TABLE edoctitle DROP INDEX idx_volume_id');
        }

        if (->indexExists('edoctitle', 'idx_doc_year')) {
            ->db->query('ALTER TABLE edoctitle DROP INDEX idx_doc_year');
        }

        ->forge->dropColumn('edoctitle', ['volume_id', 'doc_year']);
    }

    private function indexExists(string , string ): bool
    {
         = ->db->escape();
         = ->db->query("SHOW INDEX FROM {} WHERE Key_name = {}");
        return  && ->getNumRows() > 0;
    }
}

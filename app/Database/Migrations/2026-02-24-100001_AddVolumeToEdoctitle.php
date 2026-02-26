<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use Config\Database;

class AddVolumeToEdoctitle extends Migration
{
    public function up()
    {
        $db = Database::connect();

        $fieldTemplates = [
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

        $columnsToAdd = [];

        if (! $db->fieldExists('volume_id', 'edoctitle')) {
            $columnsToAdd['volume_id'] = $fieldTemplates['volume_id'];
        }

        if (! $db->fieldExists('doc_year', 'edoctitle')) {
            $docYearField = $fieldTemplates['doc_year'];

            if (! $db->fieldExists('volume_id', 'edoctitle') && ! isset($columnsToAdd['volume_id'])) {
                $docYearField['after'] = 'iddoc';
            }

            $columnsToAdd['doc_year'] = $docYearField;
        }

        if (! empty($columnsToAdd)) {
            $this->forge->addColumn('edoctitle', $columnsToAdd);
        }

        if (! $this->indexExists($db, 'edoctitle', 'idx_volume_id')) {
            $db->query('ALTER TABLE edoctitle ADD INDEX idx_volume_id (volume_id)');
        }

        if (! $this->indexExists($db, 'edoctitle', 'idx_doc_year')) {
            $db->query('ALTER TABLE edoctitle ADD INDEX idx_doc_year (doc_year)');
        }
    }

    public function down()
    {
        $db = Database::connect();

        if ($this->indexExists($db, 'edoctitle', 'idx_volume_id')) {
            $db->query('ALTER TABLE edoctitle DROP INDEX idx_volume_id');
        }

        if ($this->indexExists($db, 'edoctitle', 'idx_doc_year')) {
            $db->query('ALTER TABLE edoctitle DROP INDEX idx_doc_year');
        }

        $columns = [];

        if ($db->fieldExists('volume_id', 'edoctitle')) {
            $columns[] = 'volume_id';
        }
        if ($db->fieldExists('doc_year', 'edoctitle')) {
            $columns[] = 'doc_year';
        }

        if (! empty($columns)) {
            $this->forge->dropColumn('edoctitle', $columns);
        }
    }

    private function indexExists($db, string $table, string $index): bool
    {
        $tableName = $db->protectIdentifiers($table);
        $escapedIndex = $db->escape($index);
        $result = $db->query("SHOW INDEX FROM {$tableName} WHERE Key_name = {$escapedIndex}");

        return $result !== false && $result->getNumRows() > 0;
    }
}

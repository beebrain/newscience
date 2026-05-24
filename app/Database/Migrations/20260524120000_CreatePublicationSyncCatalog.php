<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePublicationSyncCatalog extends Migration
{
    public function up()
    {
        if (! $this->db->tableExists('publications')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sync_external_key' => ['type' => 'VARCHAR', 'constraint' => 96, 'null' => true],
                'rr_publication_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'title'             => ['type' => 'VARCHAR', 'constraint' => 500, 'default' => ''],
                'publication_year'  => ['type' => 'INT', 'constraint' => 4, 'null' => true],
                'publication_type'  => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
                'source'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'doi'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'doi_norm'          => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'sync_origin'       => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'newscience'],
                'last_synced_from'  => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'content_hash'      => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
                'metadata'          => ['type' => 'TEXT', 'null' => true],
                'is_active'         => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('sync_external_key');
            $this->forge->addKey('rr_publication_id');
            $this->forge->addKey('doi_norm');
            $this->forge->createTable('publications');
        }

        if (! $this->db->tableExists('publication_contributors')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'publication_id'          => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'contributor_email_norm' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'contributor_name_key'   => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
                'display_name'           => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
                'personnel_id'           => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'contributor_affinity'   => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'external'],
                'rr_user_uid'            => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
                'rr_faculty_id'          => ['type' => 'INT', 'constraint' => 11, 'null' => true],
                'author_order'           => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'corresponding'          => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
                'affiliation'            => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => true],
                'source'                 => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'research_record'],
                'created_at'             => ['type' => 'DATETIME', 'null' => true],
                'updated_at'             => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('publication_id');
            $this->forge->addKey('contributor_email_norm');
            $this->forge->addKey('contributor_name_key');
            $this->forge->addKey('personnel_id');
            $this->forge->createTable('publication_contributors');
        }

        if (! $this->db->tableExists('cv_section_publications')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'section_id'        => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'publication_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
                'sort_order'        => ['type' => 'INT', 'constraint' => 11, 'default' => 0],
                'visible_on_public' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
                'created_at'        => ['type' => 'DATETIME', 'null' => true],
                'updated_at'        => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addKey('section_id');
            $this->forge->addKey('publication_id');
            $this->forge->createTable('cv_section_publications');
        }

        if (! $this->db->tableExists('publication_sync_state')) {
            $this->forge->addField([
                'id' => [
                    'type'           => 'INT',
                    'constraint'     => 11,
                    'unsigned'       => true,
                    'auto_increment' => true,
                ],
                'sync_external_key'   => ['type' => 'VARCHAR', 'constraint' => 96],
                'rr_publication_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'ns_publication_id'   => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
                'sync_origin'         => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'newscience'],
                'last_synced_from'    => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'last_sync_direction' => ['type' => 'VARCHAR', 'constraint' => 32, 'null' => true],
                'content_hash_rr'     => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
                'content_hash_ns'     => ['type' => 'CHAR', 'constraint' => 64, 'null' => true],
                'last_synced_at'      => ['type' => 'DATETIME', 'null' => true],
                'created_at'          => ['type' => 'DATETIME', 'null' => true],
                'updated_at'          => ['type' => 'DATETIME', 'null' => true],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->addUniqueKey('sync_external_key');
            $this->forge->addKey('rr_publication_id');
            $this->forge->addKey('ns_publication_id');
            $this->forge->createTable('publication_sync_state');
        }
    }

    public function down()
    {
        foreach (['publication_sync_state', 'cv_section_publications', 'publication_contributors', 'publications'] as $table) {
            if ($this->db->tableExists($table)) {
                $this->forge->dropTable($table);
            }
        }
    }
}

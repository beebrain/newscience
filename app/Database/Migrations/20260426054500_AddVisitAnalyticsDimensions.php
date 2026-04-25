<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Add visit analytics dimensions for admin website reports.
 */
class AddVisitAnalyticsDimensions extends Migration
{
    public function up()
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        if ($db->tableExists('page_views')) {
            $fields = [];

            if (! $db->fieldExists('content_type', 'page_views')) {
                $fields['content_type'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'route',
                ];
            }
            if (! $db->fieldExists('content_id', 'page_views')) {
                $fields['content_id'] = [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'unsigned'   => true,
                    'null'       => true,
                    'after'      => 'content_type',
                ];
            }
            if (! $db->fieldExists('content_title_snapshot', 'page_views')) {
                $fields['content_title_snapshot'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'content_id',
                ];
            }
            if (! $db->fieldExists('referrer_host', 'page_views')) {
                $fields['referrer_host'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'after'      => 'user_agent',
                ];
            }
            if (! $db->fieldExists('traffic_source', 'page_views')) {
                $fields['traffic_source'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'referrer_host',
                ];
            }
            if (! $db->fieldExists('device_type', 'page_views')) {
                $fields['device_type'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 30,
                    'null'       => true,
                    'after'      => 'traffic_source',
                ];
            }
            if (! $db->fieldExists('browser_family', 'page_views')) {
                $fields['browser_family'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'device_type',
                ];
            }
            if (! $db->fieldExists('os_family', 'page_views')) {
                $fields['os_family'] = [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => true,
                    'after'      => 'browser_family',
                ];
            }

            foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $utmField) {
                if (! $db->fieldExists($utmField, 'page_views')) {
                    $fields[$utmField] = [
                        'type'       => 'VARCHAR',
                        'constraint' => 150,
                        'null'       => true,
                    ];
                }
            }

            if ($fields !== []) {
                $this->forge->addColumn('page_views', $fields);
            }

            $this->createIndexIfMissing('page_views', 'idx_page_views_created_content', 'created_at, content_type');
            $this->createIndexIfMissing('page_views', 'idx_page_views_content', 'content_type, content_id');
            $this->createIndexIfMissing('page_views', 'idx_page_views_source_created', 'traffic_source, created_at');
            $this->createIndexIfMissing('page_views', 'idx_page_views_device_created', 'device_type, created_at');
        }

        if ($db->tableExists('systems')) {
            $exists = $db->table('systems')->where('slug', 'visit_reports')->countAllResults();
            if ($exists === 0) {
                $db->table('systems')->insert([
                    'slug'        => 'visit_reports',
                    'name_th'     => 'รายงานผู้เข้าชมเว็บไซต์',
                    'name_en'     => 'Website Visit Reports',
                    'description' => 'ดูสถิติการเข้าเยี่ยมชมเว็บไซต์และออกรายงาน',
                    'icon'        => null,
                    'is_active'   => 1,
                    'sort_order'  => 15,
                    'created_at'  => date('Y-m-d H:i:s'),
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }

    public function down()
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;

        if ($db->tableExists('page_views')) {
            $this->dropIndexIfExists('page_views', 'idx_page_views_device_created');
            $this->dropIndexIfExists('page_views', 'idx_page_views_source_created');
            $this->dropIndexIfExists('page_views', 'idx_page_views_content');
            $this->dropIndexIfExists('page_views', 'idx_page_views_created_content');

            $columns = [
                'content_type',
                'content_id',
                'content_title_snapshot',
                'referrer_host',
                'traffic_source',
                'device_type',
                'browser_family',
                'os_family',
                'utm_source',
                'utm_medium',
                'utm_campaign',
                'utm_term',
                'utm_content',
            ];

            foreach ($columns as $column) {
                if ($db->fieldExists($column, 'page_views')) {
                    $this->forge->dropColumn('page_views', $column);
                }
            }
        }

        if ($db->tableExists('systems')) {
            $db->table('systems')->where('slug', 'visit_reports')->delete();
        }
    }

    private function createIndexIfMissing(string $table, string $indexName, string $columns): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;
        $prefixedTable = $db->prefixTable($table);
        $row = $db->query('SHOW INDEX FROM `' . $prefixedTable . '` WHERE Key_name = ?', [$indexName])->getRowArray();
        if ($row === null) {
            $db->query('CREATE INDEX `' . $indexName . '` ON `' . $prefixedTable . '` (' . $columns . ')');
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        /** @var \CodeIgniter\Database\BaseConnection $db */
        $db = $this->db;
        $prefixedTable = $db->prefixTable($table);
        $row = $db->query('SHOW INDEX FROM `' . $prefixedTable . '` WHERE Key_name = ?', [$indexName])->getRowArray();
        if ($row !== null) {
            $db->query('DROP INDEX `' . $indexName . '` ON `' . $prefixedTable . '`');
        }
    }
}

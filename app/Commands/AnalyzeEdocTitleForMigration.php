<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * วิเคราะห์ตาราง edoctitle ใน local (newScience และ/หรือ sci-edoc) เพื่อวางแผนย้ายโครงสร้างจาก sci-edoc ไป newScience
 */
class AnalyzeEdocTitleForMigration extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'edoc:analyze-title-migration';
    protected $description = 'Analyze edoctitle structure in local DB(s) for sci-edoc → newScience migration';
    protected $usage       = 'edoc:analyze-title-migration [options]';
    protected $arguments   = [];
    protected $options     = [
        'source' => 'Database group for source (sci-edoc), e.g. edoclocal',
        'target' => 'Database group for target (newScience), e.g. default',
    ];

    private function getDbConfig(string $group): array
    {
        return [
            'hostname' => (string) (env("database.{$group}.hostname") ?? 'localhost'),
            'database' => (string) (env("database.{$group}.database") ?? ''),
            'username' => (string) (env("database.{$group}.username") ?? 'root'),
            'password' => (string) (env("database.{$group}.password") ?? ''),
            'DBDriver' => (string) (env("database.{$group}.DBDriver") ?? 'MySQLi'),
            'port'     => (int) (env("database.{$group}.port") ?? 3306),
        ];
    }

    private function connectGroup(string $group): ?\CodeIgniter\Database\BaseConnection
    {
        $config = $this->getDbConfig($group);
        if (empty($config['database'])) {
            CLI::write("  Group '{$group}' has no database in .env.", 'yellow');
            return null;
        }
        try {
            $fullConfig = array_merge(
                (new \Config\Database())->default,
                $config
            );
            $db = \Config\Database::connect($fullConfig);
            if (method_exists($db, 'initialize')) {
                $db->initialize();
            }
            return $db;
        } catch (\Throwable $e) {
            CLI::error("Could not connect to group '{$group}': " . $e->getMessage());
            return null;
        }
    }

    private function analyzeTable(\CodeIgniter\Database\BaseConnection $db, string $label): void
    {
        if (! $db->tableExists('edoctitle')) {
            CLI::write("  [{$label}] Table edoctitle does not exist.", 'yellow');
            return;
        }

        $fields = $db->getFieldNames('edoctitle');
        CLI::write("  [{$label}] Columns (" . count($fields) . "): " . implode(', ', $fields), 'green');

        $hasVolumeId = $db->fieldExists('volume_id', 'edoctitle');
        $hasDocYear  = $db->fieldExists('doc_year', 'edoctitle');
        CLI::write("  [{$label}] volume_id: " . ($hasVolumeId ? 'yes' : 'no') . ", doc_year: " . ($hasDocYear ? 'yes' : 'no'), 'cyan');

        $count = $db->table('edoctitle')->countAllResults();
        CLI::write("  [{$label}] Row count: {$count}", 'green');

        $sample = $db->table('edoctitle')->limit(2)->get()->getResultArray();
        if (! empty($sample)) {
            $first = $sample[0];
            $participant = $first['participant'] ?? '(null)';
            $len = strlen($participant);
            CLI::write("  [{$label}] Sample participant (length {$len}): " . mb_substr($participant, 0, 60) . ($len > 60 ? '…' : ''), 'cyan');
        }
    }

    public function run(array $params): int
    {
        CLI::write('=== วิเคราะห์ edoctitle สำหรับการย้าย sci-edoc → newScience ===', 'yellow');
        CLI::newLine();

        $sourceGroup = $params[0] ?? CLI::getOption('source') ?? 'edoclocal';
        $targetGroup = $params[1] ?? CLI::getOption('target') ?? 'default';

        CLI::write("Source group (sci-edoc): {$sourceGroup}", 'cyan');
        $sourceDb = $this->connectGroup($sourceGroup);
        if ($sourceDb !== null) {
            $this->analyzeTable($sourceDb, "sci-edoc ({$sourceGroup})");
            $sourceDb->close();
        }

        CLI::newLine();
        CLI::write("Target group (newScience): {$targetGroup}", 'cyan');
        $targetDb = $this->connectGroup($targetGroup);
        if ($targetDb !== null) {
            $this->analyzeTable($targetDb, "newScience ({$targetGroup})");
            $targetDb->close();
        }

        CLI::newLine();
        CLI::write('--- โครงสร้างเป้าหมาย newScience (edoctitle) ---', 'yellow');
        CLI::write('  iddoc, volume_id, doc_year, officeiddoc, title, datedoc, doctype, owner, participant, fileaddress, userid, pages, copynum, `order`, regisdate');
        CLI::write('  + index: volume_id, doc_year');
        CLI::newLine();
        CLI::write('--- ขั้นตอนย้ายข้อมูล ---', 'yellow');
        CLI::write('  1) โคลน sci-edoc ลง local: php spark db:clone-to-local edocserver edoclocal');
        CLI::write('  2) นำเข้า edoctitle: php spark import:edoc (หรือใช้คำสั่งที่รองรับ volume_id/doc_year)');
        CLI::write('  3) เติม volume_id/doc_year + participant→edoc_document_tags: php spark edoc:migrate-to-new-structure');
        CLI::newLine();
        return 0;
    }
}

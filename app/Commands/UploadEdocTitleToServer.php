<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Upload edoctitle table from local newScience DB to server newScience DB.
 * Source = default (local); Target = database.server.* in .env
 */
class UploadEdocTitleToServer extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'edoc:upload-title';
    protected $description = 'Upload edoctitle from local (default) to server newScience database';
    protected $usage       = 'edoc:upload-title [source-group] [target-group]. Example: edoc:upload-title default server';
    protected $arguments   = [];
    protected $options     = [];

    private function getDbConfig(string $group): array
    {
        $base = (new \Config\Database())->default;
        return array_merge($base, [
            'hostname' => (string) (env("database.{$group}.hostname") ?? $base['hostname'] ?? 'localhost'),
            'database' => (string) (env("database.{$group}.database") ?? $base['database'] ?? ''),
            'username' => (string) (env("database.{$group}.username") ?? $base['username'] ?? 'root'),
            'password' => (string) (env("database.{$group}.password") ?? $base['password'] ?? ''),
            'DBDriver' => (string) (env("database.{$group}.DBDriver") ?? $base['DBDriver'] ?? 'MySQLi'),
            'port'     => (int) (env("database.{$group}.port") ?? $base['port'] ?? 3306),
        ]);
    }

    public function run(array $params)
    {
        $sourceGroup = $params[0] ?? 'default';
        $targetGroup = $params[1] ?? 'server';

        CLI::write("Upload edoctitle: {$sourceGroup} (local) → {$targetGroup} (server)...", 'yellow');

        $sourceConfig = $this->getDbConfig($sourceGroup);
        $targetConfig = $this->getDbConfig($targetGroup);

        if (empty($sourceConfig['database']) || empty($targetConfig['database'])) {
            CLI::error("Set database.{$sourceGroup}.database and database.{$targetGroup}.database in .env");
            return 1;
        }

        try {
            $sourceDB = \Config\Database::connect($sourceConfig);
            $targetDB = \Config\Database::connect($targetConfig);
        } catch (\Throwable $e) {
            CLI::error('Connection failed: ' . $e->getMessage());
            return 1;
        }

        if (!$sourceDB->tableExists('edoctitle')) {
            CLI::error("Source database has no table 'edoctitle'.");
            return 1;
        }

        if (!$targetDB->tableExists('edoctitle')) {
            CLI::error("Target database has no table 'edoctitle'. Create the table on server first.");
            return 1;
        }

        $columns = $sourceDB->getFieldNames('edoctitle');
        $columns = array_values(array_filter($columns, function ($c) {
            return strtolower($c) !== ''; // exclude empty
        }));
        if (empty($columns)) {
            CLI::error('Could not get edoctitle columns.');
            return 1;
        }

        $colsList = '`' . implode('`,`', $columns) . '`';
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $updates = [];
        foreach ($columns as $col) {
            if (strtolower($col) === 'iddoc') {
                continue;
            }
            $updates[] = "`{$col}` = VALUES(`{$col}`)";
        }
        $updateClause = implode(', ', $updates);

        $sql = "INSERT INTO edoctitle ({$colsList}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updateClause}";
        $rows = $sourceDB->query('SELECT * FROM edoctitle')->getResultArray();
        $total = count($rows);
        CLI::write("Found {$total} rows in source edoctitle.", 'cyan');

        $success = 0;
        $fail = 0;
        foreach ($rows as $i => $row) {
            try {
                $values = [];
                foreach ($columns as $col) {
                    $values[] = $row[$col] ?? null;
                }
                $targetDB->query($sql, $values);
                $success++;
            } catch (\Throwable $e) {
                $fail++;
                if ($fail <= 5) {
                    CLI::write("  Row iddoc=" . ($row['iddoc'] ?? '?') . ": " . $e->getMessage(), 'red');
                }
            }
            if (($i + 1) % 500 === 0 && ($i + 1) > 0) {
                CLI::write("  Uploaded " . ($i + 1) . "/{$total}...", 'green');
            }
        }

        CLI::write("Done. Uploaded: {$success}, Failed: {$fail}", $fail > 0 ? 'yellow' : 'green');
        return $fail > 0 ? 1 : 0;
    }
}

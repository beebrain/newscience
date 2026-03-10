<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Upload edoc_volumes table from local newScience DB to server newScience DB.
 * Source = default (local); Target = database.server.* in .env
 */
class UploadEdocVolumesToServer extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'edoc:upload-volumes';
    protected $description = 'Upload edoc_volumes from local (default) to server newScience database';
    protected $usage       = 'edoc:upload-volumes [source-group] [target-group]. Example: edoc:upload-volumes default server';
    protected $arguments   = [];
    protected $options     = [];

    private function getDbConfig(string $group): array
    {
        $base = (new Database())->default;
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

        CLI::write("Upload edoc_volumes: {$sourceGroup} (local) → {$targetGroup} (server)...", 'yellow');

        $sourceConfig = $this->getDbConfig($sourceGroup);
        $targetConfig = $this->getDbConfig($targetGroup);

        if (empty($sourceConfig['database']) || empty($targetConfig['database'])) {
            CLI::error("Set database.{$sourceGroup}.database and database.{$targetGroup}.database in .env");
            return 1;
        }

        try {
            $sourceDB = Database::connect($sourceConfig);
            $targetDB = Database::connect($targetConfig);
        } catch (\Throwable $e) {
            CLI::error('Connection failed: ' . $e->getMessage());
            return 1;
        }

        if (! $sourceDB->tableExists('edoc_volumes')) {
            CLI::error("Source database has no table 'edoc_volumes'.");
            return 1;
        }

        if (! $targetDB->tableExists('edoc_volumes')) {
            CLI::error("Target database has no table 'edoc_volumes'. Create the table on server first.");
            return 1;
        }

        $columns = $sourceDB->getFieldNames('edoc_volumes');
        $columns = array_values(array_filter($columns, static function ($c) {
            return strtolower($c) !== '';
        }));
        if (empty($columns)) {
            CLI::error('Could not get edoc_volumes columns.');
            return 1;
        }

        $colsList    = '`' . implode('`,`', $columns) . '`';
        $placeholders = implode(',', array_fill(0, count($columns), '?'));
        $updates = [];
        foreach ($columns as $col) {
            if (strtolower($col) === 'id') {
                continue;
            }
            $updates[] = "`{$col}` = VALUES(`{$col}`)";
        }
        $updateClause = implode(', ', $updates);

        $sql   = "INSERT INTO edoc_volumes ({$colsList}) VALUES ({$placeholders}) ON DUPLICATE KEY UPDATE {$updateClause}";
        $rows  = $sourceDB->query('SELECT * FROM edoc_volumes')->getResultArray();
        $total = count($rows);
        CLI::write("Found {$total} rows in source edoc_volumes.", 'cyan');

        $success = 0;
        $fail    = 0;
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
                    CLI::write("  Row id=" . ($row['id'] ?? '?') . ": " . $e->getMessage(), 'red');
                }
            }
            if (($i + 1) % 100 === 0 && ($i + 1) > 0) {
                CLI::write("  Uploaded " . ($i + 1) . "/{$total}...", 'green');
            }
        }

        CLI::write("Done. Uploaded: {$success}, Failed: {$fail}", $fail > 0 ? 'yellow' : 'green');
        return $fail > 0 ? 1 : 0;
    }
}

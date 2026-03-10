<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * เปรียบเทียบโครงสร้าง DB ระหว่าง Local (default) กับ Server (database.server ใน .env)
 * ใช้: php spark schema:compare
 */
class CompareSchemaLocalServer extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'schema:compare';
    protected $description = 'Compare database structure: Local (default) vs Server (database.server)';
    protected $usage       = 'schema:compare';

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

    /**
     * @return array [tableName => [ ['name'=>..., 'type'=>..., 'null'=>..., 'default'=>...], ... ]]
     */
    private function getTablesAndColumns($db): array
    {
        $tables = $db->listTables();
        $out = [];
        foreach ($tables as $table) {
            $out[$table] = [];
            $res = $db->query("SHOW FULL COLUMNS FROM `" . str_replace('`', '``', $table) . "`");
            if (! $res) {
                continue;
            }
            $rows = $res->getResultArray();
            foreach ($rows as $r) {
                $out[$table][] = [
                    'name'    => $r['Field'] ?? '',
                    'type'    => $r['Type'] ?? '',
                    'null'    => (($r['Null'] ?? '') === 'YES'),
                    'default' => (string) ($r['Default'] ?? ''),
                ];
            }
        }
        return $out;
    }

    public function run(array $params)
    {
        CLI::write('Compare schema: Local (default) vs Server (database.server)', 'yellow');
        CLI::newLine();

        $localConfig  = $this->getDbConfig('default');
        $serverConfig = $this->getDbConfig('server');

        if (empty($localConfig['database']) || empty($serverConfig['database'])) {
            CLI::error('Set database.default.* and database.server.* in .env (hostname, database, username, password).');
            return 1;
        }

        try {
            $local  = Database::connect($localConfig);
        } catch (\Throwable $e) {
            CLI::error('Local (default) connection failed: ' . $e->getMessage());
            return 1;
        }

        try {
            $server = Database::connect($serverConfig);
        } catch (\Throwable $e) {
            CLI::error('Server connection failed: ' . $e->getMessage());
            return 1;
        }

        CLI::write('Local:  ' . $localConfig['hostname'] . ' / ' . $localConfig['database'], 'green');
        CLI::write('Server: ' . $serverConfig['hostname'] . ' / ' . $serverConfig['database'], 'green');
        CLI::newLine();

        $localTables  = $local->listTables();
        $serverTables = $server->listTables();
        sort($localTables);
        sort($serverTables);

        $onlyLocal  = array_diff($localTables, $serverTables);
        $onlyServer = array_diff($serverTables, $localTables);
        $common      = array_intersect($localTables, $serverTables);
        sort($common);

        $same = true;

        if (! empty($onlyLocal)) {
            $same = false;
            CLI::write('ตารางที่มีเฉพาะ Local (' . count($onlyLocal) . '):', 'yellow');
            foreach ($onlyLocal as $t) {
                CLI::write('  - ' . $t);
            }
            CLI::newLine();
        }

        if (! empty($onlyServer)) {
            $same = false;
            CLI::write('ตารางที่มีเฉพาะ Server (' . count($onlyServer) . '):', 'yellow');
            foreach ($onlyServer as $t) {
                CLI::write('  - ' . $t);
            }
            CLI::newLine();
        }

        $localSchema  = $this->getTablesAndColumns($local);
        $serverSchema = $this->getTablesAndColumns($server);

        foreach ($common as $table) {
            $lCols = $localSchema[$table]  ?? [];
            $sCols = $serverSchema[$table] ?? [];
            $lNames = array_column($lCols, 'name');
            $sNames = array_column($sCols, 'name');
            $onlyL = array_diff($lNames, $sNames);
            $onlyS = array_diff($sNames, $lNames);
            $colDiffs = [];
            foreach ($lCols as $lc) {
                $sc = null;
                foreach ($sCols as $s) {
                    if ($s['name'] === $lc['name']) {
                        $sc = $s;
                        break;
                    }
                }
                if ($sc === null) {
                    continue;
                }
                if ($lc['type'] !== $sc['type'] || $lc['null'] !== $sc['null'] || (string)$lc['default'] !== (string)$sc['default']) {
                    $colDiffs[] = [
                        'col'   => $lc['name'],
                        'local' => $lc['type'] . ($lc['null'] ? ' NULL' : ' NOT NULL') . ($lc['default'] !== '' ? ' DEFAULT ' . $lc['default'] : ''),
                        'server'=> $sc['type'] . ($sc['null'] ? ' NULL' : ' NOT NULL') . ($sc['default'] !== '' ? ' DEFAULT ' . $sc['default'] : ''),
                    ];
                }
            }
            if (! empty($onlyL) || ! empty($onlyS) || ! empty($colDiffs)) {
                $same = false;
                CLI::write('ตาราง ' . $table . ' — แตกต่าง:', 'yellow');
                if (! empty($onlyL)) {
                    CLI::write('  คอลัมน์ที่มีเฉพาะ Local: ' . implode(', ', $onlyL));
                }
                if (! empty($onlyS)) {
                    CLI::write('  คอลัมน์ที่มีเฉพาะ Server: ' . implode(', ', $onlyS));
                }
                foreach ($colDiffs as $d) {
                    CLI::write('  คอลัมน์ ' . $d['col'] . ':');
                    CLI::write('    Local:  ' . $d['local']);
                    CLI::write('    Server: ' . $d['server']);
                }
                CLI::newLine();
            }
        }

        if ($same) {
            CLI::write('โครงสร้าง Local กับ Server เหมือนกัน', 'green');
        } else {
            CLI::write('สรุป: โครงสร้างไม่เหมือนกัน — ดูรายการด้านบน', 'red');
        }

        return 0;
    }
}

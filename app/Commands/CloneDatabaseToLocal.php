<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Clone database from remote server (database.server) to local (database.default)
 * using mysqldump and mysql. Requires MySQL client tools in PATH or in XAMPP.
 */
class CloneDatabaseToLocal extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'db:clone-to-local';
    protected $description = 'Clone remote database (database.server) to local (database.default)';
    protected $usage       = 'db:clone-to-local [from-group] [to-group] [options]. Example: db:clone-to-local edocserver edoclocal';
    protected $arguments   = [];
    protected $options     = [
        'dump-only' => 'Only dump from remote to a file (do not import)',
        'file'      => 'Use this dump file path (for import only or dump output)',
        'from'      => 'Source env group (e.g. server or edocserver)',
        'to'        => 'Target env group (e.g. default or edoclocal)',
    ];

    private function getEnvVal(string $key, string $default = ''): string
    {
        $v = env($key);
        return $v !== null && $v !== '' ? (string) $v : $default;
    }

    private function getDbGroupConfig(string $group): array
    {
        return [
            'hostname' => $this->getEnvVal("database.{$group}.hostname", 'localhost'),
            'port'     => (int) $this->getEnvVal("database.{$group}.port", '3306'),
            'username' => $this->getEnvVal("database.{$group}.username", 'root'),
            'password' => $this->getEnvVal("database.{$group}.password", ''),
            'database' => $this->getEnvVal("database.{$group}.database", ''),
        ];
    }

    private function getServerConfig(): array
    {
        return $this->getDbGroupConfig('server');
    }

    private function getDefaultConfig(): array
    {
        return $this->getDbGroupConfig('default');
    }

    private function findMysqlBin(): string
    {
        $paths = [
            FCPATH . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mysql' . DIRECTORY_SEPARATOR . 'bin',
            'C:\\xampp\\mysql\\bin',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin',
        ];
        foreach ($paths as $p) {
            $real = realpath($p);
            if ($real !== false && is_dir($real)) {
                return rtrim($real, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            }
        }
        return '';
    }

    private function exeName(string $base): string
    {
        if (DIRECTORY_SEPARATOR === '\\' && ! str_ends_with($base, '.exe')) {
            return $base . '.exe';
        }
        return $base;
    }

    /** Run a command with proc_open; for mysqldump stdout is written to $outFile. */
    private function runDump(string $binPath, array $server, string $outFile): bool
    {
        $exe = $binPath . $this->exeName('mysqldump');
        if (! is_file($exe)) {
            CLI::error("mysqldump not found: {$exe}");
            return false;
        }
        $args = [
            $exe,
            '-h', $server['hostname'],
            '-P', (string) $server['port'],
            '-u', $server['username'],
            '--single-transaction',
            '--routines',
            '--triggers',
            $server['database'],
        ];
        if ($server['password'] !== '') {
            $args[] = '-p' . $server['password'];
        }
        $spec = [
            0 => ['pipe', 'r'],
            1 => ['file', $outFile, 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open(
            $args,
            $spec,
            $pipes,
            null,
            null
        );
        if (! is_resource($proc)) {
            CLI::error('Failed to start mysqldump.');
            return false;
        }
        fclose($pipes[0]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        if ($code !== 0 && $stderr !== '') {
            CLI::error('mysqldump: ' . trim($stderr));
        }
        return $code === 0;
    }

    /** Run mysql import with stdin from $dumpFile. */
    private function runImport(string $binPath, array $local, string $dumpFile): bool
    {
        $exe = $binPath . $this->exeName('mysql');
        if (! is_file($exe)) {
            CLI::error("mysql not found: {$exe}");
            return false;
        }
        $args = [
            $exe,
            '-h', $local['hostname'],
            '-P', (string) $local['port'],
            '-u', $local['username'],
            $local['database'],
        ];
        if ($local['password'] !== '') {
            $args[] = '-p' . $local['password'];
        }
        $spec = [
            0 => ['file', $dumpFile, 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];
        $proc = proc_open(
            $args,
            $spec,
            $pipes,
            null,
            null
        );
        if (! is_resource($proc)) {
            CLI::error('Failed to start mysql import.');
            return false;
        }
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($proc);
        if ($code !== 0 && $stderr !== '') {
            CLI::error('mysql: ' . trim($stderr));
        }
        return $code === 0;
    }

    /**
     * Rewrite dump so MySQL 8.0 collations become utf8mb4_general_ci for MariaDB/older MySQL.
     * Returns path to file to import from (temp file or original).
     */
    private function normalizeDumpCollation(string $dumpFile): string
    {
        $replace = [
            'utf8mb4_0900_ai_ci'   => 'utf8mb4_general_ci',
            'utf8mb4_0900_as_cs'   => 'utf8mb4_general_ci',
            'utf8mb4_0900_as_ci'   => 'utf8mb4_general_ci',
            'utf8mb4_ja_0900_as_cs' => 'utf8mb4_general_ci',
        ];
        $content = file_get_contents($dumpFile);
        if ($content === false) {
            return $dumpFile;
        }
        $changed = false;
        foreach ($replace as $from => $to) {
            if (str_contains($content, $from)) {
                $content = str_replace($from, $to, $content);
                $changed = true;
            }
        }
        if (! $changed) {
            return $dumpFile;
        }
        $temp = WRITEPATH . 'database_dump_import_' . uniqid('', true) . '.sql';
        if (file_put_contents($temp, $content) === false) {
            CLI::write('Could not write temp dump; importing from original.', 'yellow');
            return $dumpFile;
        }
        CLI::write('Collation adjusted for local server (utf8mb4_general_ci).', 'green');
        return $temp;
    }

    public function run(array $params): int
    {
        $fromGroup = $params[0] ?? CLI::getOption('from') ?? null;
        $toGroup   = $params[1] ?? CLI::getOption('to') ?? null;
        if (is_string($fromGroup) && $fromGroup !== '') {
            $server = $this->getDbGroupConfig($fromGroup);
        } else {
            $server = $this->getServerConfig();
        }
        if (is_string($toGroup) && $toGroup !== '') {
            $local = $this->getDbGroupConfig($toGroup);
        } else {
            $local = $this->getDefaultConfig();
        }

        if ($server['database'] === '' || $local['database'] === '') {
            CLI::error('Set database.<from>.* and database.<to>.* in .env (e.g. database.server / database.default or database.edocserver / database.edoclocal).');
            return 1;
        }

        $bin = $this->findMysqlBin();
        if ($bin === '') {
            CLI::error('MySQL bin directory not found. Add XAMPP mysql/bin to PATH or install MySQL client.');
            return 1;
        }

        $customFile = CLI::getOption('file');
        $dumpOnly   = CLI::getOption('dump-only') === true;
        $dumpFile   = $customFile !== null && $customFile !== ''
            ? $customFile
            : WRITEPATH . 'database_dump_' . date('Y-m-d_His') . '.sql';

        CLI::write('Source (remote): ' . $server['hostname'] . ' / ' . $server['database'], 'cyan');
        CLI::write('Target (local):  ' . $local['hostname'] . ' / ' . $local['database'], 'cyan');
        CLI::write('Dump file: ' . $dumpFile, 'cyan');

        CLI::write('Dumping from remote...', 'yellow');
        if (! $this->runDump($bin, $server, $dumpFile)) {
            return 1;
        }
        if (! is_file($dumpFile) || filesize($dumpFile) === 0) {
            CLI::error('Dump file is missing or empty.');
            return 1;
        }
        CLI::write('Dump saved: ' . $dumpFile, 'green');

        if ($dumpOnly) {
            CLI::write('Done (dump only).', 'green');
            return 0;
        }

        $importFile = $this->normalizeDumpCollation($dumpFile);
        CLI::write('Importing to local...', 'yellow');
        $ok = $this->runImport($bin, $local, $importFile);
        if ($importFile !== $dumpFile && is_file($importFile)) {
            @unlink($importFile);
        }
        if (! $ok) {
            return 1;
        }
        CLI::write('Clone completed successfully.', 'green');
        return 0;
    }
}

<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

/**
 * Map email and name from user table to evaluate_teaching in target DB
 * Uses source user table (sci-edoc) to update target evaluate_teaching (newScience)
 *
 * Usage: php spark map:teaching-eval-email [source-group]
 * Example: php spark map:teaching-eval-email edocserver
 */
class MapTeachingEvalEmail extends BaseCommand
{
    protected $group       = 'Database';
    protected $name        = 'map:teaching-eval-email';
    protected $description = 'Map email and name from user table to evaluate_teaching';
    protected $usage       = 'map:teaching-eval-email [source-group]. Example: map:teaching-eval-email edocserver';
    protected $arguments   = [];
    protected $options     = [
        'dry-run' => 'Show what would be updated without making changes',
    ];

    private function getSourceConfig(string $sourceGroup): array
    {
        $base = (new Database())->default;
        $dbName = env("database.{$sourceGroup}.database") ?: 'sci-edoc';
        return array_merge($base, [
            'hostname' => (string) (env("database.{$sourceGroup}.hostname") ?? $base['hostname'] ?? 'localhost'),
            'database' => (string) $dbName,
            'username' => (string) (env("database.{$sourceGroup}.username") ?? $base['username'] ?? 'root'),
            'password' => (string) (env("database.{$sourceGroup}.password") ?? $base['password'] ?? ''),
            'DBDriver' => (string) (env("database.{$sourceGroup}.DBDriver") ?? $base['DBDriver'] ?? 'MySQLi'),
            'port'     => (int) (env("database.{$sourceGroup}.port") ?? $base['port'] ?? 3306),
        ]);
    }

    private function connectGroup(string $group, ?array $configOverride = null): ?\CodeIgniter\Database\BaseConnection
    {
        $config = $configOverride ?? $this->getSourceConfig($group);
        if (empty($config['database'])) {
            return null;
        }
        try {
            $fullConfig = array_merge((new Database())->default, $config);
            $db = Database::connect($fullConfig);
            if (method_exists($db, 'initialize')) {
                $db->initialize();
            }
            return $db;
        } catch (\Throwable $e) {
            CLI::error('Connection failed: ' . $e->getMessage());
            return null;
        }
    }

    public function run(array $params): int
    {
        CLI::write('=== Map email and name from user table to evaluate_teaching ===', 'yellow');
        CLI::newLine();

        $sourceGroup = $params[0] ?? 'edocserver';
        $sourceConfig = $this->getSourceConfig($sourceGroup);
        $sourceConfig['database'] = $sourceConfig['database'] ?: 'sci-edoc';

        CLI::write("Connecting to source: {$sourceConfig['hostname']} / {$sourceConfig['database']} ...", 'light_gray');
        $source = $this->connectGroup($sourceGroup, $sourceConfig);
        if ($source === null) {
            return 1;
        }
        CLI::write('Connected to source DB', 'green');

        $target = Database::connect();
        if (! $target->tableExists('evaluate_teaching')) {
            CLI::error('Target DB must have evaluate_teaching table. Run: php spark migrate');
            $source->close();
            return 1;
        }
        CLI::write('Connected to target DB', 'green');

        $isDryRun = CLI::getOption('dry-run') !== null;
        if ($isDryRun) {
            CLI::write('DRY RUN MODE - No changes will be made', 'yellow');
            CLI::newLine();
        }

        // Build uid → user data map from source
        CLI::write('Building uid→user map from source user table...', 'light_gray');

        // Auto-detect column names in user table
        $userFields = $source->getFieldNames('user');
        $uidCol = in_array('uid', $userFields, true) ? 'uid' : (in_array('id', $userFields, true) ? 'id' : null);
        $emailCol = in_array('email', $userFields, true) ? 'email' : null;
        $fnameCol = in_array('gf_name', $userFields, true) ? 'gf_name' : (in_array('FirstName', $userFields, true) ? 'FirstName' : (in_array('first_name', $userFields, true) ? 'first_name' : null));
        $lnameCol = in_array('gl_name', $userFields, true) ? 'gl_name' : (in_array('LastName', $userFields, true) ? 'LastName' : (in_array('last_name', $userFields, true) ? 'last_name' : null));
        $fnameThaiCol = in_array('thai_name', $userFields, true) ? 'thai_name' : null;
        $lnameThaiCol = in_array('thai_lastname', $userFields, true) ? 'thai_lastname' : null;

        CLI::write("  User columns detected: uid={$uidCol}, email={$emailCol}, first_name={$fnameCol}, last_name={$lnameCol}, thai_name={$fnameThaiCol}, thai_lastname={$lnameThaiCol}", 'light_gray');

        if ($uidCol === null) {
            CLI::error('Could not find uid/id column in user table');
            $source->close();
            return 1;
        }

        $userMap = [];
        try {
            $selectCols = [$uidCol];
            if ($emailCol) $selectCols[] = $emailCol;
            if ($fnameCol) $selectCols[] = $fnameCol;
            if ($lnameCol) $selectCols[] = $lnameCol;
            if ($fnameThaiCol) $selectCols[] = $fnameThaiCol;
            if ($lnameThaiCol) $selectCols[] = $lnameThaiCol;

            $userRows = $source->table('user')->select(implode(', ', $selectCols))->get()->getResultArray();
            foreach ($userRows as $row) {
                $uid = $row[$uidCol] ?? null;
                if ($uid !== null) {
                    $userMap[(int) $uid] = [
                        'email'      => $emailCol ? ($row[$emailCol] ?? null) : null,
                        'first_name' => $fnameCol ? ($row[$fnameCol] ?? null) : null,
                        'last_name'  => $lnameCol ? ($row[$lnameCol] ?? null) : null,
                        'thai_name'  => $fnameThaiCol ? ($row[$fnameThaiCol] ?? null) : null,
                        'thai_lastname' => $lnameThaiCol ? ($row[$lnameThaiCol] ?? null) : null,
                    ];
                }
            }
            CLI::write('  Found ' . count($userMap) . ' users in source DB', 'green');
        } catch (\Throwable $e) {
            CLI::error('Failed to read user table: ' . $e->getMessage());
            $source->close();
            return 1;
        }

        // Get all evaluate_teaching that have uid but missing email
        CLI::write('Fetching evaluate_teaching from target DB...', 'light_gray');
        $evalRows = $target->table('evaluate_teaching')
            ->select('id, uid, email, first_name, last_name')
            ->where('uid IS NOT NULL')
            ->get()
            ->getResultArray();
        CLI::write('  Found ' . count($evalRows) . ' evaluations with uid', 'green');

        $updated = 0;
        $skipped = 0;
        $notFound = 0;

        CLI::write('Processing updates...', 'light_gray');

        foreach ($evalRows as $row) {
            $id = (int) $row['id'];
            $uid = $row['uid'] !== null ? (int) $row['uid'] : null;

            if ($uid === null || !isset($userMap[$uid])) {
                $notFound++;
                continue;
            }

            $userData = $userMap[$uid];
            $updateData = [];

            // Map email if empty or null
            if (empty($row['email']) && !empty($userData['email'])) {
                $updateData['email'] = $userData['email'];
            }

            // Map first_name if empty (prefer Thai names if available)
            if (empty($row['first_name']) && $fnameThaiCol && !empty($userData['thai_name'])) {
                $updateData['first_name'] = $userData['thai_name'];
            } elseif (empty($row['first_name']) && $fnameCol && !empty($userData['first_name'])) {
                $updateData['first_name'] = $userData['first_name'];
            }

            // Map last_name if empty (prefer Thai names if available)
            if (empty($row['last_name']) && $lnameThaiCol && !empty($userData['thai_lastname'])) {
                $updateData['last_name'] = $userData['thai_lastname'];
            } elseif (empty($row['last_name']) && $lnameCol && !empty($userData['last_name'])) {
                $updateData['last_name'] = $userData['last_name'];
            }

            if (empty($updateData)) {
                $skipped++;
                continue;
            }

            if ($isDryRun) {
                CLI::write("  [DRY] ID {$id} (uid={$uid}): " . json_encode($updateData, JSON_UNESCAPED_UNICODE), 'cyan');
                $updated++;
            } else {
                try {
                    $target->table('evaluate_teaching')
                        ->where('id', $id)
                        ->update($updateData);
                    $updated++;
                    if ($updated % 10 === 0) {
                        CLI::write("  Updated {$updated} rows...", 'light_gray');
                    }
                } catch (\Throwable $e) {
                    CLI::error("  Failed to update ID {$id}: " . $e->getMessage());
                }
            }
        }

        CLI::newLine();
        CLI::write('Results:', 'green');
        CLI::write("  Updated: {$updated} rows", $isDryRun ? 'cyan' : 'green');
        CLI::write("  Skipped (already has data): {$skipped} rows", 'yellow');
        CLI::write("  User not found in source: {$notFound} rows", 'yellow');

        $source->close();
        CLI::newLine();
        CLI::write('Done!', 'green');
        return 0;
    }
}

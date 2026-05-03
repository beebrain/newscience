<?php
/**
 * ดึงข้อมูลจาก Database Server มาไว้ที่ Local ทั้งหมด
 * อ่าน database.server.* (ต้นทาง) และ database.default.* (ปลายทาง) จาก .env เท่านั้น
 * รันบนเครื่องเดียว: Server -> Local (แทนที่ข้อมูลใน Local ทั้งหมด)
 *
 * ใช้: php scripts/sync_server_to_local.php
 */

$projectRoot = dirname(__DIR__);
$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';

if (!is_file($envFile) || !is_readable($envFile)) {
    fwrite(STDERR, "Error: .env not found or not readable at: " . $envFile . "\n");
    exit(1);
}

function parseEnvGroup(string $envFile, string $group): array {
    $config = [
        'hostname' => null,
        'database' => null,
        'username' => null,
        'password' => null,
        'port'     => 3306,
    ];
    $prefix = 'database.' . $group . '.';
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#') {
            continue;
        }
        if (strpos($line, '=') === false) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\"'");
        if (stripos($key, $prefix) !== 0) {
            continue;
        }
        $k = strtolower(str_replace($prefix, '', $key));
        if ($k === 'hostname') {
            $config['hostname'] = $value;
        } elseif ($k === 'database') {
            $config['database'] = $value;
        } elseif ($k === 'username') {
            $config['username'] = $value;
        } elseif ($k === 'password') {
            $config['password'] = $value;
        } elseif ($k === 'port') {
            $config['port'] = (int) $value ?: 3306;
        }
    }
    if ($config['password'] === null) {
        $config['password'] = '';
    }
    if ($config['port'] <= 0) {
        $config['port'] = 3306;
    }
    return $config;
}

/** @return string[] column names in ordinal order */
function tableColumns(mysqli $mysqli, string $schema, string $table): array {
    $s = $mysqli->real_escape_string($schema);
    $t = $mysqli->real_escape_string($table);
    $res = $mysqli->query(
        "SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$s}' AND TABLE_NAME = '{$t}' ORDER BY ORDINAL_POSITION"
    );
    if (!$res) {
        return [];
    }
    $cols = [];
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['COLUMN_NAME'];
    }
    return $cols;
}

$serverConfig = parseEnvGroup($envFile, 'server');
$localConfig  = parseEnvGroup($envFile, 'default');

foreach (['hostname', 'database', 'username'] as $k) {
    if (empty($serverConfig[$k])) {
        fwrite(STDERR, "Error: database.server.{$k} ไม่มีใน .env หรือว่าง\n");
        exit(1);
    }
    if (empty($localConfig[$k])) {
        fwrite(STDERR, "Error: database.default.{$k} ไม่มีใน .env หรือว่าง\n");
        exit(1);
    }
}

try {
    $server = new mysqli(
        $serverConfig['hostname'],
        $serverConfig['username'],
        $serverConfig['password'],
        $serverConfig['database'],
        $serverConfig['port']
    );
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Server connection failed: " . $e->getMessage() . "\n");
    exit(2);
}
if ($server->connect_error) {
    fwrite(STDERR, "Server connection failed: " . $server->connect_error . "\n");
    exit(2);
}
$server->set_charset('utf8mb4');

try {
    $local = new mysqli(
        $localConfig['hostname'],
        $localConfig['username'],
        $localConfig['password'],
        $localConfig['database'],
        $localConfig['port']
    );
} catch (mysqli_sql_exception $e) {
    fwrite(STDERR, "Local connection failed: " . $e->getMessage() . "\n");
    fwrite(STDERR, "Host: {$localConfig['hostname']}:{$localConfig['port']}. ตรวจสอบว่า MySQL (XAMPP) กำลังรันอยู่\n");
    exit(3);
}
if ($local->connect_error) {
    fwrite(STDERR, "Local connection failed: " . $local->connect_error . "\n");
    fwrite(STDERR, "Host: {$localConfig['hostname']}:{$localConfig['port']}. ตรวจสอบว่า MySQL (XAMPP) กำลังรันอยู่\n");
    exit(3);
}
$local->set_charset('utf8mb4');

$db = $server->real_escape_string($serverConfig['database']);
$res = $server->query("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$db}' AND TABLE_TYPE = 'BASE TABLE' ORDER BY TABLE_NAME");
if (!$res) {
    fwrite(STDERR, "Failed to get table list (server)\n");
    exit(4);
}
$tables = [];
while ($row = $res->fetch_array()) {
    $tables[] = $row[0];
}

$local->query("SET FOREIGN_KEY_CHECKS = 0");

$batchSize = 500;
$totalRows = 0;
$errors = [];

foreach ($tables as $table) {
    $tableEsc = $local->real_escape_string($table);
    $countRes = $server->query("SELECT COUNT(*) FROM `{$tableEsc}`");
    $numRows = $countRes ? (int) $countRes->fetch_array()[0] : 0;

    try {
        $local->query("TRUNCATE TABLE `{$tableEsc}`");
    } catch (mysqli_sql_exception $e) {
        $errors[] = "{$table}: TRUNCATE failed - " . $e->getMessage();
        echo "Skip {$table} (TRUNCATE failed)\n";
        continue;
    }
    if ($local->errno) {
        $errors[] = "{$table}: TRUNCATE failed - " . $local->error;
        echo "Skip {$table} (TRUNCATE failed)\n";
        continue;
    }

    if ($numRows === 0) {
        echo "OK {$table} (0 rows)\n";
        continue;
    }

    $serverCols = tableColumns($server, $serverConfig['database'], $table);
    $localCols  = tableColumns($local, $localConfig['database'], $table);
    $localSet   = array_flip($localCols);
    $common     = [];
    foreach ($serverCols as $c) {
        if (isset($localSet[$c])) {
            $common[] = $c;
        }
    }
    if ($common === []) {
        $errors[] = "{$table}: no overlapping columns between server and local (skip data)";
        echo "Skip {$table} (no common columns)\n";
        continue;
    }
    $onlyOnServer = array_diff($serverCols, $common);
    if ($onlyOnServer !== []) {
        echo "Note {$table}: omitting server-only columns: " . implode(', ', $onlyOnServer) . "\n";
    }

    $columns = [];
    foreach ($common as $name) {
        $columns[] = '`' . $local->real_escape_string($name) . '`';
    }
    $colsList = implode(',', $columns);

    $offset = 0;
    $inserted = 0;
    while (true) {
        $sel = $server->query(
            "SELECT {$colsList} FROM `{$tableEsc}` LIMIT " . (int) $batchSize . " OFFSET " . (int) $offset
        );
        if (!$sel || $sel->num_rows === 0) {
            break;
        }
        $values = [];
        while ($row = $sel->fetch_assoc()) {
            $vals = [];
            foreach ($common as $col) {
                $v = $row[$col] ?? null;
                if ($v === null) {
                    $vals[] = 'NULL';
                } elseif (is_numeric($v) && (string)(int)$v === (string)$v) {
                    $vals[] = (int) $v;
                } elseif (is_numeric($v)) {
                    $vals[] = (float) $v;
                } else {
                    $vals[] = "'" . $local->real_escape_string((string) $v) . "'";
                }
            }
            $values[] = '(' . implode(',', $vals) . ')';
        }
        $sql = "INSERT INTO `{$tableEsc}` ({$colsList}) VALUES " . implode(',', $values);
        $local->query($sql);
        if ($local->errno) {
            $errors[] = "{$table} @ offset {$offset}: " . $local->error;
            break;
        }
        $inserted += count($values);
        $offset += $batchSize;
    }
    $totalRows += $inserted;
    echo "OK {$table} ({$inserted} rows)\n";
}

$local->query("SET FOREIGN_KEY_CHECKS = 1");
$server->close();
$local->close();

echo "\nDone. Total rows copied: {$totalRows}\n";
if (!empty($errors)) {
    fwrite(STDERR, "\nErrors:\n" . implode("\n", $errors) . "\n");
    exit(5);
}

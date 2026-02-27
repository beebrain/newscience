<?php
/**
 * Export โครงสร้าง Database จาก .env เท่านั้น — รันบนเครื่องเดียว
 * อ่าน database.default.* (Local) และ database.server.* (Server) จาก .env
 * แล้ว export เป็น 2 ไฟล์ไว้เปรียบเทียบ
 *
 * ใช้: php scripts/export_db_schema.php
 * ได้: scripts/schema_local.txt (จาก database.default) และ scripts/schema_server.txt (จาก database.server)
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

function exportSchema(array $config): string {
    $mysqli = @new mysqli(
        $config['hostname'],
        $config['username'],
        $config['password'],
        $config['database'],
        $config['port']
    );
    if ($mysqli->connect_error) {
        throw new RuntimeException("Connection failed: " . $mysqli->connect_error);
    }
    $mysqli->set_charset('utf8mb4');
    $out = "# hostname={$config['hostname']} database={$config['database']} | " . date('Y-m-d H:i:s') . "\n\n";
    $res = $mysqli->query("SHOW TABLES");
    if (!$res) {
        $mysqli->close();
        throw new RuntimeException("SHOW TABLES failed");
    }
    $tables = [];
    while ($row = $res->fetch_array()) {
        $tables[] = $row[0];
    }
    sort($tables);
    foreach ($tables as $table) {
        $out .= "## TABLE: {$table}\n";
        $create = $mysqli->query("SHOW CREATE TABLE `" . $mysqli->real_escape_string($table) . "`");
        if ($create && $row = $create->fetch_array()) {
            $out .= $row[1] . "\n";
        }
        $out .= "\n";
    }
    $mysqli->close();
    $out .= "# END\n";
    return $out;
}

$default = parseEnvGroup($envFile, 'default');
$server  = parseEnvGroup($envFile, 'server');

foreach (['hostname', 'database', 'username'] as $k) {
    if (empty($default[$k])) {
        fwrite(STDERR, "Error: database.default.{$k} ไม่มีใน .env หรือว่าง\n");
        exit(1);
    }
    if (empty($server[$k])) {
        fwrite(STDERR, "Error: database.server.{$k} ไม่มีใน .env หรือว่าง\n");
        exit(1);
    }
}

$scriptDir = __DIR__;
$localFile  = $scriptDir . DIRECTORY_SEPARATOR . 'schema_local.txt';
$serverFile = $scriptDir . DIRECTORY_SEPARATOR . 'schema_server.txt';

try {
    $localSchema = exportSchema($default);
    file_put_contents($localFile, $localSchema);
    echo "OK Local  -> " . $localFile . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Local (database.default): " . $e->getMessage() . "\n");
    exit(2);
}

try {
    $serverSchema = exportSchema($server);
    file_put_contents($serverFile, $serverSchema);
    echo "OK Server -> " . $serverFile . "\n";
} catch (Throwable $e) {
    fwrite(STDERR, "Server (database.server): " . $e->getMessage() . "\n");
    exit(3);
}

echo "\nเปรียบเทียบ: fc scripts\\schema_local.txt scripts\\schema_server.txt\n";

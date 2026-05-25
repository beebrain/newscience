<?php

/**
 * Run: php scripts/run_add_barcode_events_join_code.php
 * Uses database.* from .env via mysqli (host shared_mysql in Docker).
 */
$root = dirname(__DIR__);
$envFile = $root . '/.env';
if (! is_file($envFile)) {
    fwrite(STDERR, "Missing .env\n");
    exit(1);
}
$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        continue;
    }
    if (preg_match('/^database\.default\.(\w+)\s*=\s*(.+)$/', $line, $m)) {
        $env[$m[1]] = trim($m[2], " \t\"'");
    }
}
$host = $env['hostname'] ?? 'localhost';
$user = $env['username'] ?? 'root';
$pass = $env['password'] ?? '';
$db   = $env['database'] ?? 'newscience';
$port = (int) ($env['port'] ?? 3306);

$sqlFile = $root . '/database/add_barcode_events_join_code.sql';
$sql = file_get_contents($sqlFile);
if ($sql === false) {
    fwrite(STDERR, "Cannot read SQL file\n");
    exit(1);
}

$conn = @new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    fwrite(STDERR, 'Connection failed: ' . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

if ($conn->query("SHOW COLUMNS FROM barcode_events LIKE 'join_code'")->num_rows > 0) {
    echo "OK: join_code already exists\n";
    $conn->close();
    exit(0);
}

if (! $conn->multi_query($sql)) {
    fwrite(STDERR, 'Migration failed: ' . $conn->error . "\n");
    exit(1);
}
while ($conn->more_results() && $conn->next_result()) {
}
echo "OK: added barcode_events.join_code\n";
$conn->close();
exit(0);

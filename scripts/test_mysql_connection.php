<?php
/**
 * Test MySQL connection (local and/or remote)
 * Run: php scripts/test_mysql_connection.php
 *      php scripts/test_mysql_connection.php remote
 *      php scripts/test_mysql_connection.php local remote
 */

$local = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$remote = [
    'host' => '49.231.30.18',
    'user' => 'root',
    'pass' => 'admin@SCI@2026',
    'db'   => 'newscience',
];

$args = array_slice($argv, 1);
if (empty($args)) {
    $targets = ['local'];
} else {
    $targets = $args;
}

function testConnection($label, $host, $user, $pass, $db) {
    echo "[$label] Connecting to $host ... ";
    $t = microtime(true);
    try {
        $conn = @new mysqli($host, $user, $pass, $db);
        $elapsed = round((microtime(true) - $t) * 1000);
        if ($conn->connect_error) {
            echo "FAIL\n";
            echo "        Error: " . $conn->connect_error . " (Code: " . $conn->connect_errno . ")\n";
            return false;
        }
        $conn->set_charset('utf8mb4');
        $version = $conn->server_info;
        $conn->close();
        echo "OK ({$elapsed} ms)\n";
        echo "        MySQL version: $version\n";
        return true;
    } catch (Throwable $e) {
        echo "FAIL\n";
        echo "        Exception: " . $e->getMessage() . "\n";
        return false;
    }
}

echo "=== MySQL connection test ===\n\n";

$ok = 0;
$fail = 0;

if (in_array('local', $targets)) {
    if (testConnection('LOCAL', $local['host'], $local['user'], $local['pass'], $local['db'])) {
        $ok++;
    } else {
        $fail++;
    }
    echo "\n";
}

if (in_array('remote', $targets)) {
    if (testConnection('REMOTE', $remote['host'], $remote['user'], $remote['pass'], $remote['db'])) {
        $ok++;
    } else {
        $fail++;
    }
    echo "\n";
}

echo "--- Result: $ok OK, $fail FAIL ---\n";
exit($fail > 0 ? 1 : 0);

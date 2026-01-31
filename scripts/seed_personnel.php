<?php
/**
 * Clear personnel table and insert sample data (local).
 * Run: php scripts/seed_personnel.php
 */

$local = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$conn = @new mysqli($local['host'], $local['user'], $local['pass'], $local['db']);
if ($conn->connect_error) {
    fwrite(STDERR, "Connection failed: " . $conn->connect_error . "\n");
    exit(1);
}

$conn->set_charset('utf8mb4');

// Load and run SQL file
$sqlFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seed_personnel.sql';
if (!is_file($sqlFile)) {
    fwrite(STDERR, "File not found: $sqlFile\n");
    exit(1);
}

$sql = file_get_contents($sqlFile);

if (!$conn->multi_query($sql)) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

do {
    if ($result = $conn->store_result()) {
        $result->free();
    }
} while ($conn->more_results() && $conn->next_result());

if ($conn->error) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$count = $conn->query("SELECT COUNT(*) AS c FROM personnel")->fetch_assoc()['c'];
echo "OK: Personnel table cleared and reseeded. Total rows: $count\n";

$conn->close();
exit(0);

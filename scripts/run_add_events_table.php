<?php

/**
 * Create events table for "Events coming up" feature.
 * Run: php scripts/run_add_events_table.php
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

$sql = file_get_contents(__DIR__ . '/../database/add_events_table.sql');
if ($sql === false) {
    fwrite(STDERR, "Could not read add_events_table.sql\n");
    exit(1);
}

// Execute multi-query (CREATE TABLE + SET)
if (!$conn->multi_query($sql)) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

do {
    if ($conn->more_results()) {
        $conn->next_result();
    }
} while ($conn->more_results());

echo "OK: events table created (or already exists).\n";
$conn->close();
exit(0);

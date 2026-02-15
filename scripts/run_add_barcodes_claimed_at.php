<?php

/**
 * Add claimed_at column to barcodes table.
 * Run: php scripts/run_add_barcodes_claimed_at.php
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

$sql = file_get_contents(__DIR__ . '/../database/add_barcodes_claimed_at.sql');
if ($sql === false) {
    fwrite(STDERR, "Could not read add_barcodes_claimed_at.sql\n");
    exit(1);
}

if (!$conn->query($sql)) {
    if ($conn->errno === 1060) {
        echo "Column claimed_at already exists.\n";
        $conn->close();
        exit(0);
    }
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

echo "OK: barcodes.claimed_at added.\n";
$conn->close();
exit(0);

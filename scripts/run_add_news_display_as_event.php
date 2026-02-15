<?php

/**
 * Add display_as_event column to news table.
 * Run: php scripts/run_add_news_display_as_event.php
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

// Check if column already exists
$r = $conn->query("SHOW COLUMNS FROM news LIKE 'display_as_event'");
if ($r && $r->num_rows > 0) {
    echo "OK: display_as_event already exists on news.\n";
    $conn->close();
    exit(0);
}

$sql = file_get_contents(__DIR__ . '/../database/add_news_display_as_event.sql');
if ($sql === false) {
    fwrite(STDERR, "Could not read add_news_display_as_event.sql\n");
    exit(1);
}

if (!$conn->query($sql)) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

echo "OK: news.display_as_event column added.\n";
$conn->close();
exit(0);

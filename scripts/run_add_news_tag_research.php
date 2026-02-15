<?php

/**
 * Add news tag "งานวิจัย" (slug: research).
 * Run: php scripts/run_add_news_tag_research.php
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

$sql = file_get_contents(__DIR__ . '/../database/add_news_tag_research.sql');
if ($sql === false) {
    fwrite(STDERR, "Could not read add_news_tag_research.sql\n");
    exit(1);
}

if (!$conn->query($sql)) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

echo "OK: news tag งานวิจัย (research) added if not exists.\n";
$conn->close();
exit(0);

<?php

/**
 * Create student_user, barcode_events, barcodes, barcode_event_eligibles tables.
 * Run: php scripts/run_add_student_user_and_barcode_tables.php
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

$sql = file_get_contents(__DIR__ . '/../database/add_student_user_and_barcode_tables.sql');
if ($sql === false) {
    fwrite(STDERR, "Could not read add_student_user_and_barcode_tables.sql\n");
    exit(1);
}

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

echo "OK: student_user, barcode_events, barcodes, barcode_event_eligibles tables created (or already exist).\n";
$conn->close();
exit(0);

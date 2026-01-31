<?php
/**
 * Add program_id column to personnel table (local).
 * Run: php scripts/run_add_personnel_program_id.php
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
$r = $conn->query("SHOW COLUMNS FROM `personnel` LIKE 'program_id'");
if ($r && $r->num_rows > 0) {
    echo "Column personnel.program_id already exists. Nothing to do.\n";
    $conn->close();
    exit(0);
}

$sql = "ALTER TABLE `personnel`
ADD COLUMN `program_id` INT UNSIGNED DEFAULT NULL AFTER `department_id`,
ADD KEY `program_id` (`program_id`)";

if ($conn->query($sql)) {
    echo "OK: Added column personnel.program_id successfully.\n";
} else {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$conn->close();
exit(0);

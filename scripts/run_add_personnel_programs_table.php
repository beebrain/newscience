<?php
/**
 * Create personnel_programs pivot table (อาจารย์ 1 คน สังกัดได้หลายหลักสูตร).
 * Run: php scripts/run_add_personnel_programs_table.php
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

$sql = "CREATE TABLE IF NOT EXISTS `personnel_programs` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `personnel_id` INT UNSIGNED NOT NULL,
    `program_id` INT UNSIGNED NOT NULL,
    `role_in_curriculum` VARCHAR(100) DEFAULT NULL,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `personnel_program` (`personnel_id`, `program_id`),
    KEY `program_id` (`program_id`),
    KEY `personnel_id` (`personnel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql)) {
    echo "OK: Table personnel_programs exists or was created successfully.\n";
} else {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$conn->close();
exit(0);

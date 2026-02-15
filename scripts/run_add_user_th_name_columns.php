<?php

/**
 * เพิ่มคอลัมน์ชื่อไทยใน user: th_name, thai_lastname (ใช้แสดงชื่อภาษาไทยเป็นหลัก)
 * Run: php scripts/run_add_user_th_name_columns.php
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

$defs = [
    ['name' => 'th_name',       'after' => 'tl_name', 'def' => 'VARCHAR(255) DEFAULT NULL COMMENT \'ชื่อ (ไทย)\''],
    ['name' => 'thai_lastname', 'after' => 'th_name', 'def' => 'VARCHAR(255) DEFAULT NULL COMMENT \'นามสกุล (ไทย)\''],
];

$added = 0;
$after = 'tl_name';
foreach ($defs as $d) {
    $col = $d['name'];
    $r = $conn->query("SHOW COLUMNS FROM `user` LIKE '" . $conn->real_escape_string($col) . "'");
    if ($r && $r->num_rows > 0) {
        $after = $col;
        continue;
    }
    $insertAfter = $d['after'];
    $r2 = $conn->query("SHOW COLUMNS FROM `user` LIKE '" . $conn->real_escape_string($insertAfter) . "'");
    if (!$r2 || $r2->num_rows === 0) {
        $insertAfter = $after;
    }
    $sql = "ALTER TABLE `user` ADD COLUMN `$col` " . $d['def'] . " AFTER `$insertAfter`";
    if (!$conn->query($sql)) {
        fwrite(STDERR, "Error adding user.$col: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    $added++;
    $after = $col;
}

if ($added > 0) {
    echo "OK: Added $added column(s) to user table (th_name, thai_lastname).\n";
} else {
    echo "OK: user table already has th_name and thai_lastname.\n";
}
$conn->close();
exit(0);

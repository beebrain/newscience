<?php

/**
 * เพิ่มคอลัมน์ที่ขาดใน user (ตาราง clone จาก researchrecord อาจไม่มี gf_name, gl_name, tf_name, tl_name, profile_image, title)
 * Run: php scripts/run_add_user_name_columns.php
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

// คอลัมน์ที่อาจขาด (เรียงตาม schema: หลัง password -> title, gf_name, gl_name, tf_name, tl_name; หลัง role -> profile_image)
$defs = [
    ['name' => 'title',       'after' => 'password', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'gf_name',     'after' => 'title',    'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'gl_name',     'after' => 'gf_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'tf_name',     'after' => 'gl_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'tl_name',     'after' => 'tf_name', 'def' => 'VARCHAR(255) DEFAULT NULL'],
    ['name' => 'profile_image', 'after' => 'role',  'def' => 'VARCHAR(255) DEFAULT NULL'],
];

$added = 0;
$after = 'password';
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
    echo "OK: Added $added column(s) to user table (title, gf_name, gl_name, tf_name, tl_name, profile_image).\n";
} else {
    echo "OK: user table already has all expected columns.\n";
}
$conn->close();
exit(0);

<?php
/**
 * Add user_uid to personnel (ลิงก์กับตาราง user โดยอีเมล).
 * Run: php scripts/run_add_personnel_user_uid.php
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

$r = $conn->query("SHOW COLUMNS FROM personnel LIKE 'user_uid'");
if ($r && $r->num_rows > 0) {
    echo "OK: Column personnel.user_uid already exists.\n";
    $conn->close();
    exit(0);
}

$conn->query("ALTER TABLE personnel ADD COLUMN user_uid INT UNSIGNED DEFAULT NULL COMMENT 'ลิงก์ user (อ้างอิงโดย email)' AFTER email");
if ($conn->error) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}
$conn->query("ALTER TABLE personnel ADD KEY user_uid (user_uid)");
echo "OK: personnel.user_uid added.\n";
$conn->close();
exit(0);

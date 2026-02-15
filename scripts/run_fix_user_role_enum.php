<?php
/**
 * เพิ่ม 'admin' และ 'editor' ใน role ENUM ของตาราง user (หลัง clone จาก researchrecord)
 * Run: php scripts/run_fix_user_role_enum.php
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

$sql = "ALTER TABLE `user` MODIFY COLUMN `role` ENUM('user','admin','editor','faculty_admin','super_admin') DEFAULT 'user'";
if (!$conn->query($sql)) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}
echo "OK: user.role ENUM updated (admin, editor added).\n";

// Set the admin user (login_uid=admin) to role=admin
$conn->query("UPDATE `user` SET role = 'admin' WHERE login_uid = 'admin' LIMIT 1");
$affected = $conn->affected_rows;
echo "OK: Set role=admin for login_uid=admin ($affected row(s)).\n";

$conn->close();
exit(0);

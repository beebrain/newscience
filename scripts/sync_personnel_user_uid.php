<?php
/**
 * Sync personnel.user_uid จากอีเมล (personnel.email = user.email).
 * รันครั้งเดียวหลังเพิ่มคอลัมน์ user_uid: php scripts/sync_personnel_user_uid.php
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
if (!$r || $r->num_rows === 0) {
    echo "Column personnel.user_uid does not exist. Run scripts/run_add_personnel_user_uid.php first.\n";
    exit(1);
}

$sql = "UPDATE personnel p INNER JOIN user u ON TRIM(u.email) = TRIM(p.email) SET p.user_uid = u.uid WHERE p.email IS NOT NULL AND TRIM(p.email) != ''";
$conn->query($sql);
$affected = $conn->affected_rows;
echo "OK: Synced personnel.user_uid from email (" . (int) $affected . " rows updated).\n";
$conn->close();
exit(0);

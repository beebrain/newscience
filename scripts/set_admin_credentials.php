<?php
/**
 * Set admin login to: username "admin", password "admin123"
 * Run: php scripts/set_admin_credentials.php
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

$passwordHash = password_hash('admin123', PASSWORD_DEFAULT);
$loginUid = 'admin';
$email = 'admin@localhost';

// Check if login_uid column exists
$r = $conn->query("SHOW COLUMNS FROM `user` LIKE 'login_uid'");
if (!$r || $r->num_rows === 0) {
    $conn->query("ALTER TABLE `user` ADD COLUMN `login_uid` VARCHAR(255) DEFAULT NULL AFTER `uid`");
}

// Update existing admin user(s): set login_uid and password (keep existing email)
$stmt = $conn->prepare("UPDATE `user` SET `login_uid` = ?, `password` = ? WHERE `role` = 'admin' LIMIT 1");
$stmt->bind_param('ss', $loginUid, $passwordHash);
$stmt->execute();
$updated = $stmt->affected_rows;
$stmt->close();

if ($updated > 0) {
    echo "OK: Admin user updated. Login: admin / admin123\n";
} else {
    // No admin found - try to insert
    $stmt = $conn->prepare("INSERT INTO `user` (`login_uid`, `email`, `password`, `gf_name`, `gl_name`, `tf_name`, `tl_name`, `role`, `status`) VALUES (?, ?, ?, 'Admin', 'User', 'ผู้ดูแล', 'ระบบ', 'admin', 'active')");
    $stmt->bind_param('sss', $loginUid, $email, $passwordHash);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        echo "OK: Admin user created. Login: admin / admin123\n";
    } else {
        fwrite(STDERR, "Could not create admin user. You may need to update manually.\n");
        exit(1);
    }
    $stmt->close();
}

$conn->close();
exit(0);

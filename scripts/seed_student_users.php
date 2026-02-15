<?php

/**
 * เพิ่มนักศึกษาทดสอบ 2 คน ใน student_user
 * - u123@live.uru.ac.th = student admin (role club)
 * - u321@live.uru.ac.th = student (role student)
 * รหัสผ่านเริ่มต้น: password123
 * Run: php scripts/seed_student_users.php
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

$passwordHash = password_hash('password123', PASSWORD_DEFAULT);

$students = [
    [
        'email' => 'u123@live.uru.ac.th',
        'login_uid' => 'u123',
        'role' => 'club',
        'th_name' => 'นักศึกษาสโมสร',
        'thai_lastname' => 'ทดสอบ',
    ],
    [
        'email' => 'u321@live.uru.ac.th',
        'login_uid' => 'u321',
        'role' => 'student',
        'th_name' => 'นักศึกษา',
        'thai_lastname' => 'ทดสอบ',
    ],
];

foreach ($students as $s) {
    $email = $conn->real_escape_string($s['email']);
    $login_uid = $conn->real_escape_string($s['login_uid']);
    $role = $conn->real_escape_string($s['role']);
    $th_name = $conn->real_escape_string($s['th_name']);
    $thai_lastname = $conn->real_escape_string($s['thai_lastname']);
    $password = $conn->real_escape_string($passwordHash);
    $status = 'active';

    $sql = "INSERT INTO student_user (email, login_uid, password, th_name, thai_lastname, role, status)
            VALUES ('$email', '$login_uid', '$password', '$th_name', '$thai_lastname', '$role', '$status')
            ON DUPLICATE KEY UPDATE
            password = VALUES(password),
            role = VALUES(role),
            th_name = VALUES(th_name),
            thai_lastname = VALUES(thai_lastname),
            login_uid = VALUES(login_uid),
            updated_at = CURRENT_TIMESTAMP";

    if (!$conn->query($sql)) {
        fwrite(STDERR, "Error inserting $email: " . $conn->error . "\n");
        $conn->close();
        exit(1);
    }
    echo "OK: $email (role=$role)\n";
}

$conn->close();
echo "Done. รหัสผ่านทั้งสองบัญชี: password123\n";
exit(0);

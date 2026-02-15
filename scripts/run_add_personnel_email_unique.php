<?php

/**
 * เพิ่ม UNIQUE KEY บน personnel.email เพื่อใช้ email เป็นตัวเชื่อมกับ App ภายนอกและ user
 * Run: php scripts/run_add_personnel_email_unique.php
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

// Check if unique key already exists
$r = $conn->query("SHOW INDEX FROM personnel WHERE Key_name = 'email' AND Non_unique = 0");
if ($r && $r->num_rows > 0) {
    echo "OK: personnel.email already has UNIQUE key.\n";
    $conn->close();
    exit(0);
}

// Check for duplicate non-empty emails
$dup = $conn->query("
    SELECT email, COUNT(*) AS cnt
    FROM personnel
    WHERE email IS NOT NULL AND TRIM(email) != ''
    GROUP BY email
    HAVING cnt > 1
");
if ($dup && $dup->num_rows > 0) {
    fwrite(STDERR, "Error: Duplicate emails found. Resolve before adding UNIQUE:\n");
    while ($row = $dup->fetch_assoc()) {
        fwrite(STDERR, "  - " . $row['email'] . " (" . $row['cnt'] . " rows)\n");
    }
    $conn->close();
    exit(1);
}

$ok = $conn->query("ALTER TABLE personnel ADD UNIQUE KEY `email` (`email`)");
if (!$ok) {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

echo "OK: personnel.email UNIQUE key added. Email can be used as cross-app linking key.\n";
$conn->close();
exit(0);

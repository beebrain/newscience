<?php
/**
 * เพิ่ม comment กฎธุรกิจให้ตาราง personnel_programs
 * Run: php scripts/run_enforce_chair_rules_comment.php
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

$sql = "ALTER TABLE `personnel_programs`
COMMENT = 'Pivot บุคลากร–หลักสูตร. กฎ: 1 คนเป็นประธานได้1หลักสูตร, 1หลักสูตรมีประธานได้1คน. Enforce ในแอป'";

if ($conn->query($sql)) {
    echo "OK: Table comment updated (enforce chair rules).\n";
} else {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$conn->close();
exit(0);

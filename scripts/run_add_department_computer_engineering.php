<?php
/**
 * Add department: สาขาวิชาวิศวกรรมคอมพิวเตอร์ (Computer Engineering).
 * Run: php scripts/run_add_department_computer_engineering.php
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

$nameTh = 'สาขาวิชาวิศวกรรมคอมพิวเตอร์';
$escaped = "'" . $conn->real_escape_string($nameTh) . "'";
$check = $conn->query("SELECT 1 FROM departments WHERE name_th = $escaped LIMIT 1");
if ($check && $check->num_rows > 0) {
    echo "OK: Department '{$nameTh}' already exists.\n";
    $conn->close();
    exit(0);
}

$stmt = $conn->prepare("INSERT INTO departments (name_th, name_en, code, sort_order, status) VALUES (?, 'Computer Engineering', 'CE', 13, 'active')");
$stmt->bind_param('s', $nameTh);
if ($stmt->execute()) {
    echo "OK: Department '{$nameTh}' added successfully.\n";
} else {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}
$stmt->close();
$conn->close();
exit(0);

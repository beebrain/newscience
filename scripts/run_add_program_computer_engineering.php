<?php
/**
 * Add program: วิศวกรรมคอมพิวเตอร์ (Computer Engineering) under department สาขาวิชาวิศวกรรมคอมพิวเตอร์.
 * Run after run_add_department_computer_engineering.php.
 * Run: php scripts/run_add_program_computer_engineering.php
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

// Get department id for สาขาวิชาวิศวกรรมคอมพิวเตอร์
$nameTh = 'สาขาวิชาวิศวกรรมคอมพิวเตอร์';
$escaped = "'" . $conn->real_escape_string($nameTh) . "'";
$res = $conn->query("SELECT id FROM departments WHERE name_th = $escaped LIMIT 1");
if (!$res || $res->num_rows === 0) {
    fwrite(STDERR, "Error: Department '{$nameTh}' not found. Run run_add_department_computer_engineering.php first.\n");
    $conn->close();
    exit(1);
}
$row = $res->fetch_assoc();
$deptId = (int) $row['id'];

// Check if program already exists (same name + department)
$nameProg = 'วิศวกรรมคอมพิวเตอร์';
$escapedProg = "'" . $conn->real_escape_string($nameProg) . "'";
$check = $conn->query("SELECT 1 FROM programs WHERE name_th = $escapedProg AND department_id = $deptId LIMIT 1");
if ($check && $check->num_rows > 0) {
    echo "OK: Program '{$nameProg}' (department id {$deptId}) already exists.\n";
    $conn->close();
    exit(0);
}

// Get next sort_order
$res = $conn->query("SELECT COALESCE(MAX(sort_order), 0) + 1 AS next_order FROM programs");
$nextOrder = 14;
if ($res && $r = $res->fetch_assoc()) {
    $nextOrder = (int) $r['next_order'];
}

$stmt = $conn->prepare("INSERT INTO programs (name_th, name_en, degree_th, degree_en, level, department_id, duration, sort_order, status) VALUES (?, 'Computer Engineering', 'วิทยาศาสตรบัณฑิต', 'Bachelor of Science', 'bachelor', ?, '4 ปี', ?, 'active')");
$stmt->bind_param('sii', $nameProg, $deptId, $nextOrder);
if ($stmt->execute()) {
    echo "OK: Program '{$nameProg}' (สาขาวิชาวิศวกรรมคอมพิวเตอร์) added successfully.\n";
} else {
    fwrite(STDERR, "Error: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}
$stmt->close();
$conn->close();
exit(0);

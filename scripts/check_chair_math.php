<?php
/**
 * ตรวจสอบหลักสูตรคณิตศาสตร์ มีประธานหลักสูตรกี่คน
 * Run: php scripts/check_chair_math.php
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

$programName = 'คณิตศาสตร์';
$stmt = $conn->prepare("SELECT id, name_th, name_en FROM programs WHERE name_th LIKE ? OR name_en LIKE ?");
$like = '%' . $programName . '%';
$stmt->bind_param('ss', $like, $like);
$stmt->execute();
$res = $stmt->get_result();

$programs = [];
while ($row = $res->fetch_assoc()) {
    $programs[] = $row;
}
$stmt->close();

if (empty($programs)) {
    echo "ไม่พบหลักสูตรที่มีชื่อประกอบด้วย '{$programName}'\n";
    $conn->close();
    exit(0);
}

foreach ($programs as $prog) {
    $pid = (int) $prog['id'];
    $nameTh = $prog['name_th'] ?? '';
    $nameEn = $prog['name_en'] ?? '';

    $stmt2 = $conn->prepare("
        SELECT pp.id, pp.personnel_id, pp.role_in_curriculum,
               p.name, p.name_en, p.position
        FROM personnel_programs pp
        LEFT JOIN personnel p ON p.id = pp.personnel_id
        WHERE pp.program_id = ? AND pp.role_in_curriculum LIKE ?
    ");
    $roleLike = '%ประธาน%';
    $stmt2->bind_param('is', $pid, $roleLike);
    $stmt2->execute();
    $chairs = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt2->close();

    $count = count($chairs);
    echo "หลักสูตร: {$nameTh} (ID: {$pid})\n";
    echo "จำนวนประธานหลักสูตร: {$count} คน\n";
    if ($count > 0) {
        foreach ($chairs as $c) {
            $name = trim($c['name'] ?? '');
            $role = $c['role_in_curriculum'] ?? '';
            echo "  - personnel_id: {$c['personnel_id']}, ชื่อ: {$name}, บทบาท: {$role}\n";
        }
    }
    echo "\n";
}

$conn->close();
exit(0);

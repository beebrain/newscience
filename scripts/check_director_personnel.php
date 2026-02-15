<?php
/**
 * ตรวจสอบว่ามีบุคลากรตำแหน่ง ผู้อำนวยการสำนักงาน / ผู้อำนวยการ ในฐานข้อมูลหรือไม่
 * Run: php scripts/check_director_personnel.php
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

echo "=== ตรวจสอบบุคลากรตำแหน่ง ผู้อำนวยการสำนักงาน / ผู้อำนวยการ ===\n\n";

// 1) มีคำว่า "ผู้อำนวยการสำนักงาน" ใน position หรือ position_detail
$q1 = "SELECT id, name, name_en, position, position_en, position_detail, status 
       FROM personnel 
       WHERE status = 'active' 
         AND (position LIKE '%ผู้อำนวยการสำนักงาน%' OR position_detail LIKE '%ผู้อำนวยการสำนักงาน%')";
$r1 = $conn->query($q1);
echo "1) ตำแหน่งมีคำว่า 'ผู้อำนวยการสำนักงาน' (position หรือ position_detail):\n";
if ($r1 && $r1->num_rows > 0) {
    while ($row = $r1->fetch_assoc()) {
        echo "   ID {$row['id']}: " . ($row['name'] ?? '') . " | position: " . ($row['position'] ?? '') . " | detail: " . ($row['position_detail'] ?? '') . "\n";
    }
    echo "   รวม " . $r1->num_rows . " รายการ\n";
} else {
    echo "   ไม่พบรายการ\n";
}

echo "\n";

// 2) มีคำว่า "ผู้อำนวยการ" (ทั่วไป) ใน position หรือ position_detail
$q2 = "SELECT id, name, name_en, position, position_en, position_detail, status 
       FROM personnel 
       WHERE status = 'active' 
         AND (position LIKE '%ผู้อำนวยการ%' OR position_detail LIKE '%ผู้อำนวยการ%' OR position_en LIKE '%director%')";
$r2 = $conn->query($q2);
echo "2) ตำแหน่งมีคำว่า 'ผู้อำนวยการ' หรือ 'director' (position / position_detail / position_en):\n";
if ($r2 && $r2->num_rows > 0) {
    while ($row = $r2->fetch_assoc()) {
        echo "   ID {$row['id']}: " . ($row['name'] ?? '') . " | position: " . ($row['position'] ?? '') . " | detail: " . ($row['position_detail'] ?? '') . " | en: " . ($row['position_en'] ?? '') . "\n";
    }
    echo "   รวม " . $r2->num_rows . " รายการ\n";
} else {
    echo "   ไม่พบรายการ\n";
}

echo "\n";

// 3) มีคำว่า "ผอ." หรือ "ผอสำนักงาน"
$q3 = "SELECT id, name, name_en, position, position_detail 
       FROM personnel 
       WHERE status = 'active' 
         AND (position LIKE '%ผอ.%' OR position LIKE '%ผอสำนักงาน%' OR position_detail LIKE '%ผอ.%' OR position_detail LIKE '%ผอสำนักงาน%')";
$r3 = $conn->query($q3);
echo "3) ตำแหน่งมีคำว่า 'ผอ.' หรือ 'ผอสำนักงาน':\n";
if ($r3 && $r3->num_rows > 0) {
    while ($row = $r3->fetch_assoc()) {
        echo "   ID {$row['id']}: " . ($row['name'] ?? '') . " | position: " . ($row['position'] ?? '') . " | detail: " . ($row['position_detail'] ?? '') . "\n";
    }
    echo "   รวม " . $r3->num_rows . " รายการ\n";
} else {
    echo "   ไม่พบรายการ\n";
}

$conn->close();
echo "\n--- จบการตรวจสอบ ---\n";
exit(0);

<?php
/**
 * ตรวจสอบบุคลากรที่ควรเป็นหัวหน้าหน่วยการจัดการงานวิจัย (ใน DB มีชื่อ สุทธิดา หรือตำแหน่งเกี่ยวข้อง)
 * php scripts/check_head_research_personnel.php
 */
$db = new mysqli('localhost', 'root', '', 'newscience');
$db->set_charset('utf8mb4');

$keywords = ['สุทธิดา', 'หัวหน้าหน่วย', 'หัวหน้าหน่วยวิจัย', 'หัวหน้าหน่วยการจัดการ'];
$cond = [];
foreach ($keywords as $k) {
    $pat = '%' . $k . '%';
    $e = $db->escape_string($pat);
    $cond[] = " (p.position LIKE '$e' OR p.position_en LIKE '$e' OR p.name LIKE '$e' OR u.thai_name LIKE '$e') ";
}
$where = implode(' OR ', $cond);

$sql = "SELECT p.id, p.name, p.name_en, p.position, p.position_en, p.status, p.organization_unit_id, p.sort_order,
        u.thai_name, u.thai_lastname, u.email
        FROM personnel p
        LEFT JOIN user u ON u.uid = p.user_uid
        WHERE p.status = 'active' AND ($where)
        ORDER BY p.sort_order, p.id";
$r = $db->query($sql);
if (!$r) {
    die("Error: " . $db->error . "\n");
}

echo "=== บุคลากรที่เกี่ยวข้องกับหัวหน้าหน่วยการจัดการงานวิจัย / ชื่อ สุทธิดา ===\n\n";
$count = 0;
while ($row = $r->fetch_assoc()) {
    $count++;
    $name = trim(($row['thai_name'] ?? '') . ' ' . ($row['thai_lastname'] ?? '')) ?: ($row['name'] ?? '');
    echo "id={$row['id']} name=" . trim($name) . "\n";
    echo "  position=" . ($row['position'] ?? '') . "\n";
    echo "  position_en=" . ($row['position_en'] ?? '') . "\n";
    echo "  status={$row['status']} organization_unit_id=" . ($row['organization_unit_id'] ?? '') . "\n\n";
}
echo "พบ $count รายการ\n";
echo "\nถ้าตำแหน่งใน DB ไม่มีคำว่า 'หัวหน้าหน่วยจัดการงานวิจัย' หรือ 'หัวหน้าหน่วยวิจัย' ให้แก้ในตาราง personnel (position/position_en) ให้ตรง\n";

<?php
/**
 * Sync programs.chair_personnel_id from personnel_programs (role ประธานหลักสูตร).
 * Run once after adding chair_personnel_id: php scripts/sync_program_chairs_once.php
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

$r = $conn->query("SHOW COLUMNS FROM programs LIKE 'chair_personnel_id'");
if (!$r || $r->num_rows === 0) {
    echo "Column programs.chair_personnel_id does not exist. Run scripts/run_add_program_chair_personnel_id.php first.\n";
    exit(1);
}

// Get chairs from personnel_programs
$chairs = [];
$q = $conn->query("SELECT program_id, personnel_id FROM personnel_programs WHERE role_in_curriculum LIKE '%ประธาน%'");
if ($q) {
    while ($row = $q->fetch_assoc()) {
        $pid = (int) $row['program_id'];
        $uid = (int) $row['personnel_id'];
        if ($pid > 0 && $uid > 0) {
            $chairs[$pid] = $uid;
        }
    }
}

// Update each program
$all = $conn->query("SELECT id FROM programs");
$updated = 0;
while ($row = $all->fetch_assoc()) {
    $pid = (int) $row['id'];
    $uid = $chairs[$pid] ?? null;
    $sql = $uid !== null
        ? "UPDATE programs SET chair_personnel_id = " . (int) $uid . " WHERE id = " . $pid
        : "UPDATE programs SET chair_personnel_id = NULL WHERE id = " . $pid;
    if ($conn->query($sql)) {
        $updated++;
    }
}
echo "OK: programs.chair_personnel_id synced from personnel_programs ($updated programs updated).\n";
$conn->close();
exit(0);

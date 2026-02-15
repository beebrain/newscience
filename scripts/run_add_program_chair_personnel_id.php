<?php
/**
 * Add chair_personnel_id (ประธานหลักสูตร) to programs table.
 * Run: php scripts/run_add_program_chair_personnel_id.php
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

// Check if column already exists
$r = $conn->query("SHOW COLUMNS FROM programs LIKE 'chair_personnel_id'");
if ($r && $r->num_rows > 0) {
    echo "OK: Column programs.chair_personnel_id already exists.\n";
    $conn->close();
    exit(0);
}

$conn->begin_transaction();
try {
    $conn->query("ALTER TABLE programs ADD COLUMN chair_personnel_id INT UNSIGNED DEFAULT NULL COMMENT 'ประธานหลักสูตร (personnel.id)' AFTER coordinator_id");
    if ($conn->error) {
        throw new Exception($conn->error);
    }
    $conn->query("ALTER TABLE programs ADD KEY chair_personnel_id (chair_personnel_id)");
    if ($conn->error) {
        throw new Exception($conn->error);
    }
    $conn->query("ALTER TABLE programs ADD CONSTRAINT fk_programs_chair_personnel FOREIGN KEY (chair_personnel_id) REFERENCES personnel(id) ON DELETE SET NULL");
    if ($conn->error) {
        throw new Exception($conn->error);
    }
    $conn->commit();
    echo "OK: programs.chair_personnel_id added successfully.\n";
    // Backfill from personnel_programs (role ประธานหลักสูตร)
    $pp = $conn->query("SELECT program_id, personnel_id FROM personnel_programs WHERE role_in_curriculum LIKE '%ประธาน%'");
    if ($pp) {
        while ($row = $pp->fetch_assoc()) {
            $pid = (int) $row['program_id'];
            $uid = (int) $row['personnel_id'];
            if ($pid > 0 && $uid > 0) {
                $conn->query("UPDATE programs SET chair_personnel_id = $uid WHERE id = $pid");
            }
        }
        echo "OK: Backfilled chair_personnel_id from personnel_programs.\n";
    }
} catch (Exception $e) {
    $conn->rollback();
    fwrite(STDERR, "Error: " . $e->getMessage() . "\n");
    $conn->close();
    exit(1);
}
$conn->close();
exit(0);

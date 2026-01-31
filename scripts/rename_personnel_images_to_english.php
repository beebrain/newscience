<?php
/**
 * Rename personnel image files to English-only: personnel-{id}.ext
 * Updates DB column personnel.image. Run once to fix existing Thai-named files.
 *
 * Usage: php scripts/rename_personnel_images_to_english.php
 */

$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'newscience';

$baseDir = __DIR__ . '/../public';
$personnelDir = $baseDir . '/uploads/personnel';

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SELECT id, image FROM personnel WHERE image IS NOT NULL AND image != ''");
if (!$res) {
    die("Query failed: " . $mysqli->error);
}

$renamed = 0;
$skipped = 0;
$errors = [];

while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $current = trim($row['image']);
    // Normalize: allow "uploads/personnel/name.png" or "personnel/name.png"
    $current = str_replace('\\', '/', $current);
    if (strpos($current, 'uploads/personnel/') === 0) {
        $oldFilename = substr($current, strlen('uploads/personnel/'));
    } elseif (strpos($current, 'personnel/') === 0) {
        $oldFilename = substr($current, strlen('personnel/'));
    } else {
        $oldFilename = basename($current);
    }
    $oldPath = $personnelDir . DIRECTORY_SEPARATOR . $oldFilename;

    // New English filename: personnel-{id}.ext
    $ext = pathinfo($oldFilename, PATHINFO_EXTENSION);
    if (empty($ext) || preg_match('/[^a-zA-Z0-9]/', $ext)) {
        $ext = 'jpg';
    }
    $ext = strtolower($ext);
    if ($ext === 'jpeg') $ext = 'jpg';
    $newFilename = 'personnel-' . $id . '.' . $ext;
    $newPath = $personnelDir . DIRECTORY_SEPARATOR . $newFilename;
    $newDbValue = 'uploads/personnel/' . $newFilename;

    // Already English and correct
    if ($newFilename === $oldFilename && $current === $newDbValue) {
        $skipped++;
        continue;
    }

    if (!file_exists($oldPath)) {
        $errors[] = "id=$id: file not found: $oldPath";
        continue;
    }

    if ($oldPath === $newPath) {
        $skipped++;
        continue;
    }

    if (file_exists($newPath) && realpath($oldPath) !== realpath($newPath)) {
        @unlink($newPath);
    }
    if (!@rename($oldPath, $newPath)) {
        $errors[] = "id=$id: rename failed: $oldFilename -> $newFilename";
        continue;
    }
    $stmt = $mysqli->prepare("UPDATE personnel SET image = ? WHERE id = ?");
    $stmt->bind_param('si', $newDbValue, $id);
    if (!$stmt->execute()) {
        $errors[] = "id=$id: DB update failed";
        $stmt->close();
        continue;
    }
    $stmt->close();
    $renamed++;
    echo "  id=$id: $oldFilename -> $newFilename\n";
}

$res->free();

echo "\n=== สรุป ===\n";
echo "เปลี่ยนชื่อแล้ว: $renamed\n";
echo "ข้าม (ชื่อถูกแล้ว): $skipped\n";
if (!empty($errors)) {
    echo "ข้อผิดพลาด:\n";
    foreach ($errors as $e) echo "  - $e\n";
}
echo "Done.\n";

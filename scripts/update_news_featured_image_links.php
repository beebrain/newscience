<?php

/**
 * อัปเดต link รูปหน้าปกข่าวกับฐานข้อมูล
 * - เปลี่ยนชื่อไฟล์ภาพหน้าปกเป็น รหัสข่าว.ext (เช่น 723.jpg)
 * - อัปเดตคอลัมน์ featured_image ในตาราง news ให้ตรงกับชื่อไฟล์ใหม่
 *
 * Usage: php scripts/update_news_featured_image_links.php
 */

$rootPath = realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR;
$writablePath = $rootPath . 'writable' . DIRECTORY_SEPARATOR;
$publicPath = $rootPath . 'public' . DIRECTORY_SEPARATOR;
$uploadDir = $writablePath . 'uploads' . DIRECTORY_SEPARATOR . 'news';
$publicDir = $publicPath . 'uploads' . DIRECTORY_SEPARATOR . 'news';
$allowedExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

$db = [
    'host' => 'localhost',
    'user' => 'root',
    'pass' => '',
    'db'   => 'newscience',
];

$conn = @new mysqli($db['host'], $db['user'], $db['pass'], $db['db']);
if ($conn->connect_error) {
    fwrite(STDERR, "DB connection failed: " . $conn->connect_error . "\n");
    exit(1);
}
$conn->set_charset('utf8mb4');

if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$res = $conn->query("SELECT id, featured_image FROM news WHERE featured_image IS NOT NULL AND featured_image != ''");
if (!$res) {
    fwrite(STDERR, "Query failed: " . $conn->error . "\n");
    exit(1);
}

$updated = 0;
$skipped = 0;
$errors = [];

while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $current = trim((string) $row['featured_image']);
    $currentFile = basename(str_replace('\\', '/', $current));
    $ext = strtolower(pathinfo($currentFile, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        $ext = 'jpg';
    }
    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }
    $newFileName = $id . '.' . $ext;

    if ($currentFile === $newFileName) {
        $skipped++;
        continue;
    }

    $sourcePath = null;
    if (is_file($uploadDir . $currentFile)) {
        $sourcePath = $uploadDir . $currentFile;
    } elseif (is_file($publicDir . $currentFile)) {
        $sourcePath = $publicDir . $currentFile;
    }

    if ($sourcePath === null) {
        // ไฟล์ไม่พบ — ยังอัปเดต DB เป็น id.ext เพื่อให้ link ถูกต้อง (รูปจะโหลดเมื่อมีการอัปโหลดใหม่)
        $escNew = $conn->real_escape_string($newFileName);
        $conn->query("UPDATE news SET featured_image = '{$escNew}' WHERE id = " . $id);
        if ($conn->affected_rows > 0) {
            $updated++;
            echo "  ID {$id}: {$currentFile} -> {$newFileName} (ไฟล์ไม่พบ, อัปเดต DB เท่านั้น)\n";
        }
        continue;
    }

    $destPath = $uploadDir . $sep . $newFileName;
    if ($sourcePath === $destPath) {
        $skipped++;
        continue;
    }

    $ok = @copy($sourcePath, $destPath);
    if (!$ok) {
        $errors[] = "News ID {$id}: copy ล้มเหลว -> {$newFileName}";
        continue;
    }

    $escNew = $conn->real_escape_string($newFileName);
    $conn->query("UPDATE news SET featured_image = '{$escNew}' WHERE id = " . $id);
    if ($conn->affected_rows >= 0) {
        $updated++;
        echo "  ID {$id}: {$currentFile} -> {$newFileName}\n";
    } else {
        $errors[] = "News ID {$id}: UPDATE ล้มเหลว";
    }
}

$res->free();
$conn->close();

echo "\n" . str_repeat('=', 50) . "\n";
echo "อัปเดต link รูปหน้าปกข่าว\n";
echo str_repeat('=', 50) . "\n";
echo "อัปเดตแล้ว: {$updated} รายการ\n";
echo "ข้าม (ชื่อถูกแล้ว): {$skipped} รายการ\n";
if (!empty($errors)) {
    echo "ข้อผิดพลาด:\n";
    foreach ($errors as $e) {
        echo "  - {$e}\n";
    }
}
echo "\nเสร็จสิ้น\n";

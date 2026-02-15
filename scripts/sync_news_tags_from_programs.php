<?php

/**
 * Sync news_tags from programs table:
 * - Ensures tag "งานวิจัย" (slug research) exists
 * - For each program: insert or update news_tag with slug program_{id}, name = name_th
 * Run: php scripts/sync_news_tags_from_programs.php
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

// Ensure news_tags table exists
$r = $conn->query("SHOW TABLES LIKE 'news_tags'");
if (!$r || $r->num_rows === 0) {
    fwrite(STDERR, "Table news_tags not found. Run database/add_news_tags.sql first.\n");
    exit(1);
}

// 1) Ensure "งานวิจัย" tag exists
$conn->query("INSERT IGNORE INTO news_tags (name, slug, sort_order) VALUES ('งานวิจัย', 'research', 4)");
if ($conn->affected_rows > 0) {
    echo "Added tag: งานวิจัย (research)\n";
}

// 2) Check programs table exists
$r = $conn->query("SHOW TABLES LIKE 'programs'");
if (!$r || $r->num_rows === 0) {
    echo "Table programs not found. Skipping program tags.\n";
    $conn->close();
    exit(0);
}

$res = $conn->query("SELECT id, name_th, sort_order FROM programs ORDER BY sort_order ASC, id ASC");
if (!$res) {
    fwrite(STDERR, "Error reading programs: " . $conn->error . "\n");
    $conn->close();
    exit(1);
}

$baseSort = 50; // program tags after general/student_activity/research_grant/research
$added = 0;
$updated = 0;

while ($row = $res->fetch_assoc()) {
    $id = (int) $row['id'];
    $name = $conn->real_escape_string(trim($row['name_th']));
    $sortOrder = $baseSort + (int) ($row['sort_order'] ?? 0);
    $slug = 'program_' . $id;

    // INSERT or ON DUPLICATE KEY UPDATE (slug is UNIQUE)
    $sql = "INSERT INTO news_tags (name, slug, sort_order) VALUES ('$name', '$slug', $sortOrder)
            ON DUPLICATE KEY UPDATE name = VALUES(name), sort_order = VALUES(sort_order)";
    if (!$conn->query($sql)) {
        fwrite(STDERR, "Error upserting tag program_$id: " . $conn->error . "\n");
        continue;
    }
    if ($conn->affected_rows === 1) {
        $added++;
        echo "Added tag: $name ($slug)\n";
    } elseif ($conn->affected_rows === 2) {
        $updated++;
        echo "Updated tag: $name ($slug)\n";
    }
}

echo "Done. Added: $added, Updated: $updated\n";
$conn->close();
exit(0);

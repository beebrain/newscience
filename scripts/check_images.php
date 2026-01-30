<?php
// Check news image links
$db = new mysqli('localhost', 'root', '', 'newscience');
$db->set_charset('utf8mb4');

echo "=== Checking News Image Links ===\n\n";

$result = $db->query("SELECT id, title, featured_image FROM news ORDER BY id DESC LIMIT 15");

while($row = $result->fetch_assoc()) {
    $imgPath = $row['featured_image'];
    $fullPath = __DIR__ . '/../public' . $imgPath;
    $exists = file_exists($fullPath) ? "OK" : "MISSING";
    
    echo "ID: " . $row['id'] . "\n";
    echo "   Image: " . $imgPath . "\n";
    echo "   Status: " . $exists . "\n\n";
}

$db->close();

<?php
// Fix news image paths - remove /public/ prefix 
// Path should be just "newsimages/xxx.jpg" so base_url() can work correctly
$db = new mysqli('localhost', 'root', '', 'newscience');
$db->set_charset('utf8mb4');

echo "=== Fixing News Image Paths ===\n\n";

// Update paths: /public/newsimages/... -> newsimages/...
$result = $db->query("
    UPDATE news 
    SET featured_image = REPLACE(featured_image, '/public/newsimages/', 'newsimages/') 
    WHERE featured_image LIKE '/public/newsimages/%'
");

$affected = $db->affected_rows;
echo "Updated $affected records\n\n";

// Verify
echo "=== Sample Updated Paths ===\n\n";
$result = $db->query("SELECT id, featured_image FROM news WHERE featured_image LIKE 'newsimages%' ORDER BY id DESC LIMIT 10");

while($row = $result->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | " . $row['featured_image'] . "\n";
}

$db->close();
echo "\nDone!\n";
echo "\nNote: Use base_url() in view to prepend the correct base URL\n";

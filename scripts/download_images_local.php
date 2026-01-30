<?php
/**
 * Download News Images from sci.uru.ac.th to Local
 * Saves images to /public/newsimages/ and updates database
 */

// Database configuration
$dbHost = 'localhost';
$dbUser = 'root';
$dbPass = '';
$dbName = 'newscience';

// Paths
$publicPath = __DIR__ . '/../public/newsimages';
$webPath = '/newsimages'; // Path relative to web root

// Connect to database
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
$mysqli->set_charset('utf8mb4');

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Create directory if not exists
if (!is_dir($publicPath)) {
    mkdir($publicPath, 0755, true);
}

echo "=======================================================\n";
echo "Download News Images to Local\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n";
echo "=======================================================\n\n";

// Function to download image
function downloadImage($url, $savePath) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    
    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($httpCode == 200 && $data) {
        // Determine extension from content type
        $ext = '.jpg';
        if (strpos($contentType, 'png') !== false) {
            $ext = '.png';
        } elseif (strpos($contentType, 'gif') !== false) {
            $ext = '.gif';
        } elseif (strpos($contentType, 'webp') !== false) {
            $ext = '.webp';
        }
        
        $fullPath = $savePath . $ext;
        file_put_contents($fullPath, $data);
        return $ext;
    }
    
    return false;
}

// Get all news with external image URLs
$result = $mysqli->query("
    SELECT id, featured_image 
    FROM news 
    WHERE featured_image LIKE '%sci.uru.ac.th%' 
    ORDER BY id
");

$total = $result->num_rows;
echo "Found $total news with external images\n\n";

$downloaded = 0;
$skipped = 0;
$errors = 0;
$count = 0;

while ($row = $result->fetch_assoc()) {
    $count++;
    $newsId = $row['id'];
    $imageUrl = $row['featured_image'];
    
    echo "[$count/$total] News ID $newsId: ";
    
    // Extract original news ID from URL (for naming)
    preg_match('/getimage\/(\d+)/', $imageUrl, $matches);
    $originalId = $matches[1] ?? $newsId;
    
    // Check if image already exists locally
    $localFileName = "news_{$originalId}";
    $existingFiles = glob("$publicPath/{$localFileName}.*");
    
    if (!empty($existingFiles)) {
        // Already downloaded, just update DB
        $existingFile = basename($existingFiles[0]);
        $localPath = "$webPath/$existingFile";
        
        // Update database
        $stmt = $mysqli->prepare("UPDATE news SET featured_image = ? WHERE id = ?");
        $stmt->bind_param('si', $localPath, $newsId);
        $stmt->execute();
        $stmt->close();
        
        echo "Already exists ($existingFile)\n";
        $skipped++;
        continue;
    }
    
    // Download image
    $savePath = "$publicPath/$localFileName";
    $ext = downloadImage($imageUrl, $savePath);
    
    if ($ext) {
        $localPath = "$webPath/{$localFileName}{$ext}";
        
        // Update database
        $stmt = $mysqli->prepare("UPDATE news SET featured_image = ? WHERE id = ?");
        $stmt->bind_param('si', $localPath, $newsId);
        $stmt->execute();
        $stmt->close();
        
        echo "Downloaded ({$localFileName}{$ext})\n";
        $downloaded++;
    } else {
        echo "Failed to download\n";
        $errors++;
    }
    
    usleep(100000); // 0.1 second delay
}

$result->free();
$mysqli->close();

echo "\n=======================================================\n";
echo "COMPLETE\n";
echo "=======================================================\n";
echo "Downloaded: $downloaded\n";
echo "Already existed: $skipped\n";
echo "Errors: $errors\n";
echo "Images saved to: $publicPath\n";
echo "Done at: " . date('Y-m-d H:i:s') . "\n";

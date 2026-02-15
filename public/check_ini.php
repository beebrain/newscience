<?php
echo "<h1>PHP Configuration Info</h1>";
echo "<p><strong>Loaded Configuration File (php.ini):</strong> " . php_ini_loaded_file() . "</p>";
echo "<p><strong>Scan this dir for additional .ini files:</strong> " . php_ini_scanned_files() . "</p>";
echo "<hr>";
echo "<h3>Upload Limits</h3>";
echo "<ul>";
echo "<li>upload_max_filesize: " . ini_get('upload_max_filesize') . "</li>";
echo "<li>post_max_size: " . ini_get('post_max_size') . "</li>";
echo "<li>memory_limit: " . ini_get('memory_limit') . "</li>";
echo "</ul>";

echo "<hr>";
echo "<h3>Directory Permissions Information</h3>";
$rootPath = dirname(__DIR__); // Go up one level from public
$dirsToCheck = [
    'writable',
    'writable/uploads',
    'writable/uploads/news',
    'public/uploads',
    'public/uploads/news'
];

echo "<ul>";
foreach ($dirsToCheck as $relPath) {
    $fullPath = $rootPath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
    echo "<li><strong>{$relPath}</strong>: ";
    if (is_dir($fullPath)) {
        echo "Exists - ";
        if (is_writable($fullPath)) {
             echo "<span style='color: green; font-weight: bold;'>Writable (OK)</span>";
        } else {
             echo "<span style='color: red; font-weight: bold;'>Not Writable (Permission Denied)</span> - Run: <code>chmod 777 {$relPath}</code>";
        }
    } else {
        echo "<span style='color: orange;'>Does not exist</span> - (Code might try to create it, but parent must be writable)";
    }
    echo "</li>";
}
echo "</ul>";

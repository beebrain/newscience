<?php
// Simple script to drop exam tables using mysqli directly

// Read .env file for database credentials
$envFile = file_get_contents('.env');
preg_match('/database.default.hostname\s*=\s*(.+)/', $envFile, $hostnameMatch);
preg_match('/database.default.database\s*=\s*(.+)/', $envFile, $databaseMatch);
preg_match('/database.default.username\s*=\s*(.+)/', $envFile, $usernameMatch);
preg_match('/database.default.password\s*=\s*(.+)/', $envFile, $passwordMatch);

$hostname = trim($hostnameMatch[1] ?? 'localhost');
$database = trim($databaseMatch[1] ?? 'newscience');
$username = trim($usernameMatch[1] ?? 'root');
$password = trim($passwordMatch[1] ?? '');

echo "Connecting to database: {$database}@{$hostname}\n";

$mysqli = new mysqli($hostname, $username, $password, $database);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error . "\n");
}

echo "Dropping exam tables...\n";

$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");
$mysqli->query("DROP TABLE IF EXISTS exam_schedule_user_links");
$mysqli->query("DROP TABLE IF EXISTS exam_publish_versions");
$mysqli->query("DROP TABLE IF EXISTS exam_schedules");
$mysqli->query("DROP TABLE IF EXISTS exam_import_batches");
$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Exam tables dropped successfully!\n";

$mysqli->close();

<?php
/**
 * Script to add program_id column to user table
 * Run: php scripts/add_user_program_id.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load CodeIgniter database
$db = \Config\Database::connect();

$sql = "ALTER TABLE `user`
ADD COLUMN IF NOT EXISTS `program_id` INT UNSIGNED DEFAULT NULL AFTER `role`,
ADD KEY `program_id` (`program_id`);";

try {
    $db->query($sql);
    echo "✅ Successfully added program_id column to user table\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

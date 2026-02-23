<?php

/**
 * Clean up test data and reset system
 */

$pdo = new PDO('mysql:host=localhost;dbname=newscience;charset=utf8mb4', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "=== Cleaning Up Test Data ===\n\n";

// Clean up test content blocks first (to avoid foreign key issues)
echo "1. Cleaning up test content blocks...\n";
$stmt = $pdo->prepare("DELETE FROM program_content_blocks WHERE title LIKE 'Test Block%' OR title LIKE 'Public Test%' OR title LIKE 'Reorder Test%'");
$deletedBlocks = $stmt->rowCount();
$stmt->execute();
echo "✓ Deleted $deletedBlocks test content blocks\n";

// Clean up test personnel_programs relationships
echo "\n2. Cleaning up test personnel_programs relationships...\n";
$stmt = $pdo->prepare("
    DELETE pp FROM personnel_programs pp 
    JOIN personnel p ON pp.personnel_id = p.id 
    WHERE p.email LIKE 'test_%@university.edu'
");
$deletedRelationships = $stmt->rowCount();
$stmt->execute();
echo "✓ Deleted $deletedRelationships test personnel_programs relationships\n";

// Clean up test personnel records
echo "\n3. Cleaning up test personnel records...\n";
$stmt = $pdo->prepare("DELETE FROM personnel WHERE email LIKE 'test_%@university.edu'");
$deletedPersonnel = $stmt->rowCount();
$stmt->execute();
echo "✓ Deleted $deletedPersonnel test personnel records\n";

// Clean up test users (now that personnel records are gone)
echo "\n4. Cleaning up test users...\n";
$stmt = $pdo->prepare("DELETE FROM `user` WHERE `email` LIKE 'test_%@university.edu'");
$deletedUsers = $stmt->rowCount();
$stmt->execute();
echo "✓ Deleted $deletedUsers test users\n";

// Reset any auto-increment counters if needed (optional)
echo "\n5. Resetting auto-increment counters...\n";
$pdo->exec("ALTER TABLE `user` AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE personnel AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE personnel_programs AUTO_INCREMENT = 1");
$pdo->exec("ALTER TABLE program_content_blocks AUTO_INCREMENT = 1");
echo "✓ Reset auto-increment counters\n";

// Verify cleanup
echo "\n=== Verification ===\n";

// Check remaining test users
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM `user` WHERE email LIKE 'test_%@university.edu'");
$stmt->execute();
$remainingUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Remaining test users: $remainingUsers\n";

// Check remaining test personnel
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM personnel WHERE email LIKE 'test_%@university.edu'");
$stmt->execute();
$remainingPersonnel = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Remaining test personnel: $remainingPersonnel\n";

// Check remaining test blocks
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM program_content_blocks WHERE title LIKE 'Test Block%' OR title LIKE 'Public Test%' OR title LIKE 'Reorder Test%'");
$stmt->execute();
$remainingBlocks = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Remaining test blocks: $remainingBlocks\n";

if ($remainingUsers == 0 && $remainingPersonnel == 0 && $remainingBlocks == 0) {
    echo "\n✅ All test data cleaned up successfully!\n";
} else {
    echo "\n⚠️ Some test data may remain. Please check manually.\n";
}

// Show current system state
echo "\n=== Current System State ===\n";

// Show regular users count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM `user` WHERE email NOT LIKE 'test_%@university.edu'");
$regularUsers = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Regular users: $regularUsers\n";

// Show programs count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM programs WHERE status = 'active'");
$activePrograms = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Active programs: $activePrograms\n";

// Show content blocks count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM program_content_blocks");
$totalBlocks = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Total content blocks: $totalBlocks\n";

// Show published blocks count
$stmt = $pdo->query("SELECT COUNT(*) as count FROM program_content_blocks WHERE is_published = 1 AND is_active = 1");
$publishedBlocks = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "Published + active blocks: $publishedBlocks\n";

echo "\n=== Cleanup Complete ===\n";
echo "System has been reset to its original state (minus any legitimate changes made during testing).\n";

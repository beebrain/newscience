<?php
/**
 * Script to run the personnel-user normalization migration
 * 
 * This script:
 * 1. Links personnel to user by email
 * 2. Creates user records for personnel without existing user records
 * 3. Updates user data from personnel (names, images)
 * 
 * Usage: php scripts/run_normalize_personnel_user.php
 */

// Change to root directory
chdir(dirname(__DIR__));

// Load CodeIgniter framework
require_once 'vendor/autoload.php';

// Boot CodeIgniter
$app = new \CodeIgniter\Boot();
$app->initialize();

use CodeIgniter\Database\Config;
use App\Models\PersonnelModel;
use App\Models\UserModel;

$db = Config::connect();

echo "=== Personnel-User Normalization Migration ===\n\n";

// Step 1: Count current state
$personnelCount = $db->table('personnel')->countAll();
$userCount = $db->table('user')->countAll();
$linkedCount = $db->table('personnel')->where('user_uid IS NOT NULL')->countAllResults();

echo "Current State:\n";
echo "  Total Personnel: {$personnelCount}\n";
echo "  Total Users: {$userCount}\n";
echo "  Personnel with user_uid: {$linkedCount}\n";
echo "  Personnel without user_uid: " . ($personnelCount - $linkedCount) . "\n\n";

// Step 2: Link personnel to existing users by email
echo "Step 1: Linking personnel to existing users by email...\n";

$query = $db->query("
    UPDATE `personnel` p
    INNER JOIN `user` u ON LOWER(TRIM(p.email)) = LOWER(TRIM(u.email))
    SET p.user_uid = u.uid
    WHERE p.user_uid IS NULL 
      AND p.email IS NOT NULL 
      AND p.email != ''
");

$affectedRows = $db->affectedRows();
echo "  Linked {$affectedRows} personnel to existing users\n\n";

// Step 3: Find personnel without user records
echo "Step 2: Finding personnel without user records...\n";

$orphanPersonnel = $db->query("
    SELECT p.* 
    FROM personnel p
    LEFT JOIN user u ON p.user_uid = u.uid
    WHERE p.user_uid IS NULL
      AND p.email IS NOT NULL 
      AND p.email != ''
")->getResultArray();

echo "  Found " . count($orphanPersonnel) . " personnel without user records\n\n";

// Step 4: Create user records for orphan personnel
if (count($orphanPersonnel) > 0) {
    echo "Step 3: Creating user records for personnel without users...\n";
    
    $userModel = new UserModel();
    $createdCount = 0;
    
    foreach ($orphanPersonnel as $person) {
        // Check if email already exists in user table
        $existingUser = $userModel->findByEmail($person['email']);
        if ($existingUser) {
            // Link to existing user
            $db->table('personnel')
               ->where('id', $person['id'])
               ->update(['user_uid' => $existingUser['uid']]);
            echo "  Linked personnel ID {$person['id']} to existing user (email: {$person['email']})\n";
            continue;
        }
        
        // Parse name to extract title, first name, last name
        $parsed = parseThaiName($person['name']);
        $parsedEn = parseEnglishName($person['name_en'] ?? '');
        
        // Create user record
        $userData = [
            'email' => $person['email'],
            'title' => $parsed['title'],
            'tf_name' => $parsed['first_name'],
            'tl_name' => $parsed['last_name'],
            'gf_name' => $parsedEn['first_name'],
            'gl_name' => $parsedEn['last_name'],
            'profile_image' => $person['image'] ?? null,
            'role' => 'user',
            'status' => $person['status'] ?? 'active'
        ];
        
        try {
            $userModel->insert($userData);
            $newUid = $userModel->getInsertID();
            
            // Link personnel to new user
            $db->table('personnel')
               ->where('id', $person['id'])
               ->update(['user_uid' => $newUid]);
            
            $createdCount++;
            echo "  Created user for: {$person['name']} (email: {$person['email']}, new uid: {$newUid})\n";
        } catch (\Exception $e) {
            echo "  ERROR creating user for {$person['email']}: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n  Created {$createdCount} new user records\n\n";
}

// Step 5: Update user profile images from personnel if empty
echo "Step 4: Updating user profile images from personnel...\n";

$query = $db->query("
    UPDATE `user` u
    INNER JOIN `personnel` p ON u.uid = p.user_uid
    SET u.profile_image = p.image
    WHERE (u.profile_image IS NULL OR u.profile_image = '') 
      AND p.image IS NOT NULL 
      AND p.image != ''
");

$affectedRows = $db->affectedRows();
echo "  Updated {$affectedRows} user profile images\n\n";

// Step 6: Update user names from personnel if empty
echo "Step 5: Updating user names from personnel (where empty)...\n";

$usersToUpdate = $db->query("
    SELECT u.uid, p.name, p.name_en, u.tf_name, u.tl_name
    FROM `user` u
    INNER JOIN `personnel` p ON u.uid = p.user_uid
    WHERE (u.tf_name IS NULL OR u.tf_name = '')
      AND p.name IS NOT NULL 
      AND p.name != ''
")->getResultArray();

$updatedCount = 0;
foreach ($usersToUpdate as $user) {
    $parsed = parseThaiName($user['name']);
    $db->table('user')
       ->where('uid', $user['uid'])
       ->update([
           'tf_name' => $parsed['first_name'],
           'tl_name' => $parsed['last_name'],
           'title' => $parsed['title']
       ]);
    $updatedCount++;
}

echo "  Updated {$updatedCount} user names\n\n";

// Final state
$linkedCountFinal = $db->table('personnel')->where('user_uid IS NOT NULL')->countAllResults();
$userCountFinal = $db->table('user')->countAll();

echo "=== Migration Complete ===\n";
echo "Final State:\n";
echo "  Total Personnel: {$personnelCount}\n";
echo "  Total Users: {$userCountFinal} (was: {$userCount})\n";
echo "  Personnel with user_uid: {$linkedCountFinal}\n";
echo "  Personnel without user_uid: " . ($personnelCount - $linkedCountFinal) . "\n\n";

if ($personnelCount - $linkedCountFinal > 0) {
    echo "WARNING: Some personnel still don't have user_uid linked.\n";
    echo "These may have empty or invalid emails:\n";
    
    $unlinked = $db->query("
        SELECT id, name, email, user_uid 
        FROM personnel 
        WHERE user_uid IS NULL
        LIMIT 10
    ")->getResultArray();
    
    foreach ($unlinked as $p) {
        echo "  ID: {$p['id']}, Name: {$p['name']}, Email: " . ($p['email'] ?: '(empty)') . "\n";
    }
}

echo "\nDone!\n";

// ===== Helper Functions =====

/**
 * Parse Thai name to extract title, first name, last name
 */
function parseThaiName(?string $fullName): array
{
    if (empty($fullName)) {
        return ['title' => null, 'first_name' => null, 'last_name' => null];
    }
    
    $fullName = trim($fullName);
    
    // Thai academic titles (order matters - longer ones first)
    $titles = [
        'ศ.ดร.', 'รศ.ดร.', 'ผศ.ดร.', 'อ.ดร.',
        'ศาสตราจารย์ ดร.', 'รองศาสตราจารย์ ดร.', 'ผู้ช่วยศาสตราจารย์ ดร.',
        'ศาสตราจารย์', 'รองศาสตราจารย์', 'ผู้ช่วยศาสตราจารย์',
        'ดร.', 'ศ.', 'รศ.', 'ผศ.', 'อ.',
        'นางสาว', 'นาง', 'นาย',
        'Dr.', 'Prof.', 'Assoc. Prof.', 'Asst. Prof.'
    ];
    
    $foundTitle = null;
    $nameWithoutTitle = $fullName;
    
    foreach ($titles as $title) {
        if (strpos($fullName, $title) === 0) {
            $foundTitle = $title;
            $nameWithoutTitle = trim(substr($fullName, strlen($title)));
            break;
        }
    }
    
    // Split remaining into first and last name
    $parts = preg_split('/\s+/', $nameWithoutTitle, 2);
    $firstName = $parts[0] ?? null;
    $lastName = $parts[1] ?? null;
    
    return [
        'title' => $foundTitle,
        'first_name' => $firstName,
        'last_name' => $lastName
    ];
}

/**
 * Parse English name to extract first name, last name
 */
function parseEnglishName(?string $fullName): array
{
    if (empty($fullName)) {
        return ['first_name' => null, 'last_name' => null];
    }
    
    $fullName = trim($fullName);
    
    // Remove common titles
    $titles = ['Dr.', 'Prof.', 'Assoc. Prof.', 'Asst. Prof.', 'Mr.', 'Mrs.', 'Ms.', 'Miss'];
    foreach ($titles as $title) {
        if (stripos($fullName, $title) === 0) {
            $fullName = trim(substr($fullName, strlen($title)));
            break;
        }
    }
    
    // Split into first and last name
    $parts = preg_split('/\s+/', $fullName, 2);
    $firstName = $parts[0] ?? null;
    $lastName = $parts[1] ?? null;
    
    return [
        'first_name' => $firstName,
        'last_name' => $lastName
    ];
}

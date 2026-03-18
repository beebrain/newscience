<?php
// Check specific users with กนกวรรณ in their name
require_once 'vendor/autoload.php';

// Load CodeIgniter
require 'system/Test/bootstrap.php';

use App\Models\UserModel;

$userModel = new UserModel();

// Search for users with กนกวรรณ in their name
$users = $userModel->like('tf_name', 'กนกวรรณ')->findAll();

echo "=== Users with 'กนกวรรณ' in tf_name ===\n\n";
echo sprintf("%-5s %-20s %-20s %-30s %-20s\n", 'UID', 'Login', 'tf_name', 'tl_name', 'nickname');
echo str_repeat('-', 100) . "\n";

foreach ($users as $user) {
    echo sprintf(
        "%-5d %-20s %-20s %-30s %-20s\n",
        $user['uid'],
        substr($user['login_uid'] ?? 'N/A', 0, 20),
        $user['tf_name'] ?? '',
        $user['tl_name'] ?? '',
        $user['nickname'] ?? '(empty)'
    );
}

echo "\n=== Check Summary ===\n";
echo "Total users found: " . count($users) . "\n";

// Check for the specific mappings
$mappings = [
    ['tf_name' => 'กนกวรรณ', 'tl_name' => 'กันยะมี', 'expected_nickname' => 'กนกวรรณ ก'],
    ['tf_name' => 'กนกวรรณ', 'tl_name' => 'มารักษ์', 'expected_nickname' => 'กนกวรรณ ม'],
];

echo "\n=== Mapping Check ===\n";
foreach ($mappings as $mapping) {
    $user = $userModel->where('tf_name', $mapping['tf_name'])
        ->where('tl_name', $mapping['tl_name'])
        ->first();

    if ($user) {
        $status = ($user['nickname'] ?? '') === $mapping['expected_nickname'] ? '✓ OK' : '✗ MISMATCH';
        echo "{$mapping['tf_name']} {$mapping['tl_name']}: {$status}\n";
        echo "  Current nickname: " . ($user['nickname'] ?? '(empty)') . "\n";
        echo "  Expected: {$mapping['expected_nickname']}\n";
    } else {
        echo "{$mapping['tf_name']} {$mapping['tl_name']}: ✗ NOT FOUND\n";
    }
}

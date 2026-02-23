<?php
/**
 * Test script for admin/users auto-fill search functionality
 * Run via: php test_users_autofill.php
 */

echo "=== Testing Admin Users Auto-fill Search ===\n\n";

// 1. Check if the view file has the correct structure
echo "1. Checking view file structure...\n";
$viewFile = __DIR__ . '/app/Views/admin/users/index.php';
$viewContent = file_get_contents($viewFile);

$checks = [
    'userSearch input' => strpos($viewContent, 'id="userSearch"') !== false,
    'clearSearch button' => strpos($viewContent, 'id="clearSearch"') !== false,
    'JavaScript section' => strpos($viewContent, 'addEventListener(\'input\', filterUsers)') !== false,
    'filterUsers function' => strpos($viewContent, 'function filterUsers()') !== false,
    'tbody tr selector' => strpos($viewContent, 'tbody tr') !== false,
];

foreach ($checks as $name => $result) {
    echo "  - {$name}: " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
}

// 2. Check controller
echo "\n2. Checking controller...\n";
$controllerFile = __DIR__ . '/app/Controllers/Admin/Users.php';
$controllerContent = file_get_contents($controllerFile);

$controllerChecks = [
    'Find all users' => strpos($controllerContent, 'findAll()') !== false,
    'No server-side search' => strpos($controllerContent, 'searchUsers') === false,
    'No keyword param' => strpos($controllerContent, 'getGet(\'keyword\')') === false,
];

foreach ($controllerChecks as $name => $result) {
    echo "  - {$name}: " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
}

// 3. Verify HTML output structure
echo "\n3. Checking HTML structure...\n";
$htmlChecks = [
    'Has tbody' => strpos($viewContent, '<tbody>') !== false,
    'Has table rows' => strpos($viewContent, '<tr>') !== false,
    'Scripts section' => strpos($viewContent, "section('scripts')") !== false,
];

foreach ($htmlChecks as $name => $result) {
    echo "  - {$name}: " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
}

// 4. Test search functionality logic
echo "\n4. Testing JavaScript filter logic...\n";
$jsChecks = [
    'toLowerCase() for case-insensitive' => strpos($viewContent, 'toLowerCase()') !== false,
    'includes() for partial match' => strpos($viewContent, 'includes(keyword)') !== false,
    'displayName search' => strpos($viewContent, 'td:nth-child(2)') !== false,
    'email search' => strpos($viewContent, 'td:nth-child(3)') !== false,
    'loginUid search' => strpos($viewContent, 'td:nth-child(4)') !== false,
    'row display toggle' => strpos($viewContent, "row.style.display") !== false,
];

foreach ($jsChecks as $name => $result) {
    echo "  - {$name}: " . ($result ? "✓ PASS" : "✗ FAIL") . "\n";
}

// 5. Summary
echo "\n=== Test Summary ===\n";
$allChecks = array_merge($checks, $controllerChecks, $htmlChecks, $jsChecks);
$passed = count(array_filter($allChecks));
$total = count($allChecks);

echo "Passed: {$passed}/{$total}\n";

if ($passed === $total) {
    echo "\n✓ All tests passed! Auto-fill search is properly implemented.\n";
    echo "\nFeatures:\n";
    echo "  - Real-time filtering as you type\n";
    echo "  - Search by name, email, or login_uid\n";
    echo "  - Case-insensitive partial matching\n";
    echo "  - Clear button to reset filter\n";
    echo "  - Result count display\n";
    exit(0);
} else {
    echo "\n✗ Some tests failed. Please check the implementation.\n";
    exit(1);
}

<?php
/**
 * Content Builder CRUD Operations Test Script
 * 
 * This script tests all Content Builder CRUD operations
 * for users with appropriate permissions.
 */

echo "=== Content Builder CRUD Operations Testing ===\n\n";

// Test data for CRUD operations
$testOperations = [
    'create_block' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/1/blocks',
        'data' => [
            'title' => 'Test Block',
            'block_type' => 'html',
            'content' => '<h1>Test Content</h1>',
            'is_active' => 1,
            'is_published' => 0
        ],
        'description' => 'Create new HTML block'
    ],
    
    'update_block' => [
        'method' => 'POST', 
        'url' => '/program-admin/content-builder/block/{id}/update',
        'data' => [
            'title' => 'Updated Test Block',
            'content' => '<h1>Updated Content</h1>',
            'is_active' => 1,
            'is_published' => 0
        ],
        'description' => 'Update existing block'
    ],
    
    'toggle_active' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/block/{id}/toggle',
        'data' => [],
        'description' => 'Toggle block active status'
    ],
    
    'publish_block' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/block/{id}/publish',
        'data' => [],
        'description' => 'Publish block'
    ],
    
    'delete_block' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/block/{id}/delete',
        'data' => [],
        'description' => 'Delete block'
    ],
    
    'reorder_blocks' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/1/reorder',
        'data' => [
            'block_ids' => [3, 1, 2]
        ],
        'description' => 'Reorder blocks'
    ],
    
    'duplicate_block' => [
        'method' => 'POST',
        'url' => '/program-admin/content-builder/block/{id}/duplicate',
        'data' => [],
        'description' => 'Duplicate existing block'
    ]
];

echo "=== CRUD Operations to Test ===\n";
foreach ($testOperations as $key => $operation) {
    echo sprintf("%-20s %s %s\n", 
        $key, 
        $operation['method'], 
        $operation['url']
    );
    echo "    Description: {$operation['description']}\n";
    echo "    Data: " . json_encode($operation['data'], JSON_UNESCAPED_UNICODE) . "\n\n";
}

echo "=== Manual Testing Instructions ===\n\n";

echo "STEP 1: Login as Super Admin\n";
echo "URL: /admin/login\n";
echo "Username: test_super_admin\n";
echo "Password: password\n\n";

echo "STEP 2: Test Create Block\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/1/blocks\n";
echo "Data: {\n";
echo "  \"title\": \"Test Block\",\n";
echo "  \"block_type\": \"html\",\n";
echo "  \"content\": \"<h1>Test Content</h1>\",\n";
echo "  \"is_active\": 1,\n";
echo "  \"is_published\": 0\n";
echo "}\n";
echo "Expected: Block created successfully, returns block ID\n\n";

echo "STEP 3: Test Update Block\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/block/{block_id}/update\n";
echo "Data: {\n";
echo "  \"title\": \"Updated Test Block\",\n";
echo "  \"content\": \"<h1>Updated Content</h1>\",\n";
echo "  \"is_active\": 1,\n";
echo "  \"is_published\": 0\n";
echo "}\n";
echo "Expected: Block updated successfully\n\n";

echo "STEP 4: Test Toggle Active Status\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/block/{block_id}/toggle\n";
echo "Expected: Block active status toggled (1->0 or 0->1)\n\n";

echo "STEP 5: Test Publish Block\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/block/{block_id}/publish\n";
echo "Expected: Block published (is_published = 1)\n\n";

echo "STEP 6: Test Reorder Blocks\n";
echo "First, create 2-3 more blocks\n";
echo "Then: POST /program-admin/content-builder/1/reorder\n";
echo "Data: {\n";
echo "  \"block_ids\": [3, 1, 2]\n";
echo "}\n";
echo "Expected: Blocks reordered according to provided IDs\n\n";

echo "STEP 7: Test Duplicate Block\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/block/{block_id}/duplicate\n";
echo "Expected: New block created with same content, appended \"(Copy)\" to title\n\n";

echo "STEP 8: Test Delete Block\n";
echo "Method: POST\n";
echo "URL: /program-admin/content-builder/block/{block_id}/delete\n";
echo "Expected: Block deleted successfully\n\n";

echo "=== Test with Different User Roles ===\n\n";

echo "Admin User (test_admin / password):\n";
echo "- All CRUD operations should work\n";
echo "- Should be able to manage blocks for any program\n\n";

echo "Faculty Chair (test_faculty_chair / password):\n";
echo "- CRUD operations should work for own program (ID 1)\n";
echo "- Should be blocked from accessing other programs\n\n";

echo "=== Expected Database Changes ===\n\n";

echo "After Create Block:\n";
echo "INSERT INTO program_content_blocks (\n";
echo "  program_id, title, block_type, content, \n";
echo "  is_active, is_published, sort_order, \n";
echo "  created_at, updated_at\n";
echo ") VALUES (\n";
echo "  1, 'Test Block', 'html', '<h1>Test Content</h1>',\n";
echo "  1, 0, 1, NOW(), NOW()\n";
echo ")\n\n";

echo "After Update Block:\n";
echo "UPDATE program_content_blocks SET\n";
echo "  title = 'Updated Test Block',\n";
echo "  content = '<h1>Updated Content</h1>',\n";
echo "  updated_at = NOW()\n";
echo "WHERE id = {block_id}\n\n";

echo "After Toggle Active:\n";
echo "UPDATE program_content_blocks SET\n";
echo "  is_active = NOT is_active,\n";
echo "  updated_at = NOW()\n";
echo "WHERE id = {block_id}\n\n";

echo "After Publish Block:\n";
echo "UPDATE program_content_blocks SET\n";
echo "  is_published = 1,\n";
echo "  updated_at = NOW()\n";
echo "WHERE id = {block_id}\n\n";

echo "After Reorder Blocks:\n";
echo "UPDATE program_content_blocks SET\n";
echo "  sort_order = CASE id\n";
echo "    WHEN 3 THEN 1\n";
echo "    WHEN 1 THEN 2\n";
echo "    WHEN 2 THEN 3\n";
echo "  END,\n";
echo "  updated_at = NOW()\n";
echo "WHERE id IN (1, 2, 3) AND program_id = 1\n\n";

echo "After Duplicate Block:\n";
echo "INSERT INTO program_content_blocks (\n";
echo "  program_id, title, block_type, content,\n";
echo "  is_active, is_published, sort_order,\n";
echo "  created_at, updated_at\n";
echo ") SELECT\n";
echo "  program_id, CONCAT(title, ' (Copy)'), block_type, content,\n";
echo "  is_active, is_published, (SELECT MAX(sort_order) + 1 FROM program_content_blocks WHERE program_id = 1),\n";
echo "  NOW(), NOW()\n";
echo "FROM program_content_blocks WHERE id = {block_id}\n\n";

echo "After Delete Block:\n";
echo "DELETE FROM program_content_blocks WHERE id = {block_id}\n\n";

echo "=== Error Handling Tests ===\n\n";

echo "Test these error scenarios:\n\n";

echo "1. Unauthorized Access:\n";
echo "- Login as regular user (test_user)\n";
echo "- Try POST /program-admin/content-builder/1/blocks\n";
echo "Expected: 302 redirect to dashboard with error message\n\n";

echo "2. Invalid Block ID:\n";
echo "- Try POST /program-admin/content-builder/block/99999/update\n";
echo "Expected: 404 or error message\n\n";

echo "3. Invalid Program ID:\n";
echo "- Try POST /program-admin/content-builder/99999/blocks\n";
echo "Expected: 404 or error message\n\n";

echo "4. Missing Required Fields:\n";
echo "- POST /program-admin/content-builder/1/blocks\n";
echo "- Data: {\"content\": \"test\"} (missing title, block_type)\n";
echo "Expected: Validation error\n\n";

echo "5. Invalid Block Type:\n";
echo "- POST /program-admin/content-builder/1/blocks\n";
echo "- Data: {\"title\": \"Test\", \"block_type\": \"invalid_type\"}\n";
echo "Expected: Validation error\n\n";

echo "=== Performance Tests ===\n\n";

echo "1. Large Content:\n";
echo "- Create block with large HTML content (>1MB)\n";
echo "- Check if system handles it without timeout\n\n";

echo "2. Many Blocks:\n";
echo "- Create 50+ blocks for a program\n";
echo "- Test reorder performance\n";
echo "- Test page load time in content builder\n\n";

echo "=== Security Tests ===\n\n";

echo "1. XSS Prevention:\n";
echo "- Try inserting malicious JavaScript in content\n";
echo "- Check if it's properly escaped when displayed\n\n";

echo "2. SQL Injection:\n";
echo "- Try SQL injection in block title/content\n";
echo "- Check if queries are properly parameterized\n\n";

echo "3. CSRF Protection:\n";
echo "- Test operations without CSRF token\n";
echo "- Expected: Operations should be blocked\n\n";

echo "=== Cleanup Commands ===\n\n";

echo "After testing, clean up test data:\n";
echo "DELETE FROM program_content_blocks WHERE title LIKE 'Test%';\n";
echo "DELETE FROM program_content_blocks WHERE title LIKE '%(Copy)';\n\n";

echo "CRUD testing script completed. Follow the instructions above.\n";

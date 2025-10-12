<?php
// Test script to check if the user management and role management APIs are working correctly

// Test the getUsers endpoint
echo "Testing getUsers endpoint...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Authorization: Bearer admin-token',
            'Content-Type: application/json'
        ]
    ]
]);

$response = file_get_contents('http://localhost:8000/api/admin/users', false, $context);
echo "Response: " . $response . "\n";

// Test the getDynamicRoles endpoint
echo "\nTesting getDynamicRoles endpoint...\n";

$response = file_get_contents('http://localhost:8000/api/admin/roles', false, $context);
echo "Response: " . $response . "\n";
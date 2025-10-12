<?php

// Simple test script to verify escalation management API endpoints

echo "Testing Escalation Management API Endpoints\n";
echo "==========================================\n\n";

// Test 1: Get escalation categories
echo "Test 1: Get escalation categories\n";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'Authorization: Bearer admin-token',
            'Content-Type: application/json'
        ]
    ]
]);

$response = file_get_contents('http://localhost:8000/admin/escalation-categories', false, $context);
echo "Response: " . $response . "\n\n";

// Test 2: Get escalation statuses
echo "Test 2: Get escalation statuses\n";
$response = file_get_contents('http://localhost:8000/admin/escalation-statuses', false, $context);
echo "Response: " . $response . "\n\n";

// Test 3: Get all escalations
echo "Test 3: Get all escalations\n";
$response = file_get_contents('http://localhost:8000/admin/escalations', false, $context);
echo "Response: " . $response . "\n\n";

echo "API tests completed.\n";
<?php
// Test direct user service call
echo "Testing user service directly...\n";

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'timeout' => 10,
        'ignore_errors' => true
    ]
]);

$response = file_get_contents('http://localhost:8001/api/users', false, $context);
$headers = $http_response_header ?? [];

echo "Response: " . $response . "\n";
echo "Headers: " . print_r($headers, true) . "\n";

// Test API gateway
echo "\nTesting API gateway...\n";
$response2 = file_get_contents('http://localhost:8000/api/users', false, $context);
$headers2 = $http_response_header ?? [];

echo "Response: " . $response2 . "\n";
echo "Headers: " . print_r($headers2, true) . "\n";
?>
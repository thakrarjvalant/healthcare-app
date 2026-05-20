<?php
// Test login API
$data = json_encode([
    'email' => 'admin@example.com',
    'password' => 'password123'
]);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data,
        'timeout' => 10
    ]
]);

$response = file_get_contents('http://localhost:8001/api/login', false, $context);
$headers = $http_response_header ?? [];

echo "Response: " . $response . "\n";
echo "Headers: " . print_r($headers, true) . "\n";
?>
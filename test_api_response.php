<?php
// Test API responses
$urls = [
    'http://localhost:8000/health',
    'http://localhost:8001/api/users',
    'http://localhost:8000/api/users'
];

foreach ($urls as $url) {
    echo "Testing: $url\n";
    $response = file_get_contents($url);
    $http_response_header_array = $http_response_header ?? [];
    
    echo "Response: " . $response . "\n";
    echo "Headers: " . print_r($http_response_header_array, true) . "\n";
    echo "-------------------\n";
}
?>
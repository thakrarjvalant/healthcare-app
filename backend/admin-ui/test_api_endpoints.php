<?php
// Test the getUserRoles and getRolePermissions API endpoints

// Test Receptionist user (ID 3)
echo "=== Testing Receptionist User (ID 3) ===\n";
$url = 'http://localhost:8007/admin/users/3/roles';
$token = 'admin-token';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response:\n";
echo $response . "\n";

// Test Medical Coordinator user (ID 30)
echo "\n=== Testing Medical Coordinator User (ID 30) ===\n";
$url = 'http://localhost:8007/admin/users/30/roles';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: " . $httpCode . "\n";
echo "Response:\n";
echo $response . "\n";
?>
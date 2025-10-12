<?php

// Test script to verify the new role feature access API endpoint

echo "Testing Role Feature Access API Endpoint\n";
echo "=====================================\n\n";

// Get super admin role ID from database
$pdo = new PDO('mysql:host=localhost;dbname=healthcare_db', 'healthcare_user', 'your_strong_password');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("SELECT id FROM dynamic_roles WHERE name = 'super_admin'");
$stmt->execute();
$superAdminRoleId = $stmt->fetchColumn();

if (!$superAdminRoleId) {
    echo "❌ Super admin role not found\n";
    exit(1);
}

echo "Super admin role ID: {$superAdminRoleId}\n";

// Test the API endpoint using cURL
$apiUrl = "http://localhost:8007/admin/roles/{$superAdminRoleId}/feature-access";
echo "API endpoint: {$apiUrl}\n";

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer admin-token',
    'Content-Type: application/json'
]);

// Execute the request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status Code: {$httpCode}\n";

if ($response) {
    $responseData = json_decode($response, true);
    echo "Response:\n";
    echo json_encode($responseData, JSON_PRETTY_PRINT);
    
    if ($httpCode === 200 && isset($responseData['data']['feature_access'])) {
        echo "\n✅ API endpoint test completed successfully!\n";
        echo "Feature access count: " . count($responseData['data']['feature_access']) . "\n";
        
        foreach ($responseData['data']['feature_access'] as $access) {
            echo "  - {$access['name']}: {$access['access_level']}\n";
        }
    } else {
        echo "\n❌ API endpoint test failed!\n";
        if (isset($responseData['message'])) {
            echo "Error message: {$responseData['message']}\n";
        }
    }
} else {
    echo "\n❌ Failed to get response from API endpoint\n";
}
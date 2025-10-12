<?php
// Test Medical Coordinator login

echo "=== Testing Medical Coordinator Login ===\n\n";

// Test login with Medical Coordinator credentials
$loginData = [
    'email' => 'medical.coordinator@example.com',
    'password' => 'password123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8001/login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✅ Login successful\n";
    $data = json_decode($response, true);
    if (isset($data['data']['user'])) {
        echo "User ID: {$data['data']['user']['id']}\n";
        echo "User Name: {$data['data']['user']['name']}\n";
        echo "User Role: {$data['data']['user']['role']}\n";
    }
    if (isset($data['data']['token'])) {
        echo "Token: {$data['data']['token']}\n";
    }
} else {
    echo "❌ Login failed - " . $response . "\n";
}
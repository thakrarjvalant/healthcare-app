<?php
// Detailed test for assignment endpoint

echo "=== Detailed Assignment Test ===\n\n";

// Test assignment with valid data
echo "1. Testing POST /medical-coordinator/assignments with valid data\n";
$assignmentData = [
    'patient_id' => 1,  // Patient ID 1 exists
    'doctor_id' => 2,   // Doctor ID 2 exists
    'notes' => 'Assigned by Medical Coordinator for testing'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8007/medical-coordinator/assignments");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($assignmentData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer admin-token",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

echo "HTTP Code: $httpCode\n";
echo "Content Type: $contentType\n";
echo "Response: $response\n";

if ($response === false) {
    echo "cURL Error: " . curl_error($ch) . "\n";
}

curl_close($ch);
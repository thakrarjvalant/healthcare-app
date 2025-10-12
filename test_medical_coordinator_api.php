<?php
// Test Medical Coordinator API endpoints

echo "=== Testing Medical Coordinator API Endpoints ===\n\n";

// Test 1: Get patients for assignment
echo "1. Testing GET /medical-coordinator/patients\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8007/medical-coordinator/patients");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer admin-token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✅ Success - Patients retrieved\n";
    $data = json_decode($response, true);
    if (isset($data['data']['patients'])) {
        echo "Found " . count($data['data']['patients']) . " patients\n";
        foreach ($data['data']['patients'] as $patient) {
            echo "  - {$patient['name']} (ID: {$patient['id']})";
            if (!empty($patient['assigned_doctor'])) {
                echo " - Assigned to Doctor ID: {$patient['assigned_doctor']}";
            }
            echo "\n";
        }
    }
} else {
    echo "❌ Failed - " . $response . "\n";
}
echo "\n";

// Test 2: Get doctors for assignment
echo "2. Testing GET /medical-coordinator/doctors\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8007/medical-coordinator/doctors");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer admin-token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✅ Success - Doctors retrieved\n";
    $data = json_decode($response, true);
    if (isset($data['data']['doctors'])) {
        echo "Found " . count($data['data']['doctors']) . " doctors\n";
        foreach ($data['data']['doctors'] as $doctor) {
            echo "  - {$doctor['name']} (ID: {$doctor['id']})\n";
        }
    }
} else {
    echo "❌ Failed - " . $response . "\n";
}
echo "\n";

// Test 3: Assign a patient to a doctor
echo "3. Testing POST /medical-coordinator/assignments\n";
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
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 201) {
    echo "✅ Success - Patient assigned to doctor\n";
    $data = json_decode($response, true);
    if (isset($data['data']['assignment_id'])) {
        echo "Assignment ID: {$data['data']['assignment_id']}\n";
        echo "Message: {$data['data']['message']}\n";
    }
} else {
    echo "❌ Failed - " . $response . "\n";
}
echo "\n";

// Test 4: Get patient assignment history
echo "4. Testing GET /medical-coordinator/patients/2/assignments\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8007/medical-coordinator/patients/1/assignments");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer admin-token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✅ Success - Patient assignment history retrieved\n";
    $data = json_decode($response, true);
    if (isset($data['data']['history'])) {
        echo "Found " . count($data['data']['history']) . " assignment records\n";
    }
} else {
    echo "❌ Failed - " . $response . "\n";
}
echo "\n";

// Test 5: Get patient limited history
echo "5. Testing GET /medical-coordinator/patients/2/history\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost:8007/medical-coordinator/patients/1/history");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer admin-token",
    "Content-Type: application/json"
]);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if ($httpCode === 200) {
    echo "✅ Success - Patient limited history retrieved\n";
    $data = json_decode($response, true);
    if (isset($data['data']['patient'])) {
        echo "Patient: {$data['data']['patient']['name']}\n";
    }
    if (isset($data['data']['recent_appointments'])) {
        echo "Recent appointments: " . count($data['data']['recent_appointments']) . "\n";
    }
    if (isset($data['data']['recent_prescriptions'])) {
        echo "Recent prescriptions: " . count($data['data']['recent_prescriptions']) . "\n";
    }
} else {
    echo "❌ Failed - " . $response . "\n";
}
echo "\n";

echo "=== Testing Complete ===\n";
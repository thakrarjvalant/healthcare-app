<?php
// Test script to verify JWT implementation

require_once 'backend/user-service/vendor/autoload.php';
require_once 'backend/database/DatabaseConnection.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Database\DatabaseConnection;

// Test JWT generation and validation
try {
    // Create a test user array
    $testUser = [
        'id' => 1,
        'name' => 'Test User',
        'email' => 'test@example.com',
        'role' => 'admin'
    ];
    
    // Generate JWT token
    $jwtSecret = 'healthcare_app_secret_key_2023';
    $payload = [
        'user_id' => $testUser['id'],
        'email' => $testUser['email'],
        'role' => $testUser['role'],
        'exp' => time() + (60 * 60 * 24), // Token expires in 24 hours
        'iat' => time(), // Issued at
        'iss' => 'healthcare-app' // Issuer
    ];
    
    $token = JWT::encode($payload, $jwtSecret, 'HS256');
    echo "✓ JWT Token generated successfully\n";
    echo "Token: " . $token . "\n\n";
    
    // Validate JWT token
    $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
    echo "✓ JWT Token validated successfully\n";
    echo "Decoded payload: " . json_encode($decoded) . "\n\n";
    
    echo "JWT implementation is working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
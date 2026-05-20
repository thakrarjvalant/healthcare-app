<?php

// Test script to simulate frontend feature access checking

// Include necessary files
require_once __DIR__ . '/backend/database/DatabaseConnection.php';
require_once __DIR__ . '/backend/shared/rbac/DynamicRBACManager.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

echo "Testing Frontend Feature Access Simulation\n";
echo "========================================\n\n";

// Get database connection
$db = DatabaseConnection::getInstance();
$rbacManager = new DynamicRBACManager();

// Test users with their roles
$testUsers = [
    ['name' => 'Super Admin', 'email' => 'super.admin@example.com', 'role' => 'super_admin'],
    ['name' => 'Admin User', 'email' => 'admin@example.com', 'role' => 'admin'],
    ['name' => 'Dr. Jane Smith', 'email' => 'jane.smith@example.com', 'role' => 'doctor'],
    ['name' => 'Receptionist Bob', 'email' => 'bob.receptionist@example.com', 'role' => 'receptionist'],
    ['name' => 'John Doe', 'email' => 'john.doe@example.com', 'role' => 'patient'],
    ['name' => 'Medical Coordinator', 'email' => 'medical.coordinator@example.com', 'role' => 'medical_coordinator']
];

// Get user IDs and test feature access
foreach ($testUsers as $testUser) {
    // Get user ID
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$testUser['email']]);
    $userId = $stmt->fetchColumn();
    
    if (!$userId) {
        echo "❌ User not found: {$testUser['name']} ({$testUser['email']})\n\n";
        continue;
    }
    
    echo "User: {$testUser['name']} ({$testUser['email']})\n";
    echo "Role: {$testUser['role']}\n";
    echo str_repeat("-", 50) . "\n";
    
    // Test specific feature access using the RBAC manager
    $featuresToTest = [
        'user_management',
        'appointment_management',
        'patient_management',
        'clinical_management',
        'billing_payments',
        'front_desk',
        'system_admin',
        'role_management',
        'audit_compliance',
        'reports_analytics'
    ];
    
    foreach ($featuresToTest as $feature) {
        $canAccess = $rbacManager->canAccessFeature($userId, $feature, 'read');
        $accessSymbol = $canAccess ? '✅' : '❌';
        echo "  {$accessSymbol} {$feature}\n";
    }
    
    echo "\n";
}

// Test specific permissions for Medical Coordinator
echo "Detailed Medical Coordinator Permissions Test\n";
echo "==========================================\n";

$medicalCoordinatorEmail = 'medical.coordinator@example.com';
$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$medicalCoordinatorEmail]);
$userId = $stmt->fetchColumn();

if ($userId) {
    echo "Medical Coordinator User ID: {$userId}\n\n";
    
    // Test specific permissions that should be available
    $permissionsToTest = [
        'patients.assign_clinician',
        'patients.limited_history'
    ];
    
    foreach ($permissionsToTest as $permission) {
        $hasPermission = $rbacManager->hasPermission($userId, $permission);
        $permissionSymbol = $hasPermission ? '✅' : '❌';
        echo "  {$permissionSymbol} {$permission}\n";
    }
    
    echo "\n";
    
    // Test feature access
    $featuresToTest = [
        'patient_management' => 'write',
        'audit_compliance' => 'read'
    ];
    
    foreach ($featuresToTest as $feature => $requiredLevel) {
        $canAccess = $rbacManager->canAccessFeature($userId, $feature, $requiredLevel);
        $accessSymbol = $canAccess ? '✅' : '❌';
        echo "  {$accessSymbol} {$feature} ({$requiredLevel} access)\n";
    }
}

echo "\n✅ Frontend feature access simulation completed!\n";
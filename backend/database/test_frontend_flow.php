<?php

// Test script to simulate the actual frontend flow

echo "Testing Frontend Flow Simulation\n";
echo "==============================\n\n";

// Simulate login process
echo "1. Simulating user login...\n";

// Include necessary files
require_once __DIR__ . '/DatabaseConnection.php';
require_once __DIR__ . '/../../shared/rbac/DynamicRBACManager.php';
require_once __DIR__ . '/../../admin-ui/controllers/RoleController.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

// Get database connection
$db = DatabaseConnection::getInstance();

// Get super admin user
$stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE role = 'super_admin' LIMIT 1");
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ No super admin user found\n";
    exit(1);
}

echo "✅ Found super admin user: {$user['name']} (ID: {$user['id']})\n";

// Simulate the frontend flow:
// 1. Login function calls ApiService.getUserRoles(userId)
echo "\n2. Calling getUserRoles({$user['id']})...\n";

// Create RoleController instance
$roleController = new \AdminUI\Controllers\RoleController();

// Mock request for getUserRoles
$request = [
    'params' => ['id' => $user['id']],
    'user' => ['id' => $user['id'], 'role' => 'super_admin']
];

$rolesResponse = $roleController->getUserRoles($request);
echo "✅ getUserRoles response status: {$rolesResponse['status']}\n";

if ($rolesResponse['status'] === 200 && !empty($rolesResponse['data']['roles'])) {
    $userRoles = $rolesResponse['data']['roles'];
    echo "✅ User has " . count($userRoles) . " roles\n";
    
    // 2. For each role, frontend calls ApiService.getRolePermissions(roleId)
    // and ApiService.getRoleFeatureAccess(roleId)
    $userPermissions = [];
    $userFeatureAccess = [];
    
    foreach ($userRoles as $role) {
        echo "\n3. Processing role: {$role['name']} (ID: {$role['id']})\n";
        
        // Get role permissions
        $permRequest = [
            'params' => ['id' => $role['id']],
            'user' => ['id' => $user['id'], 'role' => 'super_admin']
        ];
        
        $permResponse = $roleController->getRolePermissions($permRequest);
        if ($permResponse['status'] === 200) {
            $permissions = $permResponse['data']['permissions'];
            echo "   ✅ Retrieved " . count($permissions) . " permissions\n";
            $userPermissions = array_merge($userPermissions, array_column($permissions, 'name'));
        } else {
            echo "   ⚠️ Failed to get permissions for role {$role['id']}\n";
        }
        
        // Get role feature access
        $featureRequest = [
            'params' => ['id' => $role['id']],
            'user' => ['id' => $user['id'], 'role' => 'super_admin']
        ];
        
        $featureResponse = $roleController->getRoleFeatureAccess($featureRequest);
        if ($featureResponse['status'] === 200) {
            $featureAccess = $featureResponse['data']['feature_access'];
            echo "   ✅ Retrieved " . count($featureAccess) . " feature access entries\n";
            $userFeatureAccess[$role['id']] = $featureAccess;
        } else {
            echo "   ⚠️ Failed to get feature access for role {$role['id']}\n";
        }
    }
    
    // Remove duplicate permissions
    $userPermissions = array_unique($userPermissions);
    
    echo "\n4. Final user data:\n";
    echo "   Name: {$user['name']}\n";
    echo "   Email: {$user['email']}\n";
    echo "   Role: {$user['role']}\n";
    echo "   Roles count: " . count($userRoles) . "\n";
    echo "   Permissions count: " . count($userPermissions) . "\n";
    echo "   Feature access roles: " . count($userFeatureAccess) . "\n";
    
    // Check if super admin has access to all features
    if (isset($userFeatureAccess[$userRoles[0]['id']])) {
        $features = $userFeatureAccess[$userRoles[0]['id']];
        echo "   Feature modules accessible: " . count($features) . "\n";
        
        if (count($features) >= 10) {
            echo "\n🎉 SUCCESS: Super admin has access to all feature modules!\n";
            echo "The frontend flow is working correctly.\n";
        } else {
            echo "\n❌ ISSUE: Super admin should have access to 10+ modules, but only has " . count($features) . "\n";
        }
    } else {
        echo "\n❌ ISSUE: No feature access data found for user roles\n";
    }
} else {
    echo "❌ Failed to get user roles\n";
}

echo "\n✅ Frontend flow simulation completed!\n";
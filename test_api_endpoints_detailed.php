<?php

// Test the specific API endpoints that the frontend uses to fetch user permissions

require_once 'backend/database/DatabaseConnection.php';
require_once 'backend/admin-ui/controllers/RoleController.php';
require_once 'backend/user-service/controllers/UserController.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get the Medical Coordinator user
    $stmt = $db->prepare("SELECT id FROM users WHERE email = 'medical.coordinator@example.com'");
    $stmt->execute();
    $userId = $stmt->fetchColumn();
    
    if (!$userId) {
        echo "❌ Medical Coordinator user not found\n";
        exit(1);
    }
    
    echo "Medical Coordinator User ID: {$userId}\n\n";
    
    // Test 1: Get user roles (getUserRoles)
    echo "1. Testing getUserRoles (GET /admin/users/{$userId}/roles)\n";
    echo "========================================================\n";
    
    // Simulate the request that ApiService.getUserRoles would make
    $request = [
        'params' => ['id' => $userId],
        'user' => ['id' => $userId, 'role' => 'medical_coordinator']
    ];
    
    $roleController = new \AdminUI\Controllers\RoleController();
    $response = $roleController->getUserRoles($request);
    
    echo "Response Status: {$response['status']}\n";
    if ($response['status'] === 200) {
        echo "✅ Success\n";
        if (isset($response['data']['roles'])) {
            echo "Roles found: " . count($response['data']['roles']) . "\n";
            foreach ($response['data']['roles'] as $role) {
                echo "  - {$role['name']} (ID: {$role['id']})\n";
            }
        }
    } else {
        echo "❌ Failed: {$response['message']}\n";
    }
    
    echo "\n";
    
    // Test 2: Get role permissions (getRolePermissions)
    echo "2. Testing getRolePermissions (GET /admin/roles/6/permissions)\n";
    echo "=========================================================\n";
    
    // Get the medical_coordinator role ID
    $stmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $roleId = $stmt->fetchColumn();
    
    if ($roleId) {
        $request = [
            'params' => ['id' => $roleId],
            'user' => ['id' => $userId, 'role' => 'medical_coordinator']
        ];
        
        $response = $roleController->getRolePermissions($request);
        
        echo "Response Status: {$response['status']}\n";
        if ($response['status'] === 200) {
            echo "✅ Success\n";
            if (isset($response['data']['permissions'])) {
                echo "Permissions found: " . count($response['data']['permissions']) . "\n";
                foreach ($response['data']['permissions'] as $permission) {
                    echo "  - {$permission['name']} ({$permission['display_name']})\n";
                }
            }
        } else {
            echo "❌ Failed: {$response['message']}\n";
        }
    } else {
        echo "❌ Role not found\n";
    }
    
    echo "\n";
    
    // Test 3: Get role feature access (getRoleFeatureAccess)
    echo "3. Testing getRoleFeatureAccess (GET /admin/roles/6/features)\n";
    echo "========================================================\n";
    
    if ($roleId) {
        $request = [
            'params' => ['id' => $roleId],
            'user' => ['id' => $userId, 'role' => 'medical_coordinator']
        ];
        
        $response = $roleController->getRoleFeatureAccess($request);
        
        echo "Response Status: {$response['status']}\n";
        if ($response['status'] === 200) {
            echo "✅ Success\n";
            if (isset($response['data']['feature_access'])) {
                echo "Feature access found: " . count($response['data']['feature_access']) . "\n";
                foreach ($response['data']['feature_access'] as $feature) {
                    echo "  - {$feature['module_name']} ({$feature['access_level']})\n";
                }
            }
        } else {
            echo "❌ Failed: {$response['message']}\n";
        }
    } else {
        echo "❌ Role not found\n";
    }
    
    echo "\n";
    echo "✅ API endpoint testing completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
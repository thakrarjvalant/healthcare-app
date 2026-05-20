<?php

// Simulate the frontend login process to see what data would be stored

require_once 'backend/database/DatabaseConnection.php';
require_once 'backend/shared/rbac/DynamicRBACManager.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

try {
    $db = DatabaseConnection::getInstance();
    $rbacManager = new DynamicRBACManager();
    
    // Get the Medical Coordinator user
    $stmt = $db->prepare("SELECT id, name, email, role, verified, created_at, updated_at FROM users WHERE email = 'medical.coordinator@example.com'");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        echo "❌ Medical Coordinator user not found\n";
        exit(1);
    }
    
    echo "User Data from Database:\n";
    echo "======================\n";
    foreach ($userData as $key => $value) {
        echo "{$key}: {$value}\n";
    }
    
    echo "\n";
    
    // Simulate fetching user roles and permissions (as the frontend would do)
    echo "Fetching User Roles and Permissions:\n";
    echo "===================================\n";
    
    // Get user roles
    $userRoles = $rbacManager->getUserRoles($userData['id']);
    
    echo "User Roles:\n";
    foreach ($userRoles as $role) {
        echo "  - ID: {$role['id']}, Name: {$role['name']}, Display: {$role['display_name']}\n";
    }
    
    echo "\n";
    
    // Get permissions for each role
    $userPermissions = [];
    $userFeatureAccess = [];
    
    foreach ($userRoles as $role) {
        // Get permissions for the role
        $rolePermissions = $rbacManager->getRolePermissions($role['id']);
        echo "Permissions for role '{$role['name']}':\n";
        foreach ($rolePermissions as $permission) {
            echo "  - {$permission['name']} ({$permission['display_name']})\n";
            $userPermissions[] = $permission['name'];
        }
        
        // Get feature access for the role
        $roleFeatureAccess = $rbacManager->getRoleFeatureAccess($role['id']);
        echo "Feature access for role '{$role['name']}':\n";
        foreach ($roleFeatureAccess as $feature) {
            echo "  - {$feature['module_name']} ({$feature['access_level']})\n";
        }
        $userFeatureAccess[$role['id']] = $roleFeatureAccess;
        
        echo "\n";
    }
    
    // Remove duplicate permissions
    $userPermissions = array_unique($userPermissions);
    
    // Create the user object that would be stored in localStorage
    $cleanUser = [
        'id' => $userData['id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'roles' => $userRoles,
        'permissions' => $userPermissions,
        'featureAccess' => $userFeatureAccess,
        'verified' => $userData['verified'],
        'created_at' => $userData['created_at'],
        'updated_at' => $userData['updated_at']
    ];
    
    echo "Final User Object for localStorage:\n";
    echo "==================================\n";
    echo json_encode($cleanUser, JSON_PRETTY_PRINT);
    
    echo "\n\n";
    
    // Test permission checking as the frontend would do
    echo "Testing Permission Checks:\n";
    echo "========================\n";
    
    $requiredPermissions = ['patients.assign_clinician', 'patients.limited_history'];
    
    foreach ($requiredPermissions as $permission) {
        $hasPermission = in_array($permission, $cleanUser['permissions']);
        echo "Has '{$permission}': " . ($hasPermission ? '✅ Yes' : '❌ No') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
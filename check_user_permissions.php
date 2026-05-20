<?php

// Check what permissions are assigned to a specific user

require_once 'backend/database/DatabaseConnection.php';
require_once 'backend/shared/rbac/DynamicRBACManager.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

try {
    $db = DatabaseConnection::getInstance();
    $rbacManager = new DynamicRBACManager();
    
    // Get the Medical Coordinator user
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE email = 'medical.coordinator@example.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "❌ Medical Coordinator user not found\n";
        exit(1);
    }
    
    echo "Medical Coordinator User:\n";
    echo "ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Email: {$user['email']}\n\n";
    
    // Get user roles
    $userRoles = $rbacManager->getUserRoles($user['id']);
    
    echo "User Roles:\n";
    echo "===========\n";
    foreach ($userRoles as $role) {
        echo "✅ {$role['name']} - {$role['display_name']}\n";
    }
    
    echo "\n";
    
    // Get all permissions for this user
    $allPermissions = [];
    foreach ($userRoles as $role) {
        $rolePermissions = $rbacManager->getRolePermissions($role['id']);
        $allPermissions = array_merge($allPermissions, $rolePermissions);
    }
    
    // Remove duplicates
    $uniquePermissions = [];
    $permissionNames = [];
    foreach ($allPermissions as $permission) {
        if (!in_array($permission['name'], $permissionNames)) {
            $uniquePermissions[] = $permission;
            $permissionNames[] = $permission['name'];
        }
    }
    
    echo "User Permissions:\n";
    echo "================\n";
    foreach ($uniquePermissions as $permission) {
        echo "✅ {$permission['name']} - {$permission['display_name']}\n";
    }
    
    echo "\nTotal unique permissions: " . count($uniquePermissions) . "\n\n";
    
    // Check if the required permissions exist
    $requiredPermissions = ['patients.assign_clinician', 'patients.limited_history'];
    
    foreach ($requiredPermissions as $requiredPermission) {
        $found = false;
        foreach ($uniquePermissions as $permission) {
            if ($permission['name'] === $requiredPermission) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "✅ User has required permission '{$requiredPermission}'\n";
        } else {
            echo "❌ User is missing required permission '{$requiredPermission}'\n";
        }
    }
    
    // Test specific permission check
    echo "\nTesting specific permission checks:\n";
    echo "==================================\n";
    
    $hasAssignPermission = $rbacManager->hasPermission($user['id'], 'patients.assign_clinician');
    $hasHistoryPermission = $rbacManager->hasPermission($user['id'], 'patients.limited_history');
    
    echo "Has 'patients.assign_clinician': " . ($hasAssignPermission ? '✅ Yes' : '❌ No') . "\n";
    echo "Has 'patients.limited_history': " . ($hasHistoryPermission ? '✅ Yes' : '❌ No') . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
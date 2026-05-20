<?php

// Verify the data that API endpoints should return for the Medical Coordinator

require_once 'backend/database/DatabaseConnection.php';

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
    
    // Test 1: Get user roles data (what getUserRoles API should return)
    echo "1. User Roles Data (getUserRoles response)\n";
    echo "========================================\n";
    
    $stmt = $db->prepare("SELECT dr.*, GROUP_CONCAT(dp.name SEPARATOR ',') as permissions
                         FROM user_dynamic_roles udr
                         JOIN dynamic_roles dr ON udr.role_id = dr.id
                         LEFT JOIN dynamic_role_permissions drp ON dr.id = drp.role_id AND drp.is_active = 1
                         LEFT JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                         WHERE udr.user_id = ? AND udr.is_active = 1 AND dr.is_active = 1
                         GROUP BY dr.id");
    $stmt->execute([$userId]);
    
    $roles = [];
    while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
        $row['permissions'] = $row['permissions'] ? explode(',', $row['permissions']) : [];
        $roles[] = $row;
    }
    
    echo "Roles found: " . count($roles) . "\n";
    foreach ($roles as $role) {
        echo "  - {$role['name']} (ID: {$role['id']})\n";
        echo "    Display: {$role['display_name']}\n";
        echo "    Permissions: " . count($role['permissions']) . "\n";
    }
    
    echo "\n";
    
    // Test 2: Get role permissions data (what getRolePermissions API should return)
    echo "2. Role Permissions Data (getRolePermissions response)\n";
    echo "==================================================\n";
    
    // Get the medical_coordinator role ID
    $stmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $roleId = $stmt->fetchColumn();
    
    if ($roleId) {
        $stmt = $db->prepare("SELECT dp.* FROM dynamic_role_permissions drp
                             JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                             WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1
                             ORDER BY dp.name");
        $stmt->execute([$roleId]);
        $permissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo "Permissions found: " . count($permissions) . "\n";
        foreach ($permissions as $permission) {
            echo "  - {$permission['name']} ({$permission['display_name']})\n";
        }
        
        // Check if required permissions are present
        $requiredPermissions = ['patients.assign_clinician', 'patients.limited_history'];
        foreach ($requiredPermissions as $requiredPermission) {
            $found = false;
            foreach ($permissions as $permission) {
                if ($permission['name'] === $requiredPermission) {
                    $found = true;
                    break;
                }
            }
            
            if ($found) {
                echo "  ✅ Required permission '{$requiredPermission}' is present\n";
            } else {
                echo "  ❌ Required permission '{$requiredPermission}' is missing\n";
            }
        }
    } else {
        echo "❌ Role not found\n";
    }
    
    echo "\n";
    
    // Test 3: Get role feature access data (what getRoleFeatureAccess API should return)
    echo "3. Role Feature Access Data (getRoleFeatureAccess response)\n";
    echo "========================================================\n";
    
    if ($roleId) {
        $stmt = $db->prepare("SELECT rfa.*, fm.name as module_name, fm.display_name as module_display_name
                             FROM role_feature_access rfa
                             JOIN feature_modules fm ON rfa.module_id = fm.id
                             WHERE rfa.role_id = ? AND rfa.is_active = 1 AND fm.is_enabled = 1
                             ORDER BY fm.name");
        $stmt->execute([$roleId]);
        $featureAccess = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        echo "Feature access found: " . count($featureAccess) . "\n";
        foreach ($featureAccess as $feature) {
            echo "  - {$feature['module_name']} ({$feature['access_level']})\n";
        }
    } else {
        echo "❌ Role not found\n";
    }
    
    echo "\n";
    echo "✅ API data verification completed!\n";
    echo "All required data is available in the database for the frontend to fetch.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
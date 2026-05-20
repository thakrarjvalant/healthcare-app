<?php

// Check what permissions are assigned to the Medical Coordinator role

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get the medical_coordinator role ID
    $stmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $roleId = $stmt->fetchColumn();
    
    if (!$roleId) {
        echo "❌ Medical Coordinator role not found\n";
        exit(1);
    }
    
    echo "Medical Coordinator Role ID: {$roleId}\n\n";
    
    // Get permissions assigned to this role
    $stmt = $db->prepare("SELECT dp.name, dp.display_name 
                         FROM dynamic_role_permissions drp
                         JOIN dynamic_permissions dp ON drp.permission_id = dp.id
                         WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1
                         ORDER BY dp.name");
    $stmt->execute([$roleId]);
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Permissions assigned to Medical Coordinator role:\n";
    echo "=============================================\n";
    foreach ($permissions as $permission) {
        echo "✅ {$permission['name']} - {$permission['display_name']}\n";
    }
    
    echo "\nTotal permissions: " . count($permissions) . "\n";
    
    // Check if the required permissions exist
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
            echo "✅ Required permission '{$requiredPermission}' is assigned\n";
        } else {
            echo "❌ Required permission '{$requiredPermission}' is missing\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
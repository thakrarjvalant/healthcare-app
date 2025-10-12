<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check Medical Coordinator permissions
    $stmt = $db->getConnection()->prepare("
        SELECT dp.name 
        FROM dynamic_permissions dp 
        JOIN dynamic_role_permissions drp ON dp.id = drp.permission_id 
        JOIN dynamic_roles dr ON drp.role_id = dr.id 
        WHERE dr.name = ?
    ");
    $stmt->execute(['medical_coordinator']);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "=== Medical Coordinator Permissions ===\n";
    foreach ($permissions as $permission) {
        echo "- $permission\n";
    }
    
    // Check if the required permissions exist
    $requiredPermissions = ['patients.assign_clinician', 'patients.limited_history'];
    $missingPermissions = [];
    
    foreach ($requiredPermissions as $requiredPermission) {
        if (!in_array($requiredPermission, $permissions)) {
            $missingPermissions[] = $requiredPermission;
        }
    }
    
    if (empty($missingPermissions)) {
        echo "\n✅ All required permissions are present\n";
    } else {
        echo "\n❌ Missing permissions:\n";
        foreach ($missingPermissions as $missingPermission) {
            echo "- $missingPermission\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get the Medical Coordinator user
    $stmt = $db->getConnection()->prepare("SELECT id, name, email FROM users WHERE role = ? LIMIT 1");
    $stmt->execute(['medical_coordinator']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo "No Medical Coordinator user found\n";
        exit;
    }
    
    echo "=== Medical Coordinator User ===\n";
    echo "ID: {$user['id']}\n";
    echo "Name: {$user['name']}\n";
    echo "Email: {$user['email']}\n\n";
    
    // Check user's dynamic roles
    $stmt = $db->getConnection()->prepare("
        SELECT dr.name, dr.display_name
        FROM dynamic_roles dr
        JOIN user_dynamic_roles udr ON dr.id = udr.role_id
        WHERE udr.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== User Roles ===\n";
    foreach ($roles as $role) {
        echo "- {$role['name']} ({$role['display_name']})\n";
    }
    
    // Check user's permissions
    $stmt = $db->getConnection()->prepare("
        SELECT DISTINCT dp.name
        FROM dynamic_permissions dp
        JOIN dynamic_role_permissions drp ON dp.id = drp.permission_id
        JOIN user_dynamic_roles udr ON drp.role_id = udr.role_id
        WHERE udr.user_id = ?
    ");
    $stmt->execute([$user['id']]);
    $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\n=== User Permissions ===\n";
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
        echo "\n✅ User has all required permissions\n";
    } else {
        echo "\n❌ User missing permissions:\n";
        foreach ($missingPermissions as $missingPermission) {
            echo "- $missingPermission\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
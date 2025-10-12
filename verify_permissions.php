<?php
// Script to verify the actual permissions in the database

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get roles
    $rolesStmt = $db->prepare("SELECT id, name, display_name FROM dynamic_roles ORDER BY name");
    $rolesStmt->execute();
    $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== ROLES ===\n";
    foreach ($roles as $role) {
        echo "ID: {$role['id']}, Name: {$role['name']}, Display: {$role['display_name']}\n";
    }
    
    echo "\n=== PERMISSIONS FOR EACH ROLE ===\n";
    
    foreach ($roles as $role) {
        echo "\n{$role['display_name']} ({$role['name']}):\n";
        
        // Get permissions for this role
        $permStmt = $db->prepare("
            SELECT dp.name, dp.display_name 
            FROM dynamic_permissions dp
            JOIN dynamic_role_permissions drp ON dp.id = drp.permission_id
            WHERE drp.role_id = ? AND drp.is_active = 1
            ORDER BY dp.name
        ");
        $permStmt->execute([$role['id']]);
        $permissions = $permStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($permissions)) {
            echo "  No permissions assigned\n";
        } else {
            foreach ($permissions as $perm) {
                echo "  - {$perm['name']} ({$perm['display_name']})\n";
            }
        }
    }
    
    echo "\n=== FEATURE MODULE ACCESS ===\n";
    echo "Feature module access is no longer used\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
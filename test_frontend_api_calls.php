<?php
// Test script to simulate frontend API calls
chdir('backend/database');

require_once 'config.php';
require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Simulate getting user by email (medical.coordinator@example.com)
    $stmt = $db->prepare("SELECT * FROM users WHERE email = 'medical.coordinator@example.com'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "User found:\n";
        print_r($user);
        
        // Simulate getUserRoles API call
        echo "\n=== Simulating getUserRoles API call ===\n";
        $stmt = $db->prepare("SELECT dr.*, GROUP_CONCAT(dp.name SEPARATOR ',') as permissions FROM user_dynamic_roles udr JOIN dynamic_roles dr ON udr.role_id = dr.id LEFT JOIN dynamic_role_permissions drp ON dr.id = drp.role_id AND drp.is_active = 1 LEFT JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE udr.user_id = ? AND udr.is_active = 1 AND dr.is_active = 1 GROUP BY dr.id");
        $stmt->execute([$user['id']]);
        $roles = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['permissions'] = $row['permissions'] ? explode(',', $row['permissions']) : [];
            $roles[] = $row;
        }
        
        echo "Roles returned:\n";
        print_r($roles);
        
        // Simulate getRolePermissions for each role
        echo "\n=== Simulating getRolePermissions API calls ===\n";
        foreach ($roles as $role) {
            echo "Permissions for role: " . $role['name'] . "\n";
            $stmt = $db->prepare("SELECT dp.* FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1 ORDER BY dp.name");
            $stmt->execute([$role['id']]);
            $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($permissions as $permission) {
                echo "- " . $permission['name'] . " (" . $permission['display_name'] . ")\n";
            }
        }
    } else {
        echo "User not found\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
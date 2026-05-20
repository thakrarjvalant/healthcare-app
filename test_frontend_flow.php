<?php
// Test the exact flow that happens in the frontend AuthContext login function
chdir('backend/database');

require_once 'config.php';
require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Simulate getting user data (like what happens in getProfile)
    $stmt = $db->prepare("SELECT * FROM users WHERE email = 'medical.coordinator@example.com'");
    $stmt->execute();
    $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$userData) {
        echo "User not found\n";
        exit;
    }
    
    echo "User data from getProfile:\n";
    print_r($userData);
    
    // Simulate getUserRoles call
    $stmt = $db->prepare("SELECT dr.*, GROUP_CONCAT(dp.name SEPARATOR ',') as permissions FROM user_dynamic_roles udr JOIN dynamic_roles dr ON udr.role_id = dr.id LEFT JOIN dynamic_role_permissions drp ON dr.id = drp.role_id AND drp.is_active = 1 LEFT JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE udr.user_id = ? AND udr.is_active = 1 AND dr.is_active = 1 GROUP BY dr.id");
    $stmt->execute([$userData['id']]);
    
    $userRoles = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $row['permissions'] = $row['permissions'] ? explode(',', $row['permissions']) : [];
        $userRoles[] = $row;
    }
    
    echo "\nUser roles from getUserRoles:\n";
    print_r($userRoles);
    
    // Simulate getting permissions for each role (like what happens in getRolePermissions)
    $userPermissions = [];
    $userFeatureAccess = [];
    
    foreach ($userRoles as $role) {
        echo "\nGetting permissions for role: " . $role['name'] . " (ID: " . $role['id'] . ")\n";
        
        // Get permissions for the role
        $stmt = $db->prepare("SELECT dp.* FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE drp.role_id = ? AND drp.is_active = 1 AND dp.is_active = 1 ORDER BY dp.name");
        $stmt->execute([$role['id']]);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Permissions fetched:\n";
        foreach ($permissions as $permission) {
            echo "- " . $permission['name'] . "\n";
            $userPermissions[] = $permission['name'];
        }
        
        // Get feature access for the role
        $stmt = $db->prepare("SELECT rfa.*, fm.name as module_name, fm.display_name as module_display_name FROM role_feature_access rfa JOIN feature_modules fm ON rfa.module_id = fm.id WHERE rfa.role_id = ? AND rfa.is_active = 1");
        $stmt->execute([$role['id']]);
        $featureAccess = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Feature access fetched:\n";
        foreach ($featureAccess as $access) {
            echo "- " . $access['module_name'] . " (Level: " . $access['access_level'] . ")\n";
        }
        
        $userFeatureAccess[$role['id']] = $featureAccess;
    }
    
    // Remove duplicate permissions
    $userPermissions = array_unique($userPermissions);
    
    echo "\nFinal user permissions array:\n";
    print_r($userPermissions);
    
    echo "\nFinal user feature access:\n";
    print_r($userFeatureAccess);
    
    // Create the clean user object like in the frontend
    $cleanUser = [
        'id' => $userData['id'],
        'name' => $userData['name'],
        'email' => $userData['email'],
        'role' => $userData['role'],
        'roles' => $userRoles,
        'permissions' => array_values($userPermissions), // Convert to indexed array
        'featureAccess' => $userFeatureAccess,
        'verified' => $userData['verified'],
        'created_at' => $userData['created_at'],
        'updated_at' => $userData['updated_at']
    ];
    
    echo "\nFinal clean user object:\n";
    echo json_encode($cleanUser, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
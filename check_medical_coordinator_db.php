<?php
// Change to the backend/database directory
chdir('backend/database');

require_once 'config.php';
require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check if Medical Coordinator role exists
    $stmt = $db->prepare("SELECT * FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($role) {
        echo "Medical Coordinator Role Found:\n";
        print_r($role);
        
        // Check permissions for this role
        echo "\nPermissions for Medical Coordinator:\n";
        $stmt = $db->prepare("SELECT dp.* FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE drp.role_id = ? AND drp.is_active = 1");
        $stmt->execute([$role['id']]);
        $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($permissions as $permission) {
            echo "- " . $permission['name'] . " (" . $permission['display_name'] . ")\n";
        }
        
        // Check feature access for this role
        echo "\nFeature Access for Medical Coordinator:\n";
        $stmt = $db->prepare("SELECT rfa.*, fm.name as module_name, fm.display_name as module_display_name FROM role_feature_access rfa JOIN feature_modules fm ON rfa.module_id = fm.id WHERE rfa.role_id = ? AND rfa.is_active = 1");
        $stmt->execute([$role['id']]);
        $featureAccess = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($featureAccess as $access) {
            echo "- " . $access['module_name'] . " (" . $access['module_display_name'] . ") - Access Level: " . $access['access_level'] . "\n";
        }
    } else {
        echo "Medical Coordinator role not found in database\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
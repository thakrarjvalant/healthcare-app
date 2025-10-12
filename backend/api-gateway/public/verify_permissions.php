<?php
// API endpoint to verify permissions
header('Content-Type: application/json');

require_once '../../database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get roles
    $rolesStmt = $db->prepare("SELECT id, name, display_name FROM dynamic_roles ORDER BY name");
    $rolesStmt->execute();
    $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $result = [
        'roles' => [],
        'role_permissions' => [],
        'role_features' => []
    ];
    
    foreach ($roles as $role) {
        $result['roles'][] = $role;
        
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
        $result['role_permissions'][$role['name']] = $permissions;
        
        // Get feature module access for this role
        $modules = [];
        $result['role_features'][$role['name']] = $modules;
    }
    
    echo json_encode($result, JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
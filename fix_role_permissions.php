<?php
// Fix the role-permission assignments to match the intended implementation
// Medical Coordinator should have appointment management permissions
// Receptionist should keep appointment management permissions (as per user request)

require_once __DIR__ . '/backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Get role IDs
    $stmt = $db->prepare("SELECT id, name FROM dynamic_roles WHERE name IN ('receptionist', 'medical_coordinator')");
    $stmt->execute();
    $roles = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $roles[$row['name']] = $row['id'];
    }
    
    // Get permission IDs for appointment management
    $stmt = $db->prepare("SELECT id, name FROM dynamic_permissions WHERE name LIKE 'appointments.%'");
    $stmt->execute();
    $appointmentPermissions = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $appointmentPermissions[$row['name']] = $row['id'];
    }
    
    echo "Roles found:\n";
    print_r($roles);
    
    echo "Appointment permissions found:\n";
    print_r($appointmentPermissions);
    
    // Remove appointment permissions from Medical Coordinator
    echo "\nRemoving appointment permissions from Medical Coordinator...\n";
    foreach ($appointmentPermissions as $permissionName => $permissionId) {
        $stmt = $db->prepare("DELETE FROM dynamic_role_permissions WHERE role_id = ? AND permission_id = ?");
        $result = $stmt->execute([$roles['medical_coordinator'], $permissionId]);
        if ($result) {
            echo "  Removed $permissionName from Medical Coordinator\n";
        }
    }
    
    // Add appointment permissions to Receptionist (if not already there)
    echo "\nAdding appointment permissions to Receptionist...\n";
    foreach ($appointmentPermissions as $permissionName => $permissionId) {
        $stmt = $db->prepare("INSERT IGNORE INTO dynamic_role_permissions (role_id, permission_id) VALUES (?, ?)");
        $result = $stmt->execute([$roles['receptionist'], $permissionId]);
        if ($result) {
            echo "  Added $permissionName to Receptionist\n";
        }
    }
    
    echo "\n✅ Role-permission assignments updated successfully!\n";
    
    // Verify the changes
    echo "\n=== Verification ===\n";
    
    // Check Medical Coordinator permissions
    echo "Medical Coordinator permissions:\n";
    $stmt = $db->prepare("SELECT dp.name FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE drp.role_id = ? ORDER BY dp.name");
    $stmt->execute([$roles['medical_coordinator']]);
    while ($row = $stmt->fetch()) {
        echo "  - " . $row['name'] . "\n";
    }
    
    // Check Receptionist permissions
    echo "\nReceptionist permissions:\n";
    $stmt = $db->prepare("SELECT dp.name FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE drp.role_id = ? ORDER BY dp.name");
    $stmt->execute([$roles['receptionist']]);
    while ($row = $stmt->fetch()) {
        echo "  - " . $row['name'] . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
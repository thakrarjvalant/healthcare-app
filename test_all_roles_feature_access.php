<?php

// Test script to verify feature access for all roles

// Include necessary files
require_once __DIR__ . '/backend/database/DatabaseConnection.php';
require_once __DIR__ . '/backend/shared/rbac/DynamicRBACManager.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

echo "Testing Feature Access for All Roles\n";
echo "==================================\n\n";

// Get database connection
$db = DatabaseConnection::getInstance();
$rbacManager = new DynamicRBACManager();

// Get all roles
$stmt = $db->prepare("SELECT id, name, display_name FROM dynamic_roles WHERE is_active = 1 ORDER BY name");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Roles found: " . count($roles) . "\n\n";

foreach ($roles as $role) {
    echo "Role: {$role['display_name']} ({$role['name']})\n";
    echo str_repeat("-", 50) . "\n";
    
    $featureAccess = $rbacManager->getRoleFeatureAccess($role['id']);
    
    if (empty($featureAccess)) {
        echo "  No feature access assigned\n";
    } else {
        foreach ($featureAccess as $access) {
            echo "  - {$access['module_name']}: {$access['access_level']}\n";
        }
    }
    
    echo "\n";
}

// Test specific role feature access
echo "Testing specific role feature access...\n";
echo "=====================================\n\n";

$testRoles = [
    'super_admin' => ['user_management', 'appointment_management', 'patient_management', 'clinical_management', 'billing_payments', 'front_desk', 'system_admin', 'role_management', 'audit_compliance', 'reports_analytics'],
    'admin' => ['user_management', 'system_admin', 'audit_compliance', 'reports_analytics'],
    'doctor' => ['patient_management', 'clinical_management', 'appointment_management'],
    'receptionist' => ['front_desk', 'patient_management', 'appointment_management', 'billing_payments'],
    'patient' => ['appointment_management', 'clinical_management', 'patient_management'],
    'medical_coordinator' => ['patient_management', 'audit_compliance']
];

foreach ($testRoles as $roleName => $expectedModules) {
    // Get role ID
    $stmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = ?");
    $stmt->execute([$roleName]);
    $roleId = $stmt->fetchColumn();
    
    if (!$roleId) {
        echo "❌ Role '{$roleName}' not found\n\n";
        continue;
    }
    
    echo "Role: {$roleName}\n";
    
    $featureAccess = $rbacManager->getRoleFeatureAccess($roleId);
    $actualModules = array_column($featureAccess, 'module_name');
    
    echo "  Expected modules: " . implode(', ', $expectedModules) . "\n";
    echo "  Actual modules: " . implode(', ', $actualModules) . "\n";
    
    // Check if all expected modules are present
    $missing = array_diff($expectedModules, $actualModules);
    $extra = array_diff($actualModules, $expectedModules);
    
    if (empty($missing) && empty($extra)) {
        echo "  ✅ All modules match expected\n";
    } else {
        if (!empty($missing)) {
            echo "  ⚠️ Missing modules: " . implode(', ', $missing) . "\n";
        }
        if (!empty($extra)) {
            echo "  ⚠️ Extra modules: " . implode(', ', $extra) . "\n";
        }
    }
    
    echo "\n";
}

echo "✅ Feature access testing completed!\n";
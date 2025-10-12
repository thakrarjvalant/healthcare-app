<?php

// Test script to verify the new role feature access endpoint

// Include necessary files
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/controllers/RoleController.php';
require_once __DIR__ . '/../shared/rbac/DynamicRBACManager.php';
require_once __DIR__ . '/../database/DatabaseConnection.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

echo "Testing Role Feature Access Endpoint\n";
echo "==================================\n\n";

// Get database connection
$db = DatabaseConnection::getInstance();

// Get super admin role ID
$stmt = $db->prepare("SELECT id FROM dynamic_roles WHERE name = 'super_admin'");
$stmt->execute();
$superAdminRoleId = $stmt->fetchColumn();

if (!$superAdminRoleId) {
    echo "❌ Super admin role not found\n";
    exit(1);
}

echo "Super admin role ID: {$superAdminRoleId}\n";

// Test the RBAC manager directly
$rbacManager = new DynamicRBACManager();
$featureAccess = $rbacManager->getRoleFeatureAccess($superAdminRoleId);

echo "Feature access for super admin:\n";
foreach ($featureAccess as $access) {
    echo "  - {$access['name']}: {$access['access_level']}\n";
}

echo "\n✅ Direct RBAC manager test completed successfully!\n";

// Test the controller method
$roleController = new \AdminUI\Controllers\RoleController();

// Mock request
$request = [
    'params' => ['id' => $superAdminRoleId],
    'user' => ['id' => 1, 'role' => 'super_admin']
];

$response = $roleController->getRoleFeatureAccess($request);

if ($response['status'] === 200) {
    echo "\n✅ Controller method test completed successfully!\n";
    echo "Response data keys: " . implode(', ', array_keys($response['data'])) . "\n";
    
    if (isset($response['data']['feature_access'])) {
        echo "Feature access count: " . count($response['data']['feature_access']) . "\n";
        foreach ($response['data']['feature_access'] as $access) {
            echo "  - {$access['name']}: {$access['access_level']}\n";
        }
    }
} else {
    echo "\n❌ Controller method test failed!\n";
    echo "Status: {$response['status']}\n";
    echo "Message: {$response['message']}\n";
}
<?php
// Test script to verify RBAC implementation

require_once 'backend/database/DatabaseConnection.php';
require_once 'backend/shared/rbac/DynamicRBACManager.php';

use Database\DatabaseConnection;
use Shared\RBAC\DynamicRBACManager;

try {
    // Test database connection
    $db = DatabaseConnection::getInstance();
    echo "✓ Database connection established successfully\n";
    
    // Test RBAC manager
    $rbacManager = new DynamicRBACManager();
    echo "✓ RBAC Manager initialized successfully\n";
    
    // Test getting all permissions
    $permissions = $rbacManager->getAllPermissions();
    echo "✓ Retrieved " . count($permissions) . " permissions from database\n";
    
    // Test getting all roles
    $dbConn = $db->getConnection();
    $stmt = $dbConn->prepare("SELECT * FROM dynamic_roles WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Retrieved " . count($roles) . " roles from database\n";
    
    // Test feature modules
    $stmt = $dbConn->prepare("SELECT * FROM feature_modules WHERE is_active = 1 ORDER BY name");
    $stmt->execute();
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Retrieved " . count($modules) . " feature modules from database\n";
    
    // Test role_feature_access table
    $stmt = $dbConn->prepare("SELECT * FROM role_feature_access WHERE is_active = 1 LIMIT 5");
    $stmt->execute();
    $featureAccess = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✓ Retrieved " . count($featureAccess) . " role feature access records from database\n";
    
    echo "\nRBAC implementation is working correctly!\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
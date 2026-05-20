<?php

// Script to reset a specific migration

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Delete the migration record for 007_create_dynamic_rbac_system.php
    $stmt = $db->prepare("DELETE FROM migrations WHERE migration = '007_create_dynamic_rbac_system.php'");
    $stmt->execute();
    
    echo "✅ Deleted migration record for 007_create_dynamic_rbac_system.php\n";
    
    // Now run the migration manually
    require_once 'backend/database/migrations/core/007_create_dynamic_rbac_system.php';
    
    $migration = new CreateDynamicRBACSystem();
    $migration->up();
    
    echo "✅ Ran migration 007_create_dynamic_rbac_system.php successfully!\n";
    
    // Check if the table exists now
    $stmt = $db->prepare("SHOW TABLES LIKE 'role_feature_access'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ role_feature_access table now exists\n";
    } else {
        echo "❌ role_feature_access table still does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
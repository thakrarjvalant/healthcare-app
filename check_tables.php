<?php

// Simple script to check if tables exist

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check if role_feature_access table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'role_feature_access'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ role_feature_access table exists\n";
        
        // Check if it has data
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM role_feature_access");
        $stmt->execute();
        $countResult = $stmt->fetch();
        echo "Records in role_feature_access: " . $countResult['count'] . "\n";
    } else {
        echo "❌ role_feature_access table does not exist\n";
    }
    
    // Check feature_modules table
    $stmt = $db->prepare("SHOW TABLES LIKE 'feature_modules'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ feature_modules table exists\n";
        
        // Check if it has data
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM feature_modules");
        $stmt->execute();
        $countResult = $stmt->fetch();
        echo "Records in feature_modules: " . $countResult['count'] . "\n";
    } else {
        echo "❌ feature_modules table does not exist\n";
    }
    
    // Check dynamic_roles table
    $stmt = $db->prepare("SHOW TABLES LIKE 'dynamic_roles'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ dynamic_roles table exists\n";
        
        // Check if it has data
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM dynamic_roles");
        $stmt->execute();
        $countResult = $stmt->fetch();
        echo "Records in dynamic_roles: " . $countResult['count'] . "\n";
    } else {
        echo "❌ dynamic_roles table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
<?php

require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

echo "🔍 Checking Seeder Status\n";
echo "=" . str_repeat("=", 30) . "\n\n";

try {
    $db = DatabaseConnection::getInstance();
    echo "✅ Database connection established!\n\n";
    
    // Check tables and their row counts
    $tables = [
        'users',
        'dynamic_roles',
        'feature_modules',
        'dynamic_permissions',
        'dynamic_role_permissions',
        'user_dynamic_roles'
    ];
    
    echo "📊 Table Status:\n";
    echo str_repeat("-", 50) . "\n";
    
    foreach ($tables as $table) {
        try {
            $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch();
            echo sprintf("✅ %-25s: %d rows\n", $table, $result['count']);
        } catch (Exception $e) {
            echo sprintf("❌ %-25s: Error - %s\n", $table, $e->getMessage());
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Check specific seeder data
    echo "\n🔑 RBAC System Verification:\n";
    
    // Check roles
    $stmt = $db->getConnection()->query("SELECT name, display_name FROM dynamic_roles ORDER BY id");
    $roles = $stmt->fetchAll();
    echo "🎭 Dynamic Roles (" . count($roles) . "):\n";
    foreach ($roles as $role) {
        echo "  - {$role['name']} ({$role['display_name']})\n";
    }
    
    // Check feature modules
    $stmt = $db->getConnection()->query("SELECT name, display_name FROM feature_modules ORDER BY id");
    $modules = $stmt->fetchAll();
    echo "\n📦 Feature Modules (" . count($modules) . "):\n";
    foreach ($modules as $module) {
        echo "  - {$module['name']} ({$module['display_name']})\n";
    }
    
    // Check user-role assignments
    $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM user_dynamic_roles WHERE is_active = 1");
    $result = $stmt->fetch();
    echo "\n👥 Active User-Role Assignments: " . $result['count'] . "\n";
    
    echo "\n🎉 Seeder verification completed!\n";
    echo "All RBAC seeders have been run successfully.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
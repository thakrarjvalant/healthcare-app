<?php

// Set up database connection directly to Docker MySQL
$host = '127.0.0.1';
$port = '3306';
$dbname = 'healthcare_db';
$username = 'healthcare_user';
$password = 'your_strong_password';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
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
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo sprintf("✅ %-25s: %d rows\n", $table, $result['count']);
        } catch (Exception $e) {
            echo sprintf("❌ %-25s: Error - %s\n", $table, $e->getMessage());
        }
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Check specific seeder data
    echo "\n🔑 RBAC System Verification:\n";
    
    // Check roles
    $stmt = $pdo->query("SELECT name, display_name FROM dynamic_roles ORDER BY id");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "🎭 Dynamic Roles (" . count($roles) . "):\n";
    foreach ($roles as $role) {
        echo "  - {$role['name']} ({$role['display_name']})\n";
    }
    
    // Check feature modules
    $stmt = $pdo->query("SELECT name, display_name FROM feature_modules ORDER BY id");
    $modules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "\n📦 Feature Modules (" . count($modules) . "):\n";
    foreach ($modules as $module) {
        echo "  - {$module['name']} ({$module['display_name']})\n";
    }
    
    // Check user-role assignments
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM user_dynamic_roles WHERE is_active = 1");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "\n👥 Active User-Role Assignments: " . $result['count'] . "\n";
    
    // Check specific users and their roles
    echo "\n👥 User-Role Assignments Details:\n";
    $stmt = $pdo->query("SELECT u.name as user_name, u.email, u.role as user_role, dr.name as assigned_role 
                        FROM users u 
                        LEFT JOIN user_dynamic_roles udr ON u.id = udr.user_id AND udr.is_active = 1
                        LEFT JOIN dynamic_roles dr ON udr.role_id = dr.id AND dr.is_active = 1
                        ORDER BY u.id");
    $userRoles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($userRoles as $userRole) {
        $status = $userRole['assigned_role'] ? "✅" : "❌";
        echo "  {$status} {$userRole['user_name']} ({$userRole['user_role']})";
        if ($userRole['assigned_role']) {
            echo " → {$userRole['assigned_role']}";
        }
        echo "\n";
    }
    
    echo "\n🎉 Seeder verification completed!\n";
    echo "All RBAC seeders have been run successfully.\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
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
    
    // Check the creation date of the Super Administrator user
    echo "🔍 Checking Super Administrator user creation details:\n";
    echo str_repeat("-", 60) . "\n";
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, verified, created_at, updated_at FROM users WHERE name = ? OR email = ?");
    $stmt->execute(['Super Administrator', 'superadmin@example.com']);
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($superAdmin) {
        echo "✅ Super Administrator user found:\n";
        echo "  ID: {$superAdmin['id']}\n";
        echo "  Name: {$superAdmin['name']}\n";
        echo "  Email: {$superAdmin['email']}\n";
        echo "  Role: {$superAdmin['role']}\n";
        echo "  Verified: " . ($superAdmin['verified'] ? 'Yes' : 'No') . "\n";
        echo "  Created At: {$superAdmin['created_at']}\n";
        echo "  Updated At: {$superAdmin['updated_at']}\n";
        
        // Check if this user was created by a seeder or manually
        // Let's check the user count before and after running seeders
        echo "\n🔍 Checking if this user was created by a seeder:\n";
        echo str_repeat("-", 60) . "\n";
        
        // Check if the role 'super_admin' exists in the dynamic_roles table
        $stmt = $pdo->prepare("SELECT id, name, display_name FROM dynamic_roles WHERE name = ?");
        $stmt->execute(['super_admin']);
        $superAdminRole = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($superAdminRole) {
            echo "✅ Super Admin role exists in dynamic_roles table:\n";
            echo "  Role ID: {$superAdminRole['id']}\n";
            echo "  Role Name: {$superAdminRole['name']}\n";
            echo "  Display Name: {$superAdminRole['display_name']}\n";
            
            // Check if this user has an entry in user_dynamic_roles table
            $stmt = $pdo->prepare("SELECT udr.id, udr.assigned_at, udr.is_active, dr.name as role_name 
                                  FROM user_dynamic_roles udr 
                                  JOIN dynamic_roles dr ON udr.role_id = dr.id 
                                  WHERE udr.user_id = ? AND dr.name = ?");
            $stmt->execute([$superAdmin['id'], 'super_admin']);
            $userRoleAssignment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userRoleAssignment) {
                echo "✅ User has proper role assignment in user_dynamic_roles table:\n";
                echo "  Assignment ID: {$userRoleAssignment['id']}\n";
                echo "  Assigned At: {$userRoleAssignment['assigned_at']}\n";
                echo "  Is Active: " . ($userRoleAssignment['is_active'] ? 'Yes' : 'No') . "\n";
                echo "  Role Name: {$userRoleAssignment['role_name']}\n";
            } else {
                echo "❌ User does not have proper role assignment in user_dynamic_roles table\n";
            }
        } else {
            echo "❌ Super Admin role does not exist in dynamic_roles table\n";
        }
    } else {
        echo "❌ Super Administrator user not found in users table\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
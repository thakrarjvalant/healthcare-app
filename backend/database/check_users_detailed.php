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
    
    // Check users table
    echo "📋 Users Table Data:\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $pdo->query("SELECT id, name, email, role, verified FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    printf("%-5s %-30s %-35s %-20s %-10s\n", "ID", "Name", "Email", "Role", "Verified");
    echo str_repeat("-", 100) . "\n";
    
    foreach ($users as $user) {
        printf("%-5s %-30s %-35s %-20s %-10s\n", 
            $user['id'], 
            $user['name'], 
            $user['email'], 
            $user['role'], 
            $user['verified'] ? 'Yes' : 'No'
        );
    }
    
    echo "\n" . str_repeat("-", 100) . "\n";
    
    // Check user passwords
    echo "\n🔐 Password Hashes:\n";
    echo str_repeat("-", 100) . "\n";
    
    $stmt = $pdo->query("SELECT id, name, email, password FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($users as $user) {
        echo "User: {$user['name']} ({$user['email']})\n";
        echo "  Password Hash: {$user['password']}\n";
        echo "  Hash Length: " . strlen($user['password']) . "\n\n";
    }
    
    // Check dynamic roles
    echo "\n🎭 Dynamic Roles:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SELECT id, name, display_name FROM dynamic_roles ORDER BY id");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($roles as $role) {
        echo "  - {$role['name']} ({$role['display_name']})\n";
    }
    
    // Check user-dynamic roles assignments
    echo "\n👥 User-Dynamic Role Assignments:\n";
    echo str_repeat("-", 80) . "\n";
    
    $stmt = $pdo->query("SELECT u.name as user_name, dr.name as role_name 
                        FROM users u 
                        JOIN user_dynamic_roles udr ON u.id = udr.user_id 
                        JOIN dynamic_roles dr ON udr.role_id = dr.id 
                        WHERE udr.is_active = 1 
                        ORDER BY u.id");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($assignments as $assignment) {
        echo "  - {$assignment['user_name']} → {$assignment['role_name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
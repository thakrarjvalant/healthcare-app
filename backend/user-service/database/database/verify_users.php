<?php

require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

echo "🔍 Verifying Healthcare Management System Users\n";
echo "=" . str_repeat("=", 50) . "\n\n";

try {
    $db = DatabaseConnection::getInstance();
    echo "✅ Database connection established successfully!\n\n";
    
    // Count users
    $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "👥 Total users in database: " . $result['count'] . "\n\n";
    
    // List all users
    $stmt = $db->getConnection()->query("SELECT id, name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    
    if (count($users) > 0) {
        echo "📋 Users list:\n";
        echo str_repeat("-", 80) . "\n";
        printf("%-5s %-25s %-30s %-20s\n", "ID", "Name", "Email", "Role");
        echo str_repeat("-", 80) . "\n";
        
        foreach ($users as $user) {
            printf("%-5s %-25s %-30s %-20s\n", 
                $user['id'], 
                substr($user['name'], 0, 24), 
                substr($user['email'], 0, 29), 
                $user['role']
            );
        }
        echo str_repeat("-", 80) . "\n\n";
        
        // Check for specific roles
        $expectedRoles = ['admin', 'doctor', 'receptionist', 'patient', 'medical_coordinator'];
        echo "✅ Verification Results:\n";
        
        foreach ($expectedRoles as $role) {
            $stmt = $db->getConnection()->prepare("SELECT COUNT(*) as count FROM users WHERE role = ?");
            $stmt->execute([$role]);
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                echo "  ✅ Found {$result['count']} user(s) with role: {$role}\n";
            } else {
                echo "  ❌ No users found with role: {$role}\n";
            }
        }
        
        echo "\n🎉 User verification completed!\n";
    } else {
        echo "❌ No users found in the database.\n";
        echo "💡 Please run the seeders to populate the database.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "💡 Make sure MySQL is running and the database is set up correctly.\n";
}
<?php

require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    echo "✅ Database connection successful!\n";
    
    // Check if users table exists
    $stmt = $db->getConnection()->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Users table exists\n";
        
        // Count users
        $stmt = $db->getConnection()->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch();
        echo "👥 Total users in database: " . $result['count'] . "\n";
        
        // List all users
        $stmt = $db->getConnection()->query("SELECT id, name, email, role FROM users ORDER BY id");
        $users = $stmt->fetchAll();
        echo "📋 Users list:\n";
        foreach ($users as $user) {
            echo "  - ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
        }
    } else {
        echo "❌ Users table does not exist\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
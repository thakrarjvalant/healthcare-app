<?php
/**
 * Test script to verify database connection
 */

echo "Testing database connection...\n";

// Set environment variables for testing
putenv('DB_HOST=localhost');
putenv('DB_PORT=3306');
putenv('DB_NAME=healthcare_db');
putenv('DB_USER=healthcare_user');
putenv('DB_PASS=your_strong_password');

try {
    require_once __DIR__ . '/backend/database/DatabaseConnection.php';
    
    $db = \Database\DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
    
    // Test query
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "✅ Database connection successful!\n";
    echo "Users in database: " . $result['count'] . "\n";
    
    // Test specific user query
    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Admin user found: " . $user['name'] . " (" . $user['email'] . ") - Role: " . $user['role'] . "\n";
    } else {
        echo "❌ Admin user not found\n";
    }

    $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
    $stmt->execute(['jane.smith@example.com']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ Doctor user found: " . $user['name'] . " (" . $user['email'] . ") - Role: " . $user['role'] . "\n";
    } else {
        echo "❌ Doctor user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
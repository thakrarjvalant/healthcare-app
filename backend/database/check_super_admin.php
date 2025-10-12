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
    
    // Check if Super Administrator user exists
    echo "🔍 Checking for Super Administrator user:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, verified FROM users WHERE name = ? OR email = ?");
    $stmt->execute(['Super Administrator', 'superadmin@example.com']);
    $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($superAdmin) {
        echo "✅ Super Administrator user found:\n";
        echo "  ID: {$superAdmin['id']}\n";
        echo "  Name: {$superAdmin['name']}\n";
        echo "  Email: {$superAdmin['email']}\n";
        echo "  Role: {$superAdmin['role']}\n";
        echo "  Verified: " . ($superAdmin['verified'] ? 'Yes' : 'No') . "\n";
    } else {
        echo "❌ Super Administrator user not found in users table\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Check all users with super_admin role
    echo "\n🔍 Checking for users with super_admin role:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->prepare("SELECT id, name, email, role, verified FROM users WHERE role = ?");
    $stmt->execute(['super_admin']);
    $superAdminUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($superAdminUsers)) {
        echo "✅ Found " . count($superAdminUsers) . " user(s) with super_admin role:\n";
        foreach ($superAdminUsers as $user) {
            echo "  - {$user['name']} ({$user['email']})\n";
        }
    } else {
        echo "❌ No users found with super_admin role\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n";
    
    // Check if Super Administrator user was created by a seeder
    echo "\n🔍 Checking seeder files for Super Administrator:\n";
    echo str_repeat("-", 50) . "\n";
    
    $seederFiles = glob(__DIR__ . '/seeds/*.php');
    foreach ($seederFiles as $file) {
        $content = file_get_contents($file);
        if (strpos($content, 'Super Administrator') !== false) {
            echo "✅ Found Super Administrator in: " . basename($file) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}
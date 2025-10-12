<?php

echo "🌱 Setting up Healthcare Management System Database Locally\n";
echo "=" . str_repeat("=", 60) . "\n\n";

try {
    // Try to connect to MySQL server
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS healthcare_db");
    echo "✅ Database 'healthcare_db' created or already exists\n";
    
    // Use the database
    $pdo->exec("USE healthcare_db");
    
    // Run the migrations manually
    echo "\n🔄 Running Migrations Manually...\n";
    
    // Create users table (simplified version)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient',
            verified BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Users table created\n";
    
    // Create other essential tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dynamic_roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            color VARCHAR(7),
            icon VARCHAR(50),
            is_system_role BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Dynamic roles table created\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS feature_modules (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) UNIQUE NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            description TEXT,
            icon VARCHAR(50),
            color VARCHAR(7),
            is_core_module BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Feature modules table created\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dynamic_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) UNIQUE NOT NULL,
            display_name VARCHAR(100) NOT NULL,
            module VARCHAR(50),
            feature VARCHAR(50),
            action VARCHAR(50),
            resource VARCHAR(50) NULL,
            is_system_permission BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "✅ Dynamic permissions table created\n";
    
    // Create relationship tables
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS dynamic_role_permissions (
            role_id INT,
            permission_id INT,
            PRIMARY KEY (role_id, permission_id),
            FOREIGN KEY (role_id) REFERENCES dynamic_roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES dynamic_permissions(id) ON DELETE CASCADE
        )
    ");
    echo "✅ Role-permissions relationship table created\n";
    
    echo "\n✅ All tables created successfully!\n\n";
    
    // Insert users
    echo "🔄 Seeding Users...\n";
    
    $users = [
        [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'patient',
            'verified' => 1
        ],
        [
            'name' => 'Dr. Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'doctor',
            'verified' => 1
        ],
        [
            'name' => 'Receptionist Bob',
            'email' => 'bob.receptionist@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'receptionist',
            'verified' => 1
        ],
        [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'admin',
            'verified' => 1
        ],
        [
            'name' => 'Medical Coordinator',
            'email' => 'medical.coordinator@example.com',
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'medical_coordinator',
            'verified' => 1
        ]
    ];
    
    foreach ($users as $user) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (name, email, password, role, verified) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['name'],
            $user['email'],
            $user['password'],
            $user['role'],
            $user['verified']
        ]);
        echo "✅ User '{$user['name']}' created\n";
    }
    
    echo "\n✅ Users seeded successfully!\n\n";
    
    // Show user count
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "👥 Total users in database: " . $result['count'] . "\n";
    
    // List all users
    $stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id");
    $users = $stmt->fetchAll();
    echo "📋 Users list:\n";
    foreach ($users as $user) {
        echo "  - ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "🔑 You can now log in with:\n";
    echo "   Admin: admin@example.com / password123\n";
    echo "   Medical Coordinator: medical.coordinator@example.com / password123\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
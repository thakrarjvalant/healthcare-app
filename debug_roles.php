<?php
// Simple script to debug roles in the database

// Include the database connection
require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    $connection = $db->getConnection();
    
    echo "Connected to database successfully\n\n";
    
    // Check if the dynamic_roles table exists
    $stmt = $connection->prepare("SHOW TABLES LIKE 'dynamic_roles'");
    $stmt->execute();
    $tableExists = $stmt->fetch();
    
    if (!$tableExists) {
        echo "ERROR: dynamic_roles table does not exist!\n";
        exit(1);
    }
    
    echo "dynamic_roles table exists\n\n";
    
    // Fetch all roles
    $stmt = $connection->prepare("SELECT * FROM dynamic_roles ORDER BY name");
    $stmt->execute();
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "All roles in database:\n";
    echo "=====================\n";
    foreach ($roles as $role) {
        echo "ID: " . $role['id'] . "\n";
        echo "Name: " . $role['name'] . "\n";
        echo "Display Name: " . $role['display_name'] . "\n";
        echo "Is Active: " . ($role['is_active'] ? 'Yes' : 'No') . "\n";
        echo "Is System Role: " . ($role['is_system_role'] ? 'Yes' : 'No') . "\n";
        echo "---------------------\n";
    }
    
    // Check specifically for medical coordinator
    $stmt = $connection->prepare("SELECT * FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $medicalCoordinator = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($medicalCoordinator) {
        echo "\nMedical Coordinator Role Found:\n";
        echo "===============================\n";
        print_r($medicalCoordinator);
    } else {
        echo "\nMedical Coordinator Role NOT Found!\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
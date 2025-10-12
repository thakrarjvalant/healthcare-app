<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check users table structure
    echo "=== Users Table Structure ===\n";
    $stmt = $db->getConnection()->query('DESCRIBE users');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "{$column['Field']} ({$column['Type']})\n";
    }
    
    echo "\n=== Patient-Doctor Assignments Table ===\n";
    try {
        $stmt = $db->getConnection()->query('DESCRIBE patient_doctor_assignments');
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            echo "{$column['Field']} ({$column['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Table doesn't exist: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== Sample Data ===\n";
    // Check if we have any medical coordinators
    $stmt = $db->getConnection()->prepare("SELECT id, name, email FROM users WHERE role = 'medical_coordinator' LIMIT 1");
    $stmt->execute();
    $medicalCoordinator = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($medicalCoordinator) {
        echo "Medical Coordinator: {$medicalCoordinator['name']} ({$medicalCoordinator['email']})\n";
    } else {
        echo "No medical coordinator found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
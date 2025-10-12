<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check medical coordinators
    $stmt = $db->getConnection()->prepare("SELECT id, name, email, role FROM users WHERE role = ?");
    $stmt->execute(['medical_coordinator']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Medical Coordinators ===\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
    // Check patients
    $stmt = $db->getConnection()->prepare("SELECT id, name, email, role, assigned_doctor FROM users WHERE role = ?");
    $stmt->execute(['patient']);
    $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== Patients ===\n";
    foreach ($patients as $patient) {
        echo "ID: {$patient['id']}, Name: {$patient['name']}, Email: {$patient['email']}, Assigned Doctor: {$patient['assigned_doctor']}\n";
    }
    
    // Check doctors
    $stmt = $db->getConnection()->prepare("SELECT id, name, email, role FROM users WHERE role = ?");
    $stmt->execute(['doctor']);
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n=== Doctors ===\n";
    foreach ($doctors as $doctor) {
        echo "ID: {$doctor['id']}, Name: {$doctor['name']}, Email: {$doctor['email']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check assignments
    $stmt = $db->getConnection()->query("SELECT * FROM patient_doctor_assignments");
    $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Patient-Doctor Assignments ===\n";
    foreach ($assignments as $assignment) {
        echo "ID: {$assignment['id']}, Patient: {$assignment['patient_id']}, Doctor: {$assignment['doctor_id']}, Assigned By: {$assignment['assigned_by']}, Active: {$assignment['is_active']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
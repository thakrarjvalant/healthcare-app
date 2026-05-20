<?php

// Simple script to check feature access data in the database

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Check if role_feature_access table has data
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM role_feature_access");
    $stmt->execute();
    $result = $stmt->fetch();
    
    echo "Role-Feature Access Records: " . $result['count'] . "\n";
    
    // If there are records, show some details
    if ($result['count'] > 0) {
        $stmt = $db->prepare("SELECT rfa.*, dr.name as role_name, fm.name as module_name FROM role_feature_access rfa JOIN dynamic_roles dr ON rfa.role_id = dr.id JOIN feature_modules fm ON rfa.module_id = fm.id LIMIT 10");
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\nSample Records:\n";
        foreach ($records as $record) {
            echo "- Role: {$record['role_name']}, Module: {$record['module_name']}, Access Level: {$record['access_level']}\n";
        }
    } else {
        echo "No feature access records found.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
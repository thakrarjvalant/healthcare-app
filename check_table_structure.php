<?php

// Simple script to check table structure

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Show role_feature_access table structure
    $stmt = $db->prepare("DESCRIBE role_feature_access");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "role_feature_access table structure:\n";
    echo "====================================\n";
    foreach ($columns as $column) {
        echo "{$column['Field']} {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " . ($column['Key'] ? $column['Key'] . ' ' : '') . ($column['Default'] !== null ? "DEFAULT {$column['Default']}" : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
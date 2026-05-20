<?php

// Simple script to check feature_modules table structure

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Show feature_modules table structure
    $stmt = $db->prepare("DESCRIBE feature_modules");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "feature_modules table structure:\n";
    echo "================================\n";
    foreach ($columns as $column) {
        echo "{$column['Field']} {$column['Type']} " . ($column['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . " " . ($column['Key'] ? $column['Key'] . ' ' : '') . ($column['Default'] !== null ? "DEFAULT {$column['Default']}" : '') . "\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
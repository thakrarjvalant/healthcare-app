<?php

// Simple script to show migration records

require_once 'backend/database/DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    
    // Show all migration records
    $stmt = $db->prepare("SELECT * FROM migrations ORDER BY id");
    $stmt->execute();
    $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Migration Records:\n";
    echo "==================\n";
    foreach ($migrations as $migration) {
        echo "ID: {$migration['id']}, Migration: {$migration['migration']}, Batch: {$migration['batch']}, Created: {$migration['created_at']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
<?php
require 'config.php';
require 'DatabaseConnection.php';

use Database\DatabaseConnection;

try {
    $db = DatabaseConnection::getInstance();
    $stmt = $db->getConnection()->query('SELECT id, name, email, role FROM users');
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "=== Users ===\n";
    foreach ($users as $user) {
        echo "ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}, Role: {$user['role']}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
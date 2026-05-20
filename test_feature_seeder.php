<?php

// Test script to run just the RoleFeatureAccessSeeder

require_once 'backend/database/DatabaseConnection.php';
require_once 'backend/database/seeds/RoleFeatureAccessSeeder.php';

use Database\DatabaseConnection;

try {
    echo "Testing RoleFeatureAccessSeeder...\n";
    
    $seeder = new RoleFeatureAccessSeeder();
    $seeder->seed();
    
    echo "✅ RoleFeatureAccessSeeder test completed!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
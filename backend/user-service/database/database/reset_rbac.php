<?php

require_once 'DatabaseConnection.php';
require_once 'seeds/DynamicRBACSeeder.php';

use Database\DatabaseConnection;

echo "🗑️ Resetting RBAC data...\n";

try {
    $seeder = new DynamicRBACSeeder();
    $seeder->unseed();
    
    echo "🌱 Re-seeding RBAC data...\n";
    $seeder->seed();
    
    echo "✅ RBAC reset and re-seeding completed successfully!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
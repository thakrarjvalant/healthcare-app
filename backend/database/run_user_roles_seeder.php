<?php
require 'config.php';
require 'DatabaseConnection.php';
require 'seeds/UserDynamicRolesSeeder.php';

try {
    echo "Running UserDynamicRolesSeeder...\n";
    $seeder = new UserDynamicRolesSeeder();
    $seeder->seed();
    echo "UserDynamicRolesSeeder completed successfully!\n";
} catch (Exception $e) {
    echo "Error running UserDynamicRolesSeeder: " . $e->getMessage() . "\n";
}
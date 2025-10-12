<?php
// Test script to verify user permissions

// Database connection
$host = 'db'; // Use docker service name
$dbname = 'healthcare_db';
$username = 'root';
$password = 'root_password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test Receptionist user (ID 3)
    echo "=== Receptionist User (ID 3) ===\n";
    $stmt = $pdo->prepare("SELECT u.name, u.email, dr.name as role_name FROM users u JOIN user_dynamic_roles udr ON u.id = udr.user_id JOIN dynamic_roles dr ON udr.role_id = dr.id WHERE u.id = 3");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($user);
    
    // Get Receptionist permissions
    $stmt = $pdo->prepare("SELECT dp.name as permission_name FROM dynamic_roles dr JOIN dynamic_role_permissions drp ON dr.id = drp.role_id JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE dr.name = 'receptionist' ORDER BY dp.name");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Receptionist Permissions:\n";
    foreach ($permissions as $perm) {
        echo "  - " . $perm['permission_name'] . "\n";
    }
    
    echo "\n";
    
    // Test Medical Coordinator user (ID 30)
    echo "=== Medical Coordinator User (ID 30) ===\n";
    $stmt = $pdo->prepare("SELECT u.name, u.email, dr.name as role_name FROM users u JOIN user_dynamic_roles udr ON u.id = udr.user_id JOIN dynamic_roles dr ON udr.role_id = dr.id WHERE u.id = 30");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($user);
    
    // Get Medical Coordinator permissions
    $stmt = $pdo->prepare("SELECT dp.name as permission_name FROM dynamic_roles dr JOIN dynamic_role_permissions drp ON dr.id = drp.role_id JOIN dynamic_permissions dp ON drp.permission_id = dp.id WHERE dr.name = 'medical_coordinator' ORDER BY dp.name");
    $stmt->execute();
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Medical Coordinator Permissions:\n";
    foreach ($permissions as $perm) {
        echo "  - " . $perm['permission_name'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
<?php
require_once 'backend/database/DatabaseConnection.php';

$db = \Database\DatabaseConnection::getInstance();
$connection = $db->getConnection();

// Check if medical_coordinator role exists
$stmt = $connection->prepare("SELECT * FROM dynamic_roles WHERE name = 'medical_coordinator'");
$stmt->execute();
$role = $stmt->fetch(PDO::FETCH_ASSOC);

if ($role) {
    echo "Medical Coordinator role found:\n";
    print_r($role);
} else {
    echo "Medical Coordinator role NOT found in database\n";
}

// List all roles
echo "\nAll roles in database:\n";
$stmt = $connection->prepare("SELECT * FROM dynamic_roles ORDER BY name");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($roles as $r) {
    echo "- " . $r['name'] . " (" . $r['display_name'] . ")\n";
}
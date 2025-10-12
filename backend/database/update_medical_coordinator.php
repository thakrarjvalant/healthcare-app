<?php

require_once __DIR__ . '/DatabaseConnection.php';

use Database\DatabaseConnection;

$db = DatabaseConnection::getInstance();

// Update the Medical Coordinator role with the correct description
$stmt = $db->prepare("UPDATE dynamic_roles SET 
    display_name = 'Medical Coordinator',
    description = 'Manages all appointment scheduling, rescheduling, and cancellations system-wide, resolves slot conflicts, oversees patient assignment to clinicians, and acts as liaison between clinical and administrative teams with limited audited access to patient histories',
    color = '#20c997',
    icon = 'user-clock'
    WHERE name = 'medical_coordinator'");

$result = $stmt->execute();

if ($result) {
    echo "✅ Medical Coordinator role updated successfully!\n";
    
    // Verify the update
    $stmt = $db->prepare("SELECT id, name, display_name, description, color, icon FROM dynamic_roles WHERE name = 'medical_coordinator'");
    $stmt->execute();
    $role = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($role) {
        echo "Updated role details:\n";
        echo "  ID: " . $role['id'] . "\n";
        echo "  Name: " . $role['name'] . "\n";
        echo "  Display Name: " . $role['display_name'] . "\n";
        echo "  Description: " . $role['description'] . "\n";
        echo "  Color: " . $role['color'] . "\n";
        echo "  Icon: " . $role['icon'] . "\n";
    }
} else {
    echo "❌ Failed to update Medical Coordinator role\n";
}
<?php

require_once 'DatabaseConnection.php';

use Database\DatabaseConnection;

$db = DatabaseConnection::getInstance();

echo "=== Receptionist Permissions ===\n";
$stmt = $db->prepare('SELECT dp.name, dp.display_name FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id JOIN dynamic_roles dr ON drp.role_id = dr.id WHERE dr.name = ? AND drp.is_active = 1');
$stmt->execute(['receptionist']);
while ($row = $stmt->fetch()) {
    echo $row['display_name'] . " (" . $row['name'] . ")\n";
}

echo "\n=== Medical Coordinator Permissions ===\n";
$stmt = $db->prepare('SELECT dp.name, dp.display_name FROM dynamic_role_permissions drp JOIN dynamic_permissions dp ON drp.permission_id = dp.id JOIN dynamic_roles dr ON drp.role_id = dr.id WHERE dr.name = ? AND drp.is_active = 1');
$stmt->execute(['medical_coordinator']);
while ($row = $stmt->fetch()) {
    echo $row['display_name'] . " (" . $row['name'] . ")\n";
}

echo "\n=== Feature Module Access ===\n";
// Feature module access is no longer used

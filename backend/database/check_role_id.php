<?php

require_once __DIR__ . '/DatabaseConnection.php';

use Database\DatabaseConnection;

$db = DatabaseConnection::getInstance();
$stmt = $db->prepare("SELECT id, name FROM dynamic_roles WHERE name = 'super_admin'");
$stmt->execute();
$role = $stmt->fetch(\PDO::FETCH_ASSOC);

echo "Super admin role: ";
print_r($role);
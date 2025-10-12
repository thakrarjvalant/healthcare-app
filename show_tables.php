<?php
require_once 'backend/database/DatabaseConnection.php';
$db = \Database\DatabaseConnection::getInstance();
$connection = $db->getConnection();
$stmt = $connection->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'healthcare_db'");
$stmt->execute();
$tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo "All tables in healthcare_db:\n";
foreach ($tables as $table) {
    echo "- " . $table['TABLE_NAME'] . "\n";
}

// Also check users
echo "\nUsers in database:\n";
$stmt = $connection->prepare("SELECT id, name, email, role FROM users ORDER BY id");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $user) {
    echo "- ID: " . $user['id'] . ", Name: " . $user['name'] . ", Email: " . $user['email'] . ", Role: " . $user['role'] . "\n";
}
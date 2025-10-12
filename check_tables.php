<?php
require_once '/tmp/database/DatabaseConnection.php';
$db = \Database\DatabaseConnection::getInstance();
$connection = $db->getConnection();
$stmt = $connection->prepare("SELECT table_name FROM information_schema.tables WHERE table_schema = 'healthcare_db' AND table_name LIKE '%coordinator%'");
$stmt->execute();
$tables = $stmt->fetchAll();
if (empty($tables)) {
    echo 'No coordinator tables found';
} else {
    foreach ($tables as $table) {
        echo 'Found table: ' . $table['table_name'] . PHP_EOL;
    }
}
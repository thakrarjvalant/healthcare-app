<?php

// Database configuration for the Healthcare Management System
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_NAME') ?: 'healthcare_db',
    'username' => getenv('DB_USER') ?: 'healthcare_user',
    'password' => getenv('DB_PASS') ?: 'your_strong_password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
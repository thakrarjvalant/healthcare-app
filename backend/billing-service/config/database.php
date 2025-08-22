<?php
// Database configuration for Billing Service
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_NAME') ?: 'healthcare_billing',
    'username' => getenv('DB_USER') ?: 'billing_service',
    'password' => getenv('DB_PASS') ?: 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
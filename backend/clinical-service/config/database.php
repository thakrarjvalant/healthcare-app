<?php
// Database configuration for Clinical Service
return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: 3306,
    'database' => getenv('DB_NAME') ?: 'healthcare_clinical',
    'username' => getenv('DB_USER') ?: 'clinical_service',
    'password' => getenv('DB_PASS') ?: 'password',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
];
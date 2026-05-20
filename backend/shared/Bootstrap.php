<?php
// File: backend/shared/bootstrap.php

// DO NOT require vendor/autoload.php here - each service loads its own autoloader
// Include shared components directly
require_once __DIR__ . '/DatabaseConnection.php';

// Set error reporting
error_reporting(E_ALL);

// CRITICAL: Turn off display_errors to prevent HTML error output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('html_errors', 0);
ini_set('log_errors', 1);

// Set default timezone
date_default_timezone_set('UTC');

// Ensure we're always returning JSON, even for PHP errors
set_error_handler(function($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        if (function_exists('jsonResponse')) {
            jsonResponse([
                'message' => 'Internal server error',
                'error' => $message,
            ], 500);
        }
    }
});

set_exception_handler(function($exception) {
    if (function_exists('jsonResponse')) {
        jsonResponse([
            'message' => 'Internal server error',
            'error' => $exception->getMessage(),
        ], 500);
    }
});

// Helper function for JSON responses (only define once)
if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}

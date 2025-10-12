<?php
// Minimal API router for clinical-service

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../shared/Bootstrap.php';

header('Content-Type: application/json');

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple route handling
if ($method === 'GET') {
    if ($path === '/') {
        jsonResponse(['message' => 'clinical service working', 'service' => 'clinical-service']);
    } elseif ($path === '/records') {
        jsonResponse(['message' => 'list records']);
    } else {
        jsonResponse(['message' => 'Not Found'], 404);
    }
} else {
    jsonResponse(['message' => 'Method not allowed'], 405);
}
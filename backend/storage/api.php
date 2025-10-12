<?php
// Minimal API router for storage service

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../shared/Bootstrap.php';

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$routes = [
    'POST' => [
        '#^/upload$#' => function() { jsonResponse(['message'=>'upload file']); },
    ],
    'GET' => [
        '#^/files/(\w+)$#' => function($m) { jsonResponse(['message'=>'get file','id'=>$m[1]]); },
    ],
    'DELETE' => [
        '#^/files/(\w+)$#' => function($m) { jsonResponse(['message'=>'delete file','id'=>$m[1]]); },
    ],
];

if (!isset($routes[$method])) jsonResponse(['message'=>'Method not allowed'], 405);

foreach ($routes[$method] as $route => $handler) {
    if (preg_match($route, $path, $matches)) {
        array_shift($matches);
        $handler($matches);
    }
}

jsonResponse(['message'=>'Not Found'], 404);
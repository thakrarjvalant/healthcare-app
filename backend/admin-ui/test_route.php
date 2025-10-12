<?php
// Simple test to check route matching

$path = '/api/medical-coordinator/patients';
$method = 'GET';

echo "Testing path: $path with method: $method\n";

$routes = [
    'GET' => [
        '#^/medical-coordinator/patients$#' => 'getPatients',
        '#^/medical-coordinator/doctors$#' => 'getDoctors',
    ]
];

if (!isset($routes[$method])) {
    echo "Method not allowed\n";
    exit;
}

foreach ($routes[$method] as $route => $handler) {
    echo "Checking route: $route\n";
    if (preg_match($route, $path, $matches)) {
        echo "Route matched: $route -> $handler\n";
        echo "Matches: " . json_encode($matches) . "\n";
        exit;
    }
}

echo "No route matched\n";
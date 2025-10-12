<?php

// Minimal API router for user-service

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../shared/Bootstrap.php';

use Database\DatabaseConnection;
use UserService\Controllers\UserController;
use UserService\UserService;

// Log all requests for debugging
error_log('User service request: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);

// Initialize services
try {
    $db = DatabaseConnection::getInstance();
    $userService = new UserService($db->getConnection());
    $userController = new UserController($userService);
} catch (Exception $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    error_log('Database connection failed trace: ' . $e->getTraceAsString());
    jsonResponse(['message' => 'Internal server error', 'error' => $e->getMessage()], 500);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Debug logging
error_log('Request method: ' . $method);
error_log('Request URI: ' . $_SERVER['REQUEST_URI']);
error_log('Parsed path: ' . $path);
error_log('Query string: ' . ($_SERVER['QUERY_STRING'] ?? 'none'));

// Handle nginx rewrite format where path comes as query parameter
if ($path === '/api.php' && isset($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    // If query string starts with '/', it's the rewritten path
    if (strpos($query_string, '/') === 0) {
        $path = $query_string;
        error_log('Rewritten path: ' . $path);
    }
}

// Define routes as regex => handler
$routes = [
    'POST' => [
        '#^/register$#' => function() use ($userController) { 
            // Get POST data
            $rawInput = file_get_contents('php://input');
            error_log('Register raw input: ' . $rawInput);
            // Handle escaped quotes in JSON
            $rawInput = stripslashes($rawInput);
            $input = json_decode($rawInput, true);
            if (!$input) {
                error_log('Register invalid JSON data: ' . $rawInput);
                jsonResponse(['message' => 'Invalid JSON data'], 400);
            }
            
            $result = $userController->register($input);
            jsonResponse($result['data'], $result['status']);
        },
        '#^/login$#' => function() use ($userController) { 
            error_log('Login route handler called');
            // Get POST data
            $rawInput = file_get_contents('php://input');
            error_log('Login raw input: ' . $rawInput);
            // Handle escaped quotes in JSON
            $rawInput = stripslashes($rawInput);
            error_log('Login raw input after stripslashes: ' . $rawInput);
            $input = json_decode($rawInput, true);
            error_log('Login parsed input: ' . json_encode($input));
            if (!$input) {
                error_log('Login invalid JSON data: ' . $rawInput);
                jsonResponse(['message' => 'Invalid JSON data'], 400);
            }
            
            $result = $userController->login($input);
            error_log('Login result: ' . json_encode($result));
            jsonResponse($result['data'], $result['status']);
        },
    ],
    'GET' => [
        '#^/users$#' => function() use ($userController) { 
            // Get all users
            $request = []; // In a real implementation, this would contain request data
            $result = $userController->getAllUsers($request);
            jsonResponse($result['data'], $result['status']);
        },
        '#^/users/(\d+)$#' => function($matches) use ($userController) { 
            // Get user by ID
            $userId = $matches[1]; // Use $matches[1] for the first captured group
            $request = []; // In a real implementation, this would contain request data
            $result = $userController->getUserById($request, $userId);
            jsonResponse($result['data'], $result['status']);
        },
        '#^/me$#' => function() use ($userController) { 
            // Get current user profile
            $request = []; // In a real implementation, this would contain request data like headers with auth token
            $result = $userController->getProfile($request);
            jsonResponse($result['data'], $result['status']);
        },
    ],
    'PUT' => [
        '#^/users/(\d+)$#' => function($matches) use ($userController) { 
            // Update user - still mock for now
            jsonResponse(['message'=>'update user','id'=>$matches[1]], 200); // Use $matches[1]
        },
    ],
];

error_log('Checking routes for method: ' . $method);
if (!isset($routes[$method])) {
    error_log('Method not allowed: ' . $method);
    jsonResponse(['message'=>'Method not allowed'], 405);
}

error_log('Path to match: ' . $path);
foreach ($routes[$method] as $route => $handler) {
    error_log('Checking route: ' . $route);
    if (preg_match($route, $path, $matches)) {
        error_log('Route matched: ' . $route);
        // We need to pass the captured groups to the handler
        $handler($matches);
        // handler should exit
    }
}

error_log('No route matched for path: ' . $path);
jsonResponse(['message'=>'Not Found'], 404);
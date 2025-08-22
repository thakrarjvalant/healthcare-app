<?php

// User Service API Endpoints

use UserService\Controllers\UserController;
use UserService\Middleware\AuthMiddleware;

// Get request data
$request = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'body' => json_decode(file_get_contents('php://input'), true) ?? []
];

// Route handling
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Initialize services and controllers
// In a real implementation, you would use a dependency injection container
$userService = new UserService\UserService(null); // Database connection would be passed here
$userController = new UserController($userService);

// Route matching
switch ($uri) {
    case '/api/users/register':
        if ($method === 'POST') {
            $response = $userController->register($request['body']);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    case '/api/users/login':
        if ($method === 'POST') {
            $response = $userController->login($request['body']);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    case '/api/users/profile':
        if ($method === 'GET') {
            $authResult = AuthMiddleware::requireAuth($request);
            if ($authResult['status'] !== 200) {
                http_response_code($authResult['status']);
                echo json_encode($authResult['data']);
                exit;
            }
            
            $response = $userController->getProfile($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
        exit;
}

// If no route matched
http_response_code(404);
echo json_encode(['message' => 'Endpoint not found']);
<?php
// Minimal API router for appointment-service

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
        jsonResponse(['message' => 'appointment service working', 'service' => 'appointment-service']);
    } elseif ($path === '/slots') {
        jsonResponse(['message' => 'list slots']);
    } else {
        jsonResponse(['message' => 'Not Found'], 404);
    }
} else {
    jsonResponse(['message' => 'Method not allowed'], 405);
}

// Appointment Service API Endpoints

require __DIR__ . '/vendor/autoload.php';

use AppointmentService\Controllers\AppointmentController;
use UserService\Middleware\AuthMiddleware;

// Get request data
$request = [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'],
    'headers' => getallheaders(),
    'query' => $_GET,
    'body' => json_decode(file_get_contents('php://input'), true) ?? [],
    'params' => []
];

// Route handling
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Initialize services and controllers
// For testing - create a simple mock service
try {
    $appointmentService = new AppointmentService\AppointmentService(null); // Database connection would be passed here
    $appointmentController = new AppointmentController($appointmentService);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Service initialization failed', 'message' => $e->getMessage()]);
    exit;
}

// Route matching
switch ($uri) {
    case '/appointments':
        if ($method === 'POST') {
            $response = $appointmentController->bookAppointment($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        } else if ($method === 'GET') {
            // Simple response for testing
            http_response_code(200);
            echo json_encode(['message' => 'appointments endpoint working', 'service' => 'appointment-service']);
            exit;
        }
        break;
        
    case '/appointments/availability':
        if ($method === 'GET') {
            $response = $appointmentController->getAvailableSlots($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    case (preg_match('/\/appointments\/(\d+)\/status/', $uri, $matches) ? true : false):
        if ($method === 'PUT') {
            $request['params']['id'] = $matches[1];
            $response = $appointmentController->updateAppointmentStatus($request);
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

// ReactPHP bootstrap removed so this router can be served by standard PHP runtimes
// (php -S, PHP-FPM/nginx, or `php artisan serve`)

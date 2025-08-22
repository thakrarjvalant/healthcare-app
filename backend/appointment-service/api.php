<?php

// Appointment Service API Endpoints

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
// In a real implementation, you would use a dependency injection container
$appointmentService = new AppointmentService\AppointmentService(null); // Database connection would be passed here
$appointmentController = new AppointmentController($appointmentService);

// Route matching
switch ($uri) {
    case '/api/appointments':
        if ($method === 'POST') {
            $response = $appointmentController->bookAppointment($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        } else if ($method === 'GET') {
            $response = $appointmentController->getUserAppointments($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    case '/api/appointments/availability':
        if ($method === 'GET') {
            $response = $appointmentController->getAvailableSlots($request);
            http_response_code($response['status']);
            echo json_encode($response['data']);
            exit;
        }
        break;
        
    case (preg_match('/\/api\/appointments\/(\d+)\/status/', $uri, $matches) ? true : false):
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
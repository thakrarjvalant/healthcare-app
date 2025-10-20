<?php
// File: backend/api-gateway/gateway.php

error_reporting(E_ALL);

// CRITICAL: Turn off display_errors to prevent HTML error output
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
ini_set('html_errors', 0);
ini_set('log_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Log all requests for debugging
error_log('API Gateway request: ' . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI']);

// Simple PHP API Gateway (cURL-based)
// Map incoming /api/... prefixes to backend service origins using Docker service names.
// Place this file where your webserver/PHP-FPM can execute it.

$routes = [
    '/api/users'              => 'http://user-service:8001',
    '/api/appointments'       => 'http://appointment-service:8002',
    '/api/clinical'           => 'http://clinical-service:8003',
    '/api/notifications'      => 'http://notification-service:8004',
    '/api/billing'            => 'http://billing-service:8005',
    '/api/storage'            => 'http://storage-service:8006',
    '/api/admin'              => 'http://admin-ui:8007',
    '/api/medical-coordinator' => 'http://admin-ui:8007',
];

// Health endpoint
if (php_sapi_name() !== 'cli' && (($_SERVER['REQUEST_METHOD'] === 'GET') && ($_SERVER['REQUEST_URI'] === '/health' || $_SERVER['REQUEST_URI'] === '/health/'))) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'timestamp' => time()]);
    exit;
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Debug logging
error_log("Request URI: " . $request_uri);
error_log("Parsed path: " . $path);

// Handle nginx rewrite format where path comes as query parameter
if ($path === '/gateway.php' && isset($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    // If query string starts with '/', it's the rewritten path
    if (strpos($query_string, '/') === 0) {
        $path = $query_string;
        $request_uri = $query_string;
        error_log("Rewritten path: " . $path);
    }
}

// Route the request to the appropriate service
$service_url = null;
foreach ($routes as $route => $base_url) {
    error_log("Checking route: " . $route . " against path: " . $path);
    if (strpos($path, $route) === 0) {
        // For the admin and medical-coordinator routes, we need to strip the /api prefix
        if ($route === '/api/admin' || $route === '/api/medical-coordinator') {
            // Strip /api prefix and send the remaining path to the admin service
            $remaining_path = substr($path, 4); // Remove '/api' prefix
            $service_url = $base_url . $remaining_path;
        } else {
            // For other routes, get the remaining path after the route
            $remaining_path = substr($path, strlen($route));
            // Build the service URL with just the remaining path
            $service_url = $base_url . $remaining_path;
        }
        error_log("Matched route: " . $route . ", service URL: " . $service_url);
        break;
    }
}

if (!$service_url) {
    error_log("No matching route found for path: " . $path);
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Not Found', 'path' => $path]);
    exit();
}

$ch = curl_init($service_url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $_SERVER['REQUEST_METHOD']);

// Use getallheaders() if available, otherwise fallback to parsing $_SERVER
$headers = function_exists('getallheaders') ? getallheaders() : [];
if (empty($headers)) {
    // Manually parse headers from $_SERVER
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            $header_name = str_replace('HTTP_', '', $key);
            $header_name = str_replace('_', '-', $header_name);
            $header_name = strtolower($header_name);
            $headers[$header_name] = $value;
        }
    }
}

$header_array = [];
foreach ($headers as $key => $value) {
    if ($key !== 'Host') {
        $header_array[] = "$key: $value";
    }
}
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_array);

// Disable automatic decompression to handle it manually
curl_setopt($ch, CURLOPT_ENCODING, '');

if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH'])) {
    $post_data = file_get_contents('php://input');
    error_log("POST data: " . $post_data);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
}

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$header = substr($response, 0, $header_size);
$body = substr($response, $header_size);

// Log curl errors
if (curl_errno($ch)) {
    $curl_error = curl_error($ch);
    error_log("cURL error: " . $curl_error);
}

curl_close($ch);

error_log("Service response HTTP code: " . $http_code);
error_log("Service response headers: " . $header);
error_log("Service response body: " . $body);

http_response_code($http_code);

// Parse headers to check for encoding
$contentEncoding = null;
$contentType = 'application/json'; // Default to JSON
foreach (explode("\r\n", $header) as $header_line) {
    if (stripos($header_line, 'Content-Encoding:') === 0) {
        $contentEncoding = trim(substr($header_line, 17));
        error_log("Content encoding: " . $contentEncoding);
    } elseif (stripos($header_line, 'Content-Type:') === 0) {
        $contentType = trim(substr($header_line, 13));
        error_log("Content type: " . $contentType);
        header($header_line);
    } elseif (stripos($header_line, 'Authorization:') === 0) {
        header($header_line);
    }
}

// Handle decompression if needed
if ($contentEncoding === 'gzip') {
    $body = gzdecode($body);
    error_log("Decompressed gzip body");
} elseif ($contentEncoding === 'deflate') {
    $body = gzinflate($body);
    error_log("Decompressed deflate body");
}

// Ensure we always return JSON for API endpoints
if (strpos($path, '/api/') === 0 && strpos($contentType, 'application/json') === false && !empty($body)) {
    // If it's not JSON but we expected JSON, try to convert or provide proper error
    if (strpos($body, '<') === 0) {
        // It's HTML, return a proper JSON error
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Service unavailable', 'message' => 'The service returned an HTML response instead of JSON', 'details' => substr($body, 0, 200)]);
        exit();
    }
}

echo $body;
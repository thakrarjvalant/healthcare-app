<?php
// Test script to debug routing

$path = '/api/medical-coordinator/patients';
echo "Testing path: " . $path . "\n";

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

$service_url = null;
foreach ($routes as $route => $base_url) {
    echo "Checking route: " . $route . " against path: " . $path . "\n";
    if (strpos($path, $route) === 0) {
        echo "Route matched: " . $route . "\n";
        // For the admin and medical-coordinator routes, we need to send the full path to the admin service
        if ($route === '/api/admin' || $route === '/api/medical-coordinator') {
            // For these routes, send the full path to the admin service
            $service_url = $base_url . $path;
        } else {
            // For other routes, get the remaining path after the route
            $remaining_path = substr($path, strlen($route));
            // Build the service URL with just the remaining path
            $service_url = $base_url . $remaining_path;
        }
        echo "Service URL: " . $service_url . "\n";
        break;
    }
}

if (!$service_url) {
    echo "No matching route found\n";
} else {
    echo "Final service URL: " . $service_url . "\n";
}
?>
<?php
// Minimal API router for admin-ui backend

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../shared/Bootstrap.php';
require __DIR__ . '/controllers/AdminController.php';
require __DIR__ . '/controllers/RoleController.php';
require __DIR__ . '/controllers/EscalationController.php';
require __DIR__ . '/controllers/MedicalCoordinatorController.php';

// Define development mode constant if not already defined
if (!defined('DEVELOPMENT_MODE')) {
    define('DEVELOPMENT_MODE', false);
}

// Validate JWT token and extract user information from database
function validateJwtToken($token) {
    error_log("Validating token: " . $token);
    
    // Connect to database to fetch user information
    try {
        // Get database connection
        $db = \Database\DatabaseConnection::getInstance();
        $pdo = $db->getConnection();
        
        // Import JWT library
        require_once __DIR__ . '/../shared/vendor/autoload.php';
        
        // Validate JWT token
        $jwtSecret = getenv('JWT_SECRET') ?: 'healthcare_app_secret_key_2023';
        $payload = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtSecret, 'HS256'));
        
        // Convert stdClass to array
        $payload = json_decode(json_encode($payload), true);
        
        // Extract user ID from payload
        $userId = $payload['user_id'];
        error_log("Extracted user ID: " . $userId);
        
        // Fetch user information from database
        error_log("Fetching user from database with ID: " . $userId);
        $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? AND email = ?");
        $stmt->execute([$userId, $payload['email']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($user) {
            error_log("User found: " . json_encode($user));
            return [
                'id' => $user['id'],
                'user_id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role']
            ];
        } else {
            error_log("User not found in database");
        }
        
        return null;
    } catch (Exception $e) {
        error_log("JWT validation error: " . $e->getMessage());
        return null;
    }
}

// Simple authentication middleware
function authenticate() {
    // Try to get Authorization header from getallheaders()
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
    
    $authHeader = $headers['authorization'] ?? $headers['Authorization'] ?? '';
    
    // If not found, try to get it from $_SERVER
    if (empty($authHeader)) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    }
    
    error_log("Headers received: " . json_encode($headers));
    error_log("Auth header: " . $authHeader);
    
    if (strpos($authHeader, 'Bearer ') === 0) {
        $token = substr($authHeader, 7);
        error_log("Token: " . $token);
        
        // Handle actual tokens - in a real implementation, you would validate the token
        // For now, we'll check for specific tokens and return appropriate users
        // In production, you would validate the JWT token and extract user info
        
        error_log("Calling validateJwtToken with token: " . $token);
        // Validate JWT token and extract user information
        $user = validateJwtToken($token);
        error_log("validateJwtToken returned: " . json_encode($user));
        if ($user) {
            return $user;
        }
        
        // Default to null for invalid tokens
        return null;
    }
    
    // If no token provided, but this is for development, we can allow access
    // This is only for development purposes
    if (DEVELOPMENT_MODE) {
        // In development mode, fetch the first user with a medical_coordinator role from the database
        try {
            $db = \Database\DatabaseConnection::getInstance();
            $pdo = $db->getConnection();
            
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE role = 'medical_coordinator' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return [
                    'id' => $user['id'],
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            }
            
            // If no medical coordinator found, fall back to admin
            $stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE role = 'admin' LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($user) {
                return [
                    'id' => $user['id'],
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
            }
        } catch (Exception $e) {
            error_log("Development mode user fetch error: " . $e->getMessage());
        }
        
        // If no users found with proper roles, return null to indicate authentication failure
        // This is better than using hardcoded fallback values
        return null;
    }
    
    return null;
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Handle nginx rewrite format where path comes as query parameter
if ($path === '/api.php' && isset($_SERVER['QUERY_STRING'])) {
    $query_string = $_SERVER['QUERY_STRING'];
    // If query string starts with '/', it's the rewritten path
    if (strpos($query_string, '/') === 0) {
        $path = $query_string;
    }
}

// Parse request body for POST/PUT requests
$requestBody = null;
if (in_array($method, ['POST', 'PUT'])) {
    $requestBody = json_decode(file_get_contents('php://input'), true);
}

// Try to get Authorization header from getallheaders()
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

// Create controller instances
$adminController = new \AdminUI\Controllers\AdminController();
$roleController = new \AdminUI\Controllers\RoleController();
$escalationController = new \AdminUI\Controllers\EscalationController();
$medicalCoordinatorController = new \AdminUI\Controllers\MedicalCoordinatorController();

// Get authenticated user
$user = authenticate();

// Add user info to request
$request = [
    'method' => $method,
    'path' => $path,
    'body' => $requestBody,
    'user' => $user,
    'params' => [],
    'headers' => $headers
];

$routes = [
    'GET' => [
        '#^/admin/dashboard$#' => function() use ($adminController, $request) { 
            return $adminController->getDashboard($request); 
        },
        '#^/admin/users$#' => function() use ($adminController, $request) { 
            return $adminController->getUsers($request); 
        },
        '#^/admin/users/(\d+)/roles$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->getUserRoles($request); 
        },
        '#^/admin/roles$#' => function() use ($roleController, $request) { 
            return $roleController->getAllRoles($request); 
        },
        '#^/admin/roles/(\d+)/permissions$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->getRolePermissions($request); 
        },
        '#^/admin/roles/(\d+)/features$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->getRoleFeatureAccess($request); 
        },
        '#^/admin/modules$#' => function() use ($roleController, $request) { 
            return $roleController->getFeatureModules($request); 
        },
        '#^/admin/permissions$#' => function() use ($roleController, $request) { 
            return $roleController->getAllPermissions($request); 
        },
        '#^/admin/escalations$#' => function() use ($escalationController, $request) { 
            return $escalationController->getAllEscalations($request); 
        },
        '#^/admin/escalations/(\d+)$#' => function($matches) use ($escalationController, $request) { 
            $request['params']['id'] = $matches[1];
            return $escalationController->getEscalation($request); 
        },
        '#^/admin/escalation-categories$#' => function() use ($escalationController, $request) { 
            return $escalationController->getCategories($request); 
        },
        '#^/admin/escalation-statuses$#' => function() use ($escalationController, $request) { 
            return $escalationController->getStatuses($request); 
        },
        '#^/admin/audit-logs$#' => function() use ($adminController, $request) { 
            return $adminController->getAuditLogs($request); 
        },
        // Medical Coordinator endpoints
        '#^/medical-coordinator/patients$#' => function() use ($medicalCoordinatorController, $request) { 
            return $medicalCoordinatorController->getPatients($request); 
        },
        '#^/medical-coordinator/doctors$#' => function() use ($medicalCoordinatorController, $request) { 
            return $medicalCoordinatorController->getDoctors($request); 
        },
        '#^/medical-coordinator/patients/(\d+)/history$#' => function($matches) use ($medicalCoordinatorController, $request) { 
            $request['params']['patient_id'] = $matches[1];
            return $medicalCoordinatorController->getPatientLimitedHistory($request); 
        },
        '#^/medical-coordinator/patients/(\d+)/assignments$#' => function($matches) use ($medicalCoordinatorController, $request) { 
            $request['params']['patient_id'] = $matches[1];
            return $medicalCoordinatorController->getPatientAssignmentHistory($request); 
        },
    ],
    'POST' => [
        '#^/admin/users$#' => function() use ($adminController, $request) { 
            return $adminController->createUser($request); 
        },
        '#^/admin/roles$#' => function() use ($roleController, $request) { 
            return $roleController->createRole($request); 
        },
        '#^/admin/roles/(\d+)/permissions$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->assignPermissionToRole($request); 
        },
        '#^/admin/roles/(\d+)/features$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->updateRoleFeatureAccess($request); 
        },
        '#^/admin/escalations$#' => function() use ($escalationController, $request) { 
            return $escalationController->createEscalation($request); 
        },
        '#^/admin/escalations/(\d+)/comments$#' => function($matches) use ($escalationController, $request) { 
            $request['params']['id'] = $matches[1];
            return $escalationController->addComment($request); 
        },
        // Medical Coordinator endpoints
        '#^/medical-coordinator/assignments$#' => function() use ($medicalCoordinatorController, $request) { 
            return $medicalCoordinatorController->assignPatientToDoctor($request); 
        },
    ],
    'PUT' => [
        '#^/admin/users/(\d+)$#' => function($matches) use ($adminController, $request) { 
            $request['params']['id'] = $matches[1];
            return $adminController->updateUser($request); 
        },
        '#^/admin/roles/(\d+)$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->updateRole($request); 
        },
        '#^/admin/escalations/(\d+)$#' => function($matches) use ($escalationController, $request) { 
            $request['params']['id'] = $matches[1];
            return $escalationController->updateEscalation($request); 
        },
        '#^/admin/settings$#' => function() use ($adminController, $request) { 
            return $adminController->updateSettings($request); 
        },
    ],
    'DELETE' => [
        '#^/admin/users/(\d+)$#' => function($matches) use ($adminController, $request) { 
            $request['params']['id'] = $matches[1];
            return $adminController->deleteUser($request); 
        },
        '#^/admin/roles/(\d+)$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            return $roleController->deleteRole($request); 
        },
        '#^/admin/roles/(\d+)/permissions/(\d+)$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            $request['params']['permission_id'] = $matches[2];
            return $roleController->removePermissionFromRole($request); 
        },
        '#^/admin/roles/(\d+)/features/(\d+)$#' => function($matches) use ($roleController, $request) { 
            $request['params']['id'] = $matches[1];
            $request['params']['module_id'] = $matches[2];
            return $roleController->removeRoleFeatureAccess($request); 
        },
        '#^/admin/escalations/(\d+)$#' => function($matches) use ($escalationController, $request) { 
            $request['params']['id'] = $matches[1];
            return $escalationController->deleteEscalation($request); 
        },
    ],
];

if (!isset($routes[$method])) {
    jsonResponse(['message'=>'Method not allowed'], 405);
}

// Debug logging
error_log('Checking routes for method: ' . $method);
error_log('Path to match: ' . $path);

foreach ($routes[$method] as $route => $handler) {
    error_log('Checking route: ' . $route);
    if (preg_match($route, $path, $matches)) {
        error_log('Route matched: ' . $route);
        error_log('Matches: ' . json_encode($matches));
        
        try {
            // Pass the matches to the handler
            $result = $handler($matches);
            jsonResponse($result, $result['status'] ?? 200);
        } catch (Exception $e) {
            error_log('Exception in route handler: ' . $e->getMessage());
            jsonResponse(['message' => 'Internal server error', 'error' => $e->getMessage()], 500);
        }
        exit; // Make sure we exit after handling the request
    }
}

error_log('No route matched for path: ' . $path);
jsonResponse(['message'=>'Not Found'], 404);
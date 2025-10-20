<?php

namespace UserService\Middleware;

// Import UserService for JWT validation
use UserService\UserService;
use Database\DatabaseConnection;

class AuthMiddleware {
    
    /**
     * Verify JWT token
     * @param string $token
     * @return array|null
     */
    public static function verifyToken($token) {
        // Validate JWT token using UserService
        try {
            $db = DatabaseConnection::getInstance();
            $userService = new UserService($db->getConnection());
            $user = $userService->validateJWT($token);
            
            return $user;
        } catch (\Exception $e) {
            error_log('Token validation error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Require authentication for a request
     * @param array $request
     * @return array|null
     */
    public static function requireAuth($request) {
        $authHeader = $request['headers']['Authorization'] ?? '';
        
        if (!$authHeader) {
            return [
                'status' => 401,
                'data' => [
                    'message' => 'Authorization header missing'
                ]
            ];
        }
        
        // Extract token from "Bearer token" format
        $token = str_replace('Bearer ', '', $authHeader);
        
        $user = self::verifyToken($token);
        
        if (!$user) {
            return [
                'status' => 401,
                'data' => [
                    'message' => 'Invalid or expired token'
                ]
            ];
        }
        
        return [
            'status' => 200,
            'data' => [
                'user' => $user
            ]
        ];
    }
    
    /**
     * Require specific role for a request
     * @param array $request
     * @param string|array $role
     * @return array|null
     */
    public static function requireRole($request, $role) {
        // Check if user is already authenticated and passed in the request
        if (isset($request['user']) && !empty($request['user'])) {
            $user = $request['user'];
            $userRole = $user['role'] ?? '';
            
            // Convert single role to array for consistent handling
            $requiredRoles = is_array($role) ? $role : [$role];
            
            // Check if user has required role
            if (in_array($userRole, $requiredRoles)) {
                return [
                    'status' => 200,
                    'data' => [
                        'user' => $user
                    ]
                ];
            }
            
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        // If user is not already authenticated, authenticate using headers
        $authResult = self::requireAuth($request);
        
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'] ?? '';
        
        // Convert single role to array for consistent handling
        $requiredRoles = is_array($role) ? $role : [$role];
        
        // Check if user has required role
        if (in_array($userRole, $requiredRoles)) {
            return $authResult;
        }
        
        return [
            'status' => 403,
            'data' => [
                'message' => 'Insufficient permissions'
            ]
        ];
    }
}
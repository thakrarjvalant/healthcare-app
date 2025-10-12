<?php

namespace UserService\Middleware;

class AuthMiddleware {
    
    /**
     * Verify JWT token
     * @param string $token
     * @return array|null
     */
    public static function verifyToken($token) {
        // In a real implementation, we would verify the JWT token
        // For now, we'll return different users based on the token
        if ($token === 'admin-token') {
            return [
                'user_id' => 1,
                'role' => 'admin',
                'email' => 'admin@example.com'
            ];
        } elseif ($token) {
            return [
                'user_id' => 2,
                'role' => 'patient',
                'email' => 'john.doe@example.com'
            ];
        }
        
        return null;
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
        $authResult = self::requireAuth($request);
        
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'] ?? '';
        
        // Convert single role to array for consistent handling
        $requiredRoles = is_array($role) ? $role : [$role];
        
        // Check if user has required role or is admin (admin can access everything)
        if (in_array($userRole, $requiredRoles) || $userRole === 'admin' || in_array('admin', $requiredRoles)) {
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
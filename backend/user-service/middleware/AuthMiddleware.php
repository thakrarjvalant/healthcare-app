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
        // For now, we'll return a placeholder user
        if ($token) {
            return [
                'user_id' => 1,
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
     * @param string $role
     * @return array|null
     */
    public static function requireRole($request, $role) {
        $authResult = self::requireAuth($request);
        
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'] ?? '';
        
        if ($userRole !== $role && $userRole !== 'admin') {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        return $authResult;
    }
}
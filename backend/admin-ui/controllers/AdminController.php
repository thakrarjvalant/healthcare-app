<?php

namespace AdminUI\Controllers;

use UserService\Middleware\AuthMiddleware;
use Database\DatabaseConnection;

class AdminController {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Get admin dashboard data
     * @param array $request
     * @return array
     */
    public function getDashboard($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // In a real implementation, this would fetch actual dashboard data
        $dashboardData = [
            'total_users' => 1250,
            'total_appointments' => 342,
            'total_doctors' => 15,
            'total_patients' => 1120,
            'recent_activities' => [
                [
                    'user' => 'John Doe',
                    'action' => 'Booked appointment',
                    'time' => '2023-08-20 14:30:00'
                ],
                [
                    'user' => 'Dr. Smith',
                    'action' => 'Updated treatment plan',
                    'time' => '2023-08-20 12:15:00'
                ]
            ]
        ];
        
        return [
            'status' => 200,
            'data' => $dashboardData
        ];
    }
    
    /**
     * Get all users (admin only)
     * @param array $request
     * @return array
     */
    public function getUsers($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        try {
            // Fetch all users from the database
            $stmt = $this->db->getConnection()->prepare("SELECT id, name, email, role, verified as status, created_at, updated_at FROM users ORDER BY id");
            $stmt->execute();
            $users = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Normalize status field
            foreach ($users as &$user) {
                $user['status'] = $user['status'] ? 'active' : 'inactive';
            }
            
            return [
                'status' => 200,
                'data' => [
                    'users' => $users
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch users: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch users'
                ]
            ];
        }
    }
    
    /**
     * Create a new user (admin only)
     * @param array $request
     * @return array
     */
    public function createUser($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userData = $request['body'] ?? [];
        
        // In a real implementation, this would create a user in the database
        $newUser = [
            'id' => time(), // Simple ID generation for demo
            'name' => $userData['name'] ?? '',
            'email' => $userData['email'] ?? '',
            'role' => $userData['role'] ?? 'patient',
            'status' => 'active'
        ];
        
        return [
            'status' => 201,
            'data' => [
                'user' => $newUser,
                'message' => 'User created successfully'
            ]
        ];
    }
    
    /**
     * Update an existing user (admin only)
     * @param array $request
     * @return array
     */
    public function updateUser($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $request['params']['id'] ?? null;
        $userData = $request['body'] ?? [];
        
        // In a real implementation, this would update a user in the database
        $updatedUser = [
            'id' => $userId,
            'name' => $userData['name'] ?? 'Unknown',
            'email' => $userData['email'] ?? '',
            'role' => $userData['role'] ?? 'patient',
            'status' => $userData['status'] ?? 'active'
        ];
        
        return [
            'status' => 200,
            'data' => [
                'user' => $updatedUser,
                'message' => 'User updated successfully'
            ]
        ];
    }
    
    /**
     * Delete a user (admin only)
     * @param array $request
     * @return array
     */
    public function deleteUser($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $request['params']['id'] ?? null;
        
        // In a real implementation, this would delete a user from the database
        return [
            'status' => 200,
            'data' => [
                'message' => "User with ID {$userId} deleted successfully"
            ]
        ];
    }
    
    /**
     * Update system settings
     * @param array $request
     * @return array
     */
    public function updateSettings($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // In a real implementation, this would update system settings
        $settings = $request['body'] ?? [];
        
        return [
            'status' => 200,
            'data' => [
                'message' => 'Settings updated successfully',
                'settings' => $settings
            ]
        ];
    }
    
    /**
     * Get system audit logs
     * @param array $request
     * @return array
     */
    public function getAuditLogs($request) {
        // Require admin authentication
        $authResult = AuthMiddleware::requireRole($request, 'admin');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        // In a real implementation, this would fetch audit logs from the database
        $logs = [
            [
                'timestamp' => '2023-08-25 14:30:15',
                'user' => 'admin@example.com',
                'role' => 'Admin',
                'action' => 'User Created',
                'details' => 'Created new doctor account: jane.smith@example.com',
                'ip_address' => '192.168.1.100'
            ],
            [
                'timestamp' => '2023-08-25 14:25:42',
                'user' => 'jane.smith@example.com',
                'role' => 'Doctor',
                'action' => 'Login',
                'details' => 'Successful login',
                'ip_address' => '10.0.0.25'
            ]
        ];
        
        return [
            'status' => 200,
            'data' => [
                'logs' => $logs
            ]
        ];
    }
}
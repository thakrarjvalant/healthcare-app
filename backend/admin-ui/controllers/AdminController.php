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
        
        try {
            // Fetch actual dashboard data from the database
            $totalUsersStmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users");
            $totalUsersStmt->execute();
            $totalUsers = $totalUsersStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $totalAppointmentsStmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM appointments");
            $totalAppointmentsStmt->execute();
            $totalAppointments = $totalAppointmentsStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $totalDoctorsStmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'doctor'");
            $totalDoctorsStmt->execute();
            $totalDoctors = $totalDoctorsStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            $totalPatientsStmt = $this->db->getConnection()->prepare("SELECT COUNT(*) as count FROM users WHERE role = 'patient'");
            $totalPatientsStmt->execute();
            $totalPatients = $totalPatientsStmt->fetch(\PDO::FETCH_ASSOC)['count'];
            
            // Fetch recent activities (simplified for now)
            $recentActivitiesStmt = $this->db->getConnection()->prepare("
                SELECT u.name as user, 'User activity' as action, u.updated_at as time 
                FROM users u 
                ORDER BY u.updated_at DESC 
                LIMIT 2
            ");
            $recentActivitiesStmt->execute();
            $recentActivities = $recentActivitiesStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            $dashboardData = [
                'total_users' => $totalUsers,
                'total_appointments' => $totalAppointments,
                'total_doctors' => $totalDoctors,
                'total_patients' => $totalPatients,
                'recent_activities' => $recentActivities
            ];
            
            return [
                'status' => 200,
                'data' => $dashboardData
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch dashboard data: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch dashboard data'
                ]
            ];
        }
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
        
        try {
            // Validate required fields
            if (empty($userData['name']) || empty($userData['email']) || empty($userData['password'])) {
                return [
                    'status' => 400,
                    'data' => [
                        'message' => 'Name, email, and password are required'
                    ]
                ];
            }
            
            // Hash password
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            // Insert user into database
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO users (name, email, password, role, verified, created_at, updated_at) 
                VALUES (?, ?, ?, ?, 1, NOW(), NOW())
            ");
            $stmt->execute([
                $userData['name'],
                $userData['email'],
                $hashedPassword,
                $userData['role'] ?? 'patient'
            ]);
            
            $userId = $this->db->getConnection()->lastInsertId();
            
            // Fetch the created user
            $userStmt = $this->db->getConnection()->prepare("
                SELECT id, name, email, role, verified as status, created_at, updated_at 
                FROM users 
                WHERE id = ?
            ");
            $userStmt->execute([$userId]);
            $newUser = $userStmt->fetch(\PDO::FETCH_ASSOC);
            $newUser['status'] = $newUser['status'] ? 'active' : 'inactive';
            
            return [
                'status' => 201,
                'data' => [
                    'user' => $newUser,
                    'message' => 'User created successfully'
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to create user: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to create user'
                ]
            ];
        }
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
        
        if (!$userId) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'User ID is required'
                ]
            ];
        }
        
        try {
            // Check if user exists
            $checkStmt = $this->db->getConnection()->prepare("SELECT id FROM users WHERE id = ?");
            $checkStmt->execute([$userId]);
            if (!$checkStmt->fetch()) {
                return [
                    'status' => 404,
                    'data' => [
                        'message' => 'User not found'
                    ]
                ];
            }
            
            // Update user in database
            $stmt = $this->db->getConnection()->prepare("
                UPDATE users 
                SET name = ?, email = ?, role = ?, verified = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([
                $userData['name'] ?? '',
                $userData['email'] ?? '',
                $userData['role'] ?? 'patient',
                isset($userData['status']) ? ($userData['status'] === 'active' ? 1 : 0) : 0,
                $userId
            ]);
            
            // Fetch the updated user
            $userStmt = $this->db->getConnection()->prepare("
                SELECT id, name, email, role, verified as status, created_at, updated_at 
                FROM users 
                WHERE id = ?
            ");
            $userStmt->execute([$userId]);
            $updatedUser = $userStmt->fetch(\PDO::FETCH_ASSOC);
            $updatedUser['status'] = $updatedUser['status'] ? 'active' : 'inactive';
            
            return [
                'status' => 200,
                'data' => [
                    'user' => $updatedUser,
                    'message' => 'User updated successfully'
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to update user: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to update user'
                ]
            ];
        }
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
        
        if (!$userId) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'User ID is required'
                ]
            ];
        }
        
        try {
            // Check if user exists
            $checkStmt = $this->db->getConnection()->prepare("SELECT id FROM users WHERE id = ?");
            $checkStmt->execute([$userId]);
            if (!$checkStmt->fetch()) {
                return [
                    'status' => 404,
                    'data' => [
                        'message' => 'User not found'
                    ]
                ];
            }
            
            // Delete user from database
            $stmt = $this->db->getConnection()->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            
            return [
                'status' => 200,
                'data' => [
                    'message' => "User with ID {$userId} deleted successfully"
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to delete user: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to delete user'
                ]
            ];
        }
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
        
        // In a real implementation, this would update system settings in the database
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
        
        try {
            // In a real implementation, this would fetch audit logs from the database
            // For now, we'll return a simplified version
            $logs = [
                [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'user' => 'admin@example.com',
                    'role' => 'Admin',
                    'action' => 'System Access',
                    'details' => 'Accessed admin dashboard',
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                ]
            ];
            
            return [
                'status' => 200,
                'data' => [
                    'logs' => $logs
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch audit logs: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch audit logs'
                ]
            ];
        }
    }
}
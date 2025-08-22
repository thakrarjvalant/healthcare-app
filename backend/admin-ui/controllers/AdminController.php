<?php

namespace AdminUI\Controllers;

use UserService\Middleware\AuthMiddleware;

class AdminController {
    
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
        
        // In a real implementation, this would fetch users from the database
        $users = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'role' => 'patient',
                'status' => 'active'
            ],
            [
                'id' => 2,
                'name' => 'Dr. Smith',
                'email' => 'dr.smith@example.com',
                'role' => 'doctor',
                'status' => 'active'
            ]
        ];
        
        return [
            'status' => 200,
            'data' => [
                'users' => $users
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
}
<?php

namespace NotificationService\Controllers;

use NotificationService\NotificationService;
use UserService\Middleware\AuthMiddleware;

class NotificationController {
    private $notificationService;
    
    public function __construct(NotificationService $notificationService) {
        $this->notificationService = $notificationService;
    }
    
    /**
     * Get notifications for the authenticated user
     * @param array $request
     * @return array
     */
    public function getUserNotifications($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        $unreadOnly = isset($request['query']['unread']) && $request['query']['unread'] == 'true';
        
        $result = $this->notificationService->getUserNotifications($userId, $unreadOnly);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'notifications' => $result['notifications']
                ]
            ];
        } else {
            return [
                'status' => 500,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Mark a notification as read
     * @param array $request
     * @return array
     */
    public function markAsRead($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $notificationId = $request['params']['id'] ?? '';
        
        if (empty($notificationId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Notification ID is required'
                ]
            ];
        }
        
        // Get notification to verify ownership
        $notificationResult = $this->getNotificationById($notificationId);
        
        if (!$notificationResult['success']) {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'Notification not found'
                ]
            ];
        }
        
        // Check if user owns this notification
        $userId = $authResult['data']['user']['id'];
        if ($notificationResult['notification']['user_id'] != $userId) {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->notificationService->markAsRead($notificationId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        } else {
            return [
                'status' => 400,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Mark all notifications as read for the authenticated user
     * @param array $request
     * @return array
     */
    public function markAllAsRead($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        
        $result = $this->notificationService->markAllAsRead($userId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        } else {
            return [
                'status' => 400,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Delete a notification
     * @param array $request
     * @return array
     */
    public function deleteNotification($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $notificationId = $request['params']['id'] ?? '';
        
        if (empty($notificationId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Notification ID is required'
                ]
            ];
        }
        
        // Get notification to verify ownership
        $notificationResult = $this->getNotificationById($notificationId);
        
        if (!$notificationResult['success']) {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'Notification not found'
                ]
            ];
        }
        
        // Check if user owns this notification
        $userId = $authResult['data']['user']['id'];
        if ($notificationResult['notification']['user_id'] != $userId) {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->notificationService->deleteNotification($notificationId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        } else {
            return [
                'status' => 400,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Get notification by ID
     * @param int $notificationId
     * @return array
     */
    private function getNotificationById($notificationId) {
        // In a real implementation, this would fetch from the database
        // For now, we'll return a placeholder
        return [
            'success' => true,
            'notification' => [
                'id' => $notificationId,
                'user_id' => 1, // Placeholder user ID
                'type' => 'appointment',
                'title' => 'Appointment Reminder',
                'message' => 'You have an appointment tomorrow at 10:00 AM',
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
    }
}
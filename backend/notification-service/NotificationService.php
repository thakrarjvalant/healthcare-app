<?php

namespace NotificationService;

use NotificationService\Models\Notification;

class NotificationService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new notification
     * @param array $notificationData
     * @return array
     */
    public function createNotification($notificationData) {
        // Validate input data
        if (!$this->validateNotificationData($notificationData)) {
            return ['success' => false, 'message' => 'Invalid notification data'];
        }
        
        // Create notification record
        $query = "INSERT INTO notifications (user_id, type, title, message, is_read) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $notificationData['user_id'],
            $notificationData['type'],
            $notificationData['title'],
            $notificationData['message'],
            false
        ]);
        
        if ($result) {
            $notificationId = $this->db->lastInsertId();
            return ['success' => true, 'notification_id' => $notificationId];
        }
        
        return ['success' => false, 'message' => 'Failed to create notification'];
    }
    
    /**
     * Get notifications for a user
     * @param int $userId
     * @param bool $unreadOnly
     * @return array
     */
    public function getUserNotifications($userId, $unreadOnly = false) {
        $query = "SELECT * FROM notifications WHERE user_id = ?";
        
        if ($unreadOnly) {
            $query .= " AND is_read = 0";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $notifications = $stmt->fetchAll();
        
        return ['success' => true, 'notifications' => $notifications];
    }
    
    /**
     * Mark notification as read
     * @param int $notificationId
     * @return array
     */
    public function markAsRead($notificationId) {
        $query = "UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$notificationId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Notification marked as read'];
        }
        
        return ['success' => false, 'message' => 'Failed to mark notification as read'];
    }
    
    /**
     * Mark all notifications as read for a user
     * @param int $userId
     * @return array
     */
    public function markAllAsRead($userId) {
        $query = "UPDATE notifications SET is_read = 1, updated_at = NOW() WHERE user_id = ? AND is_read = 0";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$userId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'All notifications marked as read'];
        }
        
        return ['success' => false, 'message' => 'Failed to mark notifications as read'];
    }
    
    /**
     * Delete a notification
     * @param int $notificationId
     * @return array
     */
    public function deleteNotification($notificationId) {
        $query = "DELETE FROM notifications WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$notificationId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Notification deleted'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete notification'];
    }
    
    /**
     * Send email notification
     * @param string $email
     * @param string $subject
     * @param string $message
     * @return array
     */
    public function sendEmail($email, $subject, $message) {
        // In a real implementation, this would integrate with an email service
        // For now, we'll just log the email
        error_log("Email sent to: $email, Subject: $subject, Message: $message");
        
        return ['success' => true, 'message' => 'Email sent successfully'];
    }
    
    /**
     * Send SMS notification
     * @param string $phone
     * @param string $message
     * @return array
     */
    public function sendSMS($phone, $message) {
        // In a real implementation, this would integrate with an SMS service
        // For now, we'll just log the SMS
        error_log("SMS sent to: $phone, Message: $message");
        
        return ['success' => true, 'message' => 'SMS sent successfully'];
    }
    
    /**
     * Validate notification data
     * @param array $notificationData
     * @return bool
     */
    private function validateNotificationData($notificationData) {
        // Check required fields
        $requiredFields = ['user_id', 'type', 'title', 'message'];
        foreach ($requiredFields as $field) {
            if (!isset($notificationData[$field]) || empty($notificationData[$field])) {
                return false;
            }
        }
        
        return true;
    }
}
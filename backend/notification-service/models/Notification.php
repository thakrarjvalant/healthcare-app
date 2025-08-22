<?php

namespace NotificationService\Models;

class Notification {
    private $id;
    private $user_id;
    private $type;
    private $title;
    private $message;
    private $is_read;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->user_id = $data['user_id'] ?? null;
            $this->type = $data['type'] ?? '';
            $this->title = $data['title'] ?? '';
            $this->message = $data['message'] ?? '';
            $this->is_read = $data['is_read'] ?? false;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getUserId() {
        return $this->user_id;
    }
    
    public function getType() {
        return $this->type;
    }
    
    public function getTitle() {
        return $this->title;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function isRead() {
        return $this->is_read;
    }
    
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }
    
    public function setType($type) {
        $this->type = $type;
    }
    
    public function setTitle($title) {
        $this->title = $title;
    }
    
    public function setMessage($message) {
        $this->message = $message;
    }
    
    public function setRead($is_read) {
        $this->is_read = $is_read;
    }
    
    /**
     * Convert notification object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'is_read' => $this->is_read,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
<?php

namespace StorageService\Models;

class Document {
    private $id;
    private $user_id;
    private $filename;
    private $original_filename;
    private $file_path;
    private $file_size;
    private $file_type;
    private $uploaded_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->user_id = $data['user_id'] ?? null;
            $this->filename = $data['filename'] ?? '';
            $this->original_filename = $data['original_filename'] ?? '';
            $this->file_path = $data['file_path'] ?? '';
            $this->file_size = $data['file_size'] ?? 0;
            $this->file_type = $data['file_type'] ?? '';
            $this->uploaded_at = $data['uploaded_at'] ?? null;
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
    
    public function getFilename() {
        return $this->filename;
    }
    
    public function getOriginalFilename() {
        return $this->original_filename;
    }
    
    public function getFilePath() {
        return $this->file_path;
    }
    
    public function getFileSize() {
        return $this->file_size;
    }
    
    public function getFileType() {
        return $this->file_type;
    }
    
    public function getUploadedAt() {
        return $this->uploaded_at;
    }
    
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    // Setters
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }
    
    public function setFilename($filename) {
        $this->filename = $filename;
    }
    
    public function setOriginalFilename($original_filename) {
        $this->original_filename = $original_filename;
    }
    
    public function setFilePath($file_path) {
        $this->file_path = $file_path;
    }
    
    public function setFileSize($file_size) {
        $this->file_size = $file_size;
    }
    
    public function setFileType($file_type) {
        $this->file_type = $file_type;
    }
    
    /**
     * Convert document object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'file_path' => $this->file_path,
            'file_size' => $this->file_size,
            'file_type' => $this->file_type,
            'uploaded_at' => $this->uploaded_at,
            'updated_at' => $this->updated_at
        ];
    }
}
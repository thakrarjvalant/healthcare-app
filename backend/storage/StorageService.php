<?php

namespace StorageService;

use StorageService\Models\Document;

class StorageService {
    private $db;
    private $uploadPath;
    
    public function __construct($database, $uploadPath = './uploads') {
        $this->db = $database;
        $this->uploadPath = $uploadPath;
        
        // Create upload directory if it doesn't exist
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    /**
     * Upload a file
     * @param array $file
     * @param int $userId
     * @return array
     */
    public function uploadFile($file, $userId) {
        // Validate file
        if (!$this->validateFile($file)) {
            return ['success' => false, 'message' => 'Invalid file'];
        }
        
        // Generate unique filename
        $filename = uniqid() . '_' . $file['name'];
        $filePath = $this->uploadPath . '/' . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            return ['success' => false, 'message' => 'Failed to move uploaded file'];
        }
        
        // Create document record
        $documentData = [
            'user_id' => $userId,
            'filename' => $filename,
            'original_filename' => $file['name'],
            'file_path' => $filePath,
            'file_size' => $file['size'],
            'file_type' => $file['type']
        ];
        
        $query = "INSERT INTO documents (user_id, filename, original_filename, file_path, file_size, file_type, uploaded_at) VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $documentData['user_id'],
            $documentData['filename'],
            $documentData['original_filename'],
            $documentData['file_path'],
            $documentData['file_size'],
            $documentData['file_type']
        ]);
        
        if ($result) {
            $documentId = $this->db->lastInsertId();
            return ['success' => true, 'document_id' => $documentId, 'document' => $documentData];
        }
        
        // If database insert fails, delete the uploaded file
        unlink($filePath);
        
        return ['success' => false, 'message' => 'Failed to save document record'];
    }
    
    /**
     * Get document by ID
     * @param int $documentId
     * @return array
     */
    public function getDocument($documentId) {
        $query = "SELECT * FROM documents WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$documentId]);
        $document = $stmt->fetch();
        
        if ($document) {
            return ['success' => true, 'document' => $document];
        }
        
        return ['success' => false, 'message' => 'Document not found'];
    }
    
    /**
     * Get documents for a user
     * @param int $userId
     * @return array
     */
    public function getUserDocuments($userId) {
        $query = "SELECT * FROM documents WHERE user_id = ? ORDER BY uploaded_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId]);
        $documents = $stmt->fetchAll();
        
        return ['success' => true, 'documents' => $documents];
    }
    
    /**
     * Download a document
     * @param int $documentId
     * @param int $userId
     * @return array
     */
    public function downloadDocument($documentId, $userId) {
        // Get document
        $documentResult = $this->getDocument($documentId);
        
        if (!$documentResult['success']) {
            return ['success' => false, 'message' => 'Document not found'];
        }
        
        $document = $documentResult['document'];
        
        // Check if user has permission to download this document
        if ($document['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }
        
        // Check if file exists
        if (!file_exists($document['file_path'])) {
            return ['success' => false, 'message' => 'File not found'];
        }
        
        return ['success' => true, 'document' => $document];
    }
    
    /**
     * Delete a document
     * @param int $documentId
     * @param int $userId
     * @return array
     */
    public function deleteDocument($documentId, $userId) {
        // Get document
        $documentResult = $this->getDocument($documentId);
        
        if (!$documentResult['success']) {
            return ['success' => false, 'message' => 'Document not found'];
        }
        
        $document = $documentResult['document'];
        
        // Check if user has permission to delete this document
        if ($document['user_id'] != $userId) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }
        
        // Delete file from storage
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }
        
        // Delete document record from database
        $query = "DELETE FROM documents WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$documentId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Document deleted'];
        }
        
        return ['success' => false, 'message' => 'Failed to delete document record'];
    }
    
    /**
     * Validate uploaded file
     * @param array $file
     * @return bool
     */
    private function validateFile($file) {
        // Check if file was uploaded without errors
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }
        
        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return false;
        }
        
        // Check file type (allow common document types)
        $allowedTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'application/pdf',
            'text/plain',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        return true;
    }
}
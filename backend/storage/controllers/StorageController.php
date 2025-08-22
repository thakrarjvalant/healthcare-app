<?php

namespace StorageService\Controllers;

use StorageService\StorageService;
use UserService\Middleware\AuthMiddleware;

class StorageController {
    private $storageService;
    
    public function __construct(StorageService $storageService) {
        $this->storageService = $storageService;
    }
    
    /**
     * Upload a file
     * @param array $request
     * @return array
     */
    public function uploadFile($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        
        // Check if file was uploaded
        if (!isset($_FILES['file'])) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'No file uploaded'
                ]
            ];
        }
        
        $file = $_FILES['file'];
        
        $result = $this->storageService->uploadFile($file, $userId);
        
        if ($result['success']) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'File uploaded successfully',
                    'document_id' => $result['document_id'],
                    'document' => $result['document']
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
     * Get documents for the authenticated user
     * @param array $request
     * @return array
     */
    public function getUserDocuments($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        
        $result = $this->storageService->getUserDocuments($userId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'documents' => $result['documents']
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
     * Download a document
     * @param array $request
     * @return array
     */
    public function downloadDocument($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $documentId = $request['params']['id'] ?? '';
        $userId = $authResult['data']['user']['id'];
        
        if (empty($documentId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Document ID is required'
                ]
            ];
        }
        
        $result = $this->storageService->downloadDocument($documentId, $userId);
        
        if ($result['success']) {
            // In a real implementation, this would stream the file to the client
            // For now, we'll just return the document information
            return [
                'status' => 200,
                'data' => [
                    'message' => 'Document ready for download',
                    'document' => $result['document']
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
     * Delete a document
     * @param array $request
     * @return array
     */
    public function deleteDocument($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $documentId = $request['params']['id'] ?? '';
        $userId = $authResult['data']['user']['id'];
        
        if (empty($documentId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Document ID is required'
                ]
            ];
        }
        
        $result = $this->storageService->deleteDocument($documentId, $userId);
        
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
}
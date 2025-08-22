<?php

namespace ClinicalService\Controllers;

use ClinicalService\ClinicalService;
use UserService\Middleware\AuthMiddleware;

class ClinicalController {
    private $clinicalService;
    
    public function __construct(ClinicalService $clinicalService) {
        $this->clinicalService = $clinicalService;
    }
    
    /**
     * Create a new medical record
     * @param array $request
     * @return array
     */
    public function createMedicalRecord($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'];
        
        // Only doctors can create medical records
        if ($userRole !== 'doctor') {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $recordData = [
            'patient_id' => $request['body']['patient_id'] ?? '',
            'doctor_id' => $authResult['data']['user']['id'],
            'appointment_id' => $request['body']['appointment_id'] ?? '',
            'diagnosis' => $request['body']['diagnosis'] ?? '',
            'prescription' => $request['body']['prescription'] ?? '',
            'notes' => $request['body']['notes'] ?? ''
        ];
        
        $result = $this->clinicalService->createMedicalRecord($recordData);
        
        if ($result['success']) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Medical record created successfully',
                    'record_id' => $result['record_id']
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
     * Get medical records for a patient
     * @param array $request
     * @return array
     */
    public function getPatientMedicalRecords($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $patientId = $request['params']['patient_id'] ?? '';
        
        if (empty($patientId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Patient ID is required'
                ]
            ];
        }
        
        // Check if user has permission to view these records
        $userId = $authResult['data']['user']['id'];
        $userRole = $authResult['data']['user']['role'];
        
        if ($userRole !== 'admin' && $userRole !== 'doctor' && $userId != $patientId) {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->clinicalService->getPatientMedicalRecords($patientId);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'records' => $result['records']
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
     * Get a specific medical record
     * @param array $request
     * @return array
     */
    public function getMedicalRecord($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $recordId = $request['params']['id'] ?? '';
        
        if (empty($recordId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Record ID is required'
                ]
            ];
        }
        
        $result = $this->clinicalService->getMedicalRecord($recordId);
        
        if ($result['success']) {
            // Check if user has permission to view this record
            $userId = $authResult['data']['user']['id'];
            $userRole = $authResult['data']['user']['role'];
            
            if ($userRole !== 'admin' && $userRole !== 'doctor' && $result['record']['patient_id'] != $userId) {
                return [
                    'status' => 403,
                    'data' => [
                        'message' => 'Insufficient permissions'
                    ]
                ];
            }
            
            return [
                'status' => 200,
                'data' => [
                    'record' => $result['record']
                ]
            ];
        } else {
            return [
                'status' => 404,
                'data' => [
                    'message' => $result['message']
                ]
            ];
        }
    }
    
    /**
     * Update a medical record
     * @param array $request
     * @return array
     */
    public function updateMedicalRecord($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'];
        
        // Only doctors can update medical records
        if ($userRole !== 'doctor') {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $recordId = $request['params']['id'] ?? '';
        $recordData = $request['body'] ?? [];
        
        if (empty($recordId)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Record ID is required'
                ]
            ];
        }
        
        // Get record to verify doctor is the owner
        $recordResult = $this->clinicalService->getMedicalRecord($recordId);
        
        if (!$recordResult['success']) {
            return [
                'status' => 404,
                'data' => [
                    'message' => 'Medical record not found'
                ]
            ];
        }
        
        if ($recordResult['record']['doctor_id'] != $authResult['data']['user']['id']) {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $result = $this->clinicalService->updateMedicalRecord($recordId, $recordData);
        
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
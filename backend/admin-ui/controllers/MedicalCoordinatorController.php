<?php

namespace AdminUI\Controllers;

use UserService\Middleware\AuthMiddleware;
use Database\DatabaseConnection;

class MedicalCoordinatorController {
    private $db;

    public function __construct() {
        $this->db = DatabaseConnection::getInstance();
    }
    
    /**
     * Get all patients for assignment
     * @param array $request
     * @return array
     */
    public function getPatients($request) {
        // Debug logging
        error_log('MedicalCoordinatorController::getPatients called');
        error_log('Request user: ' . json_encode($request['user']));
        
        // Require medical coordinator authentication
        $authResult = AuthMiddleware::requireRole($request, 'medical_coordinator');
        error_log('Auth result: ' . json_encode($authResult));
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        try {
            // Fetch all patients from the database
            $stmt = $this->db->getConnection()->prepare("
                SELECT u.id, u.name, u.email, u.assigned_doctor, d.name as doctor_name
                FROM users u
                LEFT JOIN users d ON u.assigned_doctor = d.id
                WHERE u.role = 'patient'
                ORDER BY u.name
            ");
            $stmt->execute();
            $patients = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'status' => 200,
                'data' => [
                    'patients' => $patients
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch patients: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch patients'
                ]
            ];
        }
    }
    
    /**
     * Get all doctors for assignment
     * @param array $request
     * @return array
     */
    public function getDoctors($request) {
        // Require medical coordinator authentication
        $authResult = AuthMiddleware::requireRole($request, 'medical_coordinator');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        try {
            // Fetch all doctors from the database
            $stmt = $this->db->getConnection()->prepare("
                SELECT id, name, email
                FROM users 
                WHERE role = 'doctor'
                ORDER BY name
            ");
            $stmt->execute();
            $doctors = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Add a default specialization for all doctors
            foreach ($doctors as &$doctor) {
                $doctor['specialization'] = 'General Practitioner';
            }
            
            return [
                'status' => 200,
                'data' => [
                    'doctors' => $doctors
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch doctors: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch doctors'
                ]
            ];
        }
    }
    
    /**
     * Assign a patient to a doctor
     * @param array $request
     * @return array
     */
    public function assignPatientToDoctor($request) {
        // Require medical coordinator authentication
        $authResult = AuthMiddleware::requireRole($request, 'medical_coordinator');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $requestData = $request['body'] ?? [];
        $patientId = $requestData['patient_id'] ?? null;
        $doctorId = $requestData['doctor_id'] ?? null;
        $notes = $requestData['notes'] ?? '';
        
        // Validate input
        if (!$patientId || !$doctorId) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Patient ID and Doctor ID are required'
                ]
            ];
        }
        
        try {
            // Check if patient and doctor exist
            $patientStmt = $this->db->getConnection()->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'patient'");
            $patientStmt->execute([$patientId]);
            $patient = $patientStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$patient) {
                return [
                    'status' => 404,
                    'data' => [
                        'message' => 'Patient not found'
                    ]
                ];
            }
            
            $doctorStmt = $this->db->getConnection()->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'doctor'");
            $doctorStmt->execute([$doctorId]);
            $doctor = $doctorStmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$doctor) {
                return [
                    'status' => 404,
                    'data' => [
                        'message' => 'Doctor not found'
                    ]
                ];
            }
            
            // Get the authenticated user ID from the auth result
            $userId = $authResult['data']['user']['user_id'] ?? null;
            
            // If we don't have a user ID, return an error
            if (!$userId) {
                return [
                    'status' => 401,
                    'data' => [
                        'message' => 'Unable to identify authenticated user'
                    ]
                ];
            }
            
            // Deactivate any existing active assignments for this patient
            $deactivateStmt = $this->db->getConnection()->prepare("
                UPDATE patient_doctor_assignments 
                SET is_active = FALSE, updated_at = NOW() 
                WHERE patient_id = ? AND is_active = TRUE
            ");
            $deactivateStmt->execute([$patientId]);
            
            // Create new assignment
            $stmt = $this->db->getConnection()->prepare("
                INSERT INTO patient_doctor_assignments (patient_id, doctor_id, assigned_by, notes) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$patientId, $doctorId, $userId, $notes]);
            
            // Update the patient's assigned_doctor field
            $updateStmt = $this->db->getConnection()->prepare("
                UPDATE users 
                SET assigned_doctor = ? 
                WHERE id = ?
            ");
            $updateStmt->execute([$doctorId, $patientId]);
            
            $assignmentId = $this->db->getConnection()->lastInsertId();
            
            return [
                'status' => 201,
                'data' => [
                    'assignment_id' => $assignmentId,
                    'message' => "Patient {$patient['name']} assigned to Dr. {$doctor['name']} successfully"
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to assign patient to doctor: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to assign patient to doctor'
                ]
            ];
        }
    }
    
    /**
     * Get patient assignment history
     * @param array $request
     * @return array
     */
    public function getPatientAssignmentHistory($request) {
        // Require medical coordinator authentication
        $authResult = AuthMiddleware::requireRole($request, 'medical_coordinator');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $patientId = $request['params']['patient_id'] ?? null;
        
        if (!$patientId) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Patient ID is required'
                ]
            ];
        }
        
        try {
            // Fetch assignment history for the patient
            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    pda.id,
                    p.name as patient_name,
                    d.name as doctor_name,
                    a.name as assigned_by_name,
                    pda.assignment_date,
                    pda.notes,
                    pda.is_active
                FROM patient_doctor_assignments pda
                JOIN users p ON pda.patient_id = p.id
                JOIN users d ON pda.doctor_id = d.id
                JOIN users a ON pda.assigned_by = a.id
                WHERE pda.patient_id = ?
                ORDER BY pda.assignment_date DESC
            ");
            $stmt->execute([$patientId]);
            $history = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'status' => 200,
                'data' => [
                    'history' => $history
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch patient assignment history: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch patient assignment history'
                ]
            ];
        }
    }
    
    /**
     * Get limited patient history (medical records)
     * @param array $request
     * @return array
     */
    public function getPatientLimitedHistory($request) {
        // Require medical coordinator authentication
        $authResult = AuthMiddleware::requireRole($request, 'medical_coordinator');
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $patientId = $request['params']['patient_id'] ?? null;
        
        if (!$patientId) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Patient ID is required'
                ]
            ];
        }
        
        try {
            // Fetch limited patient information
            $stmt = $this->db->getConnection()->prepare("
                SELECT 
                    u.id,
                    u.name,
                    u.email,
                    u.created_at,
                    d.name as assigned_doctor_name
                FROM users u
                LEFT JOIN users d ON u.assigned_doctor = d.id
                WHERE u.id = ? AND u.role = 'patient'
            ");
            $stmt->execute([$patientId]);
            $patient = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if (!$patient) {
                return [
                    'status' => 404,
                    'data' => [
                        'message' => 'Patient not found'
                    ]
                ];
            }
            
            // Fetch recent appointments
            $appointmentsStmt = $this->db->getConnection()->prepare("
                SELECT 
                    a.id,
                    a.date,
                    a.time_slot,
                    a.status,
                    d.name as doctor_name
                FROM appointments a
                JOIN users d ON a.doctor_id = d.id
                WHERE a.patient_id = ?
                ORDER BY a.date DESC, a.time_slot DESC
                LIMIT 5
            ");
            $appointmentsStmt->execute([$patientId]);
            $appointments = $appointmentsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            // Fetch recent prescriptions
            $prescriptionsStmt = $this->db->getConnection()->prepare("
                SELECT 
                    p.id,
                    p.medication_name,
                    p.dosage,
                    p.frequency,
                    p.status,
                    p.prescribed_date,
                    d.name as doctor_name
                FROM prescriptions p
                JOIN users d ON p.doctor_id = d.id
                WHERE p.patient_id = ?
                ORDER BY p.prescribed_date DESC
                LIMIT 5
            ");
            $prescriptionsStmt->execute([$patientId]);
            $prescriptions = $prescriptionsStmt->fetchAll(\PDO::FETCH_ASSOC);
            
            return [
                'status' => 200,
                'data' => [
                    'patient' => $patient,
                    'recent_appointments' => $appointments,
                    'recent_prescriptions' => $prescriptions
                ]
            ];
        } catch (\Exception $e) {
            error_log('Failed to fetch patient limited history: ' . $e->getMessage());
            return [
                'status' => 500,
                'data' => [
                    'message' => 'Failed to fetch patient limited history'
                ]
            ];
        }
    }
}
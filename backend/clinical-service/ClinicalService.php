<?php

namespace ClinicalService;

use ClinicalService\Models\MedicalRecord;

class ClinicalService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Create a new medical record
     * @param array $recordData
     * @return array
     */
    public function createMedicalRecord($recordData) {
        // Validate input data
        if (!$this->validateMedicalRecordData($recordData)) {
            return ['success' => false, 'message' => 'Invalid medical record data'];
        }
        
        // Create medical record
        $query = "INSERT INTO medical_records (patient_id, doctor_id, appointment_id, diagnosis, prescription, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $recordData['patient_id'],
            $recordData['doctor_id'],
            $recordData['appointment_id'],
            $recordData['diagnosis'],
            $recordData['prescription'],
            $recordData['notes']
        ]);
        
        if ($result) {
            $recordId = $this->db->lastInsertId();
            return ['success' => true, 'record_id' => $recordId];
        }
        
        return ['success' => false, 'message' => 'Failed to create medical record'];
    }
    
    /**
     * Get medical records for a patient
     * @param int $patientId
     * @return array
     */
    public function getPatientMedicalRecords($patientId) {
        $query = "SELECT * FROM medical_records WHERE patient_id = ? ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patientId]);
        $records = $stmt->fetchAll();
        
        return ['success' => true, 'records' => $records];
    }
    
    /**
     * Get medical record by ID
     * @param int $recordId
     * @return array
     */
    public function getMedicalRecord($recordId) {
        $query = "SELECT * FROM medical_records WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$recordId]);
        $record = $stmt->fetch();
        
        if ($record) {
            return ['success' => true, 'record' => $record];
        }
        
        return ['success' => false, 'message' => 'Medical record not found'];
    }
    
    /**
     * Update medical record
     * @param int $recordId
     * @param array $recordData
     * @return array
     */
    public function updateMedicalRecord($recordId, $recordData) {
        // Validate input data
        if (!$this->validateMedicalRecordData($recordData, false)) {
            return ['success' => false, 'message' => 'Invalid medical record data'];
        }
        
        // Build update query dynamically
        $fields = [];
        $values = [];
        
        if (isset($recordData['diagnosis'])) {
            $fields[] = "diagnosis = ?";
            $values[] = $recordData['diagnosis'];
        }
        
        if (isset($recordData['prescription'])) {
            $fields[] = "prescription = ?";
            $values[] = $recordData['prescription'];
        }
        
        if (isset($recordData['notes'])) {
            $fields[] = "notes = ?";
            $values[] = $recordData['notes'];
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No data to update'];
        }
        
        $fields[] = "updated_at = NOW()";
        $values[] = $recordId; // For the WHERE clause
        
        $query = "UPDATE medical_records SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($values);
        
        if ($result) {
            return ['success' => true, 'message' => 'Medical record updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update medical record'];
    }
    
    /**
     * Validate medical record data
     * @param array $recordData
     * @param bool $isRequired
     * @return bool
     */
    private function validateMedicalRecordData($recordData, $isRequired = true) {
        // Check required fields if needed
        if ($isRequired) {
            $requiredFields = ['patient_id', 'doctor_id', 'appointment_id'];
            foreach ($requiredFields as $field) {
                if (!isset($recordData[$field]) || empty($recordData[$field])) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
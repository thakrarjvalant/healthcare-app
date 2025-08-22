<?php

namespace ClinicalService\Models;

class MedicalRecord {
    private $id;
    private $patient_id;
    private $doctor_id;
    private $appointment_id;
    private $diagnosis;
    private $prescription;
    private $notes;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->patient_id = $data['patient_id'] ?? null;
            $this->doctor_id = $data['doctor_id'] ?? null;
            $this->appointment_id = $data['appointment_id'] ?? null;
            $this->diagnosis = $data['diagnosis'] ?? '';
            $this->prescription = $data['prescription'] ?? '';
            $this->notes = $data['notes'] ?? '';
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // Getters
    public function getId() {
        return $this->id;
    }
    
    public function getPatientId() {
        return $this->patient_id;
    }
    
    public function getDoctorId() {
        return $this->doctor_id;
    }
    
    public function getAppointmentId() {
        return $this->appointment_id;
    }
    
    public function getDiagnosis() {
        return $this->diagnosis;
    }
    
    public function getPrescription() {
        return $this->prescription;
    }
    
    public function getNotes() {
        return $this->notes;
    }
    
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    // Setters
    public function setPatientId($patient_id) {
        $this->patient_id = $patient_id;
    }
    
    public function setDoctorId($doctor_id) {
        $this->doctor_id = $doctor_id;
    }
    
    public function setAppointmentId($appointment_id) {
        $this->appointment_id = $appointment_id;
    }
    
    public function setDiagnosis($diagnosis) {
        $this->diagnosis = $diagnosis;
    }
    
    public function setPrescription($prescription) {
        $this->prescription = $prescription;
    }
    
    public function setNotes($notes) {
        $this->notes = $notes;
    }
    
    /**
     * Convert medical record object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'appointment_id' => $this->appointment_id,
            'diagnosis' => $this->diagnosis,
            'prescription' => $this->prescription,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
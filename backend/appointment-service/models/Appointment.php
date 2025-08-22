<?php

namespace AppointmentService\Models;

class Appointment {
    private $id;
    private $patient_id;
    private $doctor_id;
    private $date;
    private $time_slot;
    private $status;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->patient_id = $data['patient_id'] ?? null;
            $this->doctor_id = $data['doctor_id'] ?? null;
            $this->date = $data['date'] ?? null;
            $this->time_slot = $data['time_slot'] ?? null;
            $this->status = $data['status'] ?? 'pending';
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
    
    public function getDate() {
        return $this->date;
    }
    
    public function getTimeSlot() {
        return $this->time_slot;
    }
    
    public function getStatus() {
        return $this->status;
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
    
    public function setDate($date) {
        $this->date = $date;
    }
    
    public function setTimeSlot($time_slot) {
        $this->time_slot = $time_slot;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    /**
     * Convert appointment object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'date' => $this->date,
            'time_slot' => $this->time_slot,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
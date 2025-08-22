<?php

namespace AppointmentService\Models;

class Availability {
    private $id;
    private $doctor_id;
    private $date;
    private $time_slot;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->doctor_id = $data['doctor_id'] ?? null;
            $this->date = $data['date'] ?? null;
            $this->time_slot = $data['time_slot'] ?? null;
            $this->created_at = $data['created_at'] ?? null;
            $this->updated_at = $data['updated_at'] ?? null;
        }
    }
    
    // Getters
    public function getId() {
        return $this->id;
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
    
    public function getCreatedAt() {
        return $this->created_at;
    }
    
    public function getUpdatedAt() {
        return $this->updated_at;
    }
    
    // Setters
    public function setDoctorId($doctor_id) {
        $this->doctor_id = $doctor_id;
    }
    
    public function setDate($date) {
        $this->date = $date;
    }
    
    public function setTimeSlot($time_slot) {
        $this->time_slot = $time_slot;
    }
    
    /**
     * Convert availability object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'doctor_id' => $this->doctor_id,
            'date' => $this->date,
            'time_slot' => $this->time_slot,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
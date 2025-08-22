<?php

namespace BillingService\Models;

class Invoice {
    private $id;
    private $patient_id;
    private $appointment_id;
    private $amount;
    private $status;
    private $issued_date;
    private $due_date;
    private $paid_date;
    private $created_at;
    private $updated_at;
    
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->patient_id = $data['patient_id'] ?? null;
            $this->appointment_id = $data['appointment_id'] ?? null;
            $this->amount = $data['amount'] ?? 0.00;
            $this->status = $data['status'] ?? 'pending';
            $this->issued_date = $data['issued_date'] ?? null;
            $this->due_date = $data['due_date'] ?? null;
            $this->paid_date = $data['paid_date'] ?? null;
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
    
    public function getAppointmentId() {
        return $this->appointment_id;
    }
    
    public function getAmount() {
        return $this->amount;
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function getIssuedDate() {
        return $this->issued_date;
    }
    
    public function getDueDate() {
        return $this->due_date;
    }
    
    public function getPaidDate() {
        return $this->paid_date;
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
    
    public function setAppointmentId($appointment_id) {
        $this->appointment_id = $appointment_id;
    }
    
    public function setAmount($amount) {
        $this->amount = $amount;
    }
    
    public function setStatus($status) {
        $this->status = $status;
    }
    
    public function setIssuedDate($issued_date) {
        $this->issued_date = $issued_date;
    }
    
    public function setDueDate($due_date) {
        $this->due_date = $due_date;
    }
    
    public function setPaidDate($paid_date) {
        $this->paid_date = $paid_date;
    }
    
    /**
     * Convert invoice object to array
     * @return array
     */
    public function toArray() {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'appointment_id' => $this->appointment_id,
            'amount' => $this->amount,
            'status' => $this->status,
            'issued_date' => $this->issued_date,
            'due_date' => $this->due_date,
            'paid_date' => $this->paid_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
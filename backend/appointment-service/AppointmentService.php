<?php

namespace AppointmentService;

use AppointmentService\Models\Appointment;

class AppointmentService {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Book a new appointment
     * @param array $appointmentData
     * @return array
     */
    public function bookAppointment($appointmentData) {
        // Validate input data
        if (!$this->validateAppointmentData($appointmentData)) {
            return ['success' => false, 'message' => 'Invalid appointment data'];
        }
        
        // Check if the time slot is available
        if (!$this->isSlotAvailable($appointmentData['doctor_id'], $appointmentData['date'], $appointmentData['time_slot'])) {
            return ['success' => false, 'message' => 'Time slot is not available'];
        }
        
        // Create appointment record
        $query = "INSERT INTO appointments (patient_id, doctor_id, date, time_slot, status) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([
            $appointmentData['patient_id'],
            $appointmentData['doctor_id'],
            $appointmentData['date'],
            $appointmentData['time_slot'],
            'confirmed'
        ]);
        
        if ($result) {
            $appointmentId = $this->db->lastInsertId();
            return ['success' => true, 'appointment_id' => $appointmentId];
        }
        
        return ['success' => false, 'message' => 'Failed to book appointment'];
    }
    
    /**
     * Get available time slots for a doctor on a specific date
     * @param int $doctorId
     * @param string $date
     * @return array
     */
    public function getAvailableSlots($doctorId, $date) {
        // Get all pregenerated slots for the doctor
        $query = "SELECT time_slot FROM availability WHERE doctor_id = ? AND date = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$doctorId, $date]);
        $allSlots = $stmt->fetchAll();
        
        // Get booked appointments for the doctor on that date
        $query = "SELECT time_slot FROM appointments WHERE doctor_id = ? AND date = ? AND status != 'cancelled'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$doctorId, $date]);
        $bookedSlots = $stmt->fetchAll();
        
        // Filter out booked slots from all slots
        $bookedSlotTimes = array_column($bookedSlots, 'time_slot');
        $availableSlots = [];
        
        foreach ($allSlots as $slot) {
            if (!in_array($slot['time_slot'], $bookedSlotTimes)) {
                $availableSlots[] = $slot['time_slot'];
            }
        }
        
        return ['success' => true, 'available_slots' => $availableSlots];
    }
    
    /**
     * Get appointments for a patient
     * @param int $patientId
     * @return array
     */
    public function getPatientAppointments($patientId) {
        $query = "SELECT * FROM appointments WHERE patient_id = ? ORDER BY date, time_slot";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$patientId]);
        $appointments = $stmt->fetchAll();
        
        return ['success' => true, 'appointments' => $appointments];
    }
    
    /**
     * Get appointments for a doctor
     * @param int $doctorId
     * @return array
     */
    public function getDoctorAppointments($doctorId) {
        $query = "SELECT * FROM appointments WHERE doctor_id = ? ORDER BY date, time_slot";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$doctorId]);
        $appointments = $stmt->fetchAll();
        
        return ['success' => true, 'appointments' => $appointments];
    }
    
    /**
     * Update appointment status
     * @param int $appointmentId
     * @param string $status
     * @return array
     */
    public function updateAppointmentStatus($appointmentId, $status) {
        $validStatuses = ['pending', 'confirmed', 'cancelled', 'rescheduled', 'completed'];
        
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        $query = "UPDATE appointments SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute([$status, $appointmentId]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Appointment status updated'];
        }
        
        return ['success' => false, 'message' => 'Failed to update appointment status'];
    }
    
    /**
     * Validate appointment data
     * @param array $appointmentData
     * @return bool
     */
    private function validateAppointmentData($appointmentData) {
        // Check required fields
        $requiredFields = ['patient_id', 'doctor_id', 'date', 'time_slot'];
        foreach ($requiredFields as $field) {
            if (!isset($appointmentData[$field]) || empty($appointmentData[$field])) {
                return false;
            }
        }
        
        // Validate date format
        $date = DateTime::createFromFormat('Y-m-d', $appointmentData['date']);
        if (!$date || $date->format('Y-m-d') !== $appointmentData['date']) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if a time slot is available
     * @param int $doctorId
     * @param string $date
     * @param string $timeSlot
     * @return bool
     */
    private function isSlotAvailable($doctorId, $date, $timeSlot) {
        $query = "SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ? AND date = ? AND time_slot = ? AND status != 'cancelled'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$doctorId, $date, $timeSlot]);
        $result = $stmt->fetch();
        
        return $result['count'] == 0;
    }
}
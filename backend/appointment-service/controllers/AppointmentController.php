<?php

namespace AppointmentService\Controllers;

use AppointmentService\AppointmentService;
use UserService\Middleware\AuthMiddleware;

class AppointmentController {
    private $appointmentService;
    
    public function __construct(AppointmentService $appointmentService) {
        $this->appointmentService = $appointmentService;
    }
    
    /**
     * Handle appointment booking request
     * @param array $request
     * @return array
     */
    public function bookAppointment($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $appointmentData = [
            'patient_id' => $authResult['data']['user']['id'],
            'doctor_id' => $request['body']['doctor_id'] ?? '',
            'date' => $request['body']['date'] ?? '',
            'time_slot' => $request['body']['time_slot'] ?? ''
        ];
        
        $result = $this->appointmentService->bookAppointment($appointmentData);
        
        if ($result['success']) {
            return [
                'status' => 201,
                'data' => [
                    'message' => 'Appointment booked successfully',
                    'appointment_id' => $result['appointment_id']
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
     * Get available time slots for a doctor
     * @param array $request
     * @return array
     */
    public function getAvailableSlots($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $doctorId = $request['query']['doctor_id'] ?? '';
        $date = $request['query']['date'] ?? '';
        
        if (empty($doctorId) || empty($date)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Doctor ID and date are required'
                ]
            ];
        }
        
        $result = $this->appointmentService->getAvailableSlots($doctorId, $date);
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'available_slots' => $result['available_slots']
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
     * Get appointments for the authenticated user
     * @param array $request
     * @return array
     */
    public function getUserAppointments($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userId = $authResult['data']['user']['id'];
        $userRole = $authResult['data']['user']['role'];
        
        if ($userRole === 'patient') {
            $result = $this->appointmentService->getPatientAppointments($userId);
        } else if ($userRole === 'doctor') {
            $result = $this->appointmentService->getDoctorAppointments($userId);
        } else {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        if ($result['success']) {
            return [
                'status' => 200,
                'data' => [
                    'appointments' => $result['appointments']
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
     * Update appointment status
     * @param array $request
     * @return array
     */
    public function updateAppointmentStatus($request) {
        // Require authentication
        $authResult = AuthMiddleware::requireAuth($request);
        if ($authResult['status'] !== 200) {
            return $authResult;
        }
        
        $userRole = $authResult['data']['user']['role'];
        
        // Only doctors and receptionists can update appointment status
        if ($userRole !== 'doctor' && $userRole !== 'receptionist') {
            return [
                'status' => 403,
                'data' => [
                    'message' => 'Insufficient permissions'
                ]
            ];
        }
        
        $appointmentId = $request['params']['id'] ?? '';
        $status = $request['body']['status'] ?? '';
        
        if (empty($appointmentId) || empty($status)) {
            return [
                'status' => 400,
                'data' => [
                    'message' => 'Appointment ID and status are required'
                ]
            ];
        }
        
        $result = $this->appointmentService->updateAppointmentStatus($appointmentId, $status);
        
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
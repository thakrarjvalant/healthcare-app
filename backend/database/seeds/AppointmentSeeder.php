<?php

use Database\DatabaseConnection;

/**
 * 📅 Seed Appointments with comprehensive test data
 * This seeder creates realistic appointment scenarios for all user types
 */
class AppointmentSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // Get user IDs for seeding
        $users = $this->getUsers();
        
        if (empty($users['patients']) || empty($users['doctors'])) {
            echo "⚠️ Warning: No patients or doctors found. Please run UserSeeder first.\n";
            return;
        }

        // 📋 Comprehensive appointment scenarios
        $appointments = [
            // Current week appointments
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d'),
                'time_slot' => '09:00:00',
                'status' => 'confirmed'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+1 day')),
                'time_slot' => '10:30:00',
                'status' => 'pending'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+2 days')),
                'time_slot' => '14:00:00',
                'status' => 'confirmed'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+3 days')),
                'time_slot' => '11:15:00',
                'status' => 'rescheduled'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+4 days')),
                'time_slot' => '15:30:00',
                'status' => 'cancelled'
            ],
            
            // Historical appointments (last month)
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('-7 days')),
                'time_slot' => '09:30:00',
                'status' => 'completed'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('-14 days')),
                'time_slot' => '13:00:00',
                'status' => 'completed'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('-21 days')),
                'time_slot' => '16:00:00',
                'status' => 'completed'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('-28 days')),
                'time_slot' => '10:00:00',
                'status' => 'completed'
            ],
            
            // Emergency appointments
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d'),
                'time_slot' => '08:00:00',
                'status' => 'confirmed'
            ],
            
            // Follow-up appointments
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+7 days')),
                'time_slot' => '14:30:00',
                'status' => 'pending'
            ],
            [
                'patient_id' => $users['patients'][0],
                'doctor_id' => $users['doctors'][0],
                'date' => date('Y-m-d', strtotime('+14 days')),
                'time_slot' => '11:00:00',
                'status' => 'pending'
            ]
        ];

        foreach ($appointments as $appointment) {
            try {
                $stmt = $this->db->prepare("
                    INSERT INTO appointments (patient_id, doctor_id, date, time_slot, status) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $appointment['patient_id'],
                    $appointment['doctor_id'],
                    $appointment['date'],
                    $appointment['time_slot'],
                    $appointment['status']
                ]);
            } catch (Exception $e) {
                echo "⚠️ Warning: Could not create appointment: " . $e->getMessage() . "\n";
            }
        }

        echo "✅ Appointment data seeded successfully!\n";
        echo "📊 Created " . count($appointments) . " appointments for testing.\n";
    }

    private function getUsers()
    {
        $users = ['patients' => [], 'doctors' => []];
        
        // Get patient IDs
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'patient' LIMIT 5");
        $stmt->execute();
        $users['patients'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Get doctor IDs
        $stmt = $this->db->prepare("SELECT id FROM users WHERE role = 'doctor' LIMIT 3");
        $stmt->execute();
        $users['doctors'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        return $users;
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM appointments WHERE id > 0");
        echo "🗑️ Appointment data unseeded successfully!\n";
    }
}
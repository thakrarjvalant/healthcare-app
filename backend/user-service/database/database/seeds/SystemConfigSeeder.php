<?php

use Database\DatabaseConnection;

/**
 * ⚙️ Seed System Configuration data including settings, schedules, and operational data
 * This seeder creates comprehensive system configuration for enhanced dashboard features
 */
class SystemConfigSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        $users = $this->getUsers();
        
        // ⚙️ Seed System Settings
        $this->seedSystemSettings();
        
        // 👨‍⚕️ Seed Doctor Schedules
        $this->seedDoctorSchedules($users);
        
        // 📋 Seed Document Categories
        $this->seedDocumentCategories();
        
        // 🔔 Seed Notifications
        $this->seedNotifications($users);
        
        // 👥 Seed Check-in Queue
        $this->seedCheckInQueue($users);
        
        // 📊 Seed Audit Logs
        $this->seedAuditLogs($users);

        echo "✅ System configuration data seeded successfully!\n";
    }

    private function seedSystemSettings()
    {
        $settings = [
            // Security Settings
            ['setting_key' => 'session_timeout', 'setting_value' => '1800', 'setting_type' => 'integer', 'category' => 'security', 'description' => 'Session timeout in seconds (30 minutes)'],
            ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'setting_type' => 'integer', 'category' => 'security', 'description' => 'Maximum login attempts before lockout'],
            ['setting_key' => 'password_expiry_days', 'setting_value' => '90', 'setting_type' => 'integer', 'category' => 'security', 'description' => 'Password expiration period in days'],
            ['setting_key' => 'two_factor_enabled', 'setting_value' => 'false', 'setting_type' => 'boolean', 'category' => 'security', 'description' => 'Enable two-factor authentication'],
            ['setting_key' => 'audit_logging_enabled', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'security', 'description' => 'Enable comprehensive audit logging'],
            
            // System Configuration
            ['setting_key' => 'system_name', 'setting_value' => 'Healthcare Management System', 'setting_type' => 'string', 'category' => 'system', 'description' => 'System display name'],
            ['setting_key' => 'system_version', 'setting_value' => '2.0.0', 'setting_type' => 'string', 'category' => 'system', 'description' => 'Current system version'],
            ['setting_key' => 'maintenance_mode', 'setting_value' => 'false', 'setting_type' => 'boolean', 'category' => 'system', 'description' => 'Enable maintenance mode'],
            ['setting_key' => 'backup_frequency', 'setting_value' => 'daily', 'setting_type' => 'string', 'category' => 'system', 'description' => 'Database backup frequency'],
            ['setting_key' => 'max_file_upload_size', 'setting_value' => '10485760', 'setting_type' => 'integer', 'category' => 'system', 'description' => 'Maximum file upload size in bytes (10MB)'],
            
            // Appointment Settings
            ['setting_key' => 'appointment_slot_duration', 'setting_value' => '30', 'setting_type' => 'integer', 'category' => 'appointments', 'description' => 'Default appointment duration in minutes'],
            ['setting_key' => 'advance_booking_days', 'setting_value' => '90', 'setting_type' => 'integer', 'category' => 'appointments', 'description' => 'Maximum days in advance for booking'],
            ['setting_key' => 'cancellation_notice_hours', 'setting_value' => '24', 'setting_type' => 'integer', 'category' => 'appointments', 'description' => 'Minimum hours notice for cancellation'],
            ['setting_key' => 'auto_confirm_appointments', 'setting_value' => 'false', 'setting_type' => 'boolean', 'category' => 'appointments', 'description' => 'Automatically confirm new appointments'],
            
            // Notification Settings
            ['setting_key' => 'email_notifications', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'notifications', 'description' => 'Enable email notifications'],
            ['setting_key' => 'sms_notifications', 'setting_value' => 'false', 'setting_type' => 'boolean', 'category' => 'notifications', 'description' => 'Enable SMS notifications'],
            ['setting_key' => 'appointment_reminder_hours', 'setting_value' => '24', 'setting_type' => 'integer', 'category' => 'notifications', 'description' => 'Hours before appointment to send reminder'],
            ['setting_key' => 'notification_retention_days', 'setting_value' => '30', 'setting_type' => 'integer', 'category' => 'notifications', 'description' => 'Days to retain notifications'],
            
            // Billing Settings
            ['setting_key' => 'default_currency', 'setting_value' => 'USD', 'setting_type' => 'string', 'category' => 'billing', 'description' => 'Default currency for billing'],
            ['setting_key' => 'invoice_due_days', 'setting_value' => '30', 'setting_type' => 'integer', 'category' => 'billing', 'description' => 'Default invoice due period in days'],
            ['setting_key' => 'late_fee_percentage', 'setting_value' => '1.5', 'setting_type' => 'string', 'category' => 'billing', 'description' => 'Late fee percentage per month'],
            ['setting_key' => 'accept_online_payments', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'billing', 'description' => 'Accept online payments'],
            
            // Clinical Settings
            ['setting_key' => 'require_diagnosis_codes', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'clinical', 'description' => 'Require ICD-10 diagnosis codes'],
            ['setting_key' => 'prescription_verification', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'clinical', 'description' => 'Require prescription verification'],
            ['setting_key' => 'allergy_alert_enabled', 'setting_value' => 'true', 'setting_type' => 'boolean', 'category' => 'clinical', 'description' => 'Enable allergy alerts']
        ];

        foreach ($settings as $setting) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO system_settings (setting_key, setting_value, setting_type, category, description) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $setting['setting_key'],
                $setting['setting_value'],
                $setting['setting_type'],
                $setting['category'],
                $setting['description']
            ]);
        }
    }

    private function seedDoctorSchedules($users)
    {
        if (empty($users['doctors'])) {
            echo "⚠️ Warning: No doctors found for schedule seeding.\n";
            return;
        }

        $doctorId = $users['doctors'][0];
        
        $schedules = [
            ['doctor_id' => $doctorId, 'day_of_week' => 'Monday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_start_time' => '12:00:00', 'break_end_time' => '13:00:00', 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Tuesday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_start_time' => '12:00:00', 'break_end_time' => '13:00:00', 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Wednesday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_start_time' => '12:00:00', 'break_end_time' => '13:00:00', 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Thursday', 'start_time' => '08:00:00', 'end_time' => '17:00:00', 'break_start_time' => '12:00:00', 'break_end_time' => '13:00:00', 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Friday', 'start_time' => '08:00:00', 'end_time' => '16:00:00', 'break_start_time' => '12:00:00', 'break_end_time' => '13:00:00', 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Saturday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start_time' => null, 'break_end_time' => null, 'is_available' => true],
            ['doctor_id' => $doctorId, 'day_of_week' => 'Sunday', 'start_time' => '09:00:00', 'end_time' => '13:00:00', 'break_start_time' => null, 'break_end_time' => null, 'is_available' => false]
        ];

        foreach ($schedules as $schedule) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, break_start_time, break_end_time, is_available) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $schedule['doctor_id'],
                $schedule['day_of_week'],
                $schedule['start_time'],
                $schedule['end_time'],
                $schedule['break_start_time'],
                $schedule['break_end_time'],
                $schedule['is_available']
            ]);
        }
    }

    private function seedDocumentCategories()
    {
        $categories = [
            ['name' => 'Medical Records', 'description' => 'Patient medical records and clinical documents', 'icon' => 'medical-file', 'color' => '#007bff'],
            ['name' => 'Lab Results', 'description' => 'Laboratory test results and reports', 'icon' => 'test-tube', 'color' => '#28a745'],
            ['name' => 'Imaging', 'description' => 'X-rays, MRIs, CT scans and other imaging', 'icon' => 'scan', 'color' => '#17a2b8'],
            ['name' => 'Insurance', 'description' => 'Insurance cards and authorization documents', 'icon' => 'shield', 'color' => '#ffc107'],
            ['name' => 'Prescriptions', 'description' => 'Prescription documents and medication lists', 'icon' => 'pill', 'color' => '#fd7e14'],
            ['name' => 'Consent Forms', 'description' => 'Patient consent and authorization forms', 'icon' => 'clipboard-check', 'color' => '#6f42c1'],
            ['name' => 'Referrals', 'description' => 'Referral letters and specialist recommendations', 'icon' => 'share', 'color' => '#20c997'],
            ['name' => 'Personal Documents', 'description' => 'Personal identification and contact information', 'icon' => 'user-file', 'color' => '#dc3545']
        ];

        foreach ($categories as $category) {
            $stmt = $this->db->prepare("
                INSERT IGNORE INTO document_categories (name, description, icon, color) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $category['name'],
                $category['description'],
                $category['icon'],
                $category['color']
            ]);
        }
    }

    private function seedNotifications($users)
    {
        if (empty($users['patients']) || empty($users['doctors'])) {
            echo "⚠️ Warning: No users found for notification seeding.\n";
            return;
        }

        $notifications = [
            // Patient notifications
            [
                'user_id' => $users['patients'][0],
                'type' => 'appointment',
                'title' => 'Appointment Reminder',
                'message' => 'You have an appointment scheduled for tomorrow at 9:00 AM with Dr. Jane Smith.',
                'is_read' => 0
            ],
            [
                'user_id' => $users['patients'][0],
                'type' => 'lab_result',
                'title' => 'Lab Results Available',
                'message' => 'Your recent lab results are now available in your patient portal.',
                'is_read' => 0
            ],
            [
                'user_id' => $users['patients'][0],
                'type' => 'prescription',
                'title' => 'Prescription Refill Due',
                'message' => 'Your prescription for Lisinopril is due for refill. Contact your pharmacy.',
                'is_read' => 1
            ],
            [
                'user_id' => $users['patients'][0],
                'type' => 'billing',
                'title' => 'Payment Received',
                'message' => 'Thank you! Your payment of $150.00 has been processed successfully.',
                'is_read' => 1
            ],
            
            // Doctor notifications
            [
                'user_id' => $users['doctors'][0],
                'type' => 'appointment',
                'title' => 'New Appointment Request',
                'message' => 'John Doe has requested an appointment for next week.',
                'is_read' => 0
            ],
            [
                'user_id' => $users['doctors'][0],
                'type' => 'lab_result',
                'title' => 'Critical Lab Result',
                'message' => 'Patient John Doe has a critical lab result requiring immediate attention.',
                'is_read' => 0
            ],
            [
                'user_id' => $users['doctors'][0],
                'type' => 'system',
                'title' => 'System Maintenance',
                'message' => 'Scheduled system maintenance will occur tonight from 11 PM to 1 AM.',
                'is_read' => 1
            ],
            
            // System-wide notifications
            [
                'user_id' => $users['receptionists'][0] ?? $users['doctors'][0],
                'type' => 'system',
                'title' => 'System Update',
                'message' => 'The system has been updated to version 2.0.0 with new features.',
                'is_read' => 0
            ]
        ];

        foreach ($notifications as $notification) {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (user_id, type, title, message, is_read) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $notification['user_id'],
                $notification['type'],
                $notification['title'],
                $notification['message'],
                $notification['is_read']
            ]);
        }
    }

    private function seedCheckInQueue($users)
    {
        if (empty($users['patients'])) {
            echo "⚠️ Warning: No patients found for check-in queue seeding.\n";
            return;
        }

        // Get today's appointments
        $appointments = $this->getTodayAppointments();
        $receptionistId = $users['receptionists'][0] ?? null;

        $queueEntries = [
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => !empty($appointments) ? $appointments[0] : null,
                'status' => 'waiting',
                'priority' => 'medium',
                'estimated_wait_time' => 15,
                'notes' => 'Patient checked in on time',
                'processed_by' => $receptionistId
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => !empty($appointments) && count($appointments) > 1 ? $appointments[1] : null,
                'status' => 'in_progress',
                'priority' => 'high',
                'estimated_wait_time' => 0,
                'notes' => 'Priority patient - moved to front of queue',
                'called_time' => date('Y-m-d H:i:s', strtotime('-10 minutes')),
                'processed_by' => $receptionistId
            ],
            [
                'patient_id' => $users['patients'][0],
                'appointment_id' => null,
                'status' => 'completed',
                'priority' => 'medium',
                'estimated_wait_time' => 0,
                'notes' => 'Walk-in patient - consultation completed',
                'called_time' => date('Y-m-d H:i:s', strtotime('-45 minutes')),
                'completed_time' => date('Y-m-d H:i:s', strtotime('-20 minutes')),
                'processed_by' => $receptionistId
            ]
        ];

        foreach ($queueEntries as $entry) {
            $stmt = $this->db->prepare("
                INSERT INTO check_in_queue (patient_id, appointment_id, status, priority, estimated_wait_time, notes, called_time, completed_time, processed_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $entry['patient_id'],
                $entry['appointment_id'],
                $entry['status'],
                $entry['priority'],
                $entry['estimated_wait_time'],
                $entry['notes'],
                $entry['called_time'] ?? null,
                $entry['completed_time'] ?? null,
                $entry['processed_by']
            ]);
        }
    }

    private function seedAuditLogs($users)
    {
        $sessionId = 'session_' . time() . '_demo';
        
        $auditLogs = [
            [
                'user_id' => $users['admin'][0] ?? null,
                'action' => 'user.login',
                'table_name' => 'users',
                'record_id' => $users['admin'][0] ?? null,
                'old_values' => null,
                'new_values' => json_encode(['login_time' => date('Y-m-d H:i:s')]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => $sessionId
            ],
            [
                'user_id' => $users['doctors'][0] ?? null,
                'action' => 'medical_record.create',
                'table_name' => 'medical_records',
                'record_id' => 1,
                'old_values' => null,
                'new_values' => json_encode(['patient_id' => $users['patients'][0] ?? null, 'diagnosis' => 'Hypertension']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => $sessionId
            ],
            [
                'user_id' => $users['receptionists'][0] ?? null,
                'action' => 'appointment.schedule',
                'table_name' => 'appointments',
                'record_id' => 1,
                'old_values' => null,
                'new_values' => json_encode(['patient_id' => $users['patients'][0] ?? null, 'date' => date('Y-m-d')]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => $sessionId
            ],
            [
                'user_id' => $users['patients'][0] ?? null,
                'action' => 'document.upload',
                'table_name' => 'documents',
                'record_id' => 1,
                'old_values' => null,
                'new_values' => json_encode(['filename' => 'lab_results.pdf', 'category' => 'Lab Results']),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'session_id' => $sessionId
            ]
        ];

        foreach ($auditLogs as $log) {
            if ($log['user_id']) { // Only insert if user exists
                $stmt = $this->db->prepare("
                    INSERT INTO audit_logs (user_id, action, table_name, record_id, old_values, new_values, ip_address, user_agent, session_id) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $log['user_id'],
                    $log['action'],
                    $log['table_name'],
                    $log['record_id'],
                    $log['old_values'],
                    $log['new_values'],
                    $log['ip_address'],
                    $log['user_agent'],
                    $log['session_id']
                ]);
            }
        }
    }

    private function getUsers()
    {
        $users = ['patients' => [], 'doctors' => [], 'receptionists' => [], 'admin' => []];
        
        foreach (['patient', 'doctor', 'receptionist', 'admin'] as $role) {
            $stmt = $this->db->prepare("SELECT id FROM users WHERE role = ? LIMIT 5");
            $stmt->execute([$role]);
            $users[$role . 's'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
        
        return $users;
    }

    private function getTodayAppointments()
    {
        $stmt = $this->db->prepare("SELECT id FROM appointments WHERE date = ? LIMIT 5");
        $stmt->execute([date('Y-m-d')]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM audit_logs");
        $this->db->exec("DELETE FROM check_in_queue");
        $this->db->exec("DELETE FROM notifications WHERE id > 0");
        $this->db->exec("DELETE FROM document_categories");
        $this->db->exec("DELETE FROM doctor_schedules");
        $this->db->exec("DELETE FROM system_settings");
        echo "🗑️ System configuration data unseeded successfully!\n";
    }
}
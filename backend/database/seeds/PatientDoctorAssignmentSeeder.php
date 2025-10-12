<?php

use Database\DatabaseConnection;

/**
 * Seed the patient_doctor_assignments table with initial data
 */
class PatientDoctorAssignmentSeeder
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function seed()
    {
        // Get users by role
        $stmt = $this->db->prepare("SELECT id, name, role FROM users WHERE role IN ('patient', 'doctor', 'medical_coordinator')");
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $patients = [];
        $doctors = [];
        $medicalCoordinators = [];

        foreach ($users as $user) {
            switch ($user['role']) {
                case 'patient':
                    $patients[] = $user;
                    break;
                case 'doctor':
                    $doctors[] = $user;
                    break;
                case 'medical_coordinator':
                    $medicalCoordinators[] = $user;
                    break;
            }
        }

        // Get the first medical coordinator as the assigner
        $assignedBy = !empty($medicalCoordinators) ? $medicalCoordinators[0]['id'] : 1;

        // Create assignments for patients to doctors
        $assignments = [];
        
        if (!empty($patients) && !empty($doctors)) {
            // Assign each patient to the first doctor
            foreach ($patients as $patient) {
                $assignments[] = [
                    'patient_id' => $patient['id'],
                    'doctor_id' => $doctors[0]['id'],
                    'assigned_by' => $assignedBy,
                    'notes' => 'Initial patient assignment by Medical Coordinator'
                ];
            }
        }

        foreach ($assignments as $assignment) {
            try {
                $stmt = $this->db->prepare("INSERT IGNORE INTO patient_doctor_assignments (patient_id, doctor_id, assigned_by, notes) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $assignment['patient_id'],
                    $assignment['doctor_id'],
                    $assignment['assigned_by'],
                    $assignment['notes']
                ]);
                
                // Update the patient's assigned_doctor field
                $updateStmt = $this->db->prepare("UPDATE users SET assigned_doctor = ? WHERE id = ?");
                $updateStmt->execute([$assignment['doctor_id'], $assignment['patient_id']]);
            } catch (Exception $e) {
                echo "⚠️ Warning: Could not create assignment: " . $e->getMessage() . "\n";
            }
        }

        echo "✅ Patient-Doctor assignment data seeded successfully!\n";
    }

    public function unseed()
    {
        $this->db->exec("DELETE FROM patient_doctor_assignments");
        $this->db->exec("UPDATE users SET assigned_doctor = NULL WHERE assigned_doctor IS NOT NULL");
        echo "🗑️ Patient-Doctor assignment data unseeded successfully!\n";
    }
}
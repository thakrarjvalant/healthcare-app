<?php

use Database\DatabaseConnection;

/**
 * Migration to create the patient_doctor_assignments table
 * This table will track which doctors are assigned to which patients by the Medical Coordinator
 */
class CreatePatientDoctorAssignmentTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS patient_doctor_assignments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            assigned_by INT NOT NULL,
            assignment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            notes TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
            UNIQUE KEY unique_patient_doctor (patient_id, doctor_id, is_active),
            INDEX idx_patient_id (patient_id),
            INDEX idx_doctor_id (doctor_id),
            INDEX idx_assigned_by (assigned_by)
        )";

        $this->db->exec($sql);
        
        // Add a column to track Medical Coordinator assignments in the users table
        try {
            $this->db->exec("ALTER TABLE users ADD COLUMN assigned_doctor INT NULL DEFAULT NULL");
            $this->db->exec("ALTER TABLE users ADD FOREIGN KEY (assigned_doctor) REFERENCES users(id) ON DELETE SET NULL");
        } catch (Exception $e) {
            // Column might already exist, ignore error
        }
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS patient_doctor_assignments";
        $this->db->exec($sql);
        
        try {
            $this->db->exec("ALTER TABLE users DROP FOREIGN KEY users_ibfk_1");
            $this->db->exec("ALTER TABLE users DROP COLUMN assigned_doctor");
        } catch (Exception $e) {
            // Column might not exist, ignore error
        }
    }
}
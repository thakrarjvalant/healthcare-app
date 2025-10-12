<?php

use Database\DatabaseConnection;

/**
 * Migration to create additional tables for enhanced dashboard functionalities
 */
class CreateEnhancedTablesV1
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Roles and Permissions table
        $sql = "CREATE TABLE IF NOT EXISTS roles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL UNIQUE,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            module VARCHAR(50) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $this->db->exec($sql);

        $sql = "CREATE TABLE IF NOT EXISTS role_permissions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            role_id INT NOT NULL,
            permission_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
            UNIQUE KEY unique_role_permission (role_id, permission_id)
        )";
        $this->db->exec($sql);

        // Patient Health Records
        $sql = "CREATE TABLE IF NOT EXISTS patient_health_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            record_type ENUM('vital_signs', 'lab_results', 'imaging', 'allergies', 'medications', 'immunizations') NOT NULL,
            data JSON NOT NULL,
            recorded_date DATE NOT NULL,
            recorded_by INT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // Prescriptions
        $sql = "CREATE TABLE IF NOT EXISTS prescriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            medical_record_id INT,
            medication_name VARCHAR(255) NOT NULL,
            dosage VARCHAR(100) NOT NULL,
            frequency VARCHAR(100) NOT NULL,
            duration_days INT NOT NULL,
            instructions TEXT,
            status ENUM('active', 'completed', 'cancelled', 'expired') NOT NULL DEFAULT 'active',
            prescribed_date DATE NOT NULL,
            start_date DATE NOT NULL,
            end_date DATE NOT NULL,
            refills_remaining INT DEFAULT 0,
            total_refills INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (medical_record_id) REFERENCES medical_records(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);

        // Clinical Notes Templates
        $sql = "CREATE TABLE IF NOT EXISTS clinical_note_templates (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            template_type ENUM('general', 'followup', 'emergency', 'consultation', 'procedure') NOT NULL,
            template_content TEXT NOT NULL,
            created_by INT NOT NULL,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
        )";
        $this->db->exec($sql);

        // Clinical Notes
        $sql = "CREATE TABLE IF NOT EXISTS clinical_notes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            appointment_id INT,
            template_id INT,
            note_type ENUM('general', 'followup', 'emergency', 'consultation', 'procedure') NOT NULL,
            chief_complaint TEXT,
            history_present_illness TEXT,
            physical_examination TEXT,
            assessment TEXT,
            plan TEXT,
            additional_notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (appointment_id) REFERENCES appointments(id) ON DELETE SET NULL,
            FOREIGN KEY (template_id) REFERENCES clinical_note_templates(id) ON DELETE SET NULL
        )";
        $this->db->exec($sql);
    }

    public function down()
    {
        $this->db->exec("DROP TABLE IF EXISTS clinical_notes");
        $this->db->exec("DROP TABLE IF EXISTS clinical_note_templates");
        $this->db->exec("DROP TABLE IF EXISTS prescriptions");
        $this->db->exec("DROP TABLE IF EXISTS patient_health_records");
        $this->db->exec("DROP TABLE IF EXISTS role_permissions");
        $this->db->exec("DROP TABLE IF EXISTS permissions");
        $this->db->exec("DROP TABLE IF EXISTS roles");
    }
}
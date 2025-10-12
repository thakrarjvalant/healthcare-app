<?php

use Database\DatabaseConnection;

/**
 * Migration to create clinical notes and templates tables
 */
class CreateClinicalNotesTables
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
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
    }
}
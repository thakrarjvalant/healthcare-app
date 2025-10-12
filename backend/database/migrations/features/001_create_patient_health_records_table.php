<?php

use Database\DatabaseConnection;

/**
 * Migration to create the patient_health_records table
 */
class CreatePatientHealthRecordsTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
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
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS patient_health_records";
        $this->db->exec($sql);
    }
}
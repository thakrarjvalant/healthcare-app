<?php

use Database\DatabaseConnection;

/**
 * Migration to create the medical_records table
 */
class CreateMedicalRecordsTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS medical_records (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            diagnosis TEXT,
            treatment TEXT,
            medications TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
        )";

        $this->db->exec($sql);
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS medical_records";
        $this->db->exec($sql);
    }
}
<?php

use Database\DatabaseConnection;

/**
 * Migration to create the prescriptions table
 */
class CreatePrescriptionsTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
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
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS prescriptions";
        $this->db->exec($sql);
    }
}
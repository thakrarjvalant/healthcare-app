<?php

use Database\DatabaseConnection;

/**
 * Migration to create the appointments table
 */
class CreateAppointmentsTable
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        $sql = "CREATE TABLE IF NOT EXISTS appointments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            patient_id INT NOT NULL,
            doctor_id INT NOT NULL,
            date DATE NOT NULL,
            time_slot TIME NOT NULL,
            status ENUM('pending', 'confirmed', 'cancelled', 'rescheduled', 'completed') NOT NULL DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (patient_id) REFERENCES users(id),
            FOREIGN KEY (doctor_id) REFERENCES users(id)
        )";

        $this->db->exec($sql);
    }

    public function down()
    {
        $sql = "DROP TABLE IF EXISTS appointments";
        $this->db->exec($sql);
    }
}
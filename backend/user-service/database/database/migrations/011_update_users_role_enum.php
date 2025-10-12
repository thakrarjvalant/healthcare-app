<?php

use Database\DatabaseConnection;

/**
 * Migration to update the users table role ENUM to include medical_coordinator
 */
class UpdateUsersRoleEnum
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function up()
    {
        // Update the role ENUM to include medical_coordinator
        $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'receptionist', 'admin', 'medical_coordinator') NOT NULL DEFAULT 'patient'";
        $this->db->exec($sql);

        echo "✅ Users table role ENUM updated successfully!\n";
    }

    public function down()
    {
        // Revert the role ENUM to the original values
        $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('patient', 'doctor', 'receptionist', 'admin') NOT NULL DEFAULT 'patient'";
        $this->db->exec($sql);

        echo "🗑️ Users table role ENUM reverted successfully!\n";
    }
}
